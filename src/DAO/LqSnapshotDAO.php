<?php
// src/DAO/LqSnapshotDAO.php
namespace App\DAO;

require_once __DIR__ . '/../../ConexaoDB/conexao.php';

use Conexao;
use PDO;
use PDOException;
use Exception;

/**
 * DAO responsável pela geração e gestão do Limite Quantitativo (LQ).
 *
 * Motor Top-Down: percorre os agrupamentos de vagas de cima para baixo
 * (topo da hierarquia para a base), calculando o efeito cascata de vagas
 * disponíveis e filtrando apenas os militares com interstício mínimo cumprido
 * até a data oficial da promoção do processo, ESTRITAMENTE DENTRO DO SEU QUADRO.
 */
class LqSnapshotDAO
{
    private PDO $conexao;

    /** @var array|null Cache da tabela de interstício para evitar múltiplas consultas. */
    private ?array $tabelaIntersticio = null;

    public function __construct()
    {
        $this->conexao = Conexao::getConexao();
    }

    // =========================================================================
    // MOTOR DE GERAÇÃO DO LQ
    // =========================================================================

    public function gerarSnapshot(int $processoId): bool
    {
        try {
            $this->conexao->beginTransaction();

            $stmt = $this->conexao->prepare("SELECT * FROM processo_promocional WHERE id = ?");
            $stmt->execute([$processoId]);
            $processo = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$processo) {
                throw new Exception("Processo promocional não encontrado (ID: {$processoId}).");
            }

            $dataPromocao    = $processo['data_promocao'];
            $isDataDezembro  = (substr($dataPromocao, 5, 5) === '12-02');
            $anoProcesso     = substr($dataPromocao, 0, 4);

            // 1. Limpa snapshot anterior deste processo
            $stmt = $this->conexao->prepare("DELETE FROM lq_snapshot WHERE processo_promocional_id = ?");
            $stmt->execute([$processoId]);

            // 2. Busca todos os quadros (QOBM, QOSBM, QCOBM, QPBM)
            $stmt = $this->conexao->query("SELECT id_quadro FROM quadro ORDER BY id_quadro ASC");
            $todosQuadros = $stmt->fetchAll(PDO::FETCH_COLUMN);

            foreach ($todosQuadros as $quadroId) {
                $stmt = $this->conexao->prepare(
                    "SELECT * FROM vagas_fixadas WHERE quadro_id = ? ORDER BY id_vagas_fixadas ASC"
                );
                $stmt->execute([$quadroId]);
                $agrupamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $vpCascata = 0;

                foreach ($agrupamentos as $agrupamento) {
                    $vagasFixadasId = $agrupamento['id_vagas_fixadas'];
                    $limiteLegal    = (int)$agrupamento['quantidade'];
                    $ajusteManual   = (int)$agrupamento['ajuste_manual'];

                    $stmt = $this->conexao->prepare(
                        "SELECT posto_grad_mil, tipo_promocao FROM container_posto_map WHERE vagas_fixadas_id = ?"
                    );
                    $stmt->execute([$vagasFixadasId]);
                    $postosMapeados = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if (empty($postosMapeados)) {
                        continue;
                    }

                    $nomesPostos = array_column($postosMapeados, 'posto_grad_mil');
                    $placeholders = implode(',', array_fill(0, count($nomesPostos), '?'));

                    // CORREÇÃO 1: Ocupação filtrada estritamente pelo QUADRO do contêiner e efetivo regular (is_excedente = 0)
                    $stmt = $this->conexao->prepare(
                        "SELECT COUNT(*) FROM militar WHERE posto_grad_mil IN ({$placeholders}) AND quadro_id = ? AND status = 'ATIVO' AND is_excedente = 0"
                    );
                    $paramsOcupacao = array_merge($nomesPostos, [$quadroId]);
                    $stmt->execute($paramsOcupacao);
                    $ocupacaoAtual = (int)$stmt->fetchColumn();

                    $vr        = max(0, $limiteLegal - $ocupacaoAtual);
                    $vd        = $vpCascata;
                    $vp        = $vr + $vd + $ajusteManual;

                    $vpCascata = $vp;

                    foreach ($postosMapeados as $postoMapa) {
                        $nomePosto    = $postoMapa['posto_grad_mil'];
                        $tipoPromocao = $postoMapa['tipo_promocao'];

                        if ($tipoPromocao === 'MERECIMENTO' && !$isDataDezembro) {
                            continue;
                        }

                        // Omissão completa de Topo de Carreira como candidatos no LQ (CEL BM no Q1, TC BM nos Q2/Q3, ST BM no Q4)
                        if ($nomePosto === 'CEL BM' || $nomePosto === 'ST BM' || ($nomePosto === 'TC BM' && ($quadroId == 2 || $quadroId == 3))) {
                            continue;
                        }

                        // CORREÇÃO 2: Busca candidatos aptos filtrando obrigatoriamente pelo QUADRO
                        $candidatos = $this->buscarCandidatosAptos($nomePosto, $anoProcesso, (int)$quadroId);

                        if (empty($candidatos)) {
                            continue;
                        }

                        $postoDestino = $this->resolverPostoDestino($nomePosto);

                        if ($tipoPromocao === 'ENTRADA') {
                            $limiteLQ = 2 * $vd;
                        } else {
                            $limiteLQ = count($candidatos);
                        }

                        // CORREÇÃO: Para promoções que sobem de nível (ENTRADA e MERECIMENTO a Coronel), 
                        // a vaga disputada é a vaga do nível superior ($vd = vagas herdadas)
                        $vpGravar = ($tipoPromocao === 'ENTRADA' || $tipoPromocao === 'MERECIMENTO') ? $vd : $vp;

                        $posicao = 1;
                        foreach ($candidatos as $candidato) {
                            $dentroDoLimite = ($posicao <= $limiteLQ) ? 1 : 0;

                            $stmtIns = $this->conexao->prepare("
                                INSERT INTO lq_snapshot 
                                    (processo_promocional_id, militar_id, posto_grad_destino,
                                     vagas_fixadas_id, tipo_promocao, vp_calculadas,
                                     posicao_antiguidade, dentro_do_limite)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                            ");
                            $stmtIns->execute([
                                $processoId,
                                $candidato['id'],
                                $postoDestino,
                                $vagasFixadasId,
                                $tipoPromocao,
                                $vpGravar,
                                $posicao,
                                $dentroDoLimite,
                            ]);
                            $posicao++;
                        }
                    }
                }
            }

            $this->conexao->commit();
            return true;
        } catch (Exception $e) {
            $this->conexao->rollBack();
            error_log("Erro em LqSnapshotDAO->gerarSnapshot: " . $e->getMessage());
            throw $e;
        }
    }

