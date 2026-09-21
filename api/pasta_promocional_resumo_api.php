<?php

/**
 * API: Consulta do Resumo da Pasta Promocional de um Militar (Somente Leitura).
 * Método: GET ?militar_id=123&reuniao_id=456
 */

require_once __DIR__ . '/../Controllers/session_bootstrap.php';

ob_start();

require_once '../Controllers/nivel_gestor.php';
require_once '../vendor/autoload.php';
require_once '../ConexaoDB/conexao.php';

use App\DAO\PastaPromocionalDAO;
use App\DAO\ReuniaoComissaoDAO;
use App\DAO\MilitarDAO;
use App\DAO\QuadroAcessoDAO;

if (ob_get_length()) ob_clean();
header('Content-Type: application/json; charset=utf-8');

try {
    $militarId = isset($_GET['militar_id']) ? (int)$_GET['militar_id'] : 0;
    $reuniaoId = isset($_GET['reuniao_id']) ? (int)$_GET['reuniao_id'] : 0;

    if (!$militarId || !$reuniaoId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Parâmetros militar_id e reuniao_id são obrigatórios.']);
        exit();
    }

    $daoReuniao = new ReuniaoComissaoDAO();
    $reuniao = $daoReuniao->buscarPorId($reuniaoId);
    if (!$reuniao) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Reunião não encontrada.']);
        exit();
    }

    $daoMilitar = new MilitarDAO();
    $militar = $daoMilitar->buscarPorId($militarId);
    if (!$militar) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Militar não encontrado.']);
        exit();
    }

    $processoId = (int)$reuniao['processo_promocional_id'];
    $daoPasta = new PastaPromocionalDAO();
    $pasta = $daoPasta->buscarPastaPorMilitarEProcesso($militarId, $processoId);

    $nomeQuadro = QuadroAcessoDAO::formatarNomeQuadro($militar['quadro_id']);

    if (!$pasta) {
        echo json_encode([
            'success'          => true,
            'pasta_encontrada' => false,
            'militar' => [
                'id'     => (int)$militar['id'],
                'nome'   => $militar['nome'],
                'posto'  => $militar['posto_grad_mil'],
                'quadro' => $nomeQuadro
            ],
            'processo' => [
                'semestre' => $reuniao['processo_semestre'],
                'ano'      => $reuniao['processo_ano']
            ],
            'message' => 'Nenhuma pasta promocional cadastrada para este militar no processo vigente.'
        ]);
        exit();
    }

    $documentos = $daoPasta->buscarDocumentosDaPasta((int)$pasta['id']);

    // Contadores
    $totalEntregues = 0;
    $totalPendentes = 0;
    foreach ($documentos as $d) {
        if (!empty($d['doc_promo_url']) && (int)$d['doc_status_id'] === 1) {
            $totalEntregues++;
        } else {
            $totalPendentes++;
        }
    }

    echo json_encode([
        'success'          => true,
        'pasta_encontrada' => true,
        'pasta' => [
            'id'       => (int)$pasta['id'],
            'semestre' => $pasta['semestre_processo_promocional'] ?? $reuniao['processo_semestre'],
            'ano'      => $pasta['ano_processo_promocional'] ?? $reuniao['processo_ano']
        ],
        'militar' => [
            'id'     => (int)$militar['id'],
            'nome'   => $militar['nome'],
            'posto'  => $militar['posto_grad_mil'],
            'quadro' => $nomeQuadro
        ],
        'totais' => [
            'total'     => count($documentos),
            'entregues' => $totalEntregues,
            'pendentes' => $totalPendentes
        ],
        'documentos' => $documentos
    ]);
    exit();
} catch (Throwable $e) {
    if (ob_get_length()) ob_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno ao consultar pasta: ' . $e->getMessage()]);
}
