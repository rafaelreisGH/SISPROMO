<?php
/**
 * index.php - Tela inicial de apresentação e autenticação do SISPROMO (CBMMT)
 *
 * Página pública de acesso ao sistema com formulário/modal de login.
 */
$erro = isset($_GET['erro']) ? (int)$_GET['erro'] : 0;
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAP - CBMMT | Início</title>

    <!-- Bootstrap 5.3.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <!-- Brasão do CBMMT -->
    <link rel="icon" href="imagens/brasao_cbmmt.png" type="image/png">

    <!-- Tipografia Institucional Bungee -->
    <link href="https://fonts.googleapis.com/css?family=Bungee" rel="stylesheet">
</head>

<body class="bg-light d-flex flex-column min-vh-100">

    <!-- Barra de Navegação -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <img src="imagens/brasao_cbmmt.png" alt="Brasão CBMMT" width="45" height="45" class="me-3">
                <span style="font-family: 'Bungee', cursive; color: #e7183d; font-size: 1.5rem;">SISPROMO</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu" aria-controls="navMenu" aria-expanded="false" aria-label="Alternar navegação">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navMenu">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item me-2">
                        <a class="nav-link text-secondary" href="Views/inscrevase.php">
                            <i class="bi bi-person-plus me-1"></i> Registre-se
                        </a>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="btn btn-outline-danger px-4" data-bs-toggle="modal" data-bs-target="#modalLogin">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Entrar
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Conteúdo Principal -->
    <main class="container my-auto py-5">
        <?php if ($erro === 2): ?>
            <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> É necessário autenticar-se para acessar o sistema.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
            </div>
        <?php endif; ?>

        <div class="p-5 mb-4 bg-white rounded-3 shadow-sm border text-center">
            <h1 class="display-5 fw-bold" style="font-family: 'Bungee', cursive; color: #e7183d;">SISPROMO</h1>
            <p class="fs-4 text-secondary mb-3">Sistema de Administração de Promoções - CBMMT</p>
            <p class="lead text-muted mb-4">Acesso restrito a usuários validados da corporação.</p>
            <button type="button" class="btn btn-danger btn-lg px-5 shadow" data-bs-toggle="modal" data-bs-target="#modalLogin">
                <i class="bi bi-lock-fill me-2"></i> Acessar o Sistema
            </button>
        </div>
    </main>

    <!-- Modal de Login -->
    <div class="modal fade" id="modalLogin" tabindex="-1" aria-labelledby="modalLoginLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold" id="modalLoginLabel">
                        <i class="bi bi-shield-lock text-danger me-2"></i> Autenticação
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">Informe suas credenciais para acessar o painel do SISPROMO.</p>

                    <?php if ($erro === 1): ?>
                        <div class="alert alert-danger py-2 small" role="alert">
                            <i class="bi bi-x-circle-fill me-1"></i> Usuário e/ou senha inválido(s).
                        </div>
                    <?php elseif ($erro === 2): ?>
                        <div class="alert alert-warning py-2 small" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> É preciso fazer login no sistema.
                        </div>
                    <?php endif; ?>

                    <form method="post" action="Controllers/validar_acesso.php" id="formLogin" novalidate>
                        <div class="mb-3">
                            <label for="campo_email" class="form-label small fw-semibold">E-mail</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" class="form-control" id="campo_email" name="email" placeholder="nome@cbm.mt.gov.br" required>
                            </div>
                            <div class="invalid-feedback" id="feedback_email">
                                Por favor, informe seu e-mail.
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="campo_senha" class="form-label small fw-semibold">Senha</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-key"></i></span>
                                <input type="password" class="form-control" id="campo_senha" name="senha" placeholder="Sua senha" required>
                            </div>
                            <div class="invalid-feedback" id="feedback_senha">
                                Por favor, informe sua senha.
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-danger py-2 fw-semibold" id="btn_login">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Entrar
                            </button>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-top-0 pt-0 justify-content-center">
                    <span class="small text-muted">Ainda não possui conta? <a href="Views/inscrevase.php" class="text-decoration-none">Registre-se</a></span>
                </div>
            </div>
        </div>
    </div>

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
            const formLogin = document.getElementById('formLogin');
            const campoEmail = document.getElementById('campo_email');
            const campoSenha = document.getElementById('campo_senha');
            const modalElement = document.getElementById('modalLogin');
            const modalLogin = new bootstrap.Modal(modalElement);

            // Se a página carregar com erro vindo do backend, abre o modal de login automaticamente
            const erroCodigo = <?= (int)$erro ?>;
            if (erroCodigo > 0) {
                modalLogin.show();
            }

            // Validação de formulário no lado do cliente
            formLogin.addEventListener('submit', function (event) {
                let valido = true;

                if (!campoEmail.value.trim()) {
                    campoEmail.classList.add('is-invalid');
                    valido = false;
                } else {
                    campoEmail.classList.remove('is-invalid');
                }

                if (!campoSenha.value.trim()) {
                    campoSenha.classList.add('is-invalid');
                    valido = false;
                } else {
                    campoSenha.classList.remove('is-invalid');
                }

                if (!valido) {
                    event.preventDefault();
                    event.stopPropagation();
                }
            });

            // Limpeza dos avisos de erro ao digitar
            campoEmail.addEventListener('input', function () {
                if (this.value.trim()) this.classList.remove('is-invalid');
            });
            campoSenha.addEventListener('input', function () {
                if (this.value.trim()) this.classList.remove('is-invalid');
            });
        });
    </script>
</body>

</html>