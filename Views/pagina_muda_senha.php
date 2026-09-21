<?php
include_once './header3.php';

if (isset($_SESSION['mensagem_erro'])) {
    echo '<div class="alert alert-danger" role="alert">' . $_SESSION['mensagem_erro'] . '</div>';
    unset($_SESSION['mensagem_erro']);
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-body p-5">
                    <h3 class="text-center mb-3"><?= $_SESSION['nome'] ?>, mensagem importante!</h3>
                    <h5 class="text-center text-muted mb-4">Alteração obrigatória de senha para o primeiro acesso.</h5>
                    
                    <form method="post" action="../Controllers/muda_senha.php" id="formCadastrarse">
                        <div class="form-floating mb-4">
                            <input type="password" class="form-control" id="senhaNova" name="senhaNova" placeholder="Nova senha" required>
                            <label for="senhaNova">Nova senha</label>
                        </div>
                        <button type="submit" class="btn btn-danger w-100">Alterar senha</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once './footer2.php'; ?>
