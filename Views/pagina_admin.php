<?php
require_once '../Controllers/nivel_admin.php';
include_once './header2.php';
?>

<div class="container my-4">

    <?php
    // Verifica se há mensagem de sucesso na sessão
    if (isset($_SESSION['mensagem_sucesso'])) {
        echo '<div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>' . htmlspecialchars($_SESSION['mensagem_sucesso']) . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
              </div>';
        unset($_SESSION['mensagem_sucesso']); // Apaga após exibir
    }
    ?>

    <div class="row">
        <!-- Coluna de Perfil / Informações do Admin -->
        <div class="col-12 col-lg-3 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title fw-bold text-danger mb-3">
                        <i class="bi bi-shield-lock-fill me-1"></i> Painel do Admin
                    </h5>
                    <p class="mb-2"><strong>Bem-vindo(a),</strong><br><?= htmlspecialchars($_SESSION['nome']) ?></p>
                    <p class="mb-2"><strong>Login/E-mail:</strong><br><span class="text-muted small"><?= htmlspecialchars($_SESSION['email']) ?></span></p>
                    <hr>
                    <div class="d-grid gap-2">
                        <a href="dashboard_auditoria.php" class="btn btn-outline-danger btn-sm text-start">
                            <i class="bi bi-speedometer2 me-1"></i> Dashboard Auditoria
                        </a>
                        <a href="pagina_gestao.php" class="btn btn-outline-secondary btn-sm text-start">
                            <i class="bi bi-gear-fill me-1"></i> Gestão do Sistema
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Coluna da Tabela de Usuários Bloqueados -->
        <div class="col-12 col-lg-9">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0 fw-bold">
                        <i class="bi bi-person-x-fill text-danger me-2"></i> Usuários Bloqueados / Pendentes
                    </h5>
                    <span class="badge bg-danger">Aguardando Liberação</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width: 60px;"><i class="bi bi-gear"></i></th>
                                    <th><i class="bi bi-person me-1"></i> Nome</th>
                                    <th><i class="bi bi-envelope me-1"></i> E-mail</th>
                                    <th><i class="bi bi-person-badge me-1"></i> Perfil</th>
                                    <th>Posto/Graduação</th>
                                    <th>Situação</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                include_once '../Controllers/usuarios_bloqueados.php';
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<?php
include_once './footer2.php';
?>
