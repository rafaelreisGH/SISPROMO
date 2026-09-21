<?php

namespace App\DAO;

use PDO;
use Conexao;
use Exception;

require_once __DIR__ . '/../../ConexaoDB/conexao.php';

/**
 * DAO responsável pela gestão das Pastas Promocionais e seus Documentos (Art. 78 do Decreto 2.268/14).
 */
class PastaPromocionalDAO
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = Conexao::getConexao();
    }

    /**
     * Dicionário de tradução dos códigos técnicos de documentos para nomes formais.
     */
    public static function formatarNomeDocumento(string $codigoDoc): string
    {
        $nomes = [
            'cert_1JE' => 'Certidão Negativa 1º Grau — Justiça Estadual',
            'cert_2JE' => 'Certidão Negativa 2º Grau — Justiça Estadual',
            'cert_1JF' => 'Certidão Negativa 1º Grau — Justiça Federal',
            'cert_2JF' => 'Certidão Negativa 2º Grau — Justiça Federal',
            'cert_tse' => 'Certidão de Quitação Eleitoral — TSE',
            'fad'      => 'Ficha de Avaliação de Desempenho (FAD)',
            'rta'      => 'Relatório de Tempo Arregimentado (RTA)',
            'ais'      => 'Ata de Inspeção de Saúde (AIS / JIS)',
            'fp'       => 'Ficha Profissional (FP) — Tenentes-Coronéis'
        ];

        return $nomes[$codigoDoc] ?? strtoupper($codigoDoc);
    }

    /**
     * Busca a pasta promocional de um militar vinculada a um processo promocional específico.
     */
    public function buscarPastaPorMilitarEProcesso(int $militarId, int $processoId): ?array
    {
        try {
            // 1. Tenta buscar pelo ID do processo promocional
            $sql = "SELECT pp.*, p.ano AS ano_ref, p.semestre AS semestre_ref 
                    FROM pasta_promocional pp
                    INNER JOIN processo_promocional p ON pp.processo_promocional_id = p.id
                    WHERE pp.militar_id = :mid AND pp.processo_promocional_id = :pid
                    LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':mid' => $militarId, ':pid' => $processoId]);
            $pasta = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($pasta) {
                return $pasta;
            }

            // 2. Fallback: busca pelo ano e semestre do processo caso a FK processo_promocional_id não esteja preenchida
            $sqlProc = "SELECT ano, semestre FROM processo_promocional WHERE id = :pid LIMIT 1";
            $stmtProc = $this->conn->prepare($sqlProc);
            $stmtProc->execute([':pid' => $processoId]);
            $proc = $stmtProc->fetch(PDO::FETCH_ASSOC);

            if ($proc) {
                $sqlFallback = "SELECT * FROM pasta_promocional 
                                WHERE militar_id = :mid 
                                  AND ano_processo_promocional = :ano 
                                  AND semestre_processo_promocional = :semestre 
                                LIMIT 1";
                $stmtFb = $this->conn->prepare($sqlFallback);
                $stmtFb->execute([
                    ':mid'      => $militarId,
                    ':ano'      => $proc['ano'],
                    ':semestre' => $proc['semestre']
                ]);
                $pastaFb = $stmtFb->fetch(PDO::FETCH_ASSOC);
                return $pastaFb ?: null;
            }

            return null;
        } catch (\PDOException $e) {
            error_log("Erro em PastaPromocionalDAO::buscarPastaPorMilitarEProcesso: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Retorna a lista de documentos cadastrados para uma determinada pasta promocional.
     */
    public function buscarDocumentosDaPasta(int $pastaId): array
    {
        try {
            $sql = "SELECT dp.*, ds.descricao_status 
                    FROM documento_promocao dp
                    INNER JOIN documento_status ds ON dp.doc_status_id = ds.id_doc_status
                    WHERE dp.pasta_promocional_id = :pasta_id
                    ORDER BY dp.id_doc_promo ASC";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':pasta_id' => $pastaId]);
            $documentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($documentos as &$doc) {
                $doc['nome_legivel'] = self::formatarNomeDocumento($doc['doc_promo_nome']);
            }
            unset($doc);

            return $documentos;
        } catch (\PDOException $e) {
            error_log("Erro em PastaPromocionalDAO::buscarDocumentosDaPasta: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Provisiona pastas e documentos-template para todos os candidatos do LQ de um processo.
     * É idempotente: não cria pastas ou documentos que já existam.
     *
     * @param int $processoId ID do processo promocional.
     * @return array ['criadas' => N, 'ja_existiam' => M]
     */
    public function provisionarPastas(int $processoId): array
    {
        $resultados = ['criadas' => 0, 'ja_existiam' => 0];
        
        try {
            // Obtém ano e semestre do processo
            $sqlProc = "SELECT ano, semestre FROM processo_promocional WHERE id = :pid LIMIT 1";
            $stmtProc = $this->conn->prepare($sqlProc);
            $stmtProc->execute([':pid' => $processoId]);
            $proc = $stmtProc->fetch(PDO::FETCH_ASSOC);
            
            if (!$proc) {
                return $resultados;
            }
            
            $ano = $proc['ano'];
            $semestre = $proc['semestre'];
            
            // Busca todos os militares aptos neste processo no LQ snapshot
            $sqlSnap = "SELECT lq.militar_id, m.posto_grad_mil 
                        FROM lq_snapshot lq
                        INNER JOIN militar m ON lq.militar_id = m.id
                        WHERE lq.processo_promocional_id = :pid AND lq.dentro_do_limite = 1";
            $stmtSnap = $this->conn->prepare($sqlSnap);
            $stmtSnap->execute([':pid' => $processoId]);
            $candidatos = $stmtSnap->fetchAll(PDO::FETCH_ASSOC);
            
            $docsBase = ['cert_1JE', 'cert_2JE', 'cert_1JF', 'cert_2JF', 'cert_tse', 'fad', 'rta', 'ais'];
            
            foreach ($candidatos as $cand) {
                $militarId = $cand['militar_id'];
                $postoGrad = trim($cand['posto_grad_mil']);
                
                // Verifica se já existe pasta
                $pastaExistente = $this->buscarPastaPorMilitarEProcesso($militarId, $processoId);
                
                if ($pastaExistente) {
                    $resultados['ja_existiam']++;
                } else {
                    // Cria a pasta
                    $sqlInsertPasta = "INSERT INTO pasta_promocional 
                        (processo_promocional_id, militar_id, ano_processo_promocional, semestre_processo_promocional) 
                        VALUES (:pid, :mid, :ano, :sem)";
                    $stmtInsert = $this->conn->prepare($sqlInsertPasta);
                    $stmtInsert->execute([
                        ':pid' => $processoId,
                        ':mid' => $militarId,
                        ':ano' => $ano,
                        ':sem' => $semestre
                    ]);
                    
                    $novaPastaId = $this->conn->lastInsertId();
                    
                    // Cria os documentos
                    $docsParaCriar = $docsBase;
                    if ($postoGrad === 'TC BM') {
                        $docsParaCriar[] = 'fp';
                    }
                    
                    $sqlInsertDoc = "INSERT INTO documento_promocao 
                        (pasta_promocional_id, doc_promo_nome, doc_status_id, militar_id) 
                        VALUES (:pasta_id, :doc_nome, 2, :mid)"; // 2 = N.E. (Não Encontrado)
                    $stmtDoc = $this->conn->prepare($sqlInsertDoc);
                    
                    foreach ($docsParaCriar as $doc) {
                        $stmtDoc->execute([
                            ':pasta_id' => $novaPastaId,
                            ':doc_nome' => $doc,
                            ':mid' => $militarId
                        ]);
                    }
                    
                    $resultados['criadas']++;
                }
            }
            
            return $resultados;
        } catch (\PDOException $e) {
            error_log("Erro em PastaPromocionalDAO::provisionarPastas: " . $e->getMessage());
            return $resultados;
        }
    }

    /**
     * Limpa pastas promocionais "órfãs" (e seus documentos).
     * Pastas órfãs são aquelas vinculadas ao processo, mas cujo militar_id não está no lq_snapshot (aptos).
     *
     * @param int $processoId ID do processo promocional.
     * @return int Número de pastas removidas
     */
    public function limparPastasOrfas(int $processoId): int
    {
        $pastasRemovidas = 0;
        
        try {
            $sqlProc = "SELECT ano, semestre FROM processo_promocional WHERE id = :pid LIMIT 1";
            $stmtProc = $this->conn->prepare($sqlProc);
            $stmtProc->execute([':pid' => $processoId]);
            $proc = $stmtProc->fetch(PDO::FETCH_ASSOC);
            
            if (!$proc) {
                return 0;
            }
            
            $ano = $proc['ano'];
            $semestre = $proc['semestre'];

            // Busca todas as pastas para este processo (seja pela FK ou pelo ano+semestre)
            $sqlPastas = "SELECT id, militar_id 
                          FROM pasta_promocional 
                          WHERE processo_promocional_id = :pid 
                             OR (ano_processo_promocional = :ano AND semestre_processo_promocional = :sem)";
            $stmtPastas = $this->conn->prepare($sqlPastas);
            $stmtPastas->execute([
                ':pid' => $processoId,
                ':ano' => $ano,
                ':sem' => $semestre
            ]);
            $pastas = $stmtPastas->fetchAll(PDO::FETCH_ASSOC);

            // Busca os militares que realmente estão no snapshot como aptos
            $sqlSnap = "SELECT militar_id FROM lq_snapshot 
                        WHERE processo_promocional_id = :pid AND dentro_do_limite = 1";
            $stmtSnap = $this->conn->prepare($sqlSnap);
            $stmtSnap->execute([':pid' => $processoId]);
            $aptos = $stmtSnap->fetchAll(PDO::FETCH_COLUMN);

            // Transforma IDs para inteiros para comparação confiável
            $aptosInt = array_map('intval', $aptos);

            foreach ($pastas as $pasta) {
                $militarIdPasta = (int)$pasta['militar_id'];
                if (!in_array($militarIdPasta, $aptosInt)) {
                    // É uma pasta órfã: exclui os documentos e depois a pasta
                    $sqlDelDocs = "DELETE FROM documento_promocao WHERE pasta_promocional_id = :pasta_id";
                    $stmtDelDocs = $this->conn->prepare($sqlDelDocs);
                    $stmtDelDocs->execute([':pasta_id' => $pasta['id']]);

                    $sqlDelPasta = "DELETE FROM pasta_promocional WHERE id = :pasta_id";
                    $stmtDelPasta = $this->conn->prepare($sqlDelPasta);
                    $stmtDelPasta->execute([':pasta_id' => $pasta['id']]);

                    $pastasRemovidas++;
                }
            }

            return $pastasRemovidas;
        } catch (\PDOException $e) {
            error_log("Erro em PastaPromocionalDAO::limparPastasOrfas: " . $e->getMessage());
            return $pastasRemovidas;
        }
    }
}
