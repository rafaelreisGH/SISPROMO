<?php
require_once '../Controllers/nivel_gestor.php';
require_once '../vendor/autoload.php';
include_once './header2.php';

use App\DAO\MilitarDAO;
use App\DAO\MilitarHistoricoEventosDAO;

$daoMilitar = new MilitarDAO();
$militares = $daoMilitar->buscarTodosMilitares(); // Retorna Ativos e Inativos

$daoHistorico = new MilitarHistoricoEventosDAO();
$ultimosEventos = $daoHistorico->buscarUltimosEventosMilitares();
?>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<div class="container mt-4 mb-4">

    <!-- Toast Flutuante no Canto Inferior Direito -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1100">
        <div id="toastApp" class="toast align-items-center text-white border-0 shadow" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="toastAppMsg"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Fechar"></button>
            </div>
        </div>
    </div>

    <!-- Cabeçalho -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="mb-0"><i class="bi bi-people-fill text-primary me-2"></i> Gestão de Status e Efetivo Militar</h3>
            <p class="text-muted mb-0">Controle de ativação, inativação e condição de excedente com registro formal de atos administrativos.</p>
        </div>
        <a href="pagina_gestao.php" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>
    <hr>

    <table id="tabelaMilitares" class="table table-striped table-hover table-bordered align-middle w-100">
        <thead class="table-dark">
            <tr>
                <th class="text-center" style="width: 6%;">Antig.</th>
                <th class="text-center" style="width: 12%;">Posto / Grad</th>
                <th>Nome Completo</th>
                <th class="text-center" style="width: 12%;">Quadro</th>
                <th class="text-center" style="width: 7%;">Média</th>
                <th class="text-center" style="width: 14%;">Condição (Excedente)</th>
                <th class="text-center" style="width: 13%;">Status (Ativar / Inativar)</th>
                <th class="text-center" style="width: 12%;">Ato / Histórico</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($militares as $m): ?>
                <?php
                $aux_id = (int)$m['id'];
                $isAtivo = ($m['status'] === 'ATIVO');
                $isExcedente = ((int)($m['is_excedente'] ?? 0) === 1);
                $antiguidade = ($isAtivo && !empty($m['antiguidade'])) ? (int)$m['antiguidade'] : '—';
                $media = ($m['media'] == 0 || is_null($m['media'])) ? 'N/D' : number_format((float)$m['media'], 2, ',', '.');
                $nome_quadro = $m['nome_quadro'] ?? 'N/D';

                // Último evento de ato administrativo
                $eventoAtual = $ultimosEventos[$aux_id] ?? null;
                $temDoc = !empty($eventoAtual['url_documento']);
                $urlDoc = $temDoc ? htmlspecialchars($eventoAtual['url_documento']) : '';
                $numAto = !empty($eventoAtual['numero_publicacao']) ? htmlspecialchars($eventoAtual['numero_publicacao']) : '';
                $tipoAto = !empty($eventoAtual['tipo_publicacao']) ? htmlspecialchars($eventoAtual['tipo_publicacao']) : '';
                ?>
                <tr id="linha-militar-<?= $aux_id ?>" class="<?= $isAtivo ? '' : 'table-light text-muted' ?>">
                    <td class="text-center fw-bold col-antiguidade"><?= $antiguidade ?></td>
                    <td class="text-center"><strong><?= htmlspecialchars($m['posto_grad_mil']) ?></strong></td>
                    <td><?= htmlspecialchars($m['nome']) ?></td>
                    <td class="text-center"><span class="badge bg-light text-dark border"><?= htmlspecialchars($nome_quadro) ?></span></td>
                    <td class="text-center"><?= $media ?></td>

                    <!-- Switch de Condição (Excedente) -->
                    <td class="text-center">
                        <div class="form-check form-switch d-flex justify-content-center align-items-center gap-2">
                            <input class="form-check-input switch-excedente" 
                                   type="checkbox" 
                                   role="switch" 
                                   id="switch_exc_<?= $aux_id ?>" 
                                   data-id="<?= $aux_id ?>"
                                   data-nome="<?= htmlspecialchars($m['nome']) ?>"
                                   data-posto="<?= htmlspecialchars($m['posto_grad_mil']) ?>"
                                   <?= $isExcedente ? 'checked' : '' ?>
                                   <?= !$isAtivo ? 'disabled' : '' ?>
                                   style="width: 2.5em; height: 1.25em; cursor: pointer;"
                                   title="Alternar entre Efetivo Regular e Excedente">
                            <span class="badge <?= $isExcedente ? 'bg-warning text-dark' : 'bg-secondary' ?>" 
                                  id="badge_exc_<?= $aux_id ?>">
                                <?= $isExcedente ? 'Excedente' : 'Efetivo' ?>
                            </span>
                        </div>
                    </td>

                    <!-- Switch de Ativação / Inativação -->
                    <td class="text-center">
                        <div class="form-check form-switch d-flex justify-content-center align-items-center gap-2">
                            <input class="form-check-input switch-status" 
                                   type="checkbox" 
                                   role="switch" 
                                   id="switch_<?= $aux_id ?>" 
                                   data-id="<?= $aux_id ?>"
                                   data-nome="<?= htmlspecialchars($m['nome']) ?>"
                                   data-posto="<?= htmlspecialchars($m['posto_grad_mil']) ?>"
                                   <?= $isAtivo ? 'checked' : '' ?>
                                   style="width: 2.5em; height: 1.25em; cursor: pointer;">
                            <label class="form-check-label fw-bold small label-status <?= $isAtivo ? 'text-success' : 'text-secondary' ?>" 
                                   for="switch_<?= $aux_id ?>" 
                                   id="label_<?= $aux_id ?>">
                                <?= $isAtivo ? 'Ativo' : 'Inativo' ?>
                            </label>
                        </div>
                    </td>

                    <!-- Coluna de Ato Administrativo e Histórico -->
                    <td class="text-center">
                        <span id="container_doc_<?= $aux_id ?>">
                            <?php if ($temDoc): ?>
                                <a href="<?= $urlDoc ?>" target="_blank" class="btn btn-outline-danger btn-sm me-1" title="Ver Documento: <?= $tipoAto ?> <?= $numAto ?>">
                                    <i class="bi bi-file-earmark-pdf"></i>
                                </a>
                            <?php endif; ?>
                        </span>
                        <button type="button" 
                                class="btn btn-outline-primary btn-sm btn-ver-historico" 
                                data-id="<?= $aux_id ?>" 
                                data-nome="<?= htmlspecialchars($m['nome']) ?>" 
                                data-posto="<?= htmlspecialchars($m['posto_grad_mil']) ?>" 
                                title="Ver Histórico de Atos Administrativos">
                            <i class="bi bi-clock-history"></i>
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal 1: Registro de Ato Administrativo -->
<div class="modal fade" id="modalAtoAdministrativo" tabindex="-1" aria-labelledby="modalAtoTitulo" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalAtoTitulo">
                    <i class="bi bi-file-earmark-text me-2"></i> Registro de Ato Administrativo
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Informações do Militar e da Ação -->
                <div class="alert alert-info d-flex align-items-center mb-3" role="alert" id="modalAtoBanner">
                    <i class="bi bi-info-circle-fill fs-4 me-3"></i>
                    <div>
                        <strong id="modalAtoMilitarNome">Posto Nome</strong>
                        <div id="modalAtoDescricaoAcao" class="small mt-1 text-secondary">Transição solicitada.</div>
                    </div>
                </div>

                <form id="formAtoAdministrativo" onsubmit="return false;">
                    <input type="hidden" id="modalAtoMilitarId">
                    <input type="hidden" id="modalAtoTipoAcao">
                    <input type="hidden" id="modalAtoNovoValor">

                    <div class="row g-3">
                        <!-- Tipo de Publicação -->
                        <div class="col-md-6">
                            <label for="modalAtoTipoPub" class="form-label fw-bold">Tipo de Publicação</label>
                            <select id="modalAtoTipoPub" class="form-select">
                                <option value="">Selecione (opcional)...</option>
                                <option value="DOE">Diário Oficial do Estado (DOE)</option>
                                <option value="BGE">Boletim Geral Eletrônico (BGE)</option>
                                <option value="PORTARIA">Portaria</option>
                                <option value="DECRETO">Decreto</option>
                                <option value="DECISAO_JUDICIAL">Decisão Judicial</option>
                                <option value="OUTRO">Outro Ato Administrativo</option>
                            </select>
                        </div>

                        <!-- Número / Identificação da Publicação -->
                        <div class="col-md-6">
                            <label for="modalAtoNumeroPub" class="form-label fw-bold">Número / Descrição da Publicação</label>
                            <input type="text" id="modalAtoNumeroPub" class="form-control" placeholder="Ex: DOE nº 28.798 ou Portaria nº 042/DGP">
                        </div>

                        <!-- Data de Vigência -->
                        <div class="col-md-6">
                            <label for="modalAtoDataVigencia" class="form-label fw-bold">Data de Vigência / Início dos Efeitos</label>
                            <input type="date" id="modalAtoDataVigencia" class="form-control">
                        </div>

                        <!-- URL / Link do Documento -->
                        <div class="col-md-6">
                            <label for="modalAtoUrlDoc" class="form-label fw-bold">Link / URL do Documento (PDF)</label>
                            <input type="url" id="modalAtoUrlDoc" class="form-control" placeholder="https://.../publicacao.pdf">
                        </div>

                        <!-- Observação / Justificativa -->
                        <div class="col-12">
                            <label for="modalAtoObs" class="form-label fw-bold">Observações / Motivação Administrativa</label>
                            <textarea id="modalAtoObs" class="form-control" rows="2" placeholder="Descreva motivações adicionais, processos SEI ou observações relevantes..."></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i> Cancelar
                </button>
                <button type="button" class="btn btn-primary" id="btnSalvarAto">
                    <span id="btnSalvarAtoSpinner" class="spinner-border spinner-border-sm me-1" role="status" style="display: none;"></span>
                    <i class="bi bi-check-circle me-1" id="btnSalvarAtoIcon"></i> Confirmar e Salvar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal 2: Linha do Tempo / Histórico de Atos Administrativos -->
