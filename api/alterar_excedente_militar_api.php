<?php

/**
 * API: Alteração da Condição de Excedente do Militar (0 = Efetivo Regular, 1 = Excedente)
 * com Registro de Ato Administrativo Imutável.
 * 
 * Método: POST com JSON body:
 * {
 *   militar_id: 123,
 *   is_excedente: 1|0,
 *   tipo_publicacao: 'DOE'|'BGE'|'PORTARIA'|'DECRETO'|...,
 *   numero_publicacao: 'Portaria nº 042/DGP/2026',
 *   data_vigencia: '2026-08-02',
 *   url_documento: 'https://...',
 *   observacao: '...'
 * }
 */

require_once __DIR__ . '/../Controllers/session_bootstrap.php';

ob_start();

require_once '../Controllers/nivel_gestor.php';
require_once '../vendor/autoload.php';
require_once '../Logger/LoggerFactory.php';

use App\DAO\MilitarDAO;
use App\DAO\MilitarHistoricoEventosDAO;

if (ob_get_length()) ob_clean();
header('Content-Type: application/json; charset=utf-8');

try {
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    $militarId   = isset($input['militar_id']) ? (int)$input['militar_id'] : 0;
    $isExcedente = isset($input['is_excedente']) ? (int)$input['is_excedente'] : -1;

    if (!$militarId || !in_array($isExcedente, [0, 1], true)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Parâmetros inválidos. Informe o militar_id e a condição is_excedente (0 ou 1).']);
        exit();
    }

    $militarDao = new MilitarDAO();
    $militar = $militarDao->buscarPorId($militarId);

    if (!$militar) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Militar não encontrado.']);
        exit();
    }

    // Metadados do ato administrativo
    $tipoPublicacao   = !empty($input['tipo_publicacao']) ? trim((string)$input['tipo_publicacao']) : null;
    $numeroPublicacao = !empty($input['numero_publicacao']) ? trim((string)$input['numero_publicacao']) : null;
    $dataVigencia     = !empty($input['data_vigencia']) ? trim((string)$input['data_vigencia']) : null;
    $urlDocumento     = !empty($input['url_documento']) ? trim((string)$input['url_documento']) : null;
    $observacao       = !empty($input['observacao']) ? trim((string)$input['observacao']) : null;

    $tipoEvento = ($isExcedente === 1) ? 'EXCEDENTE_ATIVADO' : 'EXCEDENTE_DESATIVADO';

    // Transação atômica: atualiza tabela `militar` e insere em `militar_historico_eventos`
    $conn = $militarDao->getConexao();
    $conn->beginTransaction();

    try {
        $militarDao->alternarExcedente($militarId, $isExcedente, $conn);

        $historicoDao = new MilitarHistoricoEventosDAO($conn);
        $historicoDao->registrarEvento([
            'militar_id'        => $militarId,
            'tipo_evento'       => $tipoEvento,
            'url_documento'     => $urlDocumento,
            'numero_publicacao' => $numeroPublicacao,
            'tipo_publicacao'   => $tipoPublicacao,
            'data_vigencia'     => $dataVigencia,
            'observacao'        => $observacao,
            'usuario_id'        => $_SESSION['id'] ?? null
        ], $conn);

        $conn->commit();
    } catch (Throwable $eTx) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        throw $eTx;
    }

    // Auditoria Monolog conforme padrão STIC CBMMT
    $logger = LoggerFactory::createLogger();
    $condicaoTexto = ($isExcedente === 1) ? 'Excedente' : 'Efetivo Regular';
    $atoInfo = $numeroPublicacao ? " [Ato: {$tipoPublicacao} {$numeroPublicacao}]" : " [Sem número de ato]";

    $logger->info("Usuário alterou condição do militar para {$condicaoTexto} com registro de ato administrativo", [
        'id'      => $_SESSION['id']     ?? null,
        'usuario' => $_SESSION['nome']   ?? 'Desconhecido',
        'email'   => $_SESSION['email']  ?? null,
        'perfil'  => $_SESSION['nivel_de_acesso'] ?? null,
        'sujeito' => "{$militar['posto_grad_mil']} {$militar['nome']} (ID: {$militarId}) -> {$condicaoTexto}{$atoInfo}"
    ]);

    echo json_encode([
        'success'       => true,
        'message'       => "Condição do militar {$militar['posto_grad_mil']} {$militar['nome']} alterada para {$condicaoTexto} com sucesso!",
        'url_documento' => $urlDocumento,
        'tipo_evento'   => $tipoEvento
    ]);
    exit();

} catch (Throwable $e) {
    if (ob_get_length()) ob_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno ao alterar condição de excedente: ' . $e->getMessage()]);
}