    public function gerarSnapshotSimulado(int $processoId): array
    {
        try {
            $this->conexao->beginTransaction();

            $stmt = $this->conexao->prepare("SELECT * FROM processo_promocional WHERE id = ?");
            $stmt->execute([$processoId]);
            $processo = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$processo) {
                throw new Exception("Processo promocional não encontrado (ID: {$processoId}).");
            }

            $dataPromocao    = $processo['data_promocao'];
            $isDataDezembro  = (substr($dataPromocao, 5, 5) === '12-02');
            $anoProcesso     = substr($dataPromocao, 0, 4);

            $stmt = $this->conexao->prepare("DELETE FROM lq_snapshot WHERE processo_promocional_id = ?");
            $stmt->execute([$processoId]);

            $stmt = $this->conexao->query("SELECT id_quadro FROM quadro ORDER BY id_quadro ASC");
            $todosQuadros = $stmt->fetchAll(PDO::FETCH_COLUMN);

            foreach ($todosQuadros as $quadroId) {
                $stmt = $this->conexao->prepare(
                    "SELECT * FROM vagas_fixadas WHERE quadro_id = ? ORDER BY id_vagas_fixadas ASC"
                );
                $stmt->execute([$quadroId]);
                $agrupamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $vpCascata = 0;

                foreach ($agrupamentos as $agrupamento) {
                    $vagasFixadasId = $agrupamento['id_vagas_fixadas'];
                    $limiteLegal    = (int)$agrupamento['quantidade'];
                    $ajusteManual   = (int)$agrupamento['ajuste_manual'];

                    $stmt = $this->conexao->prepare(
                        "SELECT posto_grad_mil, tipo_promocao FROM container_posto_map WHERE vagas_fixadas_id = ?"
                    );
                    $stmt->execute([$vagasFixadasId]);
                    $postosMapeados = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if (empty($postosMapeados)) {
                        continue;
                    }

                    $nomesPostos = array_column($postosMapeados, 'posto_grad_mil');
                    $placeholders = implode(',', array_fill(0, count($nomesPostos), '?'));

                    $stmt = $this->conexao->prepare(
                        "SELECT COUNT(*) FROM militar WHERE posto_grad_mil IN ({$placeholders}) AND quadro_id = ? AND status = 'ATIVO' AND is_excedente = 0"
                    );
                    $paramsOcupacao = array_merge($nomesPostos, [$quadroId]);
                    $stmt->execute($paramsOcupacao);
                    $ocupacaoAtual = (int)$stmt->fetchColumn();

                    $vr        = max(0, $limiteLegal - $ocupacaoAtual);
                    $vd        = $vpCascata;
                    $vp        = $vr + $vd + $ajusteManual;

                    $vpCascata = $vp;

                    foreach ($postosMapeados as $postoMapa) {
                        $nomePosto    = $postoMapa['posto_grad_mil'];
                        $tipoPromocao = $postoMapa['tipo_promocao'];

                        if ($tipoPromocao === 'MERECIMENTO' && !$isDataDezembro) {
                            continue;
                        }

                        // Omissão completa de Topo de Carreira como candidatos no LQ (CEL BM no Q1, TC BM nos Q2/Q3, ST BM no Q4)
                        if ($nomePosto === 'CEL BM' || $nomePosto === 'ST BM' || ($nomePosto === 'TC BM' && ($quadroId == 2 || $quadroId == 3))) {
                            continue;
                        }

                        $candidatos = $this->buscarCandidatosAptos($nomePosto, $anoProcesso, (int)$quadroId);

                        if (empty($candidatos)) {
                            continue;
                        }

                        $postoDestino = $this->resolverPostoDestino($nomePosto);

                        if ($tipoPromocao === 'ENTRADA') {
                            $limiteLQ = 2 * $vd;
                        } else {
                            $limiteLQ = count($candidatos);
                        }

                        $vpGravar = ($tipoPromocao === 'ENTRADA') ? $vd : $vp;

                        $posicao = 1;
                        foreach ($candidatos as $candidato) {
                            $dentroDoLimite = ($posicao <= $limiteLQ) ? 1 : 0;

                            $stmtIns = $this->conexao->prepare("
                                INSERT INTO lq_snapshot 
                                    (processo_promocional_id, militar_id, posto_grad_destino,
                                     vagas_fixadas_id, tipo_promocao, vp_calculadas,
                                     posicao_antiguidade, dentro_do_limite)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                            ");
                            $stmtIns->execute([
                                $processoId,
                                $candidato['id'],
                                $postoDestino,
                                $vagasFixadasId,
                                $tipoPromocao,
                                $vpGravar,
                                $posicao,
                                $dentroDoLimite,
                            ]);
                            $posicao++;
                        }
                    }
                }
            }

            $filaSimulada = $this->buscarFilaPorProcesso($processoId);
            $this->conexao->rollBack();

            return $filaSimulada;
        } catch (Exception $e) {
            if ($this->conexao->inTransaction()) {
                $this->conexao->rollBack();
            }
            error_log("Erro em LqSnapshotDAO->gerarSnapshotSimulado: " . $e->getMessage());
            throw $e;
        }
    }

