<?php

/**
 * API: Salva o texto da ata final no banco de dados.
 * Método: POST com JSON body { reuniao_id, texto_ata }
 */

require_once __DIR__ . '/../Controllers/session_bootstrap.php';
header('Content-Type: application/json');

require_once '../Controllers/nivel_gestor.php';
require_once '../vendor/autoload.php';
require_once '../ConexaoDB/conexao.php';
require_once '../Logger/LoggerFactory.php';

try {
    $input = json_decode(file_get_contents('php://input'), true);

    $reuniaoId = isset($input['reuniao_id']) ? (int)$input['reuniao_id'] : 0;
    $textoAta  = $input['texto_ata'] ?? null;

    if (!$reuniaoId || $textoAta === null) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Parâmetros obrigatórios ausentes.']);
        exit();
    }

    $conn = Conexao::getConexao();

    // 1. Validação de Segurança: Verifica o status da reunião antes de permitir a alteração
    $stmtCheck = $conn->prepare("SELECT status FROM reuniao_comissao WHERE id_reuniao_comissao = :id");
    $stmtCheck->execute([':id' => $reuniaoId]);
    $status = $stmtCheck->fetchColumn();

    if (!$status) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Reunião não encontrada.']);
        exit();
    }

    if ($status === 'CONCLUIDA') {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Operação negada: Esta reunião já foi CONCLUÍDA e o texto da ata encontra-se bloqueado para alterações.'
        ]);
        exit();
    }

    // 2. Persistência do texto da ata
    $stmt = $conn->prepare("UPDATE reuniao_comissao SET texto_ata_final = :texto WHERE id_reuniao_comissao = :id");
    $ok = $stmt->execute([
        ':texto' => $textoAta,
        ':id' => $reuniaoId
    ]);

    if ($ok) {
        $logger = LoggerFactory::createLogger();
        $logger->info('Usuário atualizou texto da ata final da reunião', [
            'id' => $_SESSION['id'] ?? null,
            'usuario' => $_SESSION['nome'] ?? 'Desconhecido',
            'email' => $_SESSION['email'] ?? null,
            'perfil' => $_SESSION['nivel_de_acesso'] ?? null,
            'sujeito' => $reuniaoId
        ]);

        echo json_encode(['success' => true, 'message' => 'Ata salva com sucesso!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Falha ao salvar a ata no banco de dados.']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno ao salvar ata: ' . $e->getMessage()]);
}
