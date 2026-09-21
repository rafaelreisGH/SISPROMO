<?php
require_once '../ConexaoDB/conexao.php';
$conn = Conexao::getConexao();

function consultaRegistrosDePromocao($id)
{

    $resultados = array();
    //SELECT para buscar no BD
    $stmt = $conn->prepare("SELECT * FROM registro_de_promocoes WHERE militar_id = :id");
    $resultado = $stmt->execute(array(
        ':id' => $id,
        
    ));

    while ($resultado = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $resultados[] = $resultado;
    }
    return $resultados;
}
