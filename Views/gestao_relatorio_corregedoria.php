<?php

/**
 * Tela de Gestão dos Relatórios da Corregedoria.
 *
 * O relatório é um documento INDEPENDENTE, classificado por ano e semestre.
 * Uma vez cadastrado, pode ser referenciado por qualquer reunião (CPO ou CPP).
 */
require_once '../Controllers/nivel_gestor.php';
include_once '../Views/header2.php';
require_once '../vendor/autoload.php';

use App\DAO\RelatorioCorregedoriaDAO;

$dao       = new RelatorioCorregedoriaDAO();
$relatorios = $dao->buscarTodos();
?>

<div class="container">

    <div class="col-md-12">
        <ul class="nav nav-pills" style="list-style: none; display: flex; gap: 10px; padding: 0;">
            <li class="active"><a class="nav-link active" aria-current="page" href="../Views/pagina_gestao.php">Voltar</a></li>
        </ul>
        <hr>
    </div>

    <div class="clearfix"></div>

    <div class="col-md-12">
        <h3>Gestão de Relatórios da Corregedoria</h3>
        <p class="text-muted">
            Os relatórios cadastrados aqui são documentos independentes, classificados por ano e semestre.
            Após cadastrados, podem ser vinculados a qualquer reunião (CPO ou CPP) conforme necessário.
        </p>
        <div class="clearfix"><br></div>
    </div>

    <div class="col-md-12">
        <div class="row">

            <!-- ===================== FORMULÁRIO DE CADASTRO ===================== -->
            <div class="col-md-4">
                <div class="card">
                    <h5 class="card-header">Cadastrar Novo Relatório</h5>
                    <div class="card-body">

                        <form id="form-relatorio">
                            <!-- Ano -->
                            <div class="form-group">
                                <label for="input-ano">Ano de Referência <span class="text-danger">*</span></label>
                                <input
                                    type="number"
                                    id="input-ano"
                                    class="form-control"
                                    name="ano"
                                    min="2020"
                                    max="2099"
                                    value="<?= date('Y') ?>"
                                    required>
                            </div>

                            <!-- Semestre -->
                            <div class="form-group">
                                <label for="input-semestre">Semestre <span class="text-danger">*</span></label>
                                <select id="input-semestre" class="form-control" name="semestre" required>
                                    <option value="">Selecione...</option>
                                    <option value="1">1º Semestre</option>
                                    <option value="2">2º Semestre</option>
                                </select>
                            </div>

                            <!-- Descrição -->
                            <div class="form-group">
                                <label for="input-descricao">Descrição (opcional)</label>
                                <input
                                    type="text"
                                    id="input-descricao"
                                    class="form-control"
                                    name="descricao"
                                    maxlength="255"
                                    placeholder="Ex: Relatório 1º Semestre 2026">
                            </div>

                            <!-- URL do Documento -->
                            <div class="form-group">
                                <label for="input-url">URL do Documento <span class="text-danger">*</span></label>
                                <input
                                    type="url"
                                    id="input-url"
                                    class="form-control"
                                    name="url_arquivo"
                                    placeholder="https://..."
                                    required>
                                <small class="form-text text-muted">
                                    Link do documento no SIGADOC.
                                </small>
                            </div>

                            <button type="submit" id="btn-salvar" class="btn btn-outline-success">
                                <span id="spinner-salvar" class="spinner-border spinner-border-sm d-none" role="status"></span>
                                Salvar Relatório
                            </button>
                        </form>

                    </div><!-- /card-body -->
                </div><!-- /card -->
            </div><!-- /col-md-4 -->

            <!-- ===================== LISTAGEM DOS RELATÓRIOS ===================== -->
            <div class="col-md-8">
                <div class="card">
                    <h5 class="card-header">Relatórios Cadastrados</h5>
                    <div class="card-body">

                        <?php if (empty($relatorios)): ?>
                            <p class="text-muted">Nenhum relatório cadastrado ainda.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-hover" id="tabela-relatorios">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Ano</th>
                                            <th>Semestre</th>
                                            <th>Descrição</th>
                                            <th class="text-center">Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($relatorios as $rel): ?>
                                            <tr id="linha-relatorio-<?= (int) $rel['id_relatorio'] ?>">
                                                <td class="align-middle"><?= (int) $rel['ano'] ?></td>
                                                <td class="align-middle"><?= (int) $rel['semestre'] ?>º Sem.</td>
                                                <td class="align-middle col-descricao"><?= htmlspecialchars($rel['descricao'] ?? '—') ?></td>
                                                <td class="text-center align-middle">
                                                    <div class="d-flex justify-content-center" style="gap: 5px;">
                                                        <a href="<?= htmlspecialchars($rel['url_arquivo']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline-success btn-visualizar" title="Visualizar Documento">
                                                            <i class="bi bi-eye-fill"></i>
                                                        </a>
                                                        <button
                                                            class="btn btn-outline-warning btn-editar"
                                                            data-id="<?= (int) $rel['id_relatorio'] ?>"
                                                            data-url="<?= htmlspecialchars($rel['url_arquivo']) ?>"
                                                            data-descricao="<?= htmlspecialchars($rel['descricao'] ?? '') ?>"
                                                            data-ano="<?= (int) $rel['ano'] ?>"
                                                            data-semestre="<?= (int) $rel['semestre'] ?>"
                                                            title="Editar URL/Descrição">
                                                            <i class="bi bi-pencil-square"></i>
                                                        </button>
                                                        <button
                                                            class="btn btn-outline-danger btn-excluir"
                                                            title="Excluir Relatório"
                                                            onclick="excluirRelatorio(<?= (int) $rel['id_relatorio'] ?>)">
                                                            <i class="bi bi-trash3-fill"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>

                    </div><!-- /card-body -->
                </div><!-- /card -->
            </div><!-- /col-md-8 -->

        </div><!-- /row -->
    </div><!-- /col-md-12 -->

