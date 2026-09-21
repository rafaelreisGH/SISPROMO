<?php
$erro = isset($_GET['erro']) ? $_GET['erro'] : 0;
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
        <link rel="icon" href="imagens/brasao_cbmmt.ico">
        
        <!--Importação da fonte Bungee-->
        <link href="https://fonts.googleapis.com/css?family=Bungee" rel="stylesheet">
        
        <script>
            // código javascript
            $(document).ready(function () {
                $('#btn_login').click(function () {

                    var campo_vazio = false;

                    if ($('#campo_email').val() == '') {
                        $('#campo_email').css({'border-color': '#A94442'});
                        campo_vazio = true;
                    } else {
                        $('#campo_email').css({'border-color': '#CCC'});
                    }
                    if ($('#campo_senha').val() == '') {
                        $('#campo_senha').css({'border-color': '#A94442'});
                        campo_vazio = true;
                    } else {
                        $('#campo_senha').css({'border-color': '#CCC'});
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
                    <img src="../imagens/Vetor CBMMT.png" style="width: 45px;
                         padding: 5px 0 5px 0;"/>
                </div>

                <div id="navbar" class="navbar-collapse collapse">
                    <ul class="nav navbar-nav navbar-right">
                        <li><a href="../index.php">Voltar para Home</a></li>
                    </ul>
                </div><!--/.nav-collapse -->
            </div>
        </nav>


        <div class="container">

            <!-- Main component for a primary marketing message or call to action -->
            <div class="jumbotron">
                <h2 style="font-family: 'Bungee', cursive; text-align: justify; color: #e7183d">Cadastro no SAP/CBMMT realizado com sucesso!</h2>
                <p>Aguarde a validação pelo Administrador do SAP.</p>
            </div>

            <div class="clearfix"></div>
        </div>


    </div>

    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>

</body>
