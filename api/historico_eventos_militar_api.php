<?php

/**
 * API: Consulta do Histórico de Atos Administrativos do Militar.
 * Método: GET com query string ?militar_id=123
 */

require_once __DIR__ . '/../Controllers/session_bootstrap.php';

ob_start();

require_once '../Controllers/nivel_gestor.php';
require_once '../vendor/autoload.php';

use App\DAO\MilitarDAO;
use App\DAO\MilitarHistoricoEventosDAO;

if (ob_get_length()) ob_clean();
header('Content-Type: application/json; charset=utf-8');

try {
    $militarId = isset($_GET['militar_id']) ? (int)$_GET['militar_id'] : 0;

    if (!$militarId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID do militar não informado.']);
        exit();
    }

    $militarDao = new MilitarDAO();
    $militar = $militarDao->buscarPorId($militarId);

    if (!$militar) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Militar não encontrado.']);
        exit();
    }

    $historicoDao = new MilitarHistoricoEventosDAO();
    $eventos = $historicoDao->buscarHistoricoPorMilitar($militarId);

    echo json_encode([
        'success' => true,
        'militar' => [
            'id'             => $militar['id'],
            'nome'           => $militar['nome'],
            'posto_grad_mil' => $militar['posto_grad_mil'],
            'status'         => $militar['status'],
            'is_excedente'   => (int)($militar['is_excedente'] ?? 0)
        ],
        'eventos' => $eventos
    ]);
    exit();

} catch (Throwable $e) {
    if (ob_get_length()) ob_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno ao consultar histórico: ' . $e->getMessage()]);
}
