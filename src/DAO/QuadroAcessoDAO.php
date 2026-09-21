<?php

namespace App\DAO;

use PDO;
use Conexao;
use Exception;

require_once __DIR__ . '/../../ConexaoDB/conexao.php';

class QuadroAcessoDAO
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = Conexao::getConexao();
    }

    /**
     * Traduz o identificador ou nome bruto do quadro para a nomenclatura oficial do CBMMT.
     */
    public static function formatarNomeQuadro(string|int|null $quadro): string
    {
        $map = [
            1               => 'Quadro de Oficial do Corpo de Bombeiros Militar (QOBM)',
            '1'             => 'Quadro de Oficial do Corpo de Bombeiros Militar (QOBM)',
            'COMBATENTE'    => 'Quadro de Oficial do Corpo de Bombeiros Militar (QOBM)',
            'QOBM'          => 'Quadro de Oficial do Corpo de Bombeiros Militar (QOBM)',

            2               => 'Quadro Complementar de Oficial do Corpo de Bombeiros Militar (QCOBM)',
            '2'             => 'Quadro Complementar de Oficial do Corpo de Bombeiros Militar (QCOBM)',
            'COMPLEMENTAR'  => 'Quadro Complementar de Oficial do Corpo de Bombeiros Militar (QCOBM)',
            'QCOBM'         => 'Quadro Complementar de Oficial do Corpo de Bombeiros Militar (QCOBM)',

            3               => 'Quadro de Oficial de Saúde do Corpo de Bombeiros Militar (QOSBM)',
            '3'             => 'Quadro de Oficial de Saúde do Corpo de Bombeiros Militar (QOSBM)',
            'SAÚDE'         => 'Quadro de Oficial de Saúde do Corpo de Bombeiros Militar (QOSBM)',
            'SAUDE'         => 'Quadro de Oficial de Saúde do Corpo de Bombeiros Militar (QOSBM)',
            'QOSBM'         => 'Quadro de Oficial de Saúde do Corpo de Bombeiros Militar (QOSBM)',

            4               => 'Quadro de Praças Bombeiro Militar (QPBM)',
            '4'             => 'Quadro de Praças Bombeiro Militar (QPBM)',
            'PRAÇAS'        => 'Quadro de Praças Bombeiro Militar (QPBM)',
            'PRACAS'        => 'Quadro de Praças Bombeiro Militar (QPBM)',
            'QPBM'          => 'Quadro de Praças Bombeiro Militar (QPBM)'
        ];

        return $map[$quadro] ?? (is_string($quadro) && !empty($quadro) ? $quadro : 'Quadro Geral');
    }

    public function buscarDadosQA(int $reuniaoId): array
    {
        $daoReuniao = new ReuniaoComissaoDAO();
        $reuniao = $daoReuniao->buscarPorId($reuniaoId);
        if (!$reuniao) {
            throw new Exception("Reunião não encontrada.");
        }

        $processoId   = (int) $reuniao['processo_promocional_id'];
        $tipoComissao = $reuniao['tipo_comissao'];

        $lqDao = new LqSnapshotDAO();
        $fila = $lqDao->buscarFilaPorProcesso($processoId);

        $candidatos = [];
        foreach ($fila as $item) {
            $qId = (int)$item['quadro_id'];
            if ($tipoComissao === 'CPO' && in_array($qId, [1, 2, 3])) {
                $candidatos[] = $item;
            } elseif ($tipoComissao === 'CPP' && $qId === 4) {
                $candidatos[] = $item;
            }
        }

        // Busca impedimentos registrados na 1ª Pauta
        $criterioDao = new CriterioFalhoDAO();
        $impedidosIds = $criterioDao->buscarTodosImpedidosPorProcesso($processoId, $tipoComissao);

        // Busca notas salvas em quadro_acesso_proposta
        $stmtNotas = $this->conn->prepare("
            SELECT militar_id, nota_fad, nota_feo, nota_fp, pontuacao, posicao 
            FROM quadro_acesso_proposta 
            WHERE reuniao_comissao_id = :rid
        ");
        $stmtNotas->execute([':rid' => $reuniaoId]);
        $notasHomologadas = [];
        while ($row = $stmtNotas->fetch(PDO::FETCH_ASSOC)) {
            $notasHomologadas[(int)$row['militar_id']] = $row;
        }

        $isHomologado = !empty($notasHomologadas);

        $qaOficial  = [];
        $reserva    = [];
        $impedidos  = [];

        foreach ($candidatos as $c) {
            $mid = (int)$c['militar_id'];
            $temImpedimento = in_array($mid, $impedidosIds);

            $c['nota_fad']  = $notasHomologadas[$mid]['nota_fad'] ?? null;
            $c['nota_feo']  = $notasHomologadas[$mid]['nota_feo'] ?? null;
            $c['nota_fp']   = $notasHomologadas[$mid]['nota_fp'] ?? null;
            $c['pontuacao'] = $notasHomologadas[$mid]['pontuacao'] ?? null;

            if ($temImpedimento) {
                $c['situacao'] = 'ELIMINADO';
                $c['motivo_inaptidao'] = 'Impedido na 1ª Pauta';
                $impedidos[] = $c;
            } else {
                $c['situacao'] = 'APTO';
                if ((int)$c['dentro_do_limite'] === 1) {
                    $qaOficial[] = $c;
                } else {
                    $reserva[] = $c;
                }
            }
        }

        // Agrupa o QA Oficial com a Nomenclatura Formal dos Quadros
        $qaAgrupado = [];
        foreach ($qaOficial as $m) {
            $quadroOficial = self::formatarNomeQuadro($m['quadro_id'] ?? $m['nome_quadro']);
            $destino       = $m['posto_grad_destino'] ?? 'Destino';
            $criterio      = ($m['tipo_promocao'] === 'MERECIMENTO') ? 'Merecimento' : 'Antiguidade';
            $vp            = (int)($m['vp_calculadas'] ?? 0);

            $chave = "{$quadroOficial} — {$destino}";
            if (!isset($qaAgrupado[$chave])) {
                $qaAgrupado[$chave] = [
                    'quadro'    => $quadroOficial,
                    'destino'   => $destino,
                    'criterio'  => $criterio,
                    'vp'        => $vp,
                    'membros'   => []
                ];
            }
            $m['criterio_qa'] = $criterio;
            $qaAgrupado[$chave]['membros'][] = $m;
        }

        // Ordena cada grupo (QAM por Nota DESC + Antiguidade ASC; QAA por Antiguidade ASC)
        foreach ($qaAgrupado as &$grupo) {
            if ($grupo['criterio'] === 'Merecimento') {
                usort($grupo['membros'], function ($a, $b) {
                    $notaA = (float)($a['pontuacao'] ?? 0);
                    $notaB = (float)($b['pontuacao'] ?? 0);
                    if ($notaA !== $notaB) {
                        return ($notaA < $notaB) ? 1 : -1;
                    }
                    return ((int)($a['antiguidade'] ?? 0) <=> (int)($b['antiguidade'] ?? 0));
                });
            } else {
                usort($grupo['membros'], function ($a, $b) {
                    return ((int)($a['antiguidade'] ?? 0) <=> (int)($b['antiguidade'] ?? 0));
                });
            }
        }
        unset($grupo);

        return [
            'is_homologado'   => $isHomologado,
            'total_qa'        => count($qaOficial),
            'total_reserva'   => count($reserva),
            'total_impedidos' => count($impedidos),
            'qa_agrupado'     => array_values($qaAgrupado),
            'reserva'         => $reserva,
            'impedidos'       => $impedidos
        ];
    }

    /**
     * Retorna os dados da Proposta de Promoção (3ª Pauta):
     * - Merecimento (Coronel): 3 candidatos por vaga aberta (3 x VP).
     * - Antiguidade: Exatamente N = VP candidatos promovidos.
     */
    public function buscarDadosProposta(int $reuniaoId): array
    {
        $dadosQA = $this->buscarDadosQA($reuniaoId);
        $propostaAgrupada = [];
        $totalPropostos = 0;

        foreach ($dadosQA['qa_agrupado'] as $grupo) {
            $vp = (int)$grupo['vp'];
            $isMerecimento = ($grupo['criterio'] === 'Merecimento');
            $membrosProposta = [];

            if ($vp > 0) {
                if ($isMerecimento) {
                    // Regra: Para cada vaga concorrem 3 candidatos (ex: 2 vagas = 6 candidatos)
                    $limiteCandidatos = 3 * $vp;
                    $membrosProposta = array_slice($grupo['membros'], 0, $limiteCandidatos);
                    $totalPropostos += count($membrosProposta);
                } else {
                    // Antiguidade: Exatamente N = VP candidatos
                    $membrosProposta = array_slice($grupo['membros'], 0, $vp);
                    $totalPropostos += count($membrosProposta);
                }
            }

            $propostaAgrupada[] = [
                'quadro'           => $grupo['quadro'],
                'destino'          => $grupo['destino'],
                'criterio'         => $grupo['criterio'],
                'vp'               => $vp,
                'ha_vagas'         => ($vp > 0 && !empty($membrosProposta)),
                'membros_propostos'=> $membrosProposta
            ];
        }

        return [
            'total_propostos'   => $totalPropostos,
            'proposta_agrupada' => $propostaAgrupada
        ];
    }

    public function homologarQA(int $reuniaoId, array $notasMerecimento = []): bool
    {
        $dados = $this->buscarDadosQA($reuniaoId);

        try {
            $this->conn->beginTransaction();

            $stmtDel = $this->conn->prepare("DELETE FROM quadro_acesso_proposta WHERE reuniao_comissao_id = :rid");
            $stmtDel->execute([':rid' => $reuniaoId]);

            $stmtIns = $this->conn->prepare("
                INSERT INTO quadro_acesso_proposta 
                    (reuniao_comissao_id, militar_id, criterio, nota_fad, nota_feo, nota_fp, pontuacao, posicao, status) 
                VALUES 
                    (:rid, :mid, :criterio, :fad, :feo, :fp, :pontuacao, :posicao, 'Homologado')
            ");

            foreach ($dados['qa_agrupado'] as $grupo) {
                if ($grupo['criterio'] === 'Merecimento') {
                    foreach ($grupo['membros'] as &$m) {
                        $mid = (int)$m['militar_id'];
                        if (isset($notasMerecimento[$mid])) {
                            $m['nota_fad']  = is_numeric($notasMerecimento[$mid]['nota_fad']) ? (float)$notasMerecimento[$mid]['nota_fad'] : null;
                            $m['nota_feo']  = is_numeric($notasMerecimento[$mid]['nota_feo']) ? (float)$notasMerecimento[$mid]['nota_feo'] : null;
                            $m['nota_fp']   = is_numeric($notasMerecimento[$mid]['nota_fp'])  ? (float)$notasMerecimento[$mid]['nota_fp']  : null;
                            $m['pontuacao'] = is_numeric($notasMerecimento[$mid]['pontuacao']) ? (float)$notasMerecimento[$mid]['pontuacao'] : null;
                        }
                    }
                    unset($m);

                    usort($grupo['membros'], function ($a, $b) {
                        $notaA = (float)($a['pontuacao'] ?? 0);
                        $notaB = (float)($b['pontuacao'] ?? 0);
                        if ($notaA !== $notaB) {
                            return ($notaA < $notaB) ? 1 : -1;
                        }
                        return ((int)($a['antiguidade'] ?? 0) <=> (int)($b['antiguidade'] ?? 0));
                    });
                }

                foreach ($grupo['membros'] as $idx => $item) {
                    $posicao = $idx + 1;
                    $stmtIns->execute([
                        ':rid'       => $reuniaoId,
                        ':mid'       => (int)$item['militar_id'],
                        ':criterio'  => $item['criterio_qa'],
                        ':fad'       => $item['nota_fad'] ?? null,
                        ':feo'       => $item['nota_feo'] ?? null,
                        ':fp'        => $item['nota_fp'] ?? null,
                        ':pontuacao' => $item['pontuacao'] ?? null,
                        ':posicao'   => $posicao
                    ]);
                }
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            throw $e;
        }
    }
}