    // =========================================================================
    // MÉTODOS AUXILIARES (Lógica de Interstício)
    // =========================================================================

    /**
     * Busca os militares de um dado posto que possuem o interstício mínimo
     * cumprido, ESTRITAMENTE DENTRO DO SEU QUADRO.
     *
     * @param string $nomePosto Posto/graduação a verificar (ex: 'CAP BM').
     * @param string $lq_ano    O ano do processo promocional (para travar em 31/12).
     * @param int    $quadroId  ID do Quadro a filtrar (ex: 1 para QOBM, 2 para QOSBM).
     * @return array Lista de militares aptos ordenados por antiguidade.
     */
    private function buscarCandidatosAptos(string $nomePosto, string $lq_ano, int $quadroId): array
    {
        $stmt = $this->conexao->prepare("
            SELECT rp.a_contar_de, 
                   rp.grau_hierarquico, 
                   rp.militar_id, 
                   m.id, 
                   m.nome, 
                   m.posto_grad_mil, 
                   m.quadro_id, 
                   m.antiguidade, 
                   m.data_cumprimento_intersticio
            FROM registro_de_promocoes rp
            INNER JOIN militar m ON rp.militar_id = m.id 
            WHERE m.posto_grad_mil = :posto
              AND m.quadro_id = :quadroId
              AND m.status = 'ATIVO'
              AND m.is_excedente = 0
            ORDER BY m.antiguidade ASC
        ");
        $stmt->bindValue(':posto', $nomePosto, PDO::PARAM_STR);
        $stmt->bindValue(':quadroId', $quadroId, PDO::PARAM_INT);
        $stmt->execute();
        $consulta = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($consulta)) {
            return [];
        }

        $aptos = [];
        $data_limite_ano = \DateTime::createFromFormat('Y-m-d', $lq_ano . '-12-31');

        foreach ($consulta as $resultado) {
            $aux_a_contar_de             = $resultado['a_contar_de'];
            $aux_posto_grad              = $resultado['grau_hierarquico'];
            $aux_posto_grad_atual        = $resultado['posto_grad_mil'];
            $aux_cumprimento_intersticio = $resultado['data_cumprimento_intersticio'];

            if (is_null($aux_a_contar_de)) {
                continue;
            }

            $data_cumprimento = null;

            if (is_null($aux_cumprimento_intersticio)) {
                $intersticio = $this->getInterstitioParaPosto($aux_posto_grad_atual);

                if ($intersticio !== null && is_numeric($intersticio)) {
                    $data_cumprimento = new \DateTime($aux_a_contar_de);
                    if ($intersticio < 1 && $intersticio > 0) {
                        $meses = (int)($intersticio * 12);
                        $data_cumprimento->modify("+{$meses} months");
                    } else {
                        $anos = (int)$intersticio;
                        $data_cumprimento->modify("+{$anos} years");
                    }
                }
            } else {
                $data_cumprimento = new \DateTime($aux_cumprimento_intersticio);
            }

            if ($data_cumprimento && ($data_cumprimento <= $data_limite_ano) && ($aux_posto_grad == $aux_posto_grad_atual)) {
                $aptos[] = $resultado;
            }
        }

        return $aptos;
    }

