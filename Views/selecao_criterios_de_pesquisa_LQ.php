<?php
require_once '../Controllers/nivel_gestor.php';
include_once './header2.php';
require_once '../ConexaoDB/conexao.php';
$conn = Conexao::getConexao();

$isRelatorio = isset($_GET['relatorio']) && ($_GET['relatorio'] == 1);

if ($isRelatorio) {
    $action    = "listar_resultado_LQ_documentos.php";
    $relatorio = "Relatório de documentação entregue à Secretaria das Comissões de Promoção (SCP)";
    $btnLabel  = "Buscar Documentação";
} else {
    // Rota tradicional legada acoplada ao funcoes_LQ.php
    $action    = "listar_resultado_LQ.php";
    $relatorio = "Consulta do Limite Quantitativo (LQ) — Modo Comparativo (funcoes_LQ.php)";
    $btnLabel  = "Gerar / Comparar LQ Legado";
}
?>

<div class="container">
    <div class="col-md-12">
        <ul class="nav nav-pills">
            <li role="presentation" class="nav-item">
                <a href="pagina_ferramentas.php" class="nav-link">Voltar</a>
            </li>
        </ul>
        <hr>
    </div>

    <?php if (!$isRelatorio): ?>
        <div class="alert alert-warning d-flex justify-content-between align-items-center mb-4">
            <div>
                <i class="bi bi-info-circle-fill me-2"></i>
                <strong>Modo de Auditoria / Comparação:</strong> Este formulário executa o cálculo do LQ através do script legado (<code>funcoes_LQ.php</code>) para conferência contra o novo motor de snapshots.
            </div>
            <a href="pagina_gestao.php" class="btn btn-sm btn-outline-dark text-nowrap ms-3">
                <i class="bi bi-arrow-right-circle"></i> Ir para Novo Fluxo de Gestão
            </a>
        </div>
    <?php endif; ?>

    <form action="<?= $action ?>" method="POST" name="formLQ" onsubmit="return validateForm()">
        <h3><strong><?= $relatorio ?></strong></h3>
        <hr>
        <div class="form-row">
            <div class="form-group col-md-4">
                <div class="col">
                    <div class="input-group mb-3">
                        <span class="input-group-text" id="ano_promocao_futura">Ano</span>
                        <select class="form-select" name="criterio_ano_promocao_futura" id="FormControlSelectAno">
                            <?php
                            $ano_atual = date("Y");
                            for ($i = $ano_atual - 1; $i <= ($ano_atual + 4); $i++) {
                                $selected = ($i == $ano_atual) ? 'selected' : '';
                                echo "<option value='{$i}' {$selected}>{$i}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="input-group mb-3">
                        <span class="input-group-text" id="dia_mes_promocao_futura">Dia/mês</span>
                        <select class="form-select" name="criterio_dia_mes_promocao_futura" id="FormControlSelectDiaMes">
                            <option value="" selected disabled>Selecione o dia e o mês</option>
                            <option value="07-02">02 de julho</option>
                            <option value="12-02">02 de dezembro</option>
                            <option value="12-21">21 de dezembro</option>
                        </select>
                    </div>
                </div>
            </div>
            <br>
            <button class="btn btn-primary" type="submit" name="buscar">
                <i class="bi bi-calculator"></i> <?= $btnLabel ?>
            </button>
        </div>
    </form>
</div>

<script>
    function validateForm() {
        let m = document.forms["formLQ"]["criterio_ano_promocao_futura"].value;
        if (m === "") {
            alert("O quesito ANO deve ser preenchido.");
            return false;
        }
        let n = document.forms["formLQ"]["criterio_dia_mes_promocao_futura"].value;
        if (n === "") {
            alert("O quesito DIA/MÊS deve ser preenchido.");
            return false;
        }
        return true;
    }
</script>

<?php
include_once './footer2.php';
?>