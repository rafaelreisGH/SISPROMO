<?php
require_once '../Controllers/nivel_admin.php';
include_once './header.php';
?>

<div class="container">

    <?php
    // Verifica se há mensagem de sucesso na sessão
    if (isset($_SESSION['mensagem_sucesso'])) {
        echo '<div class="alert alert-success" style="margin-top: 15px;">' . $_SESSION['mensagem_sucesso'] . '</div>';
        unset($_SESSION['mensagem_sucesso']); // Apaga após exibir
    }
    ?>
    <br /><br />
    <div class="col-md-3">
        <h3>Página de Admin</h3>
        <p><strong>Bem-vindo(a), </strong> <?= $_SESSION['nome'] ?>.</p><br>
        <p><strong>Email/Login:</strong> <?= $_SESSION['email'] ?>.</p><br>
        <p><strong>sapcbmmt@gmail.com - bombeirosSAP</p><br>
    </div>
    <link rel="stylesheet" href="../css/tabela_usuarios_bloqueados.css">

    <div class="col-md-9">

        <div class="panel panel-default panel-table">
            <div class="panel-heading">
                <div class="row">
                    <div class="col col-xs-6">
                        <h3 class="panel-title">Usuário Bloqueados</h3>
                    </div>

                </div>
            </div>
            <div class="panel-body">
                <table class="table table-striped table-bordered table-list">
                    <thead>
                        <tr>
                            <th align="center"><em class="glyphicon glyphicon-cog"></em></th>
                            <!--<th class="hidden-xs">ID</th>-->
                            <th><em class="glyphicon glyphicon-user"></em>&nbsp;Nome</th>
                            <th><em class="glyphicon glyphicon-inbox"></em>&nbsp;E-mail</th>
                            <th><em class="glyphicon glyphicon-pencil"></em>&nbsp;Perfil de usuário</th>
                            <th>Posto/Graduação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <!--<td class="hidden-xs">1</td>-->
                            <?php
                            include_once '../Controllers/usuarios_bloqueados.php';
                            ?>

                        </tr>
                    </tbody>
                </table>

            </div>

            <div class="panel-footer">
                <div class="row">
                    <div class="col col-xs-4">Page 1 of 5
                    </div>
                    <div class="col col-xs-8">
                        <ul class="pagination hidden-xs pull-right">
                            <li><a href="#">1</a></li>
                            <li><a href="#">2</a></li>
                            <li><a href="#">3</a></li>
                            <li><a href="#">4</a></li>
                            <li><a href="#">5</a></li>
                        </ul>
                        <ul class="pagination visible-xs pull-right">
                            <li><a href="#">«</a></li>
                            <li><a href="#">»</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="col-md-3">
    </div>

    <div class="clearfix">

    </div>
    <br />

    <div class="container">
        <div class="row">

        </div>
    </div>


</div>

<?php
include_once './footer.php';
?>