<div class="modal fade" id="modalHistoricoEventos" tabindex="-1" aria-labelledby="modalHistoricoTitulo" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="modalHistoricoTitulo">
                    <i class="bi bi-clock-history text-primary me-2"></i> Histórico de Atos Administrativos do Militar
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body p-4">
                <div id="historicoLoading" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted mb-0">Carregando histórico do militar...</p>
                </div>

                <div id="historicoConteudo" style="display: none;">
                    <div class="alert alert-secondary d-flex justify-content-between align-items-center py-2 mb-3">
                        <div>
                            <strong id="histMilitarNome" class="fs-6">Posto Nome</strong>
                            <span class="badge bg-secondary ms-2" id="histMilitarStatus">Status</span>
                        </div>
                        <div id="histMilitarId" class="small text-muted">ID: #0</div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 14%;">Data Registro</th>
                                    <th style="width: 16%;">Transição</th>
                                    <th style="width: 18%;">Publicação / Ato</th>
                                    <th style="width: 12%;">Vigência</th>
                                    <th class="text-center" style="width: 10%;">Documento</th>
                                    <th>Observações</th>
                                    <th style="width: 16%;">Registrado por</th>
                                </tr>
                            </thead>
                            <tbody id="historicoTabelaCorpo"></tbody>
                        </table>
                    </div>
                </div>

                <div id="historicoVazio" class="text-center py-5 text-muted" style="display: none;">
                    <i class="bi bi-folder-x fs-1 d-block mb-2 text-secondary"></i>
                    <h5>Nenhum ato registrado</h5>
                    <p class="mb-0">Não foram encontrados atos administrativos gravados para este militar.</p>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- Scripts jQuery e DataTables -->
