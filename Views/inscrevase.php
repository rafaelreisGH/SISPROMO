<?php
/**
 * inscrevase.php - Tela de auto-cadastro de novos militares/usuários no SISPROMO.
 *
 * Utiliza o padrão visual moderno (Bootstrap 5.3.3) em conformidade com o index.php.
 * Exibe alertas de validação vindos de Controllers/registra_usuario.php.
 */

// Mensagens personalizadas vindas via query string
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
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAP - CBMMT | Registro de Usuário</title>

    <!-- Bootstrap 5.3.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <!-- Brasão do CBMMT -->
    <link rel="icon" href="../imagens/brasao_cbmmt.png" type="image/png">

    <!-- Tipografia Institucional Bungee -->
    <link href="https://fonts.googleapis.com/css?family=Bungee" rel="stylesheet">
</head>

<body class="bg-light d-flex flex-column min-vh-100">

    <!-- Barra de Navegação -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="../index.php">
                <img src="../imagens/brasao_cbmmt.png" alt="Brasão CBMMT" width="45" height="45" class="me-3">
                <span style="font-family: 'Bungee', cursive; color: #e7183d; font-size: 1.5rem;">SISPROMO</span>
            </a>
            <div class="ms-auto">
                <a href="../index.php" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Voltar para Home
                </a>
            </div>
        </div>
    </nav>

    <!-- Conteúdo Principal -->
    <main class="container my-auto py-5">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-5">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body p-4 p-md-5">
                        <div class="text-center mb-4">
                            <i class="bi bi-person-plus-fill text-danger fs-1"></i>
                            <h3 class="fw-bold mt-2">Criar Conta</h3>
                            <p class="text-muted small">Preencha os dados abaixo para solicitar acesso ao sistema.</p>
                        </div>

                        <?php if (!empty($mensagens)): ?>
                            <div class="alert alert-danger py-2 small mb-4" role="alert">
                                <ul class="mb-0 ps-3">
                                    <?php foreach ($mensagens as $mensagem): ?>
                                        <li><?= htmlspecialchars($mensagem) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form method="post" action="../Controllers/registra_usuario.php" id="formCadastrarse" novalidate>
                            <div class="mb-3">
                                <label for="nome" class="form-label small fw-semibold">Nome Completo</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input type="text" class="form-control" id="nome" name="nome" placeholder="Seu nome completo" required>
                                </div>
                                <div class="invalid-feedback">Por favor, informe seu nome completo.</div>
                            </div>

                            <div class="mb-3">
                                <label for="cpf" class="form-label small fw-semibold">CPF</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-card-heading"></i></span>
                                    <input type="text" class="form-control" id="cpf" name="cpf" placeholder="Somente números" maxlength="11" required>
                                </div>
                                <div class="invalid-feedback">Por favor, informe um CPF válido.</div>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label small fw-semibold">E-mail</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" class="form-control" id="email" name="email" placeholder="nome@cbm.mt.gov.br" required>
                                </div>
                                <div class="invalid-feedback">Por favor, informe um e-mail válido.</div>
                            </div>

                            <div class="mb-4">
                                <label for="senha" class="form-label small fw-semibold">Senha</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-key"></i></span>
                                    <input type="password" class="form-control" id="senha" name="senha" placeholder="Crie uma senha forte" required>
                                </div>
                                <div class="invalid-feedback">Por favor, informe uma senha.</div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-danger py-2 fw-semibold" id="btn_registrar">
                                    <i class="bi bi-check2-circle me-1"></i> Registrar-se
                                </button>
                            </div>
                        </form>

                        <div class="text-center mt-4">
                            <span class="small text-muted">Já possui uma conta? <a href="../index.php" class="text-danger text-decoration-none fw-semibold">Entrar</a></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Rodapé -->
    <footer class="footer mt-auto py-3 bg-white border-top text-center text-muted small">
        <div class="container">
            <span>&copy; <?= date('Y') ?> Seção de Tecnologia da Informação e Comunicação - CBMMT</span>
        </div>
    </footer>

    <!-- Bootstrap 5.3.3 Bundle com Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('formCadastrarse');

            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    </script>
</body>

</html>