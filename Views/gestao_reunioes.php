<?php

/**
 * Tela de Gestão de Reuniões das Comissões de Promoção (CPO / CPP).
 *
 * Permite:
 * - Listar histórico de reuniões por comissão (CPO ou CPP).
 * - Abrir nova sessão de reunião vinculada ao processo promocional ativo.
 * - Editar dados da reunião ou excluir sessões em preparação.
 * - Acessar a pauta de julgamento da comissão.
 */

require_once '../Controllers/nivel_gestor.php';
include_once '../Views/header2.php';
require_once '../vendor/autoload.php';

use App\DAO\ReuniaoComissaoDAO;
use App\DAO\RelatorioCorregedoriaDAO;
use App\DAO\ProcessoPromocionalDAO;

$comissao = isset($_GET['comissao']) ? strtoupper(trim($_GET['comissao'])) : 'CPO';
if (!in_array($comissao, ['CPO', 'CPP'])) {
    $comissao = 'CPO';
}

$nomeComissaoExtenso = ($comissao === 'CPO')
    ? 'Comissão de Promoção de Oficiais (CPO)'
    : 'Comissão de Promoção de Praças (CPP)';

$daoReuniao    = new ReuniaoComissaoDAO();
$daoRelatorio  = new RelatorioCorregedoriaDAO();
$daoProcesso   = new ProcessoPromocionalDAO();

$reunioes        = $daoReuniao->buscarTodasPorComissao($comissao);
$relatorios      = $daoRelatorio->buscarTodos();
$processoAtivo   = $daoProcesso->buscarAtivo(); // Processo ativo a ser vinculado às reuniões
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
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h3>Gestão de Reuniões - <?= htmlspecialchars($comissao) ?></h3>
                <p class="text-muted mb-0">
                    <?= htmlspecialchars($nomeComissaoExtenso) ?>: Gerencie a abertura de reuniões, atas e relatórios vinculados.
                </p>
            </div>
            <div>
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modal-cadastrar">
                    <i class="bi bi-plus-circle"></i> Nova Reunião
                </button>
            </div>
        </div>
        <div class="clearfix"><br></div>
    </div>

    <div class="col-md-12">
        <div class="card">
            <h5 class="card-header">Histórico de Reuniões da <?= htmlspecialchars($comissao) ?></h5>
            <div class="card-body">

                <?php if (empty($reunioes)): ?>
                    <p class="text-muted">Nenhuma reunião cadastrada para a <?= htmlspecialchars($comissao) ?> até o momento.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-hover" id="tabela-reunioes">
                            <thead class="thead-light">
                                <tr>
                                    <th class="text-center">Processo</th>
                                    <th class="text-center">Nº da Ata</th>
                                    <th class="text-center">Data da Reunião</th>
                                    <th class="text-center">Relatório Corregedoria</th>
                                    <th class="text-center">Observações</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center" style="min-width: 170px;">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reunioes as $r): ?>
                                    <?php
                                    $statusBadge = match ($r['status']) {
                                        'PREPARACAO'   => '<span class="badge bg-secondary">Em Preparação</span>',
                                        'EM_ANDAMENTO' => '<span class="badge bg-primary">Em Andamento</span>',
                                        'CONCLUIDA'    => '<span class="badge bg-success">Concluída</span>',
                                        default        => '<span class="badge bg-dark">' . htmlspecialchars($r['status']) . '</span>'
                                    };

                                    $relatorioTexto = '—';
                                    if (!empty($r['relatorio_ano']) && !empty($r['relatorio_semestre'])) {
                                        $relatorioTexto = (int) $r['relatorio_semestre'] . 'º Sem/' . (int) $r['relatorio_ano'];
                                    }

                                    $processoTexto = '—';
                                    if (!empty($r['processo_ano']) && !empty($r['processo_semestre'])) {
                                        $processoTexto = (int) $r['processo_semestre'] . 'º Sem/' . (int) $r['processo_ano'];
                                    }
                                    ?>
                                    <tr id="linha-reuniao-<?= (int) $r['id_reuniao_comissao'] ?>">
                                        <td class="text-center align-middle"><?= htmlspecialchars($processoTexto) ?></td>
                                        <td class="text-center align-middle fw-bold col-numero-ata"><?= htmlspecialchars($r['numero_ata']) ?></td>
                                        <td class="text-center align-middle col-data"><?= date('d/m/Y', strtotime($r['data_reuniao'])) ?></td>
                                        <td class="text-center align-middle col-relatorio">
                                            <?php if (!empty($r['relatorio_url'])): ?>
                                                <a href="<?= htmlspecialchars($r['relatorio_url']) ?>" target="_blank" rel="noopener noreferrer" class="badge bg-info text-decoration-none text-dark" title="<?= htmlspecialchars($r['relatorio_descricao'] ?? 'Ver Relatório') ?>">
                                                    <i class="bi bi-file-earmark-pdf"></i> <?= $relatorioTexto ?>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted"><?= $relatorioTexto ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center align-middle col-observacao"><?= htmlspecialchars($r['observacao'] ?? '—') ?></td>
                                        <td class="text-center align-middle col-status"><?= $statusBadge ?></td>
                                        <td class="text-center align-middle">
                                            <div class="d-flex justify-content-center" style="gap: 5px;">
                                                <!-- Entrar na Reunião / Pauta -->
                                                <a href="painel_comissao.php?reuniao_id=<?= (int) $r['id_reuniao_comissao'] ?>" class="btn btn-outline-primary btn-sm" title="Acessar Pauta da Reunião">
                                                    <i class="bi bi-box-arrow-in-right"></i> Entrar
                                                </a>
                                                <!-- Editar -->
                                                <button
                                                    type="button"
                                                    class="btn btn-outline-warning btn-sm btn-editar"
                                                    data-id="<?= (int) $r['id_reuniao_comissao'] ?>"
                                                    data-ata="<?= htmlspecialchars($r['numero_ata']) ?>"
                                                    data-data="<?= htmlspecialchars($r['data_reuniao']) ?>"
                                                    data-relatorio="<?= (int) ($r['relatorio_corregedoria_id'] ?? 0) ?>"
                                                    data-obs="<?= htmlspecialchars($r['observacao'] ?? '') ?>"
                                                    title="Editar Dados da Reunião">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                <!-- Excluir -->
                                                <?php if ($r['status'] !== 'CONCLUIDA'): ?>
                                                    <button
                                                        type="button"
                                                        class="btn btn-outline-danger btn-sm"
                                                        onclick="excluirReuniao(<?= (int) $r['id_reuniao_comissao'] ?>)"
                                                        title="Excluir Reunião">
                                                        <i class="bi bi-trash3-fill"></i>
                                                    </button>
                                                <?php endif; ?>
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
    </div><!-- /col-md-12 -->

