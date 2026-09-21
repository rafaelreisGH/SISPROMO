<?php

// Mensagens personalizadas
$mensagens = [];

if (isset($_GET['erro_nome'])) {
    $mensagens[] = "O nome informado já está em uso.";
}
if (isset($_GET['erro_email'])) {
    $mensagens[] = "O e-mail informado já está cadastrado.";
}
if (isset($_GET['email_invalido'])) {
    $mensagens[] = "O e-mail informado é inválido.";
}
if (isset($_GET['erro_senha'])) {
    $mensagens[] = urldecode($_GET['erro_senha']);
}
if (isset($_GET['erro_interno'])) {
    $mensagens[] = "Ocorreu um erro inesperado ao salvar os dados. Tente novamente mais tarde.";
}
if (isset($_GET['erro_cpf'])) {
    $mensagens[] = "O CPF informado já está cadastrado.";
}
if (isset($_GET['cpf_invalido'])) {
    $mensagens[] = "O CPF informado é inválido.";
}
?>


<!DOCTYPE HTML>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>SAP - CBMMT</title>
    <!-- jquery - link cdn -->
    <script src="https://code.jquery.com/jquery-2.2.4.min.js"></script>
    <!-- bootstrap - link cdn -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
    <link rel="icon" href="../imagens/brasao_cbmmt.ico">
</head>

<body>
    <!-- Static navbar -->
    <nav class="navbar navbar-default navbar-static-top">
        <div class="container">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <img src="../imagens/Vetor CBMMT.png" style="width: 45px;
                         padding: 5px 0 5px 0;" />
            </div>

            <div id="navbar" class="navbar-collapse collapse">
                <ul class="nav navbar-nav navbar-right">
                    <li><a href="../index.php">Voltar para Home</a></li>
                </ul>
            </div><!--/.nav-collapse -->
        </div>
    </nav>


    <div class="container">

        <br /><br />

        <div class="col-md-4"></div>
        <div class="col-md-4">
            <h3>Formulário de registro.</h3>
            <br />
            <form method="post" action="../Controllers/registra_usuario.php" id="formCadastrarse">
                <div class="form-group">
                    <input type="text" class="form-control" id="nome" name="nome" placeholder="Nome completo" required="required">
                </div>
                <div class="form-group">
                    <input type="text" class="form-control" id="cpf" name="cpf" placeholder="CPF (somente números)" required="required">
                </div>
                <div class="form-group">
                    <input type="email" class="form-control" id="email" name="email" placeholder="Email" required="required">
                </div>
                <div class="form-group">
                    <input type="password" class="form-control" id="senha" name="senha" placeholder="Senha" required="required">
                </div>
                <button type="submit" class="btn btn-danger form-control">Registrar-se</button>
                <div class="alert">
                    <?php
                    // Exibe as mensagens, se houver
                    if (!empty($mensagens)) {
                        echo '<div class="erros" style="background: #ffe0e0; border: 1px solid #cc0000; padding: 10px; border-radius: 5px; margin-bottom: 15px;">';
                        foreach ($mensagens as $mensagem) {
                            echo "<p style='margin: 5px 0; color: #cc0000;'>$mensagem</p>";
                        }
                        echo '</div>';
                    }
                    ?>
                </div>
            </form>
        </div>
        <div class="col-md-4"></div>

        <div class="clearfix"></div>
        <br />

    </div>


    </div>

    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>

</body>

</html>