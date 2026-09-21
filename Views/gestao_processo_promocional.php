<?php
/**
 * Tela de Gestão de Processo Promocional
 *
 * O Processo Promocional é unificado: abrange oficiais (CPO) e praças (CPP)
 * no mesmo ciclo semestral. Não há mais distinção de tipo de comissão nesta tela.
 */

require_once '../Controllers/nivel_gestor.php';
include_once '../Views/header2.php';
require_once '../vendor/autoload.php';

use App\DAO\ProcessoPromocionalDAO;

$daoProcesso = new ProcessoPromocionalDAO();
$processos   = $daoProcesso->listarTodos();
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
                <h3>Processos Promocionais</h3>
                <p class="text-muted mb-0">
                    Gerencie os ciclos de promoção semestrais (Oficiais e Praças), calcule o LQ e acompanhe o status.
                </p>
            </div>
            <div>
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modal-cadastrar">
                    <i class="bi bi-plus-circle"></i> Novo Processo Promocional
                </button>
            </div>
        </div>
        <div class="clearfix"><br></div>
    </div>

    <div class="col-md-12">
        <div class="card">
            <h5 class="card-header">Histórico de Processos Promocionais (CPO e CPP)</h5>
            <div class="card-body">

                <?php if (empty($processos)): ?>
                    <p class="text-muted">Nenhum processo promocional cadastrado até o momento.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-hover" id="tabela-processos">
                            <thead class="table-dark text-center">
                                <tr>
                                    <th style="width: 80px;">ID</th>
                                    <th>Processo (Ano / Semestre)</th>
                                    <th>Data Oficial da Promoção</th>
                                    <th>Status</th>
                                    <th style="width: 320px;">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($processos as $p): ?>
                                    <?php
                                    $statusBadge = match ($p['status']) {
                                        'EM_PREPARACAO' => '<span class="badge bg-secondary">Em Preparação (Aguardando LQ)</span>',
                                        'LQ_PUBLICADO'  => '<span class="badge bg-primary">LQ Publicado</span>',
                                        'EM_REUNIAO'    => '<span class="badge bg-warning text-dark">Em Reuniões</span>',
                                        'CONCLUIDO'     => '<span class="badge bg-success">Concluído</span>',
                                        default         => '<span class="badge bg-dark">' . htmlspecialchars($p['status']) . '</span>'
                                    };
                                    ?>
                                    <tr id="linha-processo-<?= (int) $p['id'] ?>">
                                        <td class="text-center align-middle"><?= (int) $p['id'] ?></td>
                                        <td class="text-center align-middle fw-bold"><?= (int) $p['ano'] ?> / <?= (int) $p['semestre'] ?>º Sem</td>
                                        <td class="text-center align-middle"><?= date('d/m/Y', strtotime($p['data_promocao'])) ?></td>
                                        <td class="text-center align-middle"><?= $statusBadge ?></td>
                                        <td class="text-center align-middle">
                                             <div class="d-flex justify-content-center" style="gap: 5px;">
                                                 <!-- Simular LQ -->
                                                 <?php if ($p['status'] === 'EM_PREPARACAO'): ?>
                                                     <button type="button" class="btn btn-outline-primary btn-sm btn-simular-lq" 
                                                         data-id="<?= (int) $p['id'] ?>"
                                                         data-identificacao="<?= (int)$p['ano'] ?>/<?= (int)$p['semestre'] ?>º Sem"
                                                         title="Simular Limite Quantitativo (sem salvar)">
                                                         <i class="bi bi-cpu"></i> Simular LQ
                                                     </button>
                                                 <?php endif; ?>
                                                 
                                                 <!-- Gerar/Recalcular LQ (Definitivo) -->
                                                 <?php if (in_array($p['status'], ['EM_PREPARACAO', 'LQ_PUBLICADO'])): ?>
                                                     <button type="button" class="btn btn-outline-success btn-sm btn-gerar-lq" 
                                                         data-id="<?= (int) $p['id'] ?>"
                                                         title="Calcular e Publicar Limite Quantitativo de Forma Definitiva">
                                                         <i class="bi bi-check-circle"></i> Gerar LQ
                                                     </button>
                                                 <?php endif; ?>
                                                 
                                                 <!-- Visualizar LQ (Habilitado se já publicado) -->
                                                 <?php if ($p['status'] !== 'EM_PREPARACAO'): ?>
                                                     <button type="button" class="btn btn-outline-info btn-sm btn-visualizar-lq" 
                                                         data-id="<?= (int) $p['id'] ?>"
                                                         data-identificacao="<?= (int)$p['ano'] ?>/<?= (int)$p['semestre'] ?>º Sem"
                                                         title="Visualizar LQ Publicado">
                                                         <i class="bi bi-eye"></i> Visualizar LQ
                                                     </button>
                                                 <?php else: ?>
                                                     <button type="button" class="btn btn-outline-secondary btn-sm" disabled title="Aguardando Geração do LQ">
                                                         <i class="bi bi-eye-slash"></i> Sem LQ
                                                     </button>
                                                 <?php endif; ?>

                                                 <!-- Excluir Processo (Lixeira) -->
                                                 <?php if ($p['status'] === 'EM_PREPARACAO'): ?>
                                                     <button type="button" class="btn btn-outline-danger btn-sm btn-excluir-processo" 
                                                         data-id="<?= (int) $p['id'] ?>"
                                                         data-identificacao="<?= (int)$p['ano'] ?>/<?= (int)$p['semestre'] ?>º Sem"
                                                         title="Excluir Processo Promocional">
                                                         <i class="bi bi-trash"></i> Excluir
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

            </div>
        </div>
    </div>

