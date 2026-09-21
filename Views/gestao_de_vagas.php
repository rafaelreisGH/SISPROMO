<?php

/**
 * Views/gestao_de_vagas.php
 * 
 * Gestão de Vagas por Contêiner (LC nº 530/2014 e LC nº 828/2025).
 * Contrasta: Efetivo Ativo (Ocupado) x Vagas Abertas x Limite Legal Editável.
 */

require_once '../Controllers/nivel_gestor.php';
require_once '../vendor/autoload.php';
include_once './header2.php';

use App\DAO\GestaoVagasFixadasDAO;
use App\DAO\QuadroAcessoDAO;

$dao = new GestaoVagasFixadasDAO();

// 1. Busca os dados de ocupação e limites calculados via DAO
$painel = $dao->buscarPainelOcupacaoVagas();

$totalGeralPrevisto   = $painel['total_previsto'];
$totalGeralOcupado    = $painel['total_ocupado'];
$totalGeralExcedentes = $painel['total_excedentes'] ?? 0;
$totalGeralAberto     = $painel['total_aberto'];
$dadosPorQuadro       = $painel['dados_por_quadro'];

// 2. Estrutura Canônica Institucional (Nomenclaturas Formais do CBMMT)
$estruturaQuadros = [
    1 => [
        'titulo' => QuadroAcessoDAO::formatarNomeQuadro(1),
        'postos' => ['Coronel', 'Tenente Coronel e Major', 'Capitão', 'Primeiro e Segundo Tenente']
    ],
    2 => [
        'titulo' => QuadroAcessoDAO::formatarNomeQuadro(2),
        'postos' => ['Tenente Coronel e Major', 'Capitão', 'Primeiro e Segundo Tenente']
    ],
    3 => [
        'titulo' => QuadroAcessoDAO::formatarNomeQuadro(3),
        'postos' => ['Tenente Coronel e Major', 'Capitão', 'Primeiro e Segundo Tenente']
    ],
    4 => [
        'titulo' => QuadroAcessoDAO::formatarNomeQuadro(4),
        'postos' => [
            'Subtenentes', 
            'Primeiros, Segundos e Terceiros Sargentos', 
            'Cabos e Soldados'
        ]
    ]
];

/**
 * Função auxiliar para buscar os dados de um agrupamento específico dentro do retorno do DAO.
 */
function obterDadosAgrupamento(array $dadosPorQuadro, int $idQuadro, string $nomeAgrupamento): array {
    foreach ($dadosPorQuadro as $qd) {
        if ($qd['id_quadro'] === $idQuadro) {
            foreach ($qd['agrupamentos'] as $agrup) {
                if ($agrup['nome'] === $nomeAgrupamento) {
                    return $agrup;
                }
            }
        }
    }
    return [
        'limite_legal'  => 0,
        'ocupacao'      => 0,
        'excedentes'    => 0,
        'vagas_abertas' => 0,
        'postos'        => [],
        'perc_ocupacao' => 0
    ];
}
?>

<!-- Toast Container Flutuante para feedback assíncrono -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1100">
    <div id="toastApp" class="toast align-items-center text-white border-0 shadow" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body" id="toastAppMsg"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Fechar"></button>
        </div>
    </div>
</div>

