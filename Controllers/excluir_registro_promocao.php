<?php

require_once '../ConexaoDB/conexao.php';
$conn = Conexao::getConexao();

$registro_id = isset($_POST['registro_id']) ? $_POST['registro_id'] : null;
$militar_id = isset($_POST['militar_id']) ? $_POST['militar_id'] : null;

$location = 'Location:../Views/relatorio_de_promocoes.php?militar_id=' . $militar_id . '&';

try {
    $stmt = $conn->prepare('SELECT * FROM registro_de_promocoes WHERE id = :id');
    $stmt->execute(array(
        ':id' => $registro_id
    ));
    while ($resultado = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $dados_excluidos[] = $resultado['a_contar_de'];
        $dados_excluidos[] = $resultado['grau_hierarquico'];
        $dados_excluidos[] = $resultado['modalidade'];
    }
    unset($stmt);

    if (sizeof($dados_excluidos)) {
        $location .= "sucesso_exclusao[]=" . implode("&sucesso_exclusao[]=", $dados_excluidos);
    }

    $stmt = $conn->prepare('DELETE FROM registro_de_promocoes WHERE id = :id');
    $stmt->execute(array(
        ':id' => $registro_id
    ));
    
    $rowCount = $stmt->rowCount();
    if ($rowCount != 0) {
        header($location);
    } else {
        header("Location:../Views/relatorio_de_promocoes.php?militar_id=' . $militar_id . '&nada_excluido=1");
    }

    header($location);
} catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage();
}
