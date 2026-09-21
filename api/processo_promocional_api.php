<?php

require_once '../ConexaoDB/conexao.php';
require_once '../src/DAO/ProcessoPromocionalDAO.php';
require_once '../src/DAO/LqSnapshotDAO.php';
require_once '../src/DAO/PastaPromocionalDAO.php';
require_once '../Logger/LoggerFactory.php';

use App\DAO\ProcessoPromocionalDAO;
use App\DAO\LqSnapshotDAO;
use App\DAO\PastaPromocionalDAO;

require_once __DIR__ . '/../Controllers/session_bootstrap.php';
header('Content-Type: application/json');

$logger = LoggerFactory::createLogger();
$dao = new ProcessoPromocionalDAO();
$lqDao = new LqSnapshotDAO();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? null;
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

function getContext($sujeito = null) {
    return [
        'id' => $_SESSION['id'] ?? null,
        'usuario' => $_SESSION['nome'] ?? 'Sistema',
        'email' => $_SESSION['email'] ?? '',
        'perfil' => $_SESSION['nivel_de_acesso'] ?? '',
        'sujeito' => $sujeito
    ];
}

try {
    switch ($method) {
        case 'GET':
            if ($id) {
                if (isset($_GET['action']) && $_GET['action'] === 'buscar_lq') {
                    $fila = $lqDao->buscarFilaPorProcesso($id);
                    jsonResponse($fila);
                } else {
                    $processo = $dao->buscarPorId($id);
                    if ($processo) {
                        jsonResponse($processo);
                    } else {
                        jsonResponse(['error' => 'Processo não encontrado'], 404);
                    }
                }
            } else {
                // Lista todos os processos (sem filtro por comissão — processo é unificado)
                $processos = $dao->listarTodos();
                jsonResponse($processos);
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            // Valida os campos obrigatórios (tipo_comissao removido — processo é unificado)
            if (!isset($data['ano'], $data['semestre'], $data['data_promocao'])) {
                jsonResponse(['error' => 'Dados incompletos. Informe: ano, semestre, data_promocao'], 400);
            }

            $novoId = $dao->criar(
                (int)$data['ano'],
                (int)$data['semestre'],
                $data['data_promocao']
            );

            $logger->info("Usuário criou novo processo promocional", getContext($novoId));
            jsonResponse(['success' => true, 'id' => $novoId], 201);
            break;

        case 'PUT':
            if (!$id) {
                jsonResponse(['error' => 'ID não informado'], 400);
            }

            if ($action === 'gerar_lq') {
                $success = $lqDao->gerarSnapshot($id);
                if ($success) {
                    $dao->alterarStatus($id, 'LQ_PUBLICADO');
                    
                    // Provisionar e purgar pastas promocionais
                    $pastaDao = new PastaPromocionalDAO();
                    $resProvisionamento = $pastaDao->provisionarPastas($id);
                    $orfasRemovidas = $pastaDao->limparPastasOrfas($id);
                    
                    $logger->info("Usuário gerou LQ para processo promocional", array_merge(getContext($id), [
                        'pastas_criadas' => $resProvisionamento['criadas'],
                        'pastas_existentes' => $resProvisionamento['ja_existiam'],
                        'pastas_orfas_removidas' => $orfasRemovidas
                    ]));
                    
                    jsonResponse(['success' => true]);
                } else {
                    jsonResponse(['error' => 'Falha ao gerar LQ'], 500);
                }
            } elseif ($action === 'simular_lq') {
                $fila = $lqDao->gerarSnapshotSimulado($id);
                $logger->info("Usuário realizou simulação do LQ para processo promocional", getContext($id));
                jsonResponse(['success' => true, 'dados' => $fila]);
            } elseif ($action === 'alterar_status') {
                $data = json_decode(file_get_contents('php://input'), true);
                if (!isset($data['status'])) {
                    jsonResponse(['error' => 'Status não informado'], 400);
                }
                $dao->alterarStatus($id, $data['status']);
                $logger->info("Usuário alterou status de processo promocional para " . $data['status'], getContext($id));
                jsonResponse(['success' => true]);
            } else {
                jsonResponse(['error' => 'Ação inválida'], 400);
            }
            break;

        case 'DELETE':
            if (!$id) {
                jsonResponse(['error' => 'ID não informado'], 400);
            }

            $processo = $dao->buscarPorId($id);
            if (!$processo) {
                jsonResponse(['error' => 'Processo não encontrado'], 404);
            }

            try {
                $dao->deletar($id);
                $logger->info("Usuário excluiu processo promocional", getContext($id));
                jsonResponse(['success' => true]);
            } catch (PDOException $e) {
                // Erro de chave estrangeira (1451 ou integridade de chave estrangeira Q23000)
                if ($e->getCode() == 23000 || str_contains($e->getMessage(), '1451')) {
                    jsonResponse(['error' => 'Não é possível excluir este processo promocional, pois existem reuniões de comissão vinculadas a ele.'], 409);
                }
                throw $e;
            }
            break;

        default:
            jsonResponse(['error' => 'Método não suportado'], 405);
            break;
    }
} catch (Exception $e) {
    $logger->error("Erro na API de Processo Promocional: " . $e->getMessage(), getContext($id));
    jsonResponse(['error' => 'Erro interno do servidor', 'details' => $e->getMessage()], 500);
}
