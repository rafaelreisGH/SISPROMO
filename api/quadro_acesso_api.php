<?php

/**
 * API: Gestão e Homologação do Quadro de Acesso e Proposta de Promoção.
 * GET ?reuniao_id=123 (&tipo=proposta para dados da 3ª Pauta)
 */

require_once __DIR__ . '/../Controllers/session_bootstrap.php';

ob_start();

require_once '../Controllers/nivel_gestor.php';
require_once '../vendor/autoload.php';
require_once '../Logger/LoggerFactory.php';

use App\DAO\QuadroAcessoDAO;
use App\DAO\ReuniaoComissaoDAO;

if (ob_get_length()) ob_clean();
header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];
$logger = LoggerFactory::createLogger();

try {
    $dao = new QuadroAcessoDAO();

    if ($method === 'GET') {
        $reuniaoId = isset($_GET['reuniao_id']) ? (int)$_GET['reuniao_id'] : 0;
        $tipoConsulta = $_GET['tipo'] ?? 'qa';

        if (!$reuniaoId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID da reunião inválido.']);
            exit();
        }

        if ($tipoConsulta === 'proposta') {
            $dadosProposta = $dao->buscarDadosProposta($reuniaoId);
            echo json_encode(array_merge(['success' => true], $dadosProposta));
            exit();
        }

        $dadosQA = $dao->buscarDadosQA($reuniaoId);
        echo json_encode(array_merge(['success' => true], $dadosQA));
        exit();
    }

    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $acao             = $input['acao'] ?? '';
        $reuniaoId        = isset($input['reuniao_id']) ? (int)$input['reuniao_id'] : 0;
        $notasMerecimento = $input['notas_merecimento'] ?? [];

        if (!$reuniaoId || $acao !== 'homologar') {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Ação ou parâmetros inválidos.']);
            exit();
        }

        $daoReuniao = new ReuniaoComissaoDAO();
        $reuniao = $daoReuniao->buscarPorId($reuniaoId);
        if (!$reuniao) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Reunião não encontrada.']);
            exit();
        }

        if ($reuniao['status'] === 'CONCLUIDA') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Esta reunião está CONCLUÍDA e não permite novas homologações.']);
            exit();
        }

        $ok = $dao->homologarQA($reuniaoId, $notasMerecimento);

        $logger->info('Usuário homologou Quadro de Acesso (2ª Pauta)', [
            'id'      => $_SESSION['id']     ?? null,
            'usuario' => $_SESSION['nome']   ?? 'Desconhecido',
            'email'   => $_SESSION['email']  ?? null,
            'perfil'  => $_SESSION['nivel_de_acesso'] ?? null,
            'sujeito' => $reuniaoId
        ]);

        echo json_encode(['success' => true, 'message' => 'Quadro de Acesso homologado com sucesso!']);
        exit();
    }

    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
} catch (Throwable $e) {
    if (ob_get_length()) ob_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno no QA: ' . $e->getMessage()]);
}