</div><!-- /container -->

<!-- ===================== MODAL DE CADASTRO ===================== -->
<div class="modal fade" id="modal-cadastrar" tabindex="-1" aria-labelledby="modal-cadastrar-label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-cadastrar">
                <input type="hidden" name="tipo_comissao" value="<?= htmlspecialchars($comissao) ?>">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-cadastrar-label">Cadastrar Reunião - <?= htmlspecialchars($comissao) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <!-- Processo Promocional Ativo (fixo, não editável) -->
                    <div class="mb-3">
                        <label class="form-label">Processo Promocional <span class="text-danger">*</span></label>
                        <?php if ($processoAtivo): ?>
                            <input type="text" class="form-control" readonly
                                value="<?= (int)$processoAtivo['semestre'] ?>º Semestre / <?= (int)$processoAtivo['ano'] ?> (<?= htmlspecialchars($processoAtivo['status']) ?>)">
                            <input type="hidden" id="cad-processo-id" name="processo_promocional_id" value="<?= (int)$processoAtivo['id'] ?>">
                            <div class="form-text">Reunião será vinculada ao processo promocional ativo.</div>
                        <?php else: ?>
                            <div class="alert alert-warning mb-0" role="alert">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                                Nenhum processo promocional ativo. <a href="gestao_processo_promocional.php">Crie um processo</a> antes de abrir uma reunião.
                            </div>
                            <input type="hidden" id="cad-processo-id" name="processo_promocional_id" value="">
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label for="cad-numero-ata" class="form-label">Número da Ata / Edital <span class="text-danger">*</span></label>
                        <input type="text" id="cad-numero-ata" name="numero_ata" class="form-control" placeholder="Ex: Ata 001/2026 - CPO" required>
                    </div>
                    <div class="mb-3">
                        <label for="cad-data-reuniao" class="form-label">Data da Reunião <span class="text-danger">*</span></label>
                        <input type="date" id="cad-data-reuniao" name="data_reuniao" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="cad-relatorio-id" class="form-label">Vincular Relatório da Corregedoria (opcional)</label>
                        <select id="cad-relatorio-id" name="relatorio_corregedoria_id" class="form-select">
                            <option value="">Nenhum vínculo inicial</option>
                            <?php foreach ($relatorios as $rel): ?>
                                <option value="<?= (int) $rel['id_relatorio'] ?>">
                                    <?= (int) $rel['semestre'] ?>º Semestre / <?= (int) $rel['ano'] ?> <?= !empty($rel['descricao']) ? ' - ' . htmlspecialchars($rel['descricao']) : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="cad-observacao" class="form-label">Observações</label>
                        <textarea id="cad-observacao" name="observacao" class="form-control" rows="2" placeholder="Observações pertinentes à sessão..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success btn-sm" id="btn-salvar-cadastro" <?= !$processoAtivo ? 'disabled' : '' ?>>
                        <span id="spinner-cadastrar" class="spinner-border spinner-border-sm d-none" role="status"></span>
                        Salvar Reunião
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ===================== MODAL DE EDIÇÃO ===================== -->
<div class="modal fade" id="modal-editar" tabindex="-1" aria-labelledby="modal-editar-label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-editar">
                <input type="hidden" id="edit-id" name="id">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-editar-label">Editar Dados da Reunião</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit-numero-ata" class="form-label">Número da Ata <span class="text-danger">*</span></label>
                        <input type="text" id="edit-numero-ata" name="numero_ata" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-data-reuniao" class="form-label">Data da Reunião <span class="text-danger">*</span></label>
                        <input type="date" id="edit-data-reuniao" name="data_reuniao" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-relatorio-id" class="form-label">Relatório da Corregedoria</label>
                        <select id="edit-relatorio-id" name="relatorio_corregedoria_id" class="form-select">
                            <option value="">Nenhum vínculo</option>
                            <?php foreach ($relatorios as $rel): ?>
                                <option value="<?= (int) $rel['id_relatorio'] ?>">
                                    <?= (int) $rel['semestre'] ?>º Semestre / <?= (int) $rel['ano'] ?> <?= !empty($rel['descricao']) ? ' - ' . htmlspecialchars($rel['descricao']) : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit-observacao" class="form-label">Observações</label>
                        <textarea id="edit-observacao" name="observacao" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary btn-sm" id="btn-confirmar-edicao">
                        <span id="spinner-editar" class="spinner-border spinner-border-sm d-none" role="status"></span>
                        Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- Toast Container Flutuante do Bootstrap -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1100">
    <div id="toastApp" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body" id="toastAppMsg"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Fechar"></button>
        </div>
    </div>
