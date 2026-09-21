<?php

/**
 * Views/painel_vagas.php
 * 
 * Painel de Controle e Comparativo de Vagas por Contêiner (LC nº 530/2014 e LC nº 828/2025).
 * Contrasta: Limite Legal (Fixado) x Efetivo Ativo (Ocupado) x Excedentes x Vagas Abertas.
 */

require_once '../Controllers/nivel_gestor.php';
require_once '../vendor/autoload.php';
include_once './header2.php';

use App\DAO\GestaoVagasFixadasDAO;

$dao = new GestaoVagasFixadasDAO();
$painel = $dao->buscarPainelOcupacaoVagas();

$totalGeralPrevisto   = $painel['total_previsto'];
$totalGeralOcupado    = $painel['total_ocupado'];
$totalGeralExcedentes = $painel['total_excedentes'] ?? 0;
$totalGeralAberto     = $painel['total_aberto'];
$dadosPorQuadro       = $painel['dados_por_quadro'];
?>

<div class="container mt-3 mb-5">

    <!-- Breadcrumb e Título -->
    <div class="col-md-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="pagina_gestao.php">Painel Gestão</a></li>
                <li class="breadcrumb-item active" aria-current="page">Painel de Vagas por Contêiner</li>
            </ol>
        </nav>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h3><i class="bi bi-bar-chart-steps text-primary"></i> Painel de Ocupação e Vagas por Contêiner</h3>
                <p class="text-muted mb-0">Comparativo entre o Limite Fixado em Lei (LC nº 530/14) e o Efetivo Ativo Ocupado.</p>
            </div>
            <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                <i class="bi bi-printer"></i> Imprimir Relatório
            </button>
        </div>
        <hr>
    </div>

    <!-- Cards de Resumo Geral no Topo -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-primary shadow-sm h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small text-uppercase fw-bold">Vagas Fixadas em Lei</span>
                        <h2 class="mb-0 text-primary fw-bold"><?= number_format($totalGeralPrevisto, 0, ',', '.') ?></h2>
                        <small class="text-muted">Total corporativo autorizado</small>
                    </div>
                    <i class="bi bi-bank fs-1 text-primary opacity-50"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-dark shadow-sm h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small text-uppercase fw-bold">Efetivo Regular (Ocupado)</span>
                        <h2 class="mb-0 text-dark fw-bold"><?= number_format($totalGeralOcupado, 0, ',', '.') ?></h2>
                        <small class="text-muted">Ativos ocupando contêiner</small>
                    </div>
                    <i class="bi bi-people-fill fs-1 text-dark opacity-50"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-warning shadow-sm h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small text-uppercase fw-bold">Efetivo Excedente</span>
                        <h2 class="mb-0 text-warning text-dark fw-bold"><?= number_format($totalGeralExcedentes, 0, ',', '.') ?></h2>
                        <small class="text-muted">Vagas liberadas em contêineres</small>
                    </div>
                    <i class="bi bi-person-exclamation fs-1 text-warning opacity-50"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-success shadow-sm h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small text-uppercase fw-bold">Vagas Reais em Aberto</span>
                        <h2 class="mb-0 text-success fw-bold"><?= number_format($totalGeralAberto, 0, ',', '.') ?></h2>
                        <small class="text-muted">Claros físicos na corporação</small>
                    </div>
                    <i class="bi bi-door-open-fill fs-1 text-success opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabelas Agrupadas por Quadro -->
    <?php foreach ($dadosPorQuadro as $qd): ?>
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0 text-dark fw-bold">
                    <i class="bi bi-diagram-3-fill text-primary me-2"></i> Quadro: <?= htmlspecialchars($qd['nome_quadro']) ?>
                </h5>
                <span class="badge bg-secondary fs-6"><?= count($qd['agrupamentos']) ?> Contêiner(es)</span>
            </div>

            <div class="card-body p-0">
                <?php if (empty($qd['agrupamentos'])): ?>
                    <div class="p-3 text-muted text-center">Nenhum contêiner configurado para este quadro.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover align-middle mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th style="width: 22%;">Contêiner / Agrupamento</th>
                                    <th style="width: 20%;">Postos / Graduações</th>
                                    <th class="text-center" style="width: 10%;">Vagas Lei</th>
                                    <th class="text-center" style="width: 10%;">Efetivo Regular</th>
                                    <th class="text-center" style="width: 10%;">Excedentes</th>
                                    <th class="text-center" style="width: 12%;">Vagas Abertas</th>
                                    <th class="text-center" style="width: 16%;">Ocupação / Situação</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($qd['agrupamentos'] as $item): ?>
                                    <?php
                                    $excedentes = $item['excedentes'] ?? 0;

                                    // Definição da cor da barra de progresso
                                    $progressColor = 'bg-primary';
                                    if ($item['perc_ocupacao'] >= 100) {
                                        $progressColor = ($item['ocupacao'] > $item['limite_legal']) ? 'bg-danger' : 'bg-secondary';
                                    } elseif ($item['perc_ocupacao'] >= 85) {
                                        $progressColor = 'bg-warning text-dark';
                                    }

                                    // Badge de status
                                    if ($item['vagas_abertas'] > 0) {
                                        $statusBadge = "<span class='badge bg-success'><i class='bi bi-check-circle'></i> {$item['vagas_abertas']} Aberta(s)</span>";
                                    } else {
                                        $statusBadge = "<span class='badge bg-secondary'>Lotado (100%)</span>";
                                    }
                                    ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($item['nome']) ?></strong>
                                            <?php if ($item['ajuste_manual'] != 0): ?>
                                                <br><small class="text-info fw-bold">Ajuste Manual: <?= ($item['ajuste_manual'] > 0 ? '+' : '') . $item['ajuste_manual'] ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($item['postos'])): ?>
                                                <div class="d-flex flex-wrap gap-1">
                                                    <?php foreach ($item['postos'] as $posto): ?>
                                                        <span class="badge bg-light text-dark border"><?= htmlspecialchars($posto) ?></span>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-danger small"><i class="bi bi-exclamation-triangle"></i> Sem postos mapeados</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center fw-bold fs-6"><?= $item['limite_legal'] ?></td>
                                        <td class="text-center fw-bold fs-6 text-dark"><?= $item['ocupacao'] ?></td>
                                        <td class="text-center">
                                            <?php if ($excedentes > 0): ?>
                                                <span class="badge bg-warning text-dark" title="Militares ativos marcados como excedentes">
                                                    <i class="bi bi-person-exclamation me-1"></i> <?= $excedentes ?> Excedente(s)
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">0</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center fw-bold fs-6 text-success"><?= $item['vagas_abertas'] ?></td>
                                        <td class="text-center">
                                            <div class="d-flex flex-column align-items-center">
                                                <div class="mb-1"><?= $statusBadge ?></div>
                                                <div class="progress w-100" style="height: 6px;" title="Ocupação: <?= $item['perc_ocupacao'] ?>%">
                                                    <div class="progress-bar <?= $progressColor ?>" role="progressbar" style="width: <?= min(100, $item['perc_ocupacao']) ?>%;"></div>
                                                </div>
                                                <small class="text-muted" style="font-size: 0.75rem;"><?= $item['perc_ocupacao'] ?>% ocupado</small>
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
    <?php endforeach; ?>

</div>

<?php include_once './footer2.php'; ?>