    /**
     * Retorna o valor mínimo de interstício (em anos) para um dado posto/graduação.
     */
    private function getInterstitioParaPosto(string $posto): ?float
    {
        if ($this->tabelaIntersticio === null) {
            $stmt = $this->conexao->query("SELECT * FROM intersticio LIMIT 1");
            $this->tabelaIntersticio = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        }

        $inter = $this->tabelaIntersticio;

        return match ($posto) {
            'CEL BM'    => 0,
            'TC BM'     => (float)($inter['tc_cel']    ?? 0),
            'MAJ BM'    => (float)($inter['maj_tc']    ?? 0),
            'CAP BM'    => (float)($inter['cap_maj']   ?? 0),
            '1º TEN BM' => (float)($inter['1ten_cap']  ?? 0),
            '2º TEN BM' => (float)($inter['2ten_1ten'] ?? 0),
            'ASP OF BM' => (float)($inter['asp_2ten']  ?? 0),
            'AL OF BM'  => 0,
            'ST BM'     => (float)($inter['st_2ten']   ?? 0),
            '1º SGT BM' => (float)($inter['1sgt_st']   ?? 0),
            '2º SGT BM' => (float)($inter['2sgt_1sgt'] ?? 0),
            '3º SGT BM' => (float)($inter['3sgt_2sgt'] ?? 0),
            'CB BM'     => (float)($inter['cb_3sgt']   ?? 0),
            'SD BM'     => (float)($inter['sd_cb']     ?? 0),
            default     => null,
        };
    }

