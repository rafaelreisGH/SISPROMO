<?php
require_once __DIR__ . '/../Controllers/session_bootstrap.php';
// Define o cabeçalho da resposta para JSON
header('Content-Type: application/json');

require_once '../Controllers/nivel_gestor.php';
require_once '../ConexaoDB/conexao.php';

try {
    // Parâmetros da consulta
    $ano = isset($_GET['ano']) ? $_GET['ano'] : null;
    $semestre = isset($_GET['semestre']) ? $_GET['semestre'] : null;

    if (!$ano || !$semestre) {
        throw new Exception("Parâmetros 'ano' e 'semestre' são necessários.");
    }

    $conn = Conexao::getConexao();

    $sql = "
        SELECT
            m.id AS militar_id,
            m.nome,
            m.posto_grad_mil,
            m.quadro,  -- Adicionado: a coluna do quadro do militar
            GROUP_CONCAT(dp.doc_promo_nome SEPARATOR ', ') AS documentos_positivados,
            GROUP_CONCAT(dp.doc_promo_url SEPARATOR ', ') AS urls_documentos
        FROM
            promocao.militar m
        JOIN
            promocao.pasta_promocional pp ON m.id = pp.militar_id
        JOIN
            promocao.documento_promocao dp ON pp.id = dp.pasta_promocional_id
        WHERE
            pp.ano_processo_promocional = :ano
            AND pp.semestre_processo_promocional = :semestre
            AND dp.doc_status_id = 4
        GROUP BY
            m.id, m.nome, m.posto_grad_mil;
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':ano', $ano, PDO::PARAM_INT);
    $stmt->bindParam(':semestre', $semestre, PDO::PARAM_INT);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Log de alteração de antiguidade
    require_once __DIR__ . '/../Logger/LoggerFactory.php';
    $logger = LoggerFactory::createLogger();
    $logger->info('Usuário acessou o relatório de certidões positivadas', [
        'id' => $_SESSION['id'],
        'usuario' => $_SESSION['nome'],
        'email' => $_SESSION['email'],
        'perfil' => $_SESSION['nivel_de_acesso'],
        'ano_promocional' => $ano,
        'semestre_promocional' => $semestre,
        'resultados_encontrados' => count($results)
    ]);

    echo json_encode(['success' => true, 'data' => $results]);
} catch (Exception $e) {
    // Retorna um JSON de erro
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