</div><!-- /container -->

<!-- ===================== MODAL DE EDIÇÃO ===================== -->
<div class="modal fade" id="modal-editar" tabindex="-1" aria-labelledby="modal-editar-label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-editar-label">Editar Relatório</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <p id="modal-editar-periodo" class="text-muted"></p>
                <div class="mb-3">
                    <label for="modal-input-descricao" class="form-label">Descrição</label>
                    <input type="text" id="modal-input-descricao" class="form-control" maxlength="255">
                </div>
                <div class="mb-3">
                    <label for="modal-input-url" class="form-label">Nova URL do Documento <span class="text-danger">*</span></label>
                    <input type="url" id="modal-input-url" class="form-control" placeholder="https://..." required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary btn-sm" id="btn-confirmar-edicao">
                    <span id="spinner-editar" class="spinner-border spinner-border-sm d-none" role="status"></span>
                    Salvar Alteração
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ===================== JAVASCRIPT ===================== -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        'use strict';

        const API_URL = '../api/relatorio_corregedoria_api.php';
        let relatorioIdEdicao = null;

        // Instancia o modal do Bootstrap 5 nativamente (agora com garantia de que o Bootstrap JS já foi carregado)
        const modalEditarEl = document.getElementById('modal-editar');
        const modalEditar = new bootstrap.Modal(modalEditarEl);

        // ── Cadastro ────────────────────────────────────────────────
        document.getElementById('form-relatorio').addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('[DEBUG] Cadastro submetido');

            const btnSalvar = document.getElementById('btn-salvar');
            const spinner = document.getElementById('spinner-salvar');

            btnSalvar.disabled = true;
            spinner.classList.remove('d-none');

            const payload = {
                ano: parseInt(document.getElementById('input-ano').value, 10),
                semestre: parseInt(document.getElementById('input-semestre').value, 10),
                descricao: document.getElementById('input-descricao').value.trim() || null,
                url_arquivo: document.getElementById('input-url').value.trim()
            };
            console.log('[DEBUG] Payload de cadastro:', payload);

            fetch(API_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(payload)
                })
                .then(r => {
                    console.log('[DEBUG] Resposta do fetch recebida. Status:', r.status);
                    return r.json();
                })
                .then(data => {
                    console.log('[DEBUG] JSON decodificado:', data);
                    if (data.success) {
                        showToast(data.message, 'success');
                        document.getElementById('form-relatorio').reset();
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showToast(data.message || 'Erro ao cadastrar.', 'danger');
                    }
                })
                .catch(err => {
                    console.error('[DEBUG] Erro no catch do fetch:', err);
                    showToast('Erro de comunicação com o servidor.', 'danger');
                })
                .finally(() => {
                    btnSalvar.disabled = false;
                    spinner.classList.add('d-none');
                });
        });

        // ── Abrir modal de edição ───────────────────────────────────
        document.querySelectorAll('.btn-editar').forEach(function(btn) {
            btn.addEventListener('click', function() {
                relatorioIdEdicao = this.dataset.id;
                const ano = this.dataset.ano;
                const semestre = this.dataset.semestre;

                document.getElementById('modal-editar-periodo').textContent =
                    `Editando: ${semestre}º Semestre de ${ano}`;
                document.getElementById('modal-input-url').value = this.dataset.url;
                document.getElementById('modal-input-descricao').value = this.dataset.descricao;

                modalEditar.show();
            });
        });

        // ── Confirmar edição ────────────────────────────────────────
        document.getElementById('btn-confirmar-edicao').addEventListener('click', function() {
            if (!relatorioIdEdicao) {
                console.log('[DEBUG] Sem ID de relatório para edição.');
                return;
            }
            console.log('[DEBUG] Confirmar edição clicado para ID:', relatorioIdEdicao);

            const btnConfirmar = this;
            const spinner = document.getElementById('spinner-editar');

            const novaUrl = document.getElementById('modal-input-url').value.trim();
            const descricao = document.getElementById('modal-input-descricao').value.trim() || null;
            console.log('[DEBUG] Dados da edição:', {
                novaUrl,
                descricao
            });

            if (!novaUrl) {
                showToast('Informe a URL do documento.', 'warning');
                return;
            }

            btnConfirmar.disabled = true;
            spinner.classList.remove('d-none');

            fetch(API_URL + '?id=' + relatorioIdEdicao, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        url_arquivo: novaUrl,
                        descricao: descricao
                    })
                })
                .then(r => {
                    console.log('[DEBUG] Resposta do PUT recebida. Status:', r.status);
                    return r.json();
                })
                .then(data => {
                    console.log('[DEBUG] JSON do PUT decodificado:', data);
                    if (data.success) {
                        showToast(data.message, 'success');
                        modalEditar.hide();

                        // Atualiza a linha na tabela sem recarregar a página
                        const linha = document.getElementById('linha-relatorio-' + relatorioIdEdicao);
                        if (linha) {
                            linha.querySelector('.col-descricao').textContent = descricao || '—';

                            const btnVisualizar = linha.querySelector('.btn-visualizar');
                            if (btnVisualizar) {
                                btnVisualizar.href = novaUrl;
                            }

                            const btnEditar = linha.querySelector('.btn-editar');
                            if (btnEditar) {
                                btnEditar.dataset.url = novaUrl;
                                btnEditar.dataset.descricao = descricao || '';
                            }
                        }
                    } else {
                        showToast(data.message || 'Erro ao atualizar.', 'danger');
                    }
                })
                .catch(err => {
                    console.error('[DEBUG] Erro no catch do PUT:', err);
                    showToast('Erro de comunicação com o servidor.', 'danger');
                })
                .finally(() => {
                    btnConfirmar.disabled = false;
                    spinner.classList.add('d-none');
                });
        });

        // ── Excluir Relatório (AJAX) ────────────────────────────────
        window.excluirRelatorio = function(id) {
            console.log('[DEBUG] Excluir clicado para ID:', id);
            if (!confirm("Tem certeza que deseja excluir este relatório? Esta ação não pode ser desfeita!")) {
                return;
            }

            fetch(API_URL + '?id=' + id, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                })
                .then(r => {
                    console.log('[DEBUG] Resposta do DELETE recebida. Status:', r.status);
                    return r.json();
                })
                .then(data => {
                    console.log('[DEBUG] JSON do DELETE decodificado:', data);
                    if (data.success) {
                        showToast(data.message, 'success');
                        document.getElementById('linha-relatorio-' + id).remove();

                        if (document.querySelectorAll('#tabela-relatorios tbody tr').length === 0) {
                            setTimeout(() => location.reload(), 1000);
                        }
                    } else {
                        showToast(data.message || 'Erro ao excluir.', 'danger');
                    }
                })
                .catch(err => {
                    console.error('[DEBUG] Erro no catch do DELETE:', err);
                    showToast('Erro de comunicação com o servidor ao tentar excluir.', 'danger');
                });
        };

    });
</script>

<?php include_once '../Views/footer2.php'; ?>