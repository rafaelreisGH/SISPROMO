<?php
    require_once __DIR__ . '/../Controllers/session_bootstrap.php';
    //verifica se o usuário fez o login
    if(!isset($_SESSION['email'])){
        header('Location: ../index.php?erro=1');
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
        <link rel="icon" href="../imagens/Vetor CBMMT.png">
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
                        <li><a href="../sair.php">Sair</a></li>
                    </ul>
                </div><!--/.nav-collapse -->
            </div>
        </nav>


        <div class="container">

            <br /><br />

            <div class="col-md-4"></div>
            <div class="col-md-4">
                <p><strong>Usuário:</strong> <?= $_SESSION['nome'] ?></p><br>
                <p><strong>Email:</strong> <?= $_SESSION['email'] ?></p><br>
                <p><strong>Nível de acesso:</strong> 
                <?php
                $var = $_SESSION['nivel_de_acesso'];
                switch ($var) {
                    case '2':
                        echo 'Admin';
                        break;
                    case '1':
                        echo 'Gestor';
                        break;
                    default:
                        echo 'Usuário';
                        break;
                }
                ?></p><br>



            </div>
            <div class="col-md-4"></div>

            <div class="clearfix"></div>
            <br />
            <div class="col-md-4"></div>
            <div class="col-md-4"></div>
            <div class="col-md-4"></div>

        </div>


    </div>

    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>

</body>
</html>