<div class="container mt-3 mb-5">

    <!-- Breadcrumb e Título -->
    <div class="col-md-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="pagina_gestao.php">Painel Gestão</a></li>
                <li class="breadcrumb-item active" aria-current="page">Gestão de Vagas por Contêiner</li>
            </ol>
        </nav>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h3><i class="bi bi-bar-chart-steps text-primary me-2"></i> Gestão de Vagas e Ocupação do Efetivo</h3>
                <p class="text-muted mb-0">Contraste entre o efetivo ativo ocupado no banco e as vagas fixadas em lei. Altere o limite legal e clique em Atualizar.</p>
            </div>
            <a href="pagina_gestao.php" class="btn btn-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>
        <hr>
    </div>

    <!-- Cards de Resumo no Topo -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-primary shadow-sm h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small text-uppercase fw-bold">Total Fixado em Lei</span>
                        <h2 class="mb-0 text-primary fw-bold"><?= number_format($totalGeralPrevisto, 0, ',', '.') ?></h2>
                        <small class="text-muted">Limite legal corporativo</small>
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
                        <span class="text-muted small text-uppercase fw-bold">Total de Vagas Abertas</span>
                        <h2 class="mb-0 text-success fw-bold"><?= number_format($totalGeralAberto, 0, ',', '.') ?></h2>
                        <small class="text-muted">Claros reais na corporação</small>
                    </div>
                    <i class="bi bi-door-open-fill fs-1 text-success opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabelas Agrupadas por Quadro -->
    <?php foreach ($estruturaQuadros as $idQuadro => $dadosQuadro): ?>
        <div class="card shadow-sm mb-4 border-0">
            <div class="card-header bg-light py-3 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-dark fw-bold">
                    <i class="bi bi-shield-shaded text-primary me-2"></i> <?= htmlspecialchars($dadosQuadro['titulo']) ?>
                </h5>
                <span class="badge bg-secondary"><?= count($dadosQuadro['postos']) ?> Contêiner(es)</span>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th class="text-center" style="width: 4%;">#</th>
                                <th style="width: 22%;">Contêiner / Agrupamento</th>
                                <th style="width: 20%;">Postos Abrangidos</th>
                                <th class="text-center" style="width: 10%;">Efetivo Regular</th>
                                <th class="text-center" style="width: 10%;">Excedentes</th>
                                <th class="text-center" style="width: 12%;">Vagas Abertas</th>
                                <th class="text-center" style="width: 22%;">Limite Legal (Fixado) e Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $contador = 1;
                            foreach ($dadosQuadro['postos'] as $posto): 
                                $info = obterDadosAgrupamento($dadosPorQuadro, $idQuadro, $posto);
                                $quantidadeAtual = $info['limite_legal'];
                                $ocupacao = $info['ocupacao'];
                                $excedentes = $info['excedentes'] ?? 0;
                                $vagasAbertas = $info['vagas_abertas'];
                            ?>
                            <tr>
                                <th class="text-center text-muted" scope="row"><?= $contador++ ?></th>
                                <td><strong><?= htmlspecialchars($posto) ?></strong></td>
                                <td>
                                    <?php if (!empty($info['postos'])): ?>
                                        <div class="d-flex flex-wrap gap-1">
                                            <?php foreach ($info['postos'] as $pNome): ?>
                                                <span class="badge bg-light text-dark border"><?= htmlspecialchars($pNome) ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-danger small"><i class="bi bi-exclamation-triangle"></i> Sem postos mapeados</span>
                                    <?php endif; ?>
                                </td>
                                
                                <!-- Efetivo Ativo Regular Ocupado -->
                                <td class="text-center fw-bold fs-6 text-dark"><?= $ocupacao ?></td>

                                <!-- Militares Excedentes -->
                                <td class="text-center">
                                    <?php if ($excedentes > 0): ?>
                                        <span class="badge bg-warning text-dark" title="Militares ativos marcados como excedentes">
                                            <i class="bi bi-person-exclamation me-1"></i> <?= $excedentes ?> Excedente(s)
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">0</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Vagas Reais em Aberto -->
                                <td class="text-center">
                                    <?php if ($vagasAbertas > 0): ?>
                                        <span class="badge bg-success fs-6"><i class="bi bi-check-circle"></i> <?= $vagasAbertas ?> Aberta(s)</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Lotado (0)</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Formulário de Edição do Limite Legal -->
                                <td class="text-center">
                                    <form action="../api/salvar_vaga_api.php" method="POST" class="d-flex justify-content-center align-items-center form-vaga" style="gap: 8px;">
                                        <input type="hidden" name="quadro_id" value="<?= $idQuadro ?>">
                                        <input type="hidden" name="nome_do_agrupamento" value="<?= htmlspecialchars($posto) ?>">
                                        
                                        <input type="number" 
                                               class="form-control form-control-sm text-center fw-bold" 
                                               name="quantidade" 
                                               value="<?= $quantidadeAtual ?>" 
                                               min="0" 
                                               style="width: 85px;">
                                        
                                        <button type="submit" class="btn btn-sm btn-outline-primary btn-atualizar" title="Salvar alteração do limite">
                                            <i class="bi bi-arrow-repeat"></i> Atualizar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

</div>

<script>
/**
 * Helper de Toast do Bootstrap
 */
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
    bootstrap.Toast.getOrCreateInstance(toastEl, { delay: 3500 }).show();
}

/**
 * Intercepta o submit de todos os formulários de vagas da página.
 */
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('.form-vaga');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const btn = form.querySelector('.btn-atualizar');
            const originalHtml = btn.innerHTML;

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';

            fetch(form.action, {
                method: 'POST',
                body: new FormData(form)
            })
            .then(response => {
                if (!response.ok) throw new Error('Erro de comunicação com o servidor (HTTP ' + response.status + ').');
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    showToast(data.message || 'Vagas atualizadas com sucesso!', 'success');
                    // Recarrega a página após 1s para recalcular as vagas abertas
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast(data.error || 'Ocorreu um erro ao salvar. Tente novamente.', 'danger');
                }
            })
            .catch(error => {
                console.error('[gestao_de_vagas] Erro no fetch:', error);
                showToast(error.message || 'Falha de conexão com o servidor.', 'danger');
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            });
        });
    });
});
</script>

<?php include_once './footer2.php'; ?>