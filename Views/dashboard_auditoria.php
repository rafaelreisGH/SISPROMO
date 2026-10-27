<?php
// Controle de Visualização
$nivel = 2; // Administrador
require_once '../Controllers/verifica_permissoes.php';
include_once './header2.php';
?>

<div class="container my-4">
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex flex-wrap align-items-center justify-content-between border-bottom pb-3">
                <h3 class="fw-bold mb-2 mb-md-0">
                    <i class="bi bi-speedometer2 text-danger me-2"></i> Dashboard de Auditoria e Inteligência Operacional
                </h3>
                
                <!-- Filtros Globais -->
                <form class="d-flex align-items-center gap-2" id="form-filtros">
                    <label for="filtro-dias" class="form-label mb-0 fw-semibold small text-muted text-nowrap">Período:</label>
                    <select class="form-select form-select-sm" id="filtro-dias" style="width: auto;">
                        <option value="1">Hoje</option>
                        <option value="7">Últimos 7 dias</option>
                        <option value="30" selected>Últimos 30 dias</option>
                        <option value="180">Últimos 6 meses (Ciclo)</option>
                    </select>
                    <button type="button" class="btn btn-sm btn-danger d-flex align-items-center gap-1" onclick="carregarDashboard()">
                        <i class="bi bi-funnel-fill"></i> Aplicar
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Módulo 1: Painel de Indicadores -->
    <div class="row g-3 mb-4" id="modulo-indicadores">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm border-start border-primary border-4 h-100">
                <div class="card-body">
                    <div class="text-muted small fw-semibold text-uppercase mb-1">Volume Total</div>
                    <div class="fs-2 fw-bold text-primary" id="ind-volume">...</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm border-start border-success border-4 h-100">
                <div class="card-body">
                    <div class="text-muted small fw-semibold text-uppercase mb-1">Gestores Ativos</div>
                    <div class="fs-2 fw-bold text-success" id="ind-gestores">...</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm border-start border-warning border-4 h-100">
                <div class="card-body">
                    <div class="text-muted small fw-semibold text-uppercase mb-1">Ações Críticas</div>
                    <div class="fs-2 fw-bold text-warning" id="ind-criticas">...</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm border-start border-danger border-4 h-100">
                <div class="card-body">
                    <div class="text-muted small fw-semibold text-uppercase mb-1">Falhas (Erros)</div>
                    <div class="fs-2 fw-bold text-danger" id="ind-falhas">...</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos Principais -->
    <div class="row g-3 mb-4">
        <!-- Módulo 2: Monitoramento de Uso -->
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-bottom fw-semibold">
                    <i class="bi bi-graph-up me-2 text-primary"></i> Monitoramento de Uso e Picos Temporais
                </div>
                <div class="card-body">
                    <div style="height: 300px; width: 100%;">
                        <canvas id="chart-monitoramento"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Módulo 4: Gestão de Segurança (Ações por Perfil) -->
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-bottom fw-semibold">
                    <i class="bi bi-pie-chart-fill me-2 text-danger"></i> Distribuição por Perfil
                </div>
                <div class="card-body">
                    <div style="height: 300px; width: 100%;">
                        <canvas id="chart-perfil"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row g-3 mb-4">
        <!-- Módulo 3: Auditoria (Ranking) -->
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-bottom fw-semibold">
                    <i class="bi bi-bar-chart-line-fill me-2 text-info"></i> Ranking das Ações Mais Frequentes
                </div>
                <div class="card-body">
                    <div style="height: 300px; width: 100%;">
                        <canvas id="chart-ranking"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-bottom fw-semibold">
                    <i class="bi bi-trophy-fill me-2 text-warning"></i> Top Gestores Mais Atuantes
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="tabela-top-gestores">
                            <thead class="table-light">
                                <tr>
                                    <th>Gestor</th>
                                    <th class="text-end pe-3">Total de Ações</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Módulo 5: Central de Consulta Analítica -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <span class="fw-semibold">
                        <i class="bi bi-list-columns-reverse me-2 text-secondary"></i> Central de Consulta Analítica (Feed de Auditoria)
                    </span>
                    <button class="btn btn-outline-success btn-sm d-flex align-items-center gap-1" onclick="exportarCSV()">
                        <i class="bi bi-file-earmark-spreadsheet-fill"></i> Exportar CSV
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle mb-0" id="tabela-feed">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 170px;">Data/Hora</th>
                                    <th style="width: 100px;">Nível</th>
                                    <th>Mensagem</th>
                                    <th style="width: 150px;" class="text-center">Contexto (JSON)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Carregado via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Exibir JSON Formatado -->
<div class="modal fade" id="modalContexto" tabindex="-1" aria-labelledby="modalContextoLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-bottom">
        <h5 class="modal-title fw-bold" id="modalContextoLabel">
            <i class="bi bi-code-slash text-danger me-2"></i> Detalhes do Contexto
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body p-3">
        <pre id="json-viewer" class="bg-light p-3 rounded mb-0 text-dark small" style="max-height: 450px; overflow-y: auto;"></pre>
      </div>
    </div>
  </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- Script do Dashboard -->
<script src="../js/dashboard_auditoria.js"></script>

<?php
include_once './footer2.php';
?>
