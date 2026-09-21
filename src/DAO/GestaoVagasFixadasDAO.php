<?php

namespace App\DAO;

use PDO;
use Conexao;
use Exception;

require_once __DIR__ . '/../../ConexaoDB/conexao.php';

class GestaoVagasFixadasDAO
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = Conexao::getConexao();
    }

    /**
     * Retorna todos os limites legais definidos (Carga Inicial)
     *
     * @return array
     */
    public function buscarLimitesLegais(): array
    {
        try {
            $sql = "SELECT id_vagas_fixadas, quadro_id, nome_do_agrupamento, quantidade 
                    FROM vagas_fixadas 
                    ORDER BY quadro_id ASC, id_vagas_fixadas ASC";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro no buscarLimitesLegais: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Define ou atualiza a quantidade do limite legal para um determinado Quadro e Agrupamento
     *
     * @param int $quadroId
     * @param string $nomeAgrupamento
     * @param int $quantidade
     * @return bool
     */
    public function salvarLimiteLegal(int $quadroId, string $nomeAgrupamento, int $quantidade): bool
    {
        try {
            // Verifica se o limite já está registrado
            $sqlCheck = "SELECT id_vagas_fixadas FROM vagas_fixadas 
                         WHERE quadro_id = :quadro_id 
                         AND nome_do_agrupamento = :agrupamento";

            $stmtCheck = $this->conn->prepare($sqlCheck);
            $stmtCheck->bindParam(':quadro_id', $quadroId, PDO::PARAM_INT);
            $stmtCheck->bindParam(':agrupamento', $nomeAgrupamento, PDO::PARAM_STR);
            $stmtCheck->execute();

            if ($stmtCheck->rowCount() > 0) {
                // Atualiza
                $sql = "UPDATE vagas_fixadas 
                        SET quantidade = :quantidade 
                        WHERE quadro_id = :quadro_id 
                        AND nome_do_agrupamento = :agrupamento";
            } else {
                // Insere
                $sql = "INSERT INTO vagas_fixadas (quadro_id, nome_do_agrupamento, quantidade) 
                        VALUES (:quadro_id, :agrupamento, :quantidade)";
            }

            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':quadro_id', $quadroId, PDO::PARAM_INT);
            $stmt->bindParam(':agrupamento', $nomeAgrupamento, PDO::PARAM_STR);
            $stmt->bindParam(':quantidade', $quantidade, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (\PDOException $e) {
            error_log("Erro no salvarLimiteLegal: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Retorna o panorama completo de vagas e ocupação por contêiner agrupado por quadro.
     */
    public function buscarPainelOcupacaoVagas(): array
    {
        try {
            $stmtQuadros = $this->conn->query("SELECT * FROM quadro ORDER BY id_quadro ASC");
            $quadros = $stmtQuadros->fetchAll(PDO::FETCH_ASSOC);

            $totalGeralPrevisto   = 0;
            $totalGeralOcupado    = 0;
            $totalGeralExcedentes = 0;
            $totalGeralAberto     = 0;
            $dadosPorQuadro       = [];

            foreach ($quadros as $q) {
                $quadroId = (int)$q['id_quadro'];

                $stmtVagas = $this->conn->prepare("SELECT * FROM vagas_fixadas WHERE quadro_id = :qid ORDER BY id_vagas_fixadas ASC");
                $stmtVagas->execute([':qid' => $quadroId]);
                $agrupamentos = $stmtVagas->fetchAll(PDO::FETCH_ASSOC);

                $listaAgrupamentos = [];

                foreach ($agrupamentos as $agrup) {
                    $vagasFixadasId = (int)$agrup['id_vagas_fixadas'];
                    $limiteLegal    = (int)$agrup['quantidade'];
                    $ajusteManual   = (int)($agrup['ajuste_manual'] ?? 0);

                    // Postos mapeados no contêiner
                    $stmtMap = $this->conn->prepare("SELECT posto_grad_mil, tipo_promocao FROM container_posto_map WHERE vagas_fixadas_id = :vid");
                    $stmtMap->execute([':vid' => $vagasFixadasId]);
                    $postosMapeados = $stmtMap->fetchAll(PDO::FETCH_ASSOC);

                    $postosNomes = array_column($postosMapeados, 'posto_grad_mil');

                    // Contagem do efetivo ativo regular (desconsidera excedentes)
                    $ocupacaoAtual = 0;
                    $qtdExcedentes = 0;

                    if (!empty($postosNomes)) {
                        $placeholders = implode(',', array_fill(0, count($postosNomes), '?'));
                        $params = array_merge($postosNomes, [$quadroId]);

                        // 1. Ocupação Regular (is_excedente = 0)
                        $sqlCount = "SELECT COUNT(*) FROM militar 
                                     WHERE posto_grad_mil IN ({$placeholders}) 
                                       AND quadro_id = ? 
                                       AND status = 'ATIVO'
                                       AND is_excedente = 0";
                        $stmtCount = $this->conn->prepare($sqlCount);
                        $stmtCount->execute($params);
                        $ocupacaoAtual = (int)$stmtCount->fetchColumn();

                        // 2. Quantidade de Excedentes (is_excedente = 1)
                        $sqlCountExc = "SELECT COUNT(*) FROM militar 
                                        WHERE posto_grad_mil IN ({$placeholders}) 
                                          AND quadro_id = ? 
                                          AND status = 'ATIVO'
                                          AND is_excedente = 1";
                        $stmtCountExc = $this->conn->prepare($sqlCountExc);
                        $stmtCountExc->execute($params);
                        $qtdExcedentes = (int)$stmtCountExc->fetchColumn();
                    }

                    $vagasAbertas   = max(0, $limiteLegal - $ocupacaoAtual);
                    $vagasComAjuste = max(0, $limiteLegal - $ocupacaoAtual + $ajusteManual);
                    $percOcupacao   = $limiteLegal > 0 ? round(($ocupacaoAtual / $limiteLegal) * 100, 1) : 0;

                    $totalGeralPrevisto   += $limiteLegal;
                    $totalGeralOcupado    += $ocupacaoAtual;
                    $totalGeralExcedentes += $qtdExcedentes;
                    $totalGeralAberto     += $vagasAbertas;

                    $listaAgrupamentos[] = [
                        'id'              => $vagasFixadasId,
                        'nome'            => $agrup['nome_do_agrupamento'],
                        'postos'          => $postosNomes,
                        'limite_legal'    => $limiteLegal,
                        'ajuste_manual'   => $ajusteManual,
                        'ocupacao'        => $ocupacaoAtual,
                        'excedentes'      => $qtdExcedentes,
                        'vagas_abertas'   => $vagasAbertas,
                        'vagas_ajustadas' => $vagasComAjuste,
                        'perc_ocupacao'   => $percOcupacao
                    ];
                }

                $dadosPorQuadro[] = [
                    'id_quadro'    => $quadroId,
                    'nome_quadro'  => $q['nome_quadro'],
                    'agrupamentos' => $listaAgrupamentos
                ];
            }

            return [
                'total_previsto'   => $totalGeralPrevisto,
                'total_ocupado'    => $totalGeralOcupado,
                'total_excedentes' => $totalGeralExcedentes,
                'total_aberto'     => $totalGeralAberto,
                'dados_por_quadro' => $dadosPorQuadro
            ];
        } catch (\PDOException $e) {
            error_log("Erro no buscarPainelOcupacaoVagas: " . $e->getMessage());
            return [
                'total_previsto'   => 0,
                'total_ocupado'    => 0,
                'total_aberto'     => 0,
                'dados_por_quadro' => []
            ];
        }
    }

    /**
     * Retorna as vagas reais em aberto para um contêiner específico.
     */
    public function buscarVagasAbertasPorContainer(int $vagasFixadasId): int
    {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM vagas_fixadas WHERE id_vagas_fixadas = :vid");
            $stmt->execute([':vid' => $vagasFixadasId]);
            $agrup = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$agrup) return 0;

            $stmtMap = $this->conn->prepare("SELECT posto_grad_mil FROM container_posto_map WHERE vagas_fixadas_id = :vid");
            $stmtMap->execute([':vid' => $vagasFixadasId]);
            $postos = $stmtMap->fetchAll(PDO::FETCH_COLUMN);

            if (empty($postos)) return 0;

            $placeholders = implode(',', array_fill(0, count($postos), '?'));
            $stmtCount = $this->conn->prepare("SELECT COUNT(*) FROM militar WHERE posto_grad_mil IN ({$placeholders}) AND quadro_id = ? AND status = 'ATIVO' AND is_excedente = 0");
            $stmtCount->execute(array_merge($postos, [$agrup['quadro_id']]));
            $ocupacao = (int)$stmtCount->fetchColumn();

            return max(0, (int)$agrup['quantidade'] - $ocupacao + (int)($agrup['ajuste_manual'] ?? 0));
        } catch (\PDOException $e) {
            error_log("Erro no buscarVagasAbertasPorContainer: " . $e->getMessage());
            return 0;
        }
    }
}
