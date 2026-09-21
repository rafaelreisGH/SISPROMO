<?php
require_once __DIR__ . '/../Controllers/session_bootstrap.php';
header('Content-Type: application/json');

require_once '../ConexaoDB/conexao.php';
require_once '../Controllers/nivel_gestor.php';
$conn = Conexao::getConexao();

// A consulta SQL que faz o JOIN e filtra os resultados
$query_tn_arregimentado = "
    SELECT
        m.id,
        m.nome,
        m.posto_grad_mil,
        tna.categoria,
        tna.qtde_de_dias,
        tna.tna_inicio,
        tna.tna_fim
    FROM
        militar AS m
    JOIN
        tempo_nao_arregimentado AS tna
    ON
        m.id = tna.militar_id
    WHERE
        m.posto_grad_mil = tna.posto_grad_na_epoca COLLATE utf8mb4_unicode_ci
    ORDER BY
        m.nome
";

$stmt = $conn->prepare($query_tn_arregimentado);
$stmt->execute();

$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Retorna os dados como JSON
echo json_encode($resultados);

?>