    private function resolverPostoDestino(string $postoAtual): string
    {
        return match ($postoAtual) {
            'TC BM'     => 'CEL BM',
            'MAJ BM'    => 'TC BM',
            'CAP BM'    => 'MAJ BM',
            '1º TEN BM' => 'CAP BM',
            '2º TEN BM' => '1º TEN BM',
            'ASP OF BM' => '2º TEN BM',
            '1º SGT BM' => 'ST BM',
            '2º SGT BM' => '1º SGT BM',
            '3º SGT BM' => '2º SGT BM',
            'CB BM'     => '3º SGT BM',
            'SD BM'     => 'CB BM',
            default     => $postoAtual,
        };
    }

    // =========================================================================
    // MÉTODOS DE CONSULTA E GESTÃO DO SNAPSHOT
    // =========================================================================

    public function buscarFilaPorProcesso(int $processoId, ?int $vagasFixadasId = null): array
    {
        try {
            $sql = "SELECT 
                        lq.*,
                        m.nome,
                        m.posto_grad_mil,
                        m.posto_grad_mil AS posto_atual,
                        m.antiguidade,
                        q.nome_quadro,
                        vf.nome_do_agrupamento,
                        vf.quadro_id,
                        CASE lq.tipo_promocao
                            WHEN 'MERECIMENTO' THEN 'Merecimento'
                            ELSE 'Antiguidade'
                        END AS criterio_promocao,
                        CASE lq.tipo_promocao
                            WHEN 'INTERNA'     THEN 'Interna'
                            WHEN 'ENTRADA'     THEN 'Entrada'
                            WHEN 'MERECIMENTO' THEN 'Entrada'
                            ELSE lq.tipo_promocao
                        END AS tipo_promocao_label
                    FROM lq_snapshot lq
                    INNER JOIN militar m        ON lq.militar_id       = m.id
                    INNER JOIN vagas_fixadas vf ON lq.vagas_fixadas_id = vf.id_vagas_fixadas
                    INNER JOIN quadro q         ON vf.quadro_id        = q.id_quadro
                    WHERE lq.processo_promocional_id = :pid
                      AND m.posto_grad_mil NOT IN ('CEL BM', 'ST BM')";

            if ($vagasFixadasId !== null) {
                $sql .= " AND lq.vagas_fixadas_id = :vid";
            }

            $sql .= " ORDER BY q.id_quadro ASC, lq.vagas_fixadas_id ASC, lq.tipo_promocao DESC, lq.posicao_antiguidade ASC";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(':pid', $processoId, PDO::PARAM_INT);
            if ($vagasFixadasId !== null) {
                $stmt->bindValue(':vid', $vagasFixadasId, PDO::PARAM_INT);
            }
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro em LqSnapshotDAO->buscarFilaPorProcesso: " . $e->getMessage());
            throw $e;
        }
    }

    public function atualizarSituacao(int $id, string $situacao): bool
    {
        try {
            $sql = "UPDATE lq_snapshot SET situacao = :situacao WHERE id = :id";
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(':situacao', $situacao, PDO::PARAM_STR);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro em LqSnapshotDAO->atualizarSituacao: " . $e->getMessage());
            throw $e;
        }
    }

    public function puxarProximoDaReserva(int $processoId, int $vagasFixadasId, string $tipoPromocao): bool
    {
        try {
            $sql = "SELECT id FROM lq_snapshot 
                    WHERE processo_promocional_id = :pid 
                      AND vagas_fixadas_id = :vid 
                      AND tipo_promocao = :tipo
                      AND dentro_do_limite = 0 
                      AND situacao = 'APTO' 
                    ORDER BY posicao_antiguidade ASC LIMIT 1";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(':pid',  $processoId,     PDO::PARAM_INT);
            $stmt->bindValue(':vid',  $vagasFixadasId, PDO::PARAM_INT);
            $stmt->bindValue(':tipo', $tipoPromocao,    PDO::PARAM_STR);
            $stmt->execute();

            $reserva = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($reserva) {
                $sqlUpd  = "UPDATE lq_snapshot SET dentro_do_limite = 1 WHERE id = :id";
                $stmtUpd = $this->conexao->prepare($sqlUpd);
                $stmtUpd->bindValue(':id', $reserva['id'], PDO::PARAM_INT);
                return $stmtUpd->execute();
            }

            return false;
        } catch (PDOException $e) {
            error_log("Erro em LqSnapshotDAO->puxarProximoDaReserva: " . $e->getMessage());
            throw $e;
        }
    }
}
