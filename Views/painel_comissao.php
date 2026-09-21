<?php

/**
 * Views/painel_comissao.php
 * View: Painel Unificado da Reunião (CPO / CPP).
 * 
 * - Aba 1: Documentos (Consulta das Pastas Promocionais / Art. 78 Dec. 2.268/14)
 * - Aba 2: 1ª Pauta: Impedimentos Legais (Arts. 21 e 36 da Lei 10.076/14 e Art. 78 Dec. 2.268/14)
 * - Aba 3: 2ª Pauta: Quadro de Acesso (QA)
 * - Aba 4: 3ª Pauta: Proposta de Promoção (Visualização em Cards)
 */

require_once '../Controllers/nivel_gestor.php';
require_once '../ConexaoDB/conexao.php';
require_once '../vendor/autoload.php';

use App\DAO\ReuniaoComissaoDAO;
use App\DAO\RelatorioCorregedoriaDAO;
use App\Config\CatalogoImpedimentos;

$reuniaoId = isset($_GET['reuniao_id']) ? (int)$_GET['reuniao_id'] : 0;
if (!$reuniaoId) {
    header('Location: gestao_reunioes.php');
    exit();
}

$daoReuniao = new ReuniaoComissaoDAO();
$reuniao = $daoReuniao->buscarPorId($reuniaoId);
if (!$reuniao) {
    die("Reunião não encontrada.");
}

$daoRelatorio = new RelatorioCorregedoriaDAO();
$relatorios = $daoRelatorio->buscarTodos();

$opcoesImpedimentos = CatalogoImpedimentos::getOpcoesParaJs();

include_once './header2.php';
?>