<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
        // Inicialização DataTables
        const table = $('#tabelaMilitares').DataTable({
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json"
            },
            pageLength: 20,
            responsive: true,
            order: []
        });

        // Instâncias dos Modais Bootstrap
        const modalAtoEl = document.getElementById('modalAtoAdministrativo');
        const modalAtoBs = new bootstrap.Modal(modalAtoEl);

        const modalHistEl = document.getElementById('modalHistoricoEventos');
        const modalHistBs = new bootstrap.Modal(modalHistEl);

        // Variáveis de controle do estado do modal de ato
        let switchPendente = null;
        let salvamentoConfirmado = false;

        // Toast de Notificações
        function showToast(mensagem, tipo = 'success') {
            const toastEl = document.getElementById('toastApp');
            const msgEl = document.getElementById('toastAppMsg');
            if (!toastEl || !msgEl) {
                alert(mensagem);
                return;
            }

            toastEl.classList.remove('bg-success', 'bg-danger', 'bg-warning', 'bg-info');
            const corBg = (tipo === 'danger') ? 'bg-danger' : 'bg-success';
            toastEl.classList.add(corBg);

            msgEl.textContent = mensagem;
            bootstrap.Toast.getOrCreateInstance(toastEl, { delay: 4000 }).show();
        }

        // Formatação de Datas (AAAA-MM-DD -> DD/MM/AAAA)
        function formatarData(dataStr) {
            if (!dataStr) return '—';
            const partes = dataStr.split('-');
            if (partes.length === 3) {
                return `${partes[2]}/${partes[1]}/${partes[0]}`;
            }
            return dataStr;
        }

        // Formatação de Data/Hora (AAAA-MM-DD HH:MM:SS)
        function formatarDataHora(dataHoraStr) {
            if (!dataHoraStr) return '—';
            const d = new Date(dataHoraStr);
            if (isNaN(d.getTime())) return dataHoraStr;
            return d.toLocaleString('pt-BR');
        }

        // Mapeamento visual para badges de eventos
        function formatarBadgeEvento(tipoEvento) {
            switch (tipoEvento) {
                case 'INATIVACAO':
                    return '<span class="badge bg-danger">Inativação</span>';
                case 'REATIVACAO':
                    return '<span class="badge bg-success">Reativação</span>';
                case 'EXCEDENTE_ATIVADO':
                    return '<span class="badge bg-warning text-dark">Excedente Ativado</span>';
                case 'EXCEDENTE_DESATIVADO':
                    return '<span class="badge bg-info text-dark">Retorno ao Efetivo</span>';
                default:
                    return `<span class="badge bg-secondary">${tipoEvento}</span>`;
            }
        }

        // 1. Abertura do Modal ao clicar no Switch de Excedente
        $('#tabelaMilitares tbody').on('change', '.switch-excedente', function() {
            switchPendente = this;
            salvamentoConfirmado = false;

            const militarId = parseInt(this.dataset.id);
            const militarNome = this.dataset.nome;
            const militarPosto = this.dataset.posto;
            const novoIsExcedente = this.checked ? 1 : 0;

            const acaoTexto = (novoIsExcedente === 1) 
                ? 'Marcar como EXCEDENTE (libera vaga no contêiner para efeito cascata)' 
                : 'Retornar ao EFETIVO REGULAR (ocupa vaga no contêiner)';

            document.getElementById('modalAtoMilitarId').value = militarId;
            document.getElementById('modalAtoTipoAcao').value = 'EXCEDENTE';
            document.getElementById('modalAtoNovoValor').value = novoIsExcedente;

            document.getElementById('modalAtoMilitarNome').textContent = `${militarPosto} ${militarNome}`;
            document.getElementById('modalAtoDescricaoAcao').textContent = `Transição pretendida: ${acaoTexto}`;

            // Reseta formulário do modal
            document.getElementById('modalAtoTipoPub').value = '';
            document.getElementById('modalAtoNumeroPub').value = '';
            document.getElementById('modalAtoDataVigencia').value = new Date().toISOString().split('T')[0];
            document.getElementById('modalAtoUrlDoc').value = '';
            document.getElementById('modalAtoObs').value = '';

            modalAtoBs.show();
        });

        // 2. Abertura do Modal ao clicar no Switch de Status (Ativar / Inativar)
        $('#tabelaMilitares tbody').on('change', '.switch-status', function() {
            switchPendente = this;
            salvamentoConfirmado = false;

            const militarId = parseInt(this.dataset.id);
            const militarNome = this.dataset.nome;
            const militarPosto = this.dataset.posto;
            const novoStatus = this.checked ? 'ATIVO' : 'INATIVO';

            const acaoTexto = (novoStatus === 'ATIVO') 
                ? 'REATIVAÇÃO do militar na corporação' 
                : 'INATIVAÇÃO do militar (Reserva Remunerada / Reforma)';

            document.getElementById('modalAtoMilitarId').value = militarId;
            document.getElementById('modalAtoTipoAcao').value = 'STATUS';
            document.getElementById('modalAtoNovoValor').value = novoStatus;

            document.getElementById('modalAtoMilitarNome').textContent = `${militarPosto} ${militarNome}`;
            document.getElementById('modalAtoDescricaoAcao').textContent = `Transição pretendida: ${acaoTexto}`;

            // Reseta formulário do modal
            document.getElementById('modalAtoTipoPub').value = '';
            document.getElementById('modalAtoNumeroPub').value = '';
            document.getElementById('modalAtoDataVigencia').value = new Date().toISOString().split('T')[0];
            document.getElementById('modalAtoUrlDoc').value = '';
            document.getElementById('modalAtoObs').value = '';

            modalAtoBs.show();
        });

        // 3. Reversão automática caso o modal seja fechado sem salvar
        modalAtoEl.addEventListener('hidden.bs.modal', function () {
            if (!salvamentoConfirmado && switchPendente) {
                switchPendente.checked = !switchPendente.checked;
            }
            switchPendente = null;
        });

        // 4. Gravação e Envio do Ato Administrativo
        $('#btnSalvarAto').on('click', async function() {
            const militarId = parseInt(document.getElementById('modalAtoMilitarId').value);
            const tipoAcao = document.getElementById('modalAtoTipoAcao').value;
            const novoValor = document.getElementById('modalAtoNovoValor').value;

            const tipoPub = document.getElementById('modalAtoTipoPub').value;
            const numeroPub = document.getElementById('modalAtoNumeroPub').value.trim();
            const dataVigencia = document.getElementById('modalAtoDataVigencia').value;
            const urlDoc = document.getElementById('modalAtoUrlDoc').value.trim();
            const observacao = document.getElementById('modalAtoObs').value.trim();

            const btn = document.getElementById('btnSalvarAto');
            const spinner = document.getElementById('btnSalvarAtoSpinner');
            const icon = document.getElementById('btnSalvarAtoIcon');

            btn.disabled = true;
            spinner.style.display = 'inline-block';
            icon.style.display = 'none';

            try {
                let urlEndpoint = '';
                let payload = {
                    militar_id: militarId,
                    tipo_publicacao: tipoPub || null,
                    numero_publicacao: numeroPub || null,
                    data_vigencia: dataVigencia || null,
                    url_documento: urlDoc || null,
                    observacao: observacao || null
                };

                if (tipoAcao === 'EXCEDENTE') {
                    urlEndpoint = '../api/alterar_excedente_militar_api.php';
                    payload.is_excedente = parseInt(novoValor);
                } else {
                    urlEndpoint = '../api/alterar_status_militar_api.php';
                    payload.status = novoValor;
                }

                const response = await fetch(urlEndpoint, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                const data = await response.json();

                if (data.success) {
                    salvamentoConfirmado = true;
                    modalAtoBs.hide();
                    showToast(data.message, 'success');

                    // Atualiza a linha do militar na tabela
                    if (tipoAcao === 'EXCEDENTE') {
                        const badgeEl = document.getElementById(`badge_exc_${militarId}`);
                        if (parseInt(novoValor) === 1) {
                            badgeEl.textContent = 'Excedente';
                            badgeEl.className = 'badge bg-warning text-dark';
                        } else {
                            badgeEl.textContent = 'Efetivo';
                            badgeEl.className = 'badge bg-secondary';
                        }
                    } else {
                        const labelEl = document.getElementById(`label_${militarId}`);
                        const switchExcEl = document.getElementById(`switch_exc_${militarId}`);
                        const tr = $(`#linha-militar-${militarId}`);

                        if (novoValor === 'ATIVO') {
                            labelEl.textContent = 'Ativo';
                            labelEl.className = 'form-check-label fw-bold small label-status text-success';
                            tr.removeClass('table-light text-muted');
                            if (switchExcEl) switchExcEl.disabled = false;
                        } else {
                            labelEl.textContent = 'Inativo';
                            labelEl.className = 'form-check-label fw-bold small label-status text-secondary';
                            tr.addClass('table-light text-muted');
                            if (switchExcEl) switchExcEl.disabled = true;
                        }
                    }

                    // Se foi informado link de documento, atualiza o botão na coluna de atos
                    if (urlDoc) {
                        const containerDoc = document.getElementById(`container_doc_${militarId}`);
                        if (containerDoc) {
                            containerDoc.innerHTML = `
                                <a href="${urlDoc}" target="_blank" class="btn btn-outline-danger btn-sm me-1" title="Ver Documento: ${tipoPub} ${numeroPub}">
                                    <i class="bi bi-file-earmark-pdf"></i>
                                </a>
                            `;
                        }
                    }
                } else {
                    showToast('Erro: ' + (data.message || 'Falha ao salvar ato administrativo.'), 'danger');
                    if (switchPendente) {
                        switchPendente.checked = !switchPendente.checked;
                    }
                }
            } catch (err) {
                console.error('Erro na requisição:', err);
                showToast('Erro de comunicação com o servidor.', 'danger');
                if (switchPendente) {
                    switchPendente.checked = !switchPendente.checked;
                }
            } finally {
                btn.disabled = false;
                spinner.style.display = 'none';
                icon.style.display = 'inline-block';
            }
        });

        // 5. Consulta do Histórico de Atos Administrativos
        $('#tabelaMilitares tbody').on('click', '.btn-ver-historico', async function() {
            const militarId = parseInt(this.dataset.id);
            const militarNome = this.dataset.nome;
            const militarPosto = this.dataset.posto;

            document.getElementById('histMilitarNome').textContent = `${militarPosto} ${militarNome}`;
            document.getElementById('histMilitarId').textContent = `ID: #${militarId}`;

            const loading = document.getElementById('historicoLoading');
            const conteudo = document.getElementById('historicoConteudo');
            const vazio = document.getElementById('historicoVazio');
            const tabelaCorpo = document.getElementById('historicoTabelaCorpo');

            loading.style.display = 'block';
            conteudo.style.display = 'none';
            vazio.style.display = 'none';
            tabelaCorpo.innerHTML = '';

            modalHistBs.show();

            try {
                const response = await fetch(`../api/historico_eventos_militar_api.php?militar_id=${militarId}`);
                const data = await response.json();

                loading.style.display = 'none';

                if (data.success && data.eventos && data.eventos.length > 0) {
                    conteudo.style.display = 'block';

                    const statusBadge = (data.militar.status === 'ATIVO') 
                        ? '<span class="badge bg-success ms-2">Ativo</span>' 
                        : '<span class="badge bg-secondary ms-2">Inativo</span>';
                    const excBadge = (data.militar.is_excedente === 1) 
                        ? '<span class="badge bg-warning text-dark ms-1">Excedente</span>' 
                        : '';
                    document.getElementById('histMilitarStatus').outerHTML = `<span id="histMilitarStatus">${statusBadge}${excBadge}</span>`;

                    let htmlLinhas = '';
                    data.eventos.forEach(ev => {
                        const linkDoc = ev.url_documento 
                            ? `<a href="${ev.url_documento}" target="_blank" class="btn btn-sm btn-outline-danger" title="Abrir Documento"><i class="bi bi-file-earmark-pdf"></i> Visualizar</a>` 
                            : '<span class="text-muted small">Sem link</span>';

                        const atoTexto = ev.numero_publicacao 
                            ? `<strong>${ev.tipo_publicacao || 'Ato'}:</strong> ${ev.numero_publicacao}` 
                            : (ev.tipo_publicacao || '—');

                        const usuario = ev.usuario_nome 
                            ? `${ev.posto_grad_usuario || ''} ${ev.usuario_nome}`.trim() 
                            : 'Sistema';

                        htmlLinhas += `
                            <tr>
                                <td class="small text-nowrap">${formatarDataHora(ev.criado_em)}</td>
                                <td>${formatarBadgeEvento(ev.tipo_evento)}</td>
                                <td>${atoTexto}</td>
                                <td class="small text-nowrap">${formatarData(ev.data_vigencia)}</td>
                                <td class="text-center">${linkDoc}</td>
                                <td class="small">${ev.observacao || '—'}</td>
                                <td class="small text-muted">${usuario}</td>
                            </tr>
                        `;
                    });

                    tabelaCorpo.innerHTML = htmlLinhas;
                } else {
                    vazio.style.display = 'block';
                }
            } catch (err) {
                console.error('Erro ao consultar histórico:', err);
                loading.style.display = 'none';
                vazio.style.display = 'block';
            }
        });
    });
</script>

<?php include_once './footer3.php'; ?>