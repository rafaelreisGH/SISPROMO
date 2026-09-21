<?php

/**
 * API: Vincula ou desvincula um Relatório da Corregedoria a uma Reunião de Comissão.
 *
 * Método: POST com JSON body { reuniao_id, relatorio_id }
 */

require_once __DIR__ . '/../Controllers/session_bootstrap.php';
header('Content-Type: application/json');

require_once '../Controllers/nivel_gestor.php';
require_once '../vendor/autoload.php';
require_once __DIR__ . '/../Logger/LoggerFactory.php';

use App\DAO\ReuniaoComissaoDAO;

try {
    $input = json_decode(file_get_contents('php://input'), true);

    $reuniaoId   = isset($input['reuniao_id'])  ? (int) $input['reuniao_id'] : null;
    $relatorioId = array_key_exists('relatorio_id', $input)
        ? ($input['relatorio_id'] !== null ? (int) $input['relatorio_id'] : null)
        : 'AUSENTE';

    if (!$reuniaoId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'O campo reuniao_id é obrigatório.']);
        exit();
    }

    if ($relatorioId === 'AUSENTE') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'O campo relatorio_id é obrigatório (envie null para desvincular).']);
        exit();
    }

    $dao    = new ReuniaoComissaoDAO();
    $logger = LoggerFactory::createLogger();

    if ($relatorioId === null) {
        // Desvincular
        $dao->desvincularRelatorio($reuniaoId);

        $logger->info('Usuário desvinculou relatório da reunião', [
            'id'      => $_SESSION['id']     ?? null,
            'usuario' => $_SESSION['nome']   ?? 'Desconhecido',
            'email'   => $_SESSION['email']  ?? null,
            'perfil'  => $_SESSION['nivel_de_acesso'] ?? null,
            'sujeito' => $reuniaoId
        ]);

        echo json_encode(['success' => true, 'message' => 'Relatório desvinculado com sucesso.']);
    } else {
        // Vincular
        $dao->vincularRelatorio($reuniaoId, $relatorioId);

        $logger->info('Usuário vinculou relatório à reunião', [
            'id'      => $_SESSION['id']     ?? null,
            'usuario' => $_SESSION['nome']   ?? 'Desconhecido',
            'email'   => $_SESSION['email']  ?? null,
            'perfil'  => $_SESSION['nivel_de_acesso'] ?? null,
            'sujeito' => $reuniaoId
        ]);

        echo json_encode(['success' => true, 'message' => 'Relatório vinculado com sucesso.']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno ao processar vínculo: ' . $e->getMessage()]);
}
