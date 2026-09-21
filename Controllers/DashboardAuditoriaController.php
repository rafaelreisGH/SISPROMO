<?php

require_once 'controle_de_sessao.php';

// Segurança Obrigatória: Apenas administradores (perfil 2)
if (!isset($_SESSION['nivel_de_acesso']) || $_SESSION['nivel_de_acesso'] != 2) {
    http_response_code(403);
    echo json_encode(['erro' => 'Acesso negado. Apenas administradores podem acessar esta API.']);
    exit();
}

require_once __DIR__ . '/../src/DAO/DashboardAuditoriaDAO.php';

use App\DAO\DashboardAuditoriaDAO;

header('Content-Type: application/json; charset=utf-8');

$dao = new DashboardAuditoriaDAO();
$acao = $_GET['acao'] ?? '';
$dias = isset($_GET['dias']) ? (int)$_GET['dias'] : 30;

try {
    switch ($acao) {
        case 'indicadores':
            $dados = $dao->getIndicadoresPrincipais($dias);
            echo json_encode($dados);
            break;
            
        case 'monitoramento':
            $dados = $dao->getMonitoramentoUso($dias);
            echo json_encode($dados);
            break;
            
        case 'auditoria':
            $dados = $dao->getAuditoriaRastreabilidade($dias);
            echo json_encode($dados);
            break;
            
        case 'seguranca':
            $dados = $dao->getSegurancaAcesso($dias);
            echo json_encode($dados);
            break;
            
        case 'feed':
            $nivel = $_GET['nivel'] ?? null;
            $gestor = $_GET['gestor'] ?? null;
            $keyword = $_GET['keyword'] ?? null;
            $dados = $dao->getFeed($dias, $nivel, $gestor, $keyword);
            echo json_encode($dados);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['erro' => 'Ação inválida.']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro interno ao processar a requisição: ' . $e->getMessage()]);
}
