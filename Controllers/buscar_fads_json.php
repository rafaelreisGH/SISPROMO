<?php
require_once '../ConexaoDB/conexao.php';
header('Content-Type: application/json');

$militar_id = filter_input(INPUT_GET, 'militar_id', FILTER_VALIDATE_INT);

if (!$militar_id) {
    echo json_encode([]);
    exit;
}

$conn = Conexao::getConexao();
$stmt = $conn->prepare("SELECT id, semestre, ano, nota, caminho_do_arquivo FROM fad WHERE militar_id = :id AND caminho_do_arquivo IS NOT NULL ORDER BY ano DESC, semestre DESC");
$stmt->bindParam(':id', $militar_id, PDO::PARAM_INT);
$stmt->execute();

$fads = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($fads);
