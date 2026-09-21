<?php
// Controle de Visualização
$nivel = 2; // Administrador
require_once '../Controllers/verifica_permissoes.php';
include_once './header.php';
?>

<div class="container-fluid" style="margin-top: 20px;">
    <div class="row">
        <div class="col-md-12">
            <h2 class="page-header"><i class="glyphicon glyphicon-dashboard"></i> Dashboard de Auditoria e Inteligência Operacional</h2>
            
            <!-- Filtros Globais -->
            <div class="panel panel-default">
                <div class="panel-heading">Filtros Globais</div>
                <div class="panel-body">
                    <form class="form-inline" id="form-filtros">
                        <div class="form-group">
                            <label for="filtro-dias">Período:</label>
                            <select class="form-control" id="filtro-dias">
                                <option value="1">Hoje</option>
                                <option value="7">Últimos 7 dias</option>
                                <option value="30" selected>Últimos 30 dias</option>
                                <option value="180">Últimos 6 meses (Ciclo)</option>
                            </select>
                        </div>
                        <button type="button" class="btn btn-primary" onclick="carregarDashboard()"><i class="glyphicon glyphicon-filter"></i> Aplicar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Módulo 1: Painel de Indicadores -->
    <div class="row" id="modulo-indicadores">
        <div class="col-md-3">
            <div class="panel panel-info">
                <div class="panel-heading"><h3 class="panel-title">Volume Total</h3></div>
                <div class="panel-body"><h2 id="ind-volume">...</h2></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="panel panel-success">
                <div class="panel-heading"><h3 class="panel-title">Gestores Ativos</h3></div>
                <div class="panel-body"><h2 id="ind-gestores">...</h2></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="panel panel-warning">
                <div class="panel-heading"><h3 class="panel-title">Ações Críticas</h3></div>
                <div class="panel-body"><h2 id="ind-criticas">...</h2></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="panel panel-danger">
                <div class="panel-heading"><h3 class="panel-title">Falhas (Erros)</h3></div>
                <div class="panel-body"><h2 id="ind-falhas">...</h2></div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Módulo 2: Monitoramento de Uso -->
        <div class="col-md-8">
            <div class="panel panel-default">
                <div class="panel-heading">Monitoramento de Uso e Picos Temporais</div>
                <div class="panel-body">
                    <canvas id="chart-monitoramento" style="height: 300px; width: 100%;"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Módulo 4: Gestão de Segurança (Ações por Perfil) -->
        <div class="col-md-4">
            <div class="panel panel-default">
                <div class="panel-heading">Distribuição por Perfil</div>
                <div class="panel-body">
                    <canvas id="chart-perfil" style="height: 300px; width: 100%;"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Módulo 3: Auditoria (Ranking) -->
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">Ranking das Ações Mais Frequentes</div>
                <div class="panel-body">
                    <canvas id="chart-ranking" style="height: 300px; width: 100%;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">Top Gestores Mais Atuantes</div>
                <div class="panel-body">
                    <table class="table table-striped" id="tabela-top-gestores">
                        <thead><tr><th>Gestor</th><th>Total de Ações</th></tr></thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Módulo 5: Central de Consulta Analítica -->
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Central de Consulta Analítica (Feed de Auditoria)
                    <button class="btn btn-success btn-xs pull-right" onclick="exportarCSV()">Exportar CSV</button>
                </div>
                <div class="panel-body" style="overflow-x: auto;">
                    <table class="table table-bordered table-hover" id="tabela-feed">
                        <thead>
                            <tr>
                                <th>Data/Hora</th>
                                <th>Nível</th>
                                <th>Mensagem</th>
                                <th>Contexto (JSON)</th>
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

<!-- Modal para Exibir JSON Formatado -->
<div class="modal fade" id="modalContexto" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Detalhes do Contexto</h4>
      </div>
      <div class="modal-body">
        <pre id="json-viewer" style="background-color: #f5f5f5; padding: 15px; border-radius: 5px;"></pre>
      </div>
    </div>
  </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- Script do Dashboard -->
<script src="../js/dashboard_auditoria.js"></script>

<?php
include_once './footer.php';
?>
