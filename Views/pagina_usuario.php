<?php
require_once '../Controllers/nivel_candidato.php';
include_once './header2.php';
?>

<div class="container">

    <div class="col-md-12">
        <div class="row">
            <div class="col-md-4">
                <p>
                <h2>Página do candidato</h2>
                </p>
                <p><strong>Bem-vindo(a), </strong> <?= $_SESSION['nome'] ?>.</p>
                <p><strong>Email/Login:</strong> <?= $_SESSION['email'] ?>.</p>
            </div>
            <div class="col-md-4"></div>
        </div>
    </div>
</div>


</div>

<?php
include_once './footer.php';
?>