<div class="container mt-3">

    <!-- Toast de feedback -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1100">
        <div id="toastT1" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="toastT1Msg"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Fechar"></button>
            </div>
        </div>
    </div>

    <!-- Breadcrumb / Navegação -->
    <div class="col-md-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="pagina_gestao.php">Painel Gestão</a></li>
                <li class="breadcrumb-item"><a href="gestao_reunioes.php?comissao=<?= $reuniao['tipo_comissao'] ?>">Reuniões (<?= $reuniao['tipo_comissao'] ?>)</a></li>
                <li class="breadcrumb-item active" aria-current="page">Painel da Reunião</li>
            </ol>
        </nav>
        <hr>
    </div>

    <!-- Cabeçalho da Reunião -->
    <div class="card mb-4 bg-light">
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <h4>
                        <i class="bi bi-person-workspace text-primary"></i>
                        <strong>Ata Nº <?= htmlspecialchars($reuniao['numero_ata']) ?></strong> –
                        Comissão: <span class="badge bg-primary"><?= $reuniao['tipo_comissao'] ?></span>
                    </h4>
                    <p class="text-muted mb-0">
                        Data da Reunião: <strong><?= date('d/m/Y', strtotime($reuniao['data_reuniao'])) ?></strong> |
                        Processo: <strong><?= $reuniao['processo_semestre'] ?>º Semestre / <?= $reuniao['processo_ano'] ?></strong>
                    </p>
                </div>
                <div class="col-md-4 text-md-end d-flex flex-column align-items-end justify-content-center">
                    <div class="mb-2">
                        <span class="fs-6 me-2">Status da Reunião:</span>
                        <?php
                        $statusColor = match ($reuniao['status']) {
                            'PREPARACAO'   => 'bg-secondary',
                            'EM_ANDAMENTO' => 'bg-primary',
                            'CONCLUIDA'    => 'bg-success',
                            default        => 'bg-dark'
                        };
                        ?>
                        <span class="badge <?= $statusColor ?> fs-6"><?= htmlspecialchars($reuniao['status']) ?></span>
                    </div>
                    <div>
                        <a href="editar_ata.php?reuniao_id=<?= $reuniaoId ?>" class="btn btn-outline-primary btn-sm mt-1">
                            <i class="bi bi-file-earmark-richtext"></i> Redigir / Visualizar Ata
                        </a>

                        <?php if ($reuniao['status'] === 'PREPARACAO'): ?>
                            <button class="btn btn-success btn-sm mt-1 ms-2" onclick="iniciarReuniao()">
                                <i class="bi bi-play-fill"></i> Iniciar Reunião
                            </button>
                        <?php elseif ($reuniao['status'] === 'EM_ANDAMENTO'): ?>
                            <button class="btn btn-danger btn-sm mt-1 ms-2" onclick="finalizarReuniao()">
                                <i class="bi bi-lock-fill"></i> Finalizar Reunião
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Área de Alertas -->
    <div id="areaAlerta"></div>

    <!-- Abas do Painel: Documentos na 1ª Posição -->
    <ul class="nav nav-tabs mb-4" id="painelTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="documentos-tab" data-bs-toggle="tab" data-bs-target="#documentos" type="button" role="tab" aria-controls="documentos" aria-selected="true" onclick="carregarAbaDocumentos()">
                <i class="bi bi-folder2-open text-primary me-1"></i> <strong>Documentos</strong> (Pastas)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="pauta1-tab" data-bs-toggle="tab" data-bs-target="#pauta1" type="button" role="tab" aria-controls="pauta1" aria-selected="false" onclick="carregarPauta1()">
                <strong>1ª Pauta</strong>: Impedimentos Legais
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="pauta2-tab" data-bs-toggle="tab" data-bs-target="#pauta2" type="button" role="tab" aria-controls="pauta2" aria-selected="false" onclick="carregarPauta2()">
                <strong>2ª Pauta</strong>: Quadro de Acesso (QA)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="pauta3-tab" data-bs-toggle="tab" data-bs-target="#pauta3" type="button" role="tab" aria-controls="pauta3" aria-selected="false" onclick="carregarPauta3()">
                <strong>3ª Pauta</strong>: Proposta de Promoção
            </button>
        </li>
    </ul>

    <!-- Conteúdo das Abas -->
    <div class="tab-content" id="painelTabContent">

        <!-- ABA 1: DOCUMENTOS (PASTAS PROMOCIONAIS - ART. 78 DEC. 2.268/14) -->
        <div class="tab-pane fade show active" id="documentos" role="tabpanel" aria-labelledby="documentos-tab">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <div>
                        <strong class="fs-6 text-dark"><i class="bi bi-folder-check text-primary me-2"></i> Consulta Documental das Pastas Promocionais</strong>
                        <div class="small text-muted">Auditoria de documentos entregues e pendências com base no Art. 78 do Decreto Estadual nº 2.268/2014.</div>
                    </div>
                    <span class="badge bg-primary p-2"><i class="bi bi-shield-check"></i> Consulta Somente Leitura</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped align-middle mb-0" id="tabelaDocumentos">
                            <thead class="table-dark">
                                <tr>
                                    <th style="width: 25%;">Candidato (Nome / Posto)</th>
                                    <th style="width: 20%;">Posto de Destino</th>
                                    <th class="text-center" style="width: 20%;">Corte (LQ)</th>
                                    <th class="text-center" style="width: 20%;">Pasta Promocional</th>
                                    <th class="text-center" style="width: 15%;">Ação</th>
                                </tr>
                            </thead>
                            <tbody id="corpoDocumentos">
                                <!-- Preenchido dinamicamente via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ABA 2: PRIMEIRA PAUTA (Impedimentos Legais) -->
        <div class="tab-pane fade" id="pauta1" role="tabpanel" aria-labelledby="pauta1-tab">

            <!-- Card de Vínculo do Relatório da Corregedoria -->
            <div class="card mb-4 shadow-sm border-0">
                <div class="card-header bg-light">
                    <strong><i class="bi bi-file-earmark-text text-primary me-1"></i> Vínculo de Relatório da Corregedoria</strong>
                </div>
                <div class="card-body" id="container_status_relatorio">
                    <?php if (!empty($reuniao['relatorio_corregedoria_id'])): ?>
                        <?php
                        $relVinculado = array_filter($relatorios, fn($r) => $r['id_relatorio'] == $reuniao['relatorio_corregedoria_id']);
                        $relVinculado = reset($relVinculado);
                        $textoRelatorio = $relVinculado ? "{$relVinculado['semestre']}º Semestre / {$relVinculado['ano']}" . (!empty($relVinculado['descricao']) ? ' - ' . htmlspecialchars($relVinculado['descricao']) : '') : "Relatório Vinculado";
                        ?>
                        <p class="mb-2">
                            <strong>Status:</strong> Há um relatório oficial vinculado a esta sessão deliberativa.
                            <br>
                            <strong>Relatório:</strong> <span class="text-primary fw-bold"><?= $textoRelatorio ?></span>
                        </p>
                        <div class="mt-2">
                            <a href="<?= htmlspecialchars($reuniao['relatorio_url'] ?? '#') ?>" target="_blank" class="btn btn-sm btn-primary me-2">
                                <i class="bi bi-eye"></i> Visualizar Relatório
                            </a>
                            <button class="btn btn-sm btn-outline-danger" onclick="desvincularRelatorio()">
                                <i class="bi bi-trash"></i> Desvincular
                            </button>
                        </div>
                    <?php else: ?>
                        <p class="mb-2 text-muted">Nenhum relatório oficial da corregedoria vinculado a esta sessão.</p>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalRelatorio">
                            <i class="bi bi-link-45deg"></i> Vincular Relatório
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tabela de Lançamento de Impedimentos -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <p class="text-muted mb-0">
                    Aponte as restrições normativas (Arts. 21 e 36 da Lei 10.076/14 e Art. 78 do Dec. 2.268/14). Simule a fila para conferir convocações da reserva.
                </p>
                <div class="d-flex" style="gap: 10px;">
                    <button class="btn btn-outline-primary" id="btn_simular_t2" onclick="processarAcaoPauta1('simular')">
                        <i class="bi bi-cpu"></i> Simular Fila
                    </button>
                    <button class="btn btn-success" id="btn_salvar_t2" onclick="processarAcaoPauta1('salvar')">
                        <i class="bi bi-save"></i> Salvar Impedimentos
                    </button>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-light">
                    <strong><i class="bi bi-shield-exclamation text-danger"></i> Julgamento de Restrições e Convocação de Reservas</strong>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped align-middle mb-0" id="tabelaPauta1">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 25%;">Candidato (Nome/Posto)</th>
                                    <th class="text-center" style="width: 14%;">Situação Atual</th>
                                    <th class="text-center" style="width: 14%;">Situação Simulada</th>
                                    <th style="width: 47%;">Apontar Restrições / Impedimentos Legais</th>
                                </tr>
                            </thead>
                            <tbody id="corpoPauta1">
                                <!-- Preenchido dinamicamente via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ABA 3: SEGUNDA PAUTA (Quadro de Acesso - QA) -->
        <div class="tab-pane fade" id="pauta2" role="tabpanel" aria-labelledby="pauta2-tab">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-0 text-primary"><i class="bi bi-award-fill"></i> Homologação do Quadro de Acesso (QA)</h5>
                    <small class="text-muted">Relação consolidada dos militares aptos no Limite Quantitativo (QAA e QAM) e Reservas.</small>
                </div>
                <div>
                    <button class="btn btn-success" id="btn_homologar_qa" onclick="homologarQuadroAcesso()">
                        <i class="bi bi-check2-all"></i> Homologar Quadro de Acesso
                    </button>
                </div>
            </div>

            <!-- Cards Resumo Estatístico -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card border-primary shadow-sm">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="text-muted mb-1">Integrantes no QA</h6>
                                <h3 class="mb-0 text-primary fw-bold" id="card_total_qa">0</h3>
                            </div>
                            <i class="bi bi-person-check-fill fs-1 text-primary"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-secondary shadow-sm">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="text-muted mb-1">Quadro Suplementar (Reserva)</h6>
                                <h3 class="mb-0 text-secondary fw-bold" id="card_total_reserva">0</h3>
                            </div>
                            <i class="bi bi-people-fill fs-1 text-secondary"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-danger shadow-sm">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="text-muted mb-1">Inabilitados / Impedidos</h6>
                                <h3 class="mb-0 text-danger fw-bold" id="card_total_impedidos">0</h3>
                            </div>
                            <i class="bi bi-person-x-fill fs-1 text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Container das Tabelas de QA -->
            <div id="container_tabelas_qa">
                <div class="text-center py-4 text-muted">
                    <div class="spinner-border text-primary" role="status"></div><br>Carregando Quadro de Acesso...
                </div>
            </div>
        </div>

        <!-- ABA 4: TERCEIRA PAUTA (Proposta de Promoção - Visualização em Cards) -->
        <div class="tab-pane fade" id="pauta3" role="tabpanel" aria-labelledby="pauta3-tab">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-0 text-success"><i class="bi bi-send-check-fill"></i> Proposta Oficial de Promoção</h5>
                    <small class="text-muted">Visualização dos militares contemplados pelas vagas previstas (3 candidatos por vaga no Merecimento e N = VP na Antiguidade).</small>
                </div>
                <span class="badge bg-secondary p-2"><i class="bi bi-eye"></i> Somente Leitura</span>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-12">
                    <div class="card border-success shadow-sm">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="text-muted mb-1 text-uppercase fw-bold small">Total de Militares Propostos à Promoção</h6>
                                <h2 class="mb-0 text-success fw-bold" id="card_total_propostos">0</h2>
                                <small class="text-muted">Somatório dos contemplados para envio ao Governador do Estado</small>
                            </div>
                            <i class="bi bi-award-fill fs-1 text-success opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Container dos Cards de Proposta -->
            <div id="container_cards_proposta">
                <div class="text-center py-4 text-muted">
                    <div class="spinner-border text-success" role="status"></div><br>Carregando Proposta de Promoção...
                </div>
            </div>
        </div>

    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: RESUMO DOCUMENTAL DA PASTA PROMOCIONAL (SOMENTE LEITURA)           -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalResumoDocumentos" tabindex="-1" aria-labelledby="modalResumoDocLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <div>
                    <h5 class="modal-title fw-bold text-primary mb-0" id="modalResumoDocLabel">
                        <i class="bi bi-folder2-open me-2"></i> Pasta Promocional do Candidato
                    </h5>
                    <div class="small text-muted" id="modalResumoMilitarHeader">Carregando dados do militar...</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body p-3" id="modalResumoDocBody">
                <div class="text-center py-4 text-muted">
                    <div class="spinner-border text-primary" role="status"></div><br>Buscando documentos da pasta...
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Relatório da Corregedoria -->
<div class="modal fade" id="modalRelatorio" tabindex="-1" aria-labelledby="modalRelatorioLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalRelatorioLabel"><i class="bi bi-file-earmark-text"></i> Vincular Relatório Oficial</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <label for="select_relatorio" class="form-label">Selecione o Relatório da Corregedoria</label>
                <select class="form-select" id="select_relatorio">
                    <option value="">-- Selecione --</option>
                    <?php foreach ($relatorios as $rel): ?>
                        <option value="<?= $rel['id_relatorio'] ?>" <?= $reuniao['relatorio_corregedoria_id'] == $rel['id_relatorio'] ? 'selected' : '' ?>>
                            <?= $rel['semestre'] ?>º Semestre / <?= $rel['ano'] ?> <?= !empty($rel['descricao']) ? ' - ' . htmlspecialchars($rel['descricao']) : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btn_salvar_relatorio" onclick="salvarRelatorioReuniao()">
                    <i class="bi bi-save"></i> Vincular
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script>
    const reuniaoId = <?= $reuniaoId ?>;
    const API_T2 = '../api/criterio_falho_api.php';
    const API_QA = '../api/quadro_acesso_api.php';
    const API_PASTA = '../api/pasta_promocional_resumo_api.php';
    const isReuniaoConcluida = <?= ($reuniao['status'] === 'CONCLUIDA') ? 'true' : 'false' ?>;

    const catalogoOpcoes = <?= json_encode($opcoesImpedimentos) ?>;

    let candidatosList = [];
    let impedimentosSalvos = {};
    let sugestoesLtip = {};

    document.addEventListener('DOMContentLoaded', () => {
        carregarAbaDocumentos();
    });

    // =========================================================================
    // UTILITÁRIOS & FEEDBACK
    // =========================================================================

    function exibirAlerta(mensagem, tipo = 'success') {
        const area = document.getElementById('areaAlerta');
        if (!area) return;
        area.innerHTML = `
            <div class="alert alert-${tipo} alert-dismissible fade show" role="alert">
                ${mensagem}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>`;
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }

    function exibirToast(mensagem, tipo = 'success') {
        const toastEl = document.getElementById('toastT1');
        if (!toastEl) return;
        toastEl.classList.remove('bg-success', 'bg-danger', 'bg-warning');
        toastEl.classList.add(`bg-${tipo}`);
        const msgEl = document.getElementById('toastT1Msg');
        if (msgEl) msgEl.textContent = mensagem;
        bootstrap.Toast.getOrCreateInstance(toastEl, {
            delay: 3000
        }).show();
    }

    function aplicarBloqueioConclusao() {
        if (!isReuniaoConcluida) return;

        const inputs = document.querySelectorAll(
            '#documentos input, #documentos select, #documentos button, ' +
            '#pauta1 input, #pauta1 select, #pauta1 button, ' +
            '#pauta2 input, #pauta2 select, #pauta2 button, ' +
            '#pauta3 input, #pauta3 select, #pauta3 button'
        );
        inputs.forEach(el => {
            // Permite que o botão de ver pasta continue clicável em modo leitura
            if (!el.classList.contains('btn-ver-pasta')) {
                el.disabled = true;
            }
        });

        const links = document.querySelectorAll('#pauta1 a, #pauta2 a, #pauta3 a');
        links.forEach(link => {
            if (!link.closest('#container_status_relatorio')) {
                link.classList.add('disabled');
                link.style.pointerEvents = 'none';
            }
        });

        const btnVerRelatorio = document.querySelector('#container_status_relatorio a');
        if (btnVerRelatorio) {
            btnVerRelatorio.classList.remove('disabled');
            btnVerRelatorio.style.pointerEvents = 'auto';
        }
    }

    // =========================================================================
    // ABA 1: DOCUMENTOS (PASTAS PROMOCIONAIS)
    // =========================================================================

    async function carregarAbaDocumentos() {
        try {
            const res = await fetch(`${API_T2}?reuniao_id=${reuniaoId}`);
            const data = await res.json();
            if (data.success) {
                candidatosList = data.candidatos || [];
                impedimentosSalvos = data.impedimentos || {};
                sugestoesLtip = data.sugestoes_ltip || {};

                renderizarAbaDocumentos();
            } else {
                exibirAlerta('Erro ao carregar candidatos: ' + data.message, 'danger');
            }
        } catch (err) {
            exibirAlerta('Erro de rede ao buscar dados.', 'danger');
            console.error(err);
        }
    }

    function renderizarAbaDocumentos() {
        const tbody = document.getElementById('corpoDocumentos');
        if (!tbody) return;
        tbody.innerHTML = '';

        if (candidatosList.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">Nenhum militar no Limite Quantitativo deste processo.</td></tr>';
            return;
        }

        let ultimoQuadro = null;

        candidatosList.forEach(c => {
            const quadroNome = c.nome_quadro || 'Quadro Geral';

            if (quadroNome !== ultimoQuadro) {
                ultimoQuadro = quadroNome;
                const trQuadro = document.createElement('tr');
                trQuadro.className = 'table-primary border-top border-2 border-primary';
                trQuadro.innerHTML = `
                    <td colspan="5" class="py-2 fs-6 fw-bold text-dark bg-opacity-25">
                        <i class="bi bi-diagram-3-fill text-primary me-2"></i> ${quadroNome}
                    </td>
                `;
                tbody.appendChild(trQuadro);
            }

            const tr = document.createElement('tr');
            const limiteTxt = c.dentro_do_limite == 1 ? 'No Limite' : 'Reserva';
            const limiteColor = c.dentro_do_limite == 1 ? 'badge bg-primary' : 'badge bg-secondary';

            tr.innerHTML = `
                <td><strong>${c.posto_grad_mil}</strong> - ${c.nome}</td>
                <td>${c.posto_grad_destino}</td>
                <td class="text-center"><span class="${limiteColor}">${limiteTxt}</span></td>
                <td class="text-center"><span class="badge bg-light text-dark border"><i class="bi bi-folder-check text-primary"></i> Processo Vigente</span></td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-primary btn-ver-pasta" 
                            onclick="abrirModalDocumentos(${c.militar_id}, '${c.nome.replace(/'/g, "\\'")}', '${c.posto_grad_mil}')">
                        <i class="bi bi-folder2-open"></i> Ver Documentos
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });

        aplicarBloqueioConclusao();
    }

    async function abrirModalDocumentos(militarId, militarNome, militarPosto) {
        const modalHeaderEl = document.getElementById('modalResumoMilitarHeader');
        const modalBodyEl = document.getElementById('modalResumoDocBody');
        const modalEl = document.getElementById('modalResumoDocumentos');

        modalHeaderEl.innerHTML = `<strong>${militarPosto} ${militarNome}</strong> | Processo: <?= $reuniao['processo_semestre'] ?>º Semestre / <?= $reuniao['processo_ano'] ?>`;
        modalBodyEl.innerHTML = '<div class="text-center py-4 text-muted"><div class="spinner-border text-primary" role="status"></div><br>Consultando documentos na pasta promocional...</div>';

        const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
        modalInstance.show();

        try {
            const res = await fetch(`${API_PASTA}?militar_id=${militarId}&reuniao_id=${reuniaoId}`);
            const data = await res.json();

            if (!data.success) {
                modalBodyEl.innerHTML = `<div class="alert alert-danger mb-0"><i class="bi bi-exclamation-triangle"></i> ${data.message}</div>`;
                return;
            }

            if (!data.pasta_encontrada) {
                modalBodyEl.innerHTML = `
                    <div class="alert alert-warning mb-0 text-center py-4">
                        <i class="bi bi-folder-x fs-1 text-warning d-block mb-2"></i>
                        <h6 class="fw-bold">Nenhuma Pasta Promocional Criada</h6>
                        <p class="small text-muted mb-0">Ainda não há registro de pasta promocional para este militar referente a este processo.</p>
                    </div>
                `;
                return;
            }

            // Renderiza o resumo documental
            let linhasDocs = '';
            data.documentos.forEach((d, idx) => {
                const isEntregue = (!d.doc_promo_url || d.doc_promo_url.trim() === '') ? false : true;
                const statusBadge = isEntregue ?
                    '<span class="badge bg-success"><i class="bi bi-check-circle"></i> Entregue</span>' :
                    '<span class="badge bg-danger"><i class="bi bi-x-circle"></i> Pendente / Não Entregue</span>';

                const linkBtn = isEntregue ?
                    `<a href="${d.doc_promo_url}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-primary">
                         <i class="bi bi-eye"></i> Visualizar PDF
                       </a>` :
                    '<span class="text-muted small">Sem Arquivo</span>';

                linhasDocs += `
                    <tr>
                        <td class="text-center fw-bold text-muted">${idx + 1}</td>
                        <td>
                            <strong>${d.nome_legivel}</strong>
                            <div class="small text-muted">${d.doc_promo_nome}</div>
                        </td>
                        <td class="text-center">${statusBadge}</td>
                        <td class="text-center">${linkBtn}</td>
                    </tr>
                `;
            });

            modalBodyEl.innerHTML = `
                <div class="d-flex justify-content-between align-items-center mb-3 p-2 bg-light rounded border">
                    <div>
                        <span class="badge bg-dark me-2">Pasta Nº ${data.pasta.id}</span>
                        <span class="small text-muted">Total de Itens: <strong>${data.totais.total}</strong></span>
                    </div>
                    <div>
                        <span class="badge bg-success me-1">${data.totais.entregues} Entregue(s)</span>
                        <span class="badge bg-danger">${data.totais.pendentes} Pendente(s)</span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 5%;">#</th>
                                <th style="width: 50%;">Documento (Art. 78 Dec. 2.268/14)</th>
                                <th class="text-center" style="width: 25%;">Situação</th>
                                <th class="text-center" style="width: 20%;">Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${linhasDocs}
                        </tbody>
                    </table>
                </div>
            `;

        } catch (err) {
            console.error(err);
            modalBodyEl.innerHTML = '<div class="alert alert-danger mb-0">Erro de comunicação ao carregar a pasta do militar.</div>';
        }
    }

    // =========================================================================
    // ABA 2: 1ª PAUTA: IMPEDIMENTOS LEGAIS
    // =========================================================================

    async function carregarPauta1() {
        await carregarAbaDocumentos();
        renderizarPauta1();
    }

    function renderizarPauta1(candidatosSimulados = null) {
        const tbody = document.getElementById('corpoPauta1');
        if (!tbody) return;
        tbody.innerHTML = '';

        if (candidatosList.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4">Nenhum militar cadastrado no snapshot deste processo.</td></tr>';
            aplicarBloqueioConclusao();
            return;
        }

        let ultimoQuadro = null;

        candidatosList.forEach(c => {
            const quadroNome = c.nome_quadro || 'Quadro Geral';

            if (quadroNome !== ultimoQuadro) {
                ultimoQuadro = quadroNome;
                const trQuadro = document.createElement('tr');
                trQuadro.className = 'table-primary border-top border-2 border-primary';
                trQuadro.innerHTML = `
                    <td colspan="4" class="py-2 fs-6 fw-bold text-dark bg-opacity-25">
                        <i class="bi bi-diagram-3-fill text-primary me-2"></i> ${quadroNome}
                    </td>
                `;
                tbody.appendChild(trQuadro);
            }

            const tr = document.createElement('tr');
            const militarImps = impedimentosSalvos[c.militar_id] || [];
            const temImpedimento = militarImps.length > 0 && militarImps.some(i => i.inciso_artigo_36);

            const tdMilitar = document.createElement('td');
            tdMilitar.innerHTML = `<strong>${c.posto_grad_mil}</strong> - ${c.nome}<br><small class="text-muted">Destino: ${c.posto_grad_destino}</small>`;
            tr.appendChild(tdMilitar);

            const tdSituacaoInicial = document.createElement('td');
            tdSituacaoInicial.className = 'text-center';
            if (temImpedimento || c.situacao === 'ELIMINADO' || c.situacao === 'ELIMINADO_T2' || c.situacao === 'ELIMINADO_T1') {
                tdSituacaoInicial.innerHTML = '<span class="badge bg-danger">Impedido / Inapto</span>';
            } else {
                const limiteTxt = c.dentro_do_limite == 1 ? 'No Limite' : 'Reserva';
                const limiteColor = c.dentro_do_limite == 1 ? 'bg-primary' : 'bg-secondary';
                tdSituacaoInicial.innerHTML = `<span class="badge ${limiteColor}">Apto (${limiteTxt})</span>`;
            }
            tr.appendChild(tdSituacaoInicial);

            const tdSituacaoSimulada = document.createElement('td');
            tdSituacaoSimulada.className = 'text-center';
            if (candidatosSimulados) {
                const sim = candidatosSimulados.find(s => parseInt(s.militar_id) === parseInt(c.militar_id));
                if (sim) {
                    if (sim.situacao === 'ELIMINADO' || sim.situacao === 'ELIMINADO_T2' || sim.situacao === 'ELIMINADO_T1') {
                        tdSituacaoSimulada.innerHTML = '<span class="badge bg-danger">Impedido / Inapto</span>';
                    } else {
                        const limiteTxt = sim.dentro_do_limite == 1 ? 'No Limite' : 'Reserva';
                        const limiteColor = sim.dentro_do_limite == 1 ? 'bg-primary' : 'bg-secondary';
                        tdSituacaoSimulada.innerHTML = `<span class="badge ${limiteColor}">Apto (${limiteTxt})</span>`;
                    }
                } else {
                    tdSituacaoSimulada.innerHTML = '—';
                }
            } else {
                tdSituacaoSimulada.innerHTML = '<span class="text-muted small">Aguardando...</span>';
            }
            tr.appendChild(tdSituacaoSimulada);

            const tdAcoes = document.createElement('td');

            let sugestaoHtml = '';
            if (sugestoesLtip[c.militar_id]) {
                sugestoaoHtml = `<div class="alert alert-warning p-1 mb-2 small py-0 px-2"><i class="bi bi-exclamation-triangle"></i> <strong>LTIP Detectada:</strong> ${sugestoesLtip[c.militar_id]} dias no posto. Opção L36_VI sugerida.</div>`;
            }

            let impedimentosHtml = `<div id="impedimentos_container_${c.militar_id}">`;

            if (militarImps.length > 0) {
                militarImps.forEach(imp => {
                    impedimentosHtml += gerarHtmlLinhaImpedimento(c.militar_id, imp.inciso_artigo_36, imp.descricao_impedimento);
                });
            } else {
                impedimentosHtml += gerarHtmlLinhaImpedimento(c.militar_id, '', '');
            }

            impedimentosHtml += `</div>
            <button type="button" class="btn btn-sm btn-outline-secondary mt-1" onclick="adicionarLinhaImpedimento(${c.militar_id})">
                <i class="bi bi-plus-circle"></i> Adicionar Outro Impedimento
            </button>`;

            tdAcoes.innerHTML = sugestaoHtml + impedimentosHtml;
            tr.appendChild(tdAcoes);

            tbody.appendChild(tr);
        });

        aplicarBloqueioConclusao();
    }

    function gerarHtmlLinhaImpedimento(militarId, codigoSalvo, descSalva) {
        let gruposHtml = '<option value="">-- Sem impedimentos / Apto --</option>';
        const gruposUnicos = [...new Set(catalogoOpcoes.map(i => i.grupo))];

        gruposUnicos.forEach(grupoNome => {
            gruposHtml += `<optgroup label="${grupoNome}">`;
            catalogoOpcoes.filter(i => i.grupo === grupoNome).forEach(op => {
                const selected = (codigoSalvo === op.codigo) ? 'selected' : '';
                gruposHtml += `<option value="${op.codigo}" ${selected}>${op.desc}</option>`;
            });
            gruposHtml += `</optgroup>`;
        });

        return `
            <div class="row g-2 align-items-center impedimento-row mb-2" data-militar-id="${militarId}">
                <div class="col-md-6">
                    <select class="form-select form-select-sm select-inciso" onchange="toggleJustificativaLinha(this)">
                        ${gruposHtml}
                    </select>
                </div>
                <div class="col-md-5">
                    <input type="text" class="form-control form-control-sm input-justificativa" 
                           placeholder="Justificativa / Parecer..." 
                           value="${descSalva || ''}" 
                           style="display: ${codigoSalvo ? 'block' : 'none'};">
                </div>
                <div class="col-md-1 text-end">
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removerLinhaImpedimento(this)" title="Remover">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        `;
    }

    function toggleJustificativaLinha(select) {
        const row = select.closest('.impedimento-row');
        const input = row.querySelector('.input-justificativa');
        if (select.value) {
            input.style.display = 'block';
            input.focus();
        } else {
            input.style.display = 'none';
            input.value = '';
        }
    }

    function adicionarLinhaImpedimento(militarId) {
        if (isReuniaoConcluida) return;
        const container = document.getElementById(`impedimentos_container_${militarId}`);
        if (container) {
            container.insertAdjacentHTML('beforeend', gerarHtmlLinhaImpedimento(militarId, '', ''));
        }
    }

    function removerLinhaImpedimento(btn) {
        if (isReuniaoConcluida) return;
        const row = btn.closest('.impedimento-row');
        if (row) row.remove();
    }

    async function processarAcaoPauta1(acao) {
        if (isReuniaoConcluida) return;

        const btnSimular = document.getElementById('btn_simular_t2');
        const btnSalvar = document.getElementById('btn_salvar_t2');

        if (btnSimular) btnSimular.disabled = true;
        if (btnSalvar) btnSalvar.disabled = true;

        const impedimentosList = [];
        const rows = document.querySelectorAll('.impedimento-row');

        rows.forEach(row => {
            const militarId = row.dataset.militarId;
            const select = row.querySelector('.select-inciso');
            const input = row.querySelector('.input-justificativa');

            const codigo = select.value;
            const descricao = input.value.trim();

            if (codigo) {
                impedimentosList.push({
                    militar_id: parseInt(militarId),
                    inciso_artigo_36: codigo,
                    descricao_impedimento: descricao || `Incidiu na restrição ${codigo}`
                });
            }
        });

        try {
            const response = await fetch(API_T2, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    acao: acao,
                    reuniao_id: reuniaoId,
                    impedimentos: impedimentosList
                })
            });
            const data = await response.json();

            if (data.success) {
                if (acao === 'simular') {
                    exibirAlerta('Simulação de fila calculada. Confira as movimentações.', 'info');
                    renderizarPauta1(data.candidatos);
                } else {
                    exibirAlerta('Impedimentos salvos com sucesso e reservas convocados!', 'success');
                    carregarPauta1();
                }
            } else {
                exibirAlerta('Erro: ' + data.message, 'danger');
            }
        } catch (err) {
            exibirAlerta('Erro de conexão ao processar ação.', 'danger');
            console.error(err);
        } finally {
            if (btnSimular) btnSimular.disabled = false;
            if (btnSalvar) btnSalvar.disabled = false;
        }
    }

    // =========================================================================
    // ABA 3: 2ª PAUTA: QUADRO DE ACESSO (QA)
    // =========================================================================

    async function carregarPauta2() {
        const container = document.getElementById('container_tabelas_qa');
        container.innerHTML = '<div class="text-center py-4 text-muted"><div class="spinner-border text-primary" role="status"></div><br>Carregando Quadro de Acesso...</div>';

        try {
            const res = await fetch(`${API_QA}?reuniao_id=${reuniaoId}`);
            const data = await res.json();

            if (data.success) {
                document.getElementById('card_total_qa').textContent = data.total_qa;
                document.getElementById('card_total_reserva').textContent = data.total_reserva;
                document.getElementById('card_total_impedidos').textContent = data.total_impedidos;

                const btnHomologar = document.getElementById('btn_homologar_qa');
                if (data.is_homologado) {
                    btnHomologar.className = 'btn btn-outline-success';
                    btnHomologar.innerHTML = '<i class="bi bi-check-circle-fill"></i> QA Homologado (Atualizar)';
                }

                renderizarPauta2(data);
            } else {
                exibirAlerta('Erro ao carregar QA: ' + data.message, 'danger');
            }
        } catch (err) {
            exibirAlerta('Erro de comunicação ao carregar QA.', 'danger');
            console.error(err);
        }
    }

    function renderizarPauta2(data) {
        const container = document.getElementById('container_tabelas_qa');
        container.innerHTML = '';

        if (!data.qa_agrupado || data.qa_agrupado.length === 0) {
            container.innerHTML = '<div class="alert alert-warning">Nenhum militar elegível para compor o Quadro de Acesso neste processo.</div>';
            aplicarBloqueioConclusao();
            return;
        }

        data.qa_agrupado.forEach(grupo => {
            const card = document.createElement('div');
            card.className = 'card mb-4 shadow-sm';
            const isMerecimento = (grupo.criterio === 'Merecimento');

            let theadHtml = '';
            let linhasHtml = '';

            if (isMerecimento) {
                theadHtml = `
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 6%;">Class.</th>
                            <th style="width: 12%;">Posto</th>
                            <th>Nome Completo</th>
                            <th class="text-center" style="width: 11%;">FAD (1/3)</th>
                            <th class="text-center" style="width: 11%;">FEO (1/3)</th>
                            <th class="text-center" style="width: 11%;">FP (1/3)</th>
                            <th class="text-center" style="width: 13%;">Média Final</th>
                            <th class="text-center" style="width: 10%;">Situação</th>
                        </tr>
                    </thead>
                `;

                grupo.membros.forEach((m, idx) => {
                    const fad = m.nota_fad !== null ? parseFloat(m.nota_fad).toFixed(3) : '';
                    const feo = m.nota_feo !== null ? parseFloat(m.nota_feo).toFixed(3) : '';
                    const fp = m.nota_fp !== null ? parseFloat(m.nota_fp).toFixed(3) : '';
                    const media = m.pontuacao !== null ? parseFloat(m.pontuacao).toFixed(3) : '—';

                    linhasHtml += `
                        <tr class="linha-merecimento" data-militar-id="${m.militar_id}">
                            <td class="text-center fw-bold">${idx + 1}º</td>
                            <td><strong>${m.posto_atual}</strong></td>
                            <td>${m.nome}</td>
                            <td class="text-center">
                                <input type="number" step="0.001" min="0" max="6" class="form-control form-control-sm text-center input-nota input-fad" 
                                       value="${fad}" placeholder="0.000" oninput="calcularMediaMerecimento(this)">
                            </td>
                            <td class="text-center">
                                <input type="number" step="0.001" min="0" max="6" class="form-control form-control-sm text-center input-nota input-feo" 
                                       value="${feo}" placeholder="0.000" oninput="calcularMediaMerecimento(this)">
                            </td>
                            <td class="text-center">
                                <input type="number" step="0.001" class="form-control form-control-sm text-center input-nota input-fp" 
                                       value="${fp}" placeholder="0.000" oninput="calcularMediaMerecimento(this)">
                            </td>
                            <td class="text-center fw-bold fs-6 text-primary span-media-final">${media}</td>
                            <td class="text-center"><span class="badge bg-success"><i class="bi bi-check-circle"></i> No QAM</span></td>
                        </tr>
                    `;
                });
            } else {
                theadHtml = `
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 8%;">Class.</th>
                            <th style="width: 20%;">Posto Atual</th>
                            <th>Nome Completo</th>
                            <th class="text-center" style="width: 15%;">Critério</th>
                            <th class="text-center" style="width: 15%;">Situação QA</th>
                        </tr>
                    </thead>
                `;

                grupo.membros.forEach((m, idx) => {
                    linhasHtml += `
                        <tr>
                            <td class="text-center fw-bold" style="width: 8%;">${idx + 1}º</td>
                            <td style="width: 20%;"><strong>${m.posto_atual}</strong></td>
                            <td>${m.nome}</td>
                            <td class="text-center" style="width: 15%;"><span class="badge bg-primary">${m.criterio_qa}</span></td>
                            <td class="text-center" style="width: 15%;"><span class="badge bg-success"><i class="bi bi-check-circle"></i> No QAA</span></td>
                        </tr>
                    `;
                });
            }

            const headerBadge = isMerecimento ? 'bg-warning text-dark' : 'bg-secondary';

            card.innerHTML = `
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <div>
                        <strong class="text-dark"><i class="bi bi-shield-shaded text-primary me-1"></i> ${grupo.quadro}</strong>
                        <span class="text-muted"> — Destino: <strong>${grupo.destino}</strong></span>
                    </div>
                    <div>
                        <span class="badge ${headerBadge} me-2">${grupo.criterio}</span>
                        <span class="badge bg-secondary">${grupo.membros.length} candidato(s)</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped mb-0 align-middle">
                            ${theadHtml}
                            <tbody>
                                ${linhasHtml}
                            </tbody>
                        </table>
                    </div>
                </div>
            `;
            container.appendChild(card);
        });

        // Sanfona de Reservas e Impedidos
        const extraCard = document.createElement('div');
        extraCard.className = 'accordion mb-4';
        extraCard.id = 'accordionExtras';
        extraCard.innerHTML = `
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingReserva">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseReserva">
                        <i class="bi bi-people me-2"></i> Quadro Suplementar / Reserva (${data.reserva.length})
                    </button>
                </h2>
                <div id="collapseReserva" class="accordion-collapse collapse" data-bs-parent="#accordionExtras">
                    <div class="accordion-body p-0">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light"><tr><th>Posto</th><th>Nome</th><th>Destino</th><th>Situação</th></tr></thead>
                            <tbody>
                                ${data.reserva.map(r => `<tr><td>${r.posto_atual}</td><td>${r.nome}</td><td>${r.posto_grad_destino}</td><td><span class="badge bg-secondary">Reserva (Apto)</span></td></tr>`).join('')}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingImpedidos">
                    <button class="accordion-button collapsed text-danger" type="button" data-bs-toggle="collapse" data-bs-target="#collapseImpedidos">
                        <i class="bi bi-x-circle me-2"></i> Inabilitados / Impedidos (${data.impedidos.length})
                    </button>
                </h2>
                <div id="collapseImpedidos" class="accordion-collapse collapse" data-bs-parent="#accordionExtras">
                    <div class="accordion-body p-0">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light"><tr><th>Posto</th><th>Nome</th><th>Destino</th><th>Motivo</th></tr></thead>
                            <tbody>
                                ${data.impedidos.map(i => `<tr><td>${i.posto_atual}</td><td>${i.nome}</td><td>${i.posto_grad_destino}</td><td><span class="badge bg-danger">${i.motivo_inaptidao || 'Impedido na 1ª Pauta'}</span></td></tr>`).join('')}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        `;
        container.appendChild(extraCard);

        aplicarBloqueioConclusao();
    }

    function calcularMediaMerecimento(inputEl) {
        const tr = inputEl.closest('.linha-merecimento');
        const fad = parseFloat(tr.querySelector('.input-fad').value);
        const feo = parseFloat(tr.querySelector('.input-feo').value);
        const fp = parseFloat(tr.querySelector('.input-fp').value);
        const spanMedia = tr.querySelector('.span-media-final');

        if (!isNaN(fad) && !isNaN(feo) && !isNaN(fp)) {
            const media = (fad + feo + fp) / 3;
            spanMedia.textContent = media.toFixed(3);
        } else {
            spanMedia.textContent = '—';
        }
    }

    async function homologarQuadroAcesso() {
        if (isReuniaoConcluida) return;

        const btn = document.getElementById('btn_homologar_qa');
        if (btn) btn.disabled = true;

        const notasMerecimento = {};
        document.querySelectorAll('.linha-merecimento').forEach(tr => {
            const mid = tr.dataset.militarId;
            const fad = parseFloat(tr.querySelector('.input-fad').value);
            const feo = parseFloat(tr.querySelector('.input-feo').value);
            const fp = parseFloat(tr.querySelector('.input-fp').value);

            if (!isNaN(fad) && !isNaN(feo) && !isNaN(fp)) {
                const media = (fad + feo + fp) / 3;
                notasMerecimento[mid] = {
                    nota_fad: fad.toFixed(3),
                    nota_feo: feo.toFixed(3),
                    nota_fp: fp.toFixed(3),
                    pontuacao: media.toFixed(3)
                };
            }
        });

        try {
            const res = await fetch(API_QA, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    acao: 'homologar',
                    reuniao_id: reuniaoId,
                    notas_merecimento: notasMerecimento
                })
            });
            const data = await res.json();

            if (data.success) {
                exibirToast('Quadro de Acesso homologado com sucesso!', 'success');
                carregarPauta2();
            } else {
                exibirToast('Erro ao homologar: ' + data.message, 'danger');
                if (btn) btn.disabled = false;
            }
        } catch (err) {
            exibirToast('Erro de comunicação ao homologar QA.', 'danger');
            console.error(err);
            if (btn) btn.disabled = false;
        }
    }

    // =========================================================================
    // ABA 4: 3ª PAUTA: PROPOSTA DE PROMOÇÃO (VISUALIZAÇÃO EM CARDS)
    // =========================================================================

    async function carregarPauta3() {
        const container = document.getElementById('container_cards_proposta');
        container.innerHTML = '<div class="text-center py-4 text-muted"><div class="spinner-border text-success" role="status"></div><br>Carregando Proposta de Promoção...</div>';

        try {
            const res = await fetch(`${API_QA}?reuniao_id=${reuniaoId}&tipo=proposta`);
            const data = await res.json();

            if (data.success) {
                document.getElementById('card_total_propostos').textContent = data.total_propostos;
                renderizarPauta3(data);
            } else {
                exibirAlerta('Erro ao carregar proposta: ' + data.message, 'danger');
            }
        } catch (err) {
            exibirAlerta('Erro de comunicação ao carregar Proposta.', 'danger');
            console.error(err);
        }
    }

    function renderizarPauta3(data) {
        const container = document.getElementById('container_cards_proposta');
        container.innerHTML = '';

        if (!data.proposta_agrupada || data.proposta_agrupada.length === 0) {
            container.innerHTML = '<div class="alert alert-warning">Nenhuma proposta de promoção gerada para este processo.</div>';
            return;
        }

        data.proposta_agrupada.forEach(grupo => {
            const card = document.createElement('div');
            card.className = 'card mb-4 shadow-sm border-0';
            const isMerecimento = (grupo.criterio === 'Merecimento');

            let conteudoHtml = '';

            if (!grupo.ha_vagas) {
                conteudoHtml = `
                    <div class="p-3 text-center text-muted bg-light rounded">
                        <i class="bi bi-info-circle fs-3 text-secondary d-block mb-1"></i>
                        <strong>Não há vagas disponíveis</strong> para promoção a ${grupo.destino} neste processo.
                    </div>
                `;
            } else {
                let itensHtml = '';
                grupo.membros_propostos.forEach((m, idx) => {
                    const mediaTxt = m.pontuacao !== null ? `<span class="badge bg-primary ms-2">Média: ${parseFloat(m.pontuacao).toFixed(3)}</span>` : '';
                    itensHtml += `
                        <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                            <div>
                                <span class="badge bg-dark me-2">${idx + 1}º</span>
                                <strong>${m.posto_atual}</strong> - ${m.nome}
                                ${mediaTxt}
                            </div>
                            <span class="badge bg-success"><i class="bi bi-check-lg"></i> Proposto</span>
                        </li>
                    `;
                });

                let infoRegra = isMerecimento ?
                    `<div class="p-2 bg-warning bg-opacity-10 border-start border-warning border-3 mb-2 small text-dark"><i class="bi bi-info-circle"></i> <strong>Regra do Art. 40 (Lei 10.076/14):</strong> Para ${grupo.vp} vaga(s) de Coronel, concorrem os ${grupo.membros_propostos.length} primeiros candidatos para livre escolha do Governador.</div>` :
                    `<div class="p-2 bg-success bg-opacity-10 border-start border-success border-3 mb-2 small text-dark"><i class="bi bi-check-circle"></i> <strong>Critério de Antiguidade:</strong> ${grupo.membros_propostos.length} candidato(s) contemplado(s) para ${grupo.vp} vaga(s) existente(s).</div>`;

                conteudoHtml = infoRegra + `<ul class="list-group list-group-flush">${itensHtml}</ul>`;
            }

            card.innerHTML = `
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <div>
                        <strong class="fs-6 text-dark"><i class="bi bi-shield-check text-success me-1"></i> ${grupo.quadro}</strong>
                        <span class="text-muted"> — Promoção a <strong>${grupo.destino}</strong></span>
                    </div>
                    <div>
                        <span class="badge ${isMerecimento ? 'bg-warning text-dark' : 'bg-primary'} me-2">${grupo.criterio}</span>
                        <span class="badge bg-dark">${grupo.vp} Vaga(s)</span>
                    </div>
                </div>
                <div class="card-body">
                    ${conteudoHtml}
                </div>
            `;
            container.appendChild(card);
        });

        aplicarBloqueioConclusao();
    }

    // =========================================================================
    // INICIAR / FINALIZAR REUNIÃO & RELATÓRIO
    // =========================================================================

    async function iniciarReuniao() {
        if (!confirm("Deseja iniciar formalmente os trabalhos desta sessão?")) return;
        try {
            const response = await fetch(`../api/reuniao_comissao_api.php?id=${reuniaoId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    status: 'EM_ANDAMENTO'
                })
            });
            const data = await response.json();
            if (data.success) {
                exibirToast('Trabalhos da reunião iniciados!', 'success');
                setTimeout(() => window.location.reload(), 1200);
            } else {
                exibirToast('Erro: ' + data.message, 'danger');
            }
        } catch (err) {
            exibirToast('Erro ao iniciar reunião.', 'danger');
        }
    }

    async function finalizarReuniao() {
        if (!confirm("Atenção: Deseja realmente concluir esta reunião? O painel será travado.")) return;
        try {
            const response = await fetch(`../api/reuniao_comissao_api.php?id=${reuniaoId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    status: 'CONCLUIDA'
                })
            });
            const data = await response.json();
            if (data.success) {
                exibirToast('Reunião finalizada com sucesso!', 'success');
                setTimeout(() => window.location.reload(), 1200);
            } else {
                exibirToast('Erro: ' + data.message, 'danger');
            }
        } catch (err) {
            exibirToast('Erro ao finalizar reunião.', 'danger');
        }
    }

    async function salvarRelatorioReuniao() {
        if (isReuniaoConcluida) return;
        const selectEl = document.getElementById('select_relatorio');
        const relId = selectEl ? selectEl.value : null;
        if (!relId) {
            exibirToast('Selecione um relatório na lista.', 'warning');
            return;
        }

        const btn = document.getElementById('btn_salvar_relatorio');
        if (btn) btn.disabled = true;

        try {
            const response = await fetch('../api/reuniao_vincular_relatorio_api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    reuniao_id: reuniaoId,
                    relatorio_id: parseInt(relId)
                })
            });
            const data = await response.json();
            if (data.success) {
                const modalEl = document.getElementById('modalRelatorio');
                if (modalEl) bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                exibirToast('Relatório vinculado com sucesso!', 'success');
                setTimeout(() => window.location.reload(), 1200);
            } else {
                exibirToast('Erro: ' + data.message, 'danger');
                if (btn) btn.disabled = false;
            }
        } catch (err) {
            exibirToast('Erro de conexão.', 'danger');
            if (btn) btn.disabled = false;
        }
    }

    async function desvincularRelatorio() {
        if (isReuniaoConcluida) return;
        if (!confirm("Tem certeza que deseja desvincular este relatório?")) return;

        try {
            const response = await fetch('../api/reuniao_vincular_relatorio_api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    reuniao_id: reuniaoId,
                    relatorio_id: null
                })
            });
            const data = await response.json();
            if (data.success) {
                exibirToast('Relatório desvinculado!', 'success');
                setTimeout(() => window.location.reload(), 1200);
            } else {
                exibirToast('Erro ao desvincular.', 'danger');
            }
        } catch (err) {
            exibirToast('Erro ao desvincular.', 'danger');
        }
    }
</script>

<?php include_once './footer2.php'; ?>