</div>

<!-- ===================== JAVASCRIPT ===================== -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        'use strict';

        // Exibe Toast nativo do Bootstrap no canto inferior direito
        function showToast(mensagem, tipo = 'success') {
            const toastEl = document.getElementById('toastApp');
            const msgEl = document.getElementById('toastAppMsg');

            if (!toastEl || !msgEl) {
                alert(mensagem);
                return;
            }

            // Remove cores antigas e adiciona a cor do tipo atual
            toastEl.classList.remove('bg-success', 'bg-danger', 'bg-warning', 'bg-info', 'bg-primary');
            const corBg = (tipo === 'danger') ? 'bg-danger' : ((tipo === 'warning') ? 'bg-warning' : 'bg-success');
            toastEl.classList.add(corBg);

            msgEl.textContent = mensagem;

            const toastInstance = bootstrap.Toast.getOrCreateInstance(toastEl, {
                delay: 3500
            });
            toastInstance.show();
        }

        const API_URL = '../api/reuniao_comissao_api.php';

        const modalCadastrarEl = document.getElementById('modal-cadastrar');
        const modalCadastrar = new bootstrap.Modal(modalCadastrarEl);

        const modalEditarEl = document.getElementById('modal-editar');
        const modalEditar = new bootstrap.Modal(modalEditarEl);

        // ── Cadastro de Reunião ─────────────────────────────────────
        document.getElementById('form-cadastrar').addEventListener('submit', function(e) {
            e.preventDefault();

            const btnSalvar = document.getElementById('btn-salvar-cadastro');
            const spinner = document.getElementById('spinner-cadastrar');

            btnSalvar.disabled = true;
            spinner.classList.remove('d-none');

            const payload = {
                numero_ata: document.getElementById('cad-numero-ata').value.trim(),
                data_reuniao: document.getElementById('cad-data-reuniao').value.trim(),
                tipo_comissao: '<?= htmlspecialchars($comissao) ?>',
                processo_promocional_id: document.getElementById('cad-processo-id').value || null,
                relatorio_corregedoria_id: document.getElementById('cad-relatorio-id').value || null,
                observacao: document.getElementById('cad-observacao').value.trim() || null
            };

            fetch(API_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(payload)
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        modalCadastrar.hide();
                        setTimeout(() => location.reload(), 1200);
                    } else {
                        showToast(data.message || 'Erro ao criar reunião.', 'danger');
                    }
                })
                .catch(err => {
                    console.error('Erro no cadastro:', err);
                    showToast('Erro de comunicação com o servidor.', 'danger');
                })
                .finally(() => {
                    btnSalvar.disabled = false;
                    spinner.classList.add('d-none');
                });
        });

        // ── Abrir Modal de Edição ───────────────────────────────────
        document.querySelectorAll('.btn-editar').forEach(function(btn) {
            btn.addEventListener('click', function() {
                document.getElementById('edit-id').value = this.dataset.id;
                document.getElementById('edit-numero-ata').value = this.dataset.ata;
                document.getElementById('edit-data-reuniao').value = this.dataset.data;
                document.getElementById('edit-relatorio-id').value = this.dataset.relatorio || '';
                document.getElementById('edit-observacao').value = this.dataset.obs || '';

                modalEditar.show();
            });
        });

        // ── Salvar Edição ───────────────────────────────────────────
        document.getElementById('form-editar').addEventListener('submit', async function(e) {
            e.preventDefault();

            const id = document.getElementById('edit-id').value;
            const btnConfirmar = document.getElementById('btn-confirmar-edicao');
            const spinner = document.getElementById('spinner-editar');
            const relVal = document.getElementById('edit-relatorio-id').value;

            btnConfirmar.disabled = true;
            spinner.classList.remove('d-none');

            const payload = {
                numero_ata: document.getElementById('edit-numero-ata').value.trim(),
                data_reuniao: document.getElementById('edit-data-reuniao').value.trim(),
                relatorio_corregedoria_id: relVal ? parseInt(relVal) : null,
                observacao: document.getElementById('edit-observacao').value.trim() || null
            };

            try {
                const response = await fetch(API_URL + '?id=' + id, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const rawText = await response.text();
                let data;
                try {
                    data = JSON.parse(rawText);
                } catch (parseErr) {
                    console.error('Resposta não-JSON do servidor:', rawText);
                    throw new Error('Formato de resposta inválido.');
                }

                if (data.success) {
                    showToast(data.message || 'Dados atualizados com sucesso!', 'success');
                    modalEditar.hide();
                    setTimeout(() => location.reload(), 1200);
                } else {
                    showToast(data.message || 'Erro ao atualizar.', 'danger');
                }
            } catch (err) {
                console.error('Erro na edição:', err);
                showToast('Erro ao processar atualização da reunião.', 'danger');
            } finally {
                btnConfirmar.disabled = false;
                spinner.classList.add('d-none');
            }
        });

        // ── Excluir Reunião ─────────────────────────────────────────
        window.excluirReuniao = function(id) {
            if (!confirm('Deseja realmente excluir esta sessão de reunião? Esta ação não pode ser desfeita.')) {
                return;
            }

            fetch(API_URL + '?id=' + id, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        document.getElementById('linha-reuniao-' + id)?.remove();
                        if (document.querySelectorAll('#tabela-reunioes tbody tr').length === 0) {
                            setTimeout(() => location.reload(), 1000);
                        }
                    } else {
                        showToast(data.message || 'Erro ao excluir.', 'danger');
                    }
                })
                .catch(err => {
                    console.error('Erro ao excluir:', err);
                    showToast('Erro de comunicação com o servidor.', 'danger');
                });
        };
    });
</script>

<?php include_once '../Views/footer2.php'; ?>