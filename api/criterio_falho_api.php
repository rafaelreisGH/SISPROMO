<?php

/**
 * API: Gestão de Impedimentos Legais (Tópico 2 da Ata de Promoção).
 *
 * Suporta:
 *   - GET ?reuniao_id=123: Retorna os militares do LQ, impedimentos acumulados e inaptos do T1.
 *   - POST (acao: simular ou salvar): Recebe a lista de impedimentos e simula ou persiste os dados.
 */

require_once __DIR__ . '/../Controllers/session_bootstrap.php';
header('Content-Type: application/json');

require_once '../Controllers/nivel_gestor.php';
require_once '../vendor/autoload.php';
require_once '../Logger/LoggerFactory.php';

use App\DAO\ReuniaoComissaoDAO;
use App\DAO\CriterioFalhoDAO;
use App\DAO\LqSnapshotDAO;
use App\DAO\ConceitoMoralDAO;

$method = $_SERVER['REQUEST_METHOD'];
$logger = LoggerFactory::createLogger();

try {
    $conn = Conexao::getConexao();

    if ($method === 'GET') {
        $reuniaoId = isset($_GET['reuniao_id']) ? (int)$_GET['reuniao_id'] : 0;
        if (!$reuniaoId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID da reunião inválido.']);
            exit();
        }

        $daoReuniao = new ReuniaoComissaoDAO();
        $reuniao = $daoReuniao->buscarPorId($reuniaoId);
        if (!$reuniao) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Reunião não encontrada.']);
            exit();
        }

        $processoId   = (int)$reuniao['processo_promocional_id'];
        $tipoComissao = $reuniao['tipo_comissao'];

        // 1. Buscar todos os candidatos no LQ Snapshot para este processo
        $lqDao = new LqSnapshotDAO();
        $fila = $lqDao->buscarFilaPorProcesso($processoId);

        // Filtrar a fila pelo tipo da comissão (CPO: Quadros 1, 2, 3; CPP: Quadro 4)
        $candidatos = [];
        foreach ($fila as $item) {
            $qId = (int)$item['quadro_id'];
            if ($tipoComissao === 'CPO' && in_array($qId, [1, 2, 3])) {
                $candidatos[] = $item;
            } elseif ($tipoComissao === 'CPP' && $qId === 4) {
                $candidatos[] = $item;
            }
        }

        // 2. Buscar impedimentos acumulados para o processo e comissão
        $criterioDao = new CriterioFalhoDAO();
        $impedimentosGravados = [];
        foreach ($candidatos as $c) {
            $falhas = $criterioDao->buscarFalhasPorMilitarEProcesso($c['militar_id'], $processoId, $tipoComissao);
            if (!empty($falhas)) {
                $impedimentosGravados[$c['militar_id']] = $falhas;
            }
        }

        // 3. Buscar conceito moral do Tópico 1 acumulado do processo para identificar inaptos
        $conceitoDao = new ConceitoMoralDAO();
        $semConceito = $conceitoDao->buscarSemConceitoPorProcesso($processoId, $tipoComissao);
        $inaptosT1 = array_column($semConceito, 'militar_id');

        // 4. Buscar sugestões de LTIP
        $sugestoesLtip = [];
        foreach ($candidatos as $c) {
            $stmt = $conn->prepare("SELECT SUM(qtde_de_dias) FROM tempo_nao_arregimentado WHERE militar_id = :mid AND categoria = 'inciso2' AND posto_grad_na_epoca = :posto");
            $stmt->execute([':mid' => $c['militar_id'], ':posto' => $c['posto_atual']]);
            $dias = (int)$stmt->fetchColumn();
            if ($dias > 0) {
                $sugestoesLtip[$c['militar_id']] = $dias;
            }
        }

        echo json_encode([
            'success' => true,
            'candidatos' => $candidatos,
            'impedimentos' => $impedimentosGravados,
            'inaptos_t1' => $inaptosT1,
            'sugestoes_ltip' => $sugestoesLtip
        ]);
        exit();
    }

    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $acao = $input['acao'] ?? '';
        $reuniaoId = isset($input['reuniao_id']) ? (int)$input['reuniao_id'] : 0;
        $impedimentos = $input['impedimentos'] ?? [];

        if (!in_array($acao, ['simular', 'salvar'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Ação inválida.']);
            exit();
        }

        if (!$reuniaoId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Reunião inválida.']);
            exit();
        }

        $daoReuniao = new ReuniaoComissaoDAO();
        $reuniao = $daoReuniao->buscarPorId($reuniaoId);
        if (!$reuniao) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Reunião não encontrada.']);
            exit();
        }

        $processoId   = (int)$reuniao['processo_promocional_id'];
        $tipoComissao = $reuniao['tipo_comissao'];

        $conn->beginTransaction();

        try {
            // 1. Limpar impedimentos anteriores gravados nesta reunião
            $stmtDel = $conn->prepare("DELETE FROM criterio_falho WHERE reuniao_comissao_id = ?");
            $stmtDel->execute([$reuniaoId]);

            // 2. Gravar novos impedimentos
            $stmtIns = $conn->prepare("INSERT INTO criterio_falho (militar_id, reuniao_comissao_id, inciso_artigo_36, descricao_impedimento) VALUES (?, ?, ?, ?)");
            foreach ($impedimentos as $imp) {
                $stmtIns->execute([
                    (int)$imp['militar_id'],
                    $reuniaoId,
                    trim($imp['inciso_artigo_36']),
                    trim($imp['descricao_impedimento'])
                ]);
            }

            // 3. Restaurar todas as situações para APTO no snapshot do processo
            $stmtReset = $conn->prepare("UPDATE lq_snapshot SET situacao = 'APTO' WHERE processo_promocional_id = ?");
            $stmtReset->execute([$processoId]);

            // 4. Marcar ELIMINADO_T1 (Buscando de todo o processo e comissão)
            $conceitoDao = new ConceitoMoralDAO();
            $semConceito = $conceitoDao->buscarSemConceitoPorProcesso($processoId, $tipoComissao);
            $inaptosT1 = array_column($semConceito, 'militar_id');
            if (!empty($inaptosT1)) {
                $placeholders = implode(',', array_fill(0, count($inaptosT1), '?'));
                $stmtT1 = $conn->prepare("UPDATE lq_snapshot SET situacao = 'ELIMINADO_T1' WHERE processo_promocional_id = ? AND militar_id IN ($placeholders)");
                $stmtT1->execute(array_merge([$processoId], $inaptosT1));
            }

            // 5. Marcar ELIMINADO_T2
            $impedidosIds = array_unique(array_column($impedimentos, 'militar_id'));
            if (!empty($impedidosIds)) {
                $placeholders = implode(',', array_fill(0, count($impedidosIds), '?'));
                $stmtT2 = $conn->prepare("UPDATE lq_snapshot SET situacao = 'ELIMINADO_T2' WHERE processo_promocional_id = ? AND militar_id IN ($placeholders)");
                $stmtT2->execute(array_merge([$processoId], $impedidosIds));
            }

            // 6. Rolar a fila (puxar da reserva)
            $lqDao = new LqSnapshotDAO();
            $stmtLqEstado = $conn->prepare("SELECT id, dentro_do_limite, situacao, vagas_fixadas_id, tipo_promocao FROM lq_snapshot WHERE processo_promocional_id = ?");
            $stmtLqEstado->execute([$processoId]);
            $estadoLq = $stmtLqEstado->fetchAll(PDO::FETCH_ASSOC);

            foreach ($estadoLq as $item) {
                if ($item['dentro_do_limite'] == 1 && $item['situacao'] !== 'APTO') {
                    $lqDao->puxarProximoDaReserva($processoId, $item['vagas_fixadas_id'], $item['tipo_promocao']);
                }
            }

            // 7. Se for simulação, retorna dados e executa Rollback
            if ($acao === 'simular') {
                $fila = $lqDao->buscarFilaPorProcesso($processoId);
                $candidatosSimulados = [];
                foreach ($fila as $item) {
                    $qId = (int)$item['quadro_id'];
                    if ($tipoComissao === 'CPO' && in_array($qId, [1, 2, 3])) {
                        $candidatosSimulados[] = $item;
                    } elseif ($tipoComissao === 'CPP' && $qId === 4) {
                        $candidatosSimulados[] = $item;
                    }
                }

                $conn->rollBack();

                echo json_encode([
                    'success' => true,
                    'message' => 'Simulação calculada com sucesso.',
                    'candidatos' => $candidatosSimulados
                ]);
                exit();
            }

            // 8. Se for salvar definitivo, commit
            $conn->commit();

            $logger->info('Usuário gravou impedimentos legais do Tópico 2', [
                'id' => $_SESSION['id'] ?? null,
                'usuario' => $_SESSION['nome'] ?? 'Desconhecido',
                'email' => $_SESSION['email'] ?? null,
                'perfil' => $_SESSION['nivel_de_acesso'] ?? null,
                'sujeito' => $reuniaoId
            ]);

            echo json_encode(['success' => true, 'message' => 'Impedimentos gravados com sucesso!']);
            exit();
        } catch (Exception $e) {
            $conn->rollBack();
            throw $e;
        }
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()]);
}
