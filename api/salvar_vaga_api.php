<?php
require_once __DIR__ . '/../Controllers/session_bootstrap.php';
header('Content-Type: application/json');

require_once '../Controllers/nivel_gestor.php'; // Proteção de acesso
require_once '../vendor/autoload.php';
require_once '../Logger/LoggerFactory.php';

use App\DAO\GestaoVagasFixadasDAO;

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Método de requisição inválido.");
    }

    $quadroId = isset($_POST['quadro_id']) ? (int)$_POST['quadro_id'] : 0;
    $nomeAgrupamento = isset($_POST['nome_do_agrupamento']) ? trim($_POST['nome_do_agrupamento']) : '';
    $quantidade = isset($_POST['quantidade']) ? (int)$_POST['quantidade'] : -1;

    // Auditoria
    $logger = LoggerFactory::createLogger();
    $usuario = $_SESSION['nome'] ?? 'Usuário Desconhecido';
    $userId = $_SESSION['id'] ?? null;

    if ($quadroId <= 0 || empty($nomeAgrupamento) || $quantidade < 0) {
        throw new Exception("Dados inválidos fornecidos para atualização de vagas.");
    }

    $dao = new GestaoVagasFixadasDAO();
    $sucesso = $dao->salvarLimiteLegal($quadroId, $nomeAgrupamento, $quantidade);

    if (!$sucesso) {
        throw new Exception("Falha ao salvar as vagas no banco de dados.");
    }

    // Grava log com sucesso
    $logger->info("Limite de vagas legal atualizado com sucesso via API.", [
        'usuario_id' => $userId,
        'usuario' => $usuario,
        'quadro_id' => $quadroId,
        'agrupamento' => $nomeAgrupamento,
        'nova_quantidade' => $quantidade
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Vagas atualizadas com sucesso.'
    ]);

} catch (Exception $e) {
    // Grava log de erro se logger foi inicializado
    if (isset($logger)) {
        $logger->error("Erro na API de salvar vagas: " . $e->getMessage(), [
            'usuario_id' => $userId ?? null,
            'usuario' => $usuario ?? 'N/A',
            'post_data' => $_POST
        ]);
    }
    
    // Retorna erro estruturado
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