</div>

<!-- MODAL CADASTRAR PROCESSO -->
<div class="modal fade" id="modal-cadastrar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-cadastrar">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Novo Processo Promocional</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label">Ano <span class="text-danger">*</span></label>
                            <input type="number" id="cad-ano" name="ano" class="form-control" value="<?= date('Y') ?>" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Semestre <span class="text-danger">*</span></label>
                            <select id="cad-semestre" name="semestre" class="form-select" required>
                                <option value="1">1º Semestre</option>
                                <option value="2">2º Semestre</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Data Oficial da Promoção <span class="text-danger">*</span></label>
                        <input type="date" id="cad-data" name="data_promocao" class="form-control bg-light" readonly required>
                        <div class="form-text">Vinculada automaticamente: 02 de Julho (1º Sem) ou 02 de Dezembro (2º Sem)</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success btn-sm" id="btn-salvar-cadastro">
                        <span id="spinner-cadastrar" class="spinner-border spinner-border-sm d-none"></span>
                        Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL DE DETALHES DO LQ (SIMULAÇÃO OU VISUALIZAÇÃO) -->
<div class="modal fade" id="modal-lq-detalhes" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="modal-lq-titulo">Limite Quantitativo (LQ)</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="lq-alerta-simulacao" class="alert alert-info d-none">
                    <i class="bi bi-info-circle-fill"></i> <strong>Modo Simulação:</strong> Este resultado é em tempo real com base no estado atual do efetivo. Nenhum dado foi salvo fisicamente no banco de dados.
                </div>
                
                <div class="mb-3 d-flex justify-content-between align-items-center">
                    <div>
                        <span id="lq-detalhes-processo" class="fw-bold">Processo: -</span>
                    </div>
                    <div class="d-flex gap-2">
                        <input type="text" id="lq-filtro-busca" class="form-control form-control-sm" placeholder="Buscar por nome, posto ou agrupamento..." style="width: 250px;">
                        <select id="lq-filtro-quadro" class="form-select form-select-sm" style="width: 200px;">
                            <option value="">Todos os Quadros</option>
                        </select>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-striped table-bordered table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th class="text-center" style="width: 50px;">Pos</th>
                                <th class="text-center">Posto/Grad.</th>
                                <th>Militar</th>
                                <th>Quadro</th>
                                <th>Grupo de Vagas</th>
                                <th class="text-center">Vaga Pretendida</th>
                                <th class="text-center">Critério Promoção</th>
                                <th class="text-center">Tipo Promoção</th>
                                <th class="text-center">Vagas Calc.</th>
                                <th class="text-center">Situação no LQ</th>
                            </tr>
                        </thead>
                        <tbody id="lq-tabela-corpo">
                            <tr>
                                <td colspan="10" class="text-center text-muted py-3">Carregando dados do limite quantitativo...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-success btn-sm d-none" id="btn-modal-confirmar-gerar">
                    <i class="bi bi-check-lg"></i> Confirmar e Gerar LQ Permanente
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const API_URL = '../api/processo_promocional_api.php';
    const modalCadastrar = new bootstrap.Modal(document.getElementById('modal-cadastrar'));
    const modalLq = new bootstrap.Modal(document.getElementById('modal-lq-detalhes'));

    // Vinculação automática de data/semestre
    const inputAno = document.getElementById('cad-ano');
    const inputSemestre = document.getElementById('cad-semestre');
    const inputData = document.getElementById('cad-data');

    function atualizarDataPromocao() {
        const ano = inputAno.value;
        const semestre = inputSemestre.value;
        if (!ano) return;
        
        if (semestre === '1') {
            inputData.value = `${ano}-07-02`; // 02 de Julho
        } else if (semestre === '2') {
            inputData.value = `${ano}-12-02`; // 02 de Dezembro
        }
    }

    inputAno.addEventListener('change', atualizarDataPromocao);
    inputAno.addEventListener('input', atualizarDataPromocao);
    inputSemestre.addEventListener('change', atualizarDataPromocao);
    document.getElementById('modal-cadastrar').addEventListener('show.bs.modal', atualizarDataPromocao);

    // Criar novo processo
    document.getElementById('form-cadastrar').addEventListener('submit', function(e) {
        e.preventDefault();
        const btnSalvar = document.getElementById('btn-salvar-cadastro');
        const spinner = document.getElementById('spinner-cadastrar');
        
        btnSalvar.disabled = true;
        spinner.classList.remove('d-none');

        const payload = {
            ano: document.getElementById('cad-ano').value,
            semestre: document.getElementById('cad-semestre').value,
            data_promocao: document.getElementById('cad-data').value
        };

        fetch(API_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showToast('Processo promocional criado com sucesso!', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast(data.error || 'Erro ao criar processo.', 'danger');
            }
        })
        .catch(err => showToast('Erro de comunicação com o servidor.', 'danger'))
        .finally(() => {
            btnSalvar.disabled = false;
            spinner.classList.add('d-none');
        });
    });

    // Variáveis globais para dados e filtros do LQ
    let lqDadosGlobais = [];
    let processoIdAtual = null;

    const filtroBusca = document.getElementById('lq-filtro-busca');
    const filtroQuadro = document.getElementById('lq-filtro-quadro');

    function renderizarTabelaLq(dados) {
        const corpo = document.getElementById('lq-tabela-corpo');
        corpo.innerHTML = '';

        // Oculta da visualização os postos do topo de cada quadro (CEL BM e ST BM),
        // pois não há promoção possível para eles. O cálculo do LQ os considera normalmente.
        const dadosFiltrados = dados.filter(d => {
            const posto = (d.posto_atual || '').toUpperCase().trim();
            return posto !== 'ST BM' && posto !== 'CEL BM';
        });

        if (dadosFiltrados.length === 0) {
            corpo.innerHTML = '<tr><td colspan="10" class="text-center text-muted py-3">Nenhum militar qualificado para o LQ neste processo.</td></tr>';
            return;
        }

        dadosFiltrados.forEach(d => {
            const badgeLimite = d.dentro_do_limite == 1 
                ? '<span class="badge bg-success">No Limite</span>' 
                : '<span class="badge bg-warning text-dark">Reserva</span>';

            const situacaoTexto = d.situacao || 'APTO';
            let situacaoBadge = '';
            if (situacaoTexto !== 'APTO') {
                if (situacaoTexto.startsWith('ELIMINADO')) {
                    situacaoBadge = '<span class="badge bg-light text-danger border border-danger ms-1">' + situacaoTexto + '</span>';
                } else {
                    situacaoBadge = '<span class="badge bg-secondary ms-1">' + situacaoTexto + '</span>';
                }
            }

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="text-center align-middle fw-bold">${d.posicao_antiguidade}</td>
                <td class="text-center align-middle fw-semibold">${d.posto_atual}</td>
                <td class="align-middle fw-semibold">${d.nome}</td>
                <td class="align-middle text-body">${d.nome_quadro || '—'}</td>
                <td class="align-middle">${d.nome_do_agrupamento || '—'}</td>
                <td class="text-center align-middle">${d.posto_grad_destino || '—'}</td>
                <td class="text-center align-middle">
                    <span class="badge ${d.criterio_promocao === 'Merecimento' ? 'bg-warning text-dark' : 'bg-info text-dark'}">
                        ${d.criterio_promocao || '—'}
                    </span>
                </td>
                <td class="text-center align-middle"><span class="badge bg-secondary">${d.tipo_promocao_label || '—'}</span></td>
                <td class="text-center align-middle fw-bold">${d.vp_calculadas}</td>
                <td class="text-center align-middle">${badgeLimite}${situacaoBadge}</td>
            `;
            corpo.appendChild(tr);
        });
    }

    function popularSelectQuadros(dados) {
        filtroQuadro.innerHTML = '<option value="">Todos os Quadros</option>';
        const quadrosUnicos = [...new Set(dados.map(d => d.nome_quadro).filter(Boolean))];
        quadrosUnicos.sort().forEach(q => {
            const opt = document.createElement('option');
            opt.value = q;
            opt.textContent = q;
            filtroQuadro.appendChild(opt);
        });
    }

    function aplicarFiltros() {
        const termo = filtroBusca.value.toLowerCase().trim();
        const quadroSelected = filtroQuadro.value;

        const filtrados = lqDadosGlobais.filter(d => {
            const matchTexto = d.nome.toLowerCase().includes(termo) || 
                               d.posto_atual.toLowerCase().includes(termo) || 
                               (d.nome_do_agrupamento && d.nome_do_agrupamento.toLowerCase().includes(termo));
            const matchQuadro = !quadroSelected || d.nome_quadro === quadroSelected;
            return matchTexto && matchQuadro;
        });

        renderizarTabelaLq(filtrados);
    }

    filtroBusca.addEventListener('input', aplicarFiltros);
    filtroQuadro.addEventListener('change', aplicarFiltros);

    // Clique em Simular LQ
    document.querySelectorAll('.btn-simular-lq').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const ident = this.dataset.identificacao;
            processoIdAtual = id;

            document.getElementById('modal-lq-titulo').textContent = 'Simulação do Limite Quantitativo - Processo ' + ident;
            document.getElementById('lq-detalhes-processo').textContent = 'Processo: ' + ident;
            document.getElementById('lq-alerta-simulacao').classList.remove('d-none');
            document.getElementById('btn-modal-confirmar-gerar').classList.remove('d-none');
            
            const corpo = document.getElementById('lq-tabela-corpo');
            corpo.innerHTML = '<tr><td colspan="10" class="text-center text-muted py-3"><span class="spinner-border spinner-border-sm"></span> Calculando simulação do LQ em tempo real...</td></tr>';
            
            modalLq.show();

            fetch(`${API_URL}?id=${id}&action=simular_lq`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    lqDadosGlobais = data.dados || [];
                    popularSelectQuadros(lqDadosGlobais);
                    renderizarTabelaLq(lqDadosGlobais);
                } else {
                    corpo.innerHTML = `<tr><td colspan="10" class="text-center text-danger py-3">Erro na simulação: ${data.error || 'Erro interno.'}</td></tr>`;
                }
            })
            .catch(err => {
                corpo.innerHTML = '<tr><td colspan="10" class="text-center text-danger py-3">Erro de comunicação com o servidor.</td></tr>';
            });
        });
    });

    // Clique em Confirmar e Gerar LQ Permanente
    document.getElementById('btn-modal-confirmar-gerar').addEventListener('click', function() {
        if (!processoIdAtual) return;
        if (!confirm('Atenção: Isso salvará permanentemente o Limite Quantitativo calculado no banco de dados e atualizará o status do processo. Deseja prosseguir?')) {
            return;
        }

        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Gerando...';

        fetch(`${API_URL}?id=${processoIdAtual}&action=gerar_lq`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showToast('Limite Quantitativo gerado e publicado com sucesso!', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast(data.error || 'Erro ao gerar LQ.', 'danger');
            }
        })
        .catch(err => showToast('Erro de comunicação com o servidor.', 'danger'))
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-lg"></i> Confirmar e Gerar LQ Permanente';
        });
    });

    // Clique em Visualizar LQ (Calculado)
    document.querySelectorAll('.btn-visualizar-lq').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const ident = this.dataset.identificacao;
            processoIdAtual = id;

            document.getElementById('modal-lq-titulo').textContent = 'Visualização do Limite Quantitativo - Processo ' + ident;
            document.getElementById('lq-detalhes-processo').textContent = 'Processo: ' + ident;
            document.getElementById('lq-alerta-simulacao').classList.add('d-none');
            document.getElementById('btn-modal-confirmar-gerar').classList.add('d-none');
            
            const corpo = document.getElementById('lq-tabela-corpo');
            corpo.innerHTML = '<tr><td colspan="10" class="text-center text-muted py-3"><span class="spinner-border spinner-border-sm"></span> Carregando LQ gerado do banco de dados...</td></tr>';
            
            modalLq.show();

            fetch(`${API_URL}?id=${id}&action=buscar_lq`, {
                method: 'GET'
            })
            .then(r => r.json())
            .then(data => {
                lqDadosGlobais = data || [];
                popularSelectQuadros(lqDadosGlobais);
                renderizarTabelaLq(lqDadosGlobais);
            })
            .catch(err => {
                corpo.innerHTML = '<tr><td colspan="10" class="text-center text-danger py-3">Erro ao carregar dados do banco.</td></tr>';
            });
        });
    });

    // Gerar LQ direto
    document.querySelectorAll('.btn-gerar-lq').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            if (!confirm('Atenção: Isso calculará (ou recalculará) todo o Limite Quantitativo para este semestre com base nos militares ativos agora. Deseja prosseguir?')) {
                return;
            }

            const icon = this.querySelector('i');
            icon.className = 'spinner-border spinner-border-sm';
            this.disabled = true;

            fetch(`${API_URL}?id=${id}&action=gerar_lq`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showToast('Limite Quantitativo gerado com sucesso!', 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToast(data.error || 'Erro ao gerar LQ.', 'danger');
                }
            })
            .catch(err => showToast('Erro de comunicação com o servidor.', 'danger'))
            .finally(() => {
                icon.className = 'bi bi-check-circle';
                this.disabled = false;
            });
        });
    });

    // Excluir Processo
    document.querySelectorAll('.btn-excluir-processo').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const ident = this.dataset.identificacao;
            if (!confirm(`Atenção: Tem certeza que deseja excluir permanentemente o Processo Promocional ${ident} e todos os snapshots calculados? Esta ação não pode ser desfeita.`)) {
                return;
            }

            const icon = this.querySelector('i');
            const originalClass = icon.className;
            icon.className = 'spinner-border spinner-border-sm';
            this.disabled = true;

            fetch(`${API_URL}?id=${id}`, {
                method: 'DELETE'
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showToast('Processo promocional excluído com sucesso!', 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToast(data.error || 'Erro ao excluir processo.', 'danger');
                    icon.className = originalClass;
                    this.disabled = false;
                }
            })
            .catch(err => {
                showToast('Erro de comunicação com o servidor.', 'danger');
                icon.className = originalClass;
                this.disabled = false;
            });
        });
    });
});
</script>

<?php include_once '../Views/footer2.php'; ?>
