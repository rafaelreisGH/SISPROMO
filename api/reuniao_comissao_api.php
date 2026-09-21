<?php

/**
 * API: Gestão das Reuniões da Comissão (CPO e CPP).
 */

require_once __DIR__ . '/../Controllers/session_bootstrap.php';

ob_start();

require_once '../Controllers/nivel_gestor.php';
require_once '../vendor/autoload.php';
require_once '../Logger/LoggerFactory.php';

use App\DAO\ReuniaoComissaoDAO;
use App\DAO\ProcessoPromocionalDAO;

$dao      = new ReuniaoComissaoDAO();
$daoProc  = new ProcessoPromocionalDAO();
$logger   = LoggerFactory::createLogger();
$method   = $_SERVER['REQUEST_METHOD'];

try {
    // --- GET: Listagem por comissão ou busca por ID ---
    if ($method === 'GET') {
        if (isset($_GET['id'])) {
            $id = (int) $_GET['id'];
            $reuniao = $dao->buscarPorId($id);

            if (!$reuniao) {
                if (ob_get_length()) ob_clean();
                http_response_code(404);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'message' => 'Reunião não encontrada.']);
                exit();
            }

            if (ob_get_length()) ob_clean();
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => true, 'data' => $reuniao]);
            exit();
        }

        $comissao = isset($_GET['comissao']) ? strtoupper(trim((string)$_GET['comissao'])) : '';
        if (!in_array($comissao, ['CPO', 'CPP'])) {
            if (ob_get_length()) ob_clean();
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'Comissão inválida. Deve ser CPO ou CPP.']);
            exit();
        }

        $lista = $dao->buscarTodasPorComissao($comissao);
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => true, 'data' => $lista]);
        exit();
    }

    // --- POST: Cadastro de nova reunião ---
    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];

        $numeroAta           = isset($input['numero_ata']) ? trim((string)$input['numero_ata']) : '';
        $dataReuniao         = isset($input['data_reuniao']) ? trim((string)$input['data_reuniao']) : '';
        $tipoComissao        = isset($input['tipo_comissao']) ? strtoupper(trim((string)$input['tipo_comissao'])) : '';
        $processoId          = !empty($input['processo_promocional_id']) ? (int) $input['processo_promocional_id'] : null;
        $observacao          = isset($input['observacao']) && $input['observacao'] !== null && trim((string)$input['observacao']) !== '' ? trim((string)$input['observacao']) : null;
        $relatorioId         = !empty($input['relatorio_corregedoria_id']) ? (int) $input['relatorio_corregedoria_id'] : null;

        if (empty($numeroAta)) {
            if (ob_get_length()) ob_clean();
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'O número da ata é obrigatório.']);
            exit();
        }

        if (empty($dataReuniao) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataReuniao)) {
            if (ob_get_length()) ob_clean();
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'Data da reunião inválida. Utilize o formato AAAA-MM-DD.']);
            exit();
        }

        if (!in_array($tipoComissao, ['CPO', 'CPP'])) {
            if (ob_get_length()) ob_clean();
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'Tipo de comissão inválido (deve ser CPO ou CPP).']);
            exit();
        }

        if (empty($processoId)) {
            if (ob_get_length()) ob_clean();
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'O processo promocional é obrigatório.']);
            exit();
        }
        $processoExistente = $daoProc->buscarPorId($processoId);
        if (!$processoExistente || $processoExistente['status'] === 'CONCLUIDO') {
            if (ob_get_length()) ob_clean();
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'Processo promocional inválido ou já concluído.']);
            exit();
        }

        $novoId = $dao->criarSessao(
            $numeroAta,
            $dataReuniao,
            $tipoComissao,
            $processoId,
            $observacao,
            $relatorioId
        );

        $logger->info('Usuário criou reunião da comissão', [
            'id'      => $_SESSION['id']     ?? null,
            'usuario' => $_SESSION['nome']   ?? 'Desconhecido',
            'email'   => $_SESSION['email']  ?? null,
            'perfil'  => $_SESSION['nivel_de_acesso'] ?? null,
            'sujeito' => $novoId,
        ]);

        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true,
            'message' => 'Reunião criada com sucesso!',
            'id' => $novoId
        ]);
        exit();
    }

    // --- PUT: Atualização cadastral ou alteração de status ---
    if ($method === 'PUT') {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : null;
        if (!$id) {
            if (ob_get_length()) ob_clean();
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'ID da reunião não informado.']);
            exit();
        }

        $reuniaoExistente = $dao->buscarPorId($id);
        if (!$reuniaoExistente) {
            if (ob_get_length()) ob_clean();
            http_response_code(404);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'Reunião não encontrada.']);
            exit();
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? [];

        // Se for alteração direta de status
        if (isset($input['status'])) {
            $novoStatus = strtoupper(trim((string)$input['status']));
            if (!in_array($novoStatus, ['PREPARACAO', 'EM_ANDAMENTO', 'CONCLUIDA'])) {
                if (ob_get_length()) ob_clean();
                http_response_code(400);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'message' => 'Status inválido.']);
                exit();
            }

            $dao->alterarStatus($id, $novoStatus);

            $logger->info('Usuário alterou status da reunião da comissão', [
                'id'      => $_SESSION['id']     ?? null,
                'usuario' => $_SESSION['nome']   ?? 'Desconhecido',
                'email'   => $_SESSION['email']  ?? null,
                'perfil'  => $_SESSION['nivel_de_acesso'] ?? null,
                'sujeito' => $id,
            ]);

            if (ob_get_length()) ob_clean();
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => true, 'message' => 'Status da reunião atualizado com sucesso.']);
            exit();
        }

        // Atualização de dados cadastrais (seguro contra null)
        $numeroAta   = isset($input['numero_ata']) && trim((string)$input['numero_ata']) !== ''
            ? trim((string)$input['numero_ata'])
            : ($reuniaoExistente['numero_ata'] ?? '');

        $dataReuniao = isset($input['data_reuniao']) && trim((string)$input['data_reuniao']) !== ''
            ? trim((string)$input['data_reuniao'])
            : ($reuniaoExistente['data_reuniao'] ?? '');

        $observacao  = array_key_exists('observacao', $input)
            ? (trim((string)$input['observacao']) !== '' ? trim((string)$input['observacao']) : null)
            : ($reuniaoExistente['observacao'] ?? null);

        $relatorioId = !empty($input['relatorio_corregedoria_id']) ? (int) $input['relatorio_corregedoria_id'] : null;

        if (empty($numeroAta)) {
            if (ob_get_length()) ob_clean();
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'O número da ata é obrigatório.']);
            exit();
        }

        if (empty($dataReuniao) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataReuniao)) {
            if (ob_get_length()) ob_clean();
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'Data da reunião inválida.']);
            exit();
        }

        $dao->atualizarSessao($id, $numeroAta, $dataReuniao, $observacao, $relatorioId);

        $logger->info('Usuário editou dados da reunião da comissão', [
            'id'      => $_SESSION['id']     ?? null,
            'usuario' => $_SESSION['nome']   ?? 'Desconhecido',
            'email'   => $_SESSION['email']  ?? null,
            'perfil'  => $_SESSION['nivel_de_acesso'] ?? null,
            'sujeito' => $id,
        ]);

        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => true, 'message' => 'Dados da reunião atualizados com sucesso.']);
        exit();
    }

    // --- DELETE: Exclusão de reunião ---
    if ($method === 'DELETE') {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : null;
        if (!$id) {
            if (ob_get_length()) ob_clean();
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'ID da reunião não informado para exclusão.']);
            exit();
        }

        $reuniaoExistente = $dao->buscarPorId($id);
        if (!$reuniaoExistente) {
            if (ob_get_length()) ob_clean();
            http_response_code(404);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'Reunião não encontrada.']);
            exit();
        }

        if ($reuniaoExistente['status'] === 'CONCLUIDA') {
            if (ob_get_length()) ob_clean();
            http_response_code(403);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'Não é permitido excluir uma reunião já CONCLUÍDA.']);
            exit();
        }

        $dao->excluirSessao($id);

        $logger->info('Usuário excluiu reunião da comissão', [
            'id'      => $_SESSION['id']     ?? null,
            'usuario' => $_SESSION['nome']   ?? 'Desconhecido',
            'email'   => $_SESSION['email']  ?? null,
            'perfil'  => $_SESSION['nivel_de_acesso'] ?? null,
            'sujeito' => $id,
        ]);

        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => true, 'message' => 'Reunião excluída com sucesso.']);
        exit();
    }

    if (ob_get_length()) ob_clean();
    http_response_code(405);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
} catch (Throwable $e) {
    if (ob_get_length()) ob_clean();
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()]);
}
