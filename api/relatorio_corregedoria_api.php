<?php

/**
 * API: Gestão dos Relatórios da Corregedoria (entidade independente).
 */

require_once __DIR__ . '/../Controllers/session_bootstrap.php';
header('Content-Type: application/json');

require_once '../Controllers/nivel_gestor.php';
require_once '../vendor/autoload.php';

use App\DAO\RelatorioCorregedoriaDAO;

$dao    = new RelatorioCorregedoriaDAO();
$method = $_SERVER['REQUEST_METHOD'];

try {
    // --- GET: Listagem de todos os relatórios ---
    if ($method === 'GET') {
        $lista = $dao->buscarTodos();
        echo json_encode(['success' => true, 'data' => $lista]);
        exit();
    }

    // --- POST: Cadastro de novo relatório ---
    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);

        $anoRaw     = $input['ano'] ?? null;
        $semestre   = isset($input['semestre']) ? (int) $input['semestre'] : null;
        $urlArquivo = $input['url_arquivo'] ?? null;
        $descricao  = $input['descricao']   ?? null;

        $ano = filter_var($anoRaw, FILTER_VALIDATE_INT, [
            "options" => ["min_range" => 2000, "max_range" => 2100]
        ]);

        if ($ano === false) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Ano inválido.']);
            exit();
        }

        if (!in_array($semestre, [1, 2])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Semestre inválido.']);
            exit();
        }

        if (!$urlArquivo || !filter_var($urlArquivo, FILTER_VALIDATE_URL)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'URL inválida.']);
            exit();
        }

        $existente = $dao->buscarPorPeriodo($ano, $semestre);
        if ($existente) {
            http_response_code(409);
            echo json_encode([
                'success' => false,
                'message' => "Já existe um relatório para o {$semestre}º semestre de {$ano}."
            ]);
            exit();
        }

        $novoId = $dao->salvar($ano, $semestre, $urlArquivo, $descricao);
        echo json_encode(['success' => true, 'message' => 'Relatório cadastrado com sucesso!', 'id' => $novoId]);
        exit();
    }

    // --- PUT: Atualização de URL/Descrição ---
    if ($method === 'PUT') {
        $id    = isset($_GET['id']) ? (int) $_GET['id'] : null;
        $input = json_decode(file_get_contents('php://input'), true);

        $novaUrl   = $input['url_arquivo'] ?? null;
        $descricao = $input['descricao']   ?? null;

        if (!$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID do relatório não informado.']);
            exit();
        }

        if (!$novaUrl || !filter_var($novaUrl, FILTER_VALIDATE_URL)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'URL inválida ou ausente.']);
            exit();
        }

        // Verifica se o relatório existe antes de tentar atualizar
        $relatorioExistente = $dao->buscarPorId($id);
        if (!$relatorioExistente) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Relatório não encontrado no sistema.']);
            exit();
        }

        $dao->atualizar($id, $novaUrl, $descricao);
        echo json_encode(['success' => true, 'message' => 'Relatório atualizado com sucesso.']);
        exit();
    }

    // --- DELETE: Exclusão de um relatório ---
    if ($method === 'DELETE') {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : null;

        if (!$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID do relatório não informado para exclusão.']);
            exit();
        }

        $relatorioExistente = $dao->buscarPorId($id);
        if (!$relatorioExistente) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Relatório não encontrado no sistema.']);
            exit();
        }

        $dao->excluir($id);
        echo json_encode(['success' => true, 'message' => 'Relatório excluído com sucesso.']);
        exit();
    }

    // Caso o método não seja GET, POST, PUT ou DELETE
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
} catch (Exception $e) {
    http_response_code(500);
    error_log("Erro na API RelatorioCorregedoria: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno no servidor.']);
}
