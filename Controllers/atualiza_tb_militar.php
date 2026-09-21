<?php

include_once '../ConexaoDB/conexao.php';

//$dados = $_FILES['arquivo'];
//var_dump($dados);
//die();

if (!empty($_FILES['arquivo']['tmp_name'])) {
    //$stmt = $conn->query("TRUNCATE militar");
    //$stmt->execute();

    $arquivo = new DomDocument();
    $arquivo->load($_FILES['arquivo']['tmp_name']);
    //var_dump($arquivo);

    $linhas = $arquivo->getElementsByTagName("record");
    //var_dump($linhas);

    $primeira_linha = true;

    foreach ($linhas as $linha) {
        if ($primeira_linha == false) {

            $antiguidade = $linha->getElementsByTagName("antiguidade")->item(0)->nodeValue;
            $posto_graduacao = $linha->getElementsByTagName("posto_grad_mil")->item(1)->nodeValue;
            $nome = $linha->getElementsByTagName("nome")->item(2)->nodeValue;

            $stmt = $conn->prepare("INSERT INTO militar (antiguidade, posto_grad_mil, nome) VALUES (?,?,?)");
            $stmt->bindValue(1, $antiguidade, PDO::PARAM_INT);
            $stmt->bindValue(2, $posto_graduacao, PDO::PARAM_STR);
            $stmt->bindValue(3, $nome, PDO::PARAM_STR);
            $stmt->execute();
        }
        $primeira_linha = false;
    }
}
//passar parâmetro URL página gestor informando que foi atualizado.
header('Location:../Views/pagina_gestor.php?update=1');
