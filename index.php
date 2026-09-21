<?php
require_once __DIR__ . '/Controllers/session_bootstrap.php';
$erro = isset($_GET['erro']) ? $_GET['erro'] : 0;

if (isset($_SESSION['mensagem_sucesso'])) {
    echo '<div class="alert alert-success" role="alert">' . $_SESSION['mensagem_sucesso'] . '</div>';
    unset($_SESSION['mensagem_sucesso']);
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
    <link rel="icon" href="imagens/Vetor CBMMT.png" type="image/x-icon" />

    <!--Importação da fonte Bungee-->
    <link href="https://fonts.googleapis.com/css?family=Bungee" rel="stylesheet">

    <script>
        // código javascript
        $(document).ready(function() {
            $('#btn_login').click(function() {

                var campo_vazio = false;

                if ($('#campo_email').val() == '') {
                    $('#campo_email').css({
                        'border-color': '#A94442'
                    });
                    campo_vazio = true;
                } else {
                    $('#campo_email').css({
                        'border-color': '#CCC'
                    });
                }
                if ($('#campo_senha').val() == '') {
                    $('#campo_senha').css({
                        'border-color': '#A94442'
                    });
                    campo_vazio = true;
                } else {
                    $('#campo_senha').css({
                        'border-color': '#CCC'
                    });
                }
                if (campo_vazio)
                    return false;
            });
        });
    </script>
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
                <img src="imagens/Vetor CBMMT.png" style="width: 45px;
                         padding: 5px 0 5px 0;" />
            </div>

            <div id="navbar" class="navbar-collapse collapse">
                <ul class="nav navbar-nav navbar-right">
                    <li><a href="Views/inscrevase.php">Registre-se</a></li>
                    <li class="<?= $erro == (1 || 2) ? 'open' : '' ?>">
                        <a id="entrar" data-target="#" href="#" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Entrar</a>
                        <ul class="dropdown-menu" aria-labelledby="entrar">
                            <div class="col-md-12">
                                <p>Você possui uma conta?</h3>
                                    <br />
                                <form method="post" action="Controllers/validar_acesso.php" id="formLogin">
                                    <div class="form-group">
                                        <input type="text" class="form-control" id="campo_email" name="email" placeholder="Email" />
                                    </div>

                                    <div class="form-group">
                                        <input type="password" class="form-control red" id="campo_senha" name="senha" placeholder="Senha" />
                                    </div>

                                    <button type="buttom" class="btn btn-primary" id="btn_login">Entrar</button>

                                    <br /><br />
                                </form>

                                <?php
                                if ($erro == 1) {
                                    echo '<font style="color:#ff0000">*Usuário e/ou senha inválido(s). </font>';
                                } else if ($erro == 2) {
                                    echo '<font style="color:#ff0000">*É preciso fazer login no sistema. </font>';
                                }
                                ?>

                                </form>
                        </ul>
                    </li>
                </ul>
            </div><!--/.nav-collapse -->
        </div>
    </nav>


    <div class="container">

        <!-- Main component for a primary marketing message or call to action -->
        <div class="jumbotron">
            <h1 style="font-family: 'Bungee', cursive; text-align: justify; color: #e7183d">SISPROMO</h1>
            <h2 style="font-family: 'Bungee', cursive; text-align: justify; color: #e7183d">Sistema de Administração de Promoções - CBMMT</h2>
            <p>Acesso restrito a usuários validados.</p>
        </div>

        <div class="clearfix"></div>
    </div>


    </div>

    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
</body>

</html>