<?php

/**
 * API: Inativação de Militar e Reordenação da Fila de Antiguidade.
 * 
 * Método: POST com JSON body { militar_id: 123 }
 */

require_once __DIR__ . '/../Controllers/session_bootstrap.php';

ob_start();

require_once '../Controllers/nivel_gestor.php';
require_once '../vendor/autoload.php';
require_once '../Logger/LoggerFactory.php';

use App\DAO\MilitarDAO;

if (ob_get_length()) ob_clean();
header('Content-Type: application/json; charset=utf-8');

try {
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    $militarId = isset($input['militar_id']) ? (int)$input['militar_id'] : 0;

    if (!$militarId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID do militar não informado.']);
        exit();
    }

    $militarDao = new MilitarDAO();
    $resultado = $militarDao->inativarEReordenar($militarId);
    $militar = $resultado['militar'];

    if ($resultado['ja_inativo']) {
        echo json_encode([
            'success' => true,
            'message' => "O militar {$militar['posto_grad_mil']} {$militar['nome']} já estava inativo."
        ]);
        exit();
    }

    // Auditoria / Log do sistema
    $logger = LoggerFactory::createLogger();
    $logger->info('Usuário inativou militar', [
        'id'       => $_SESSION['id'] ?? null,
        'usuario'  => $_SESSION['nome'] ?? 'Desconhecido',
        'email'    => $_SESSION['email'] ?? null,
        'perfil'   => $_SESSION['nivel_de_acesso'] ?? null,
        'sujeito'  => "{$militar['posto_grad_mil']} {$militar['nome']} (ID: {$militarId})"
    ]);

    echo json_encode([
        'success' => true,
        'message' => "Militar {$militar['posto_grad_mil']} {$militar['nome']} inativado com sucesso!"
    ]);
    exit();
} catch (Throwable $e) {
    if (ob_get_length()) ob_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno ao inativar militar: ' . $e->getMessage()]);
}
