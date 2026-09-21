<?php
require_once __DIR__ . '/../Controllers/session_bootstrap.php';
//verifica se o usuário fez o login
//se não fez, é redirecionado para a página index.php
if (!isset($_SESSION['email'])) {
    header('Location: ../index.php?erro=1');
}
?>

<!DOCTYPE HTML>
<html lang="pt-br">

<head>
    <meta http-equiv="content-type" content="text/html;charset=utf-8" />
    <title>SAP - CBMMT</title>

    <!-- Bootstrap 5.3.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <link rel="icon" href="../imagens/brasao_cbmmt.png">
</head>

<body>
    <div class="container">
        <nav class="navbar navbar-light">
            <div class="container">
                <img src="../imagens/brasao_cbmmt.png" style="width: 45px; padding: 5px 0 5px 0;" />
                <div id="navbar" class="navbar-collapse collapse">
                    <ul class="nav navbar-nav navbar-right">
                        <li><a href="../sair.php">Sair</a></li>
                    </ul>
                </div>
                <!--/.nav-collapse -->
            </div>
        </nav>
<div class="col-md-12">
            <ul class="nav nav-pills">
                <li class="nav-item"><a class="nav-link " aria-current="page" href="pagina_gestor.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="../sair.php">Sair</a></li>
            </ul>
            <hr>
        </div>
        <div class="clearfix"></div>
    </div>