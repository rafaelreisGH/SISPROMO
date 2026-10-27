<?php
include_once '../Controllers/controle_de_sessao.php';
include_once '../Controllers/verifica_permissoes.php'; //verifica o perfil do usuário, e bloqueia página que não pode acessar
?>
<!DOCTYPE HTML>
<html lang="pt-br">

<head>
    <meta http-equiv="content-type" content="text/html;charset=utf-8" />
    <title>SAP - CBMMT</title>

    <!-- Bootstrap 5.3.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <!-- DataTables CSS com suporte ao Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

    <link rel="icon" href="../imagens/brasao_cbmmt.png">

    <!-- TinyMCE WYSIWYG — editor de ata de reunião -->
    <?php
    require_once '../vendor/autoload.php';
    \App\Config\Env::init();
    $tinyMceKey = $_ENV['TINYMCE_API_KEY'] ?? getenv('TINYMCE_API_KEY') ?: 'no-api-key';
    ?>
    <script src="https://cdn.tiny.cloud/1/<?= htmlspecialchars($tinyMceKey) ?>/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>


    <!--Importação da fonte Bungee-->
    <link href="https://fonts.googleapis.com/css?family=Bungee" rel="stylesheet">
</head>

<body>
    <div class="container">
        <nav class="navbar navbar-light">
            <div class="container d-flex align-items-center justify-content-start">
                <div class="d-flex align-items-center">
                    <img src="../imagens/brasao_cbmmt.png" style="width: 45px; margin-right: 15px;" />
                    <h1 style="font-family: 'Bungee', cursive; color: #e7183d; margin-bottom: 0;">SISPROMO</h1>
                </div>

                <div class="ms-auto">
                    <ul class="navbar-nav">
                        <li class="nav-item"><a class="nav-link" href="../sair.php">Sair</a></li>
                    </ul>
                </div>
            </div>
        </nav>

        <div class="col-md-12">
            <ul class="nav nav-pills">
                <?php
                $homeRoute = 'pagina_usuario.php';
                if (isset($_SESSION['nivel_de_acesso'])) {
                    if ($_SESSION['nivel_de_acesso'] == '2') {
                        $homeRoute = 'pagina_admin.php';
                    } elseif ($_SESSION['nivel_de_acesso'] == '1') {
                        $homeRoute = 'pagina_gestor.php';
                    }
                }
                ?>
                <li class="nav-item"><a class="nav-link" aria-current="page" href="<?= $homeRoute ?>">Home</a></li>
            </ul>
            <hr>
        </div>
        <div class="clearfix"></div>
    </div>