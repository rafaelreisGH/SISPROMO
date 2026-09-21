<?php

/**
 * API: Gestão de Conceito Moral para o Tópico 1 da Ata de Promoção.
 */

require_once __DIR__ . '/../Controllers/session_bootstrap.php';
header('Content-Type: application/json');

require_once '../Controllers/nivel_gestor.php';
require_once '../vendor/autoload.php';
require_once '../ConexaoDB/conexao.php';
require_once '../Logger/LoggerFactory.php';

use App\DAO\ConceitoMoralDAO;
use App\DAO\ReuniaoComissaoDAO;

try {
    $input = json_decode(file_get_contents('php://input'), true);

    $acao       = $input['acao']       ?? null;
    $militarId  = isset($input['militar_id'])  ? (int) $input['militar_id']  : null;
    $reuniaoId  = isset($input['reuniao_id'])  ? (int) $input['reuniao_id']  : null;
    $parecer    = $input['parecer']    ?? null;

    if (!$acao || !$militarId || !$reuniaoId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Parâmetros obrigatórios ausentes.']);
        exit();
    }

    $daoReuniao = new ReuniaoComissaoDAO();
    $reuniao = $daoReuniao->buscarPorId($reuniaoId);
    if (!$reuniao) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Reunião não encontrada.']);
        exit();
    }

    $dao    = new ConceitoMoralDAO();
    $logger = LoggerFactory::createLogger();

    if ($acao === 'marcar') {
        $ok = $dao->marcarSemConceitoMoral($militarId, $reuniaoId, $parecer);
        if ($ok) {
            $logger->info('Usuário marcou militar como sem conceito moral', [
                'id'      => $_SESSION['id']               ?? null,
                'usuario' => $_SESSION['nome']             ?? 'Desconhecido',
                'email'   => $_SESSION['email']            ?? null,
                'perfil'  => $_SESSION['nivel_de_acesso']  ?? null,
                'sujeito' => $militarId
            ]);
        }
        echo json_encode(['success' => $ok, 'message' => $ok ? 'Militar marcado como sem conceito moral.' : 'Erro ao salvar.']);
    } elseif ($acao === 'desmarcar') {
        // Desmarca em todas as sessões deste mesmo processo e comissão
        $ok = $dao->desmarcarPendenciaNoProcesso($militarId, (int)$reuniao['processo_promocional_id'], $reuniao['tipo_comissao']);
        if ($ok) {
            $logger->info('Usuário restaurou conceito moral de militar', [
                'id'      => $_SESSION['id']               ?? null,
                'usuario' => $_SESSION['nome']             ?? 'Desconhecido',
                'email'   => $_SESSION['email']            ?? null,
                'perfil'  => $_SESSION['nivel_de_acesso']  ?? null,
                'sujeito' => $militarId
            ]);
        }
        echo json_encode(['success' => $ok, 'message' => $ok ? 'Marcação removida com sucesso.' : 'Erro ao remover.']);
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Ação inválida.']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()]);
}
