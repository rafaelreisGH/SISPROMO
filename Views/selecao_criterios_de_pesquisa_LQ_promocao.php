<?php
require_once '../Controllers/nivel_gestor.php';
include_once './header2.php';
require_once '../ConexaoDB/conexao.php';
require_once '../Controllers/select_LQ.php';

$conn = Conexao::getConexao();
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
    <?php
    $aux = verificaSeExisteLQv1($conn, $ano_atual, $semestre_atual);
    if (!is_null($aux)) {
        $ano_atual = $aux['ano_atual'];
        $semestre_atual = $aux['semestre_atual'];
    } else {
        echo '</p>Não existe LQ para o ano e semestre atuais.</p>';
        exit;
    }
    ?>
    <form action="../Views/listar_militares_promocao_em_lote_v2.php" method="POST" name="formLQpromo" onsubmit="return validateForm()">
        <h3><strong>Teste</strong></h3>
        <hr>
        <div class="form-row">
            <div class="form-group col-md-3">
                <div class="col">
                    <div class="input-group mb-3">
                        <span class="input-group-text" id="ano_promocao_futura">Ano</span>
                        <select class="form-select" name="criterio_ano_promocao_futura" id="FormControlSelectAno">
                            <option value="<?= $ano_atual ?>" selected><?= $ano_atual ?></option>
                        </select>
                    </div>
                    <div class="input-group mb-3">
                        <span class="input-group-text" id="semestre_promocao_futura">Dia/mês</span>
                        <select class="form-select" name="criterio_semestre_promocao_futura">
                            <option value="<?= $semestre_atual ?>" selected><?= $semestre_atual ?>º semestre</option>
                        </select>
                    </div>
                    <div class="input-group mb-3">
                        <span class="input-group-text" id="postoGraduacao">Posto/Graduação</span>
                        <select class="form-select" name="criterio_posto_grad" id="FormControlSelectPosto">
                            <option selected disabled>Selecione o posto ou graduação</option>
                            <option value="CEL BM">Coronel</option>
                            <option value="TC BM">Tenente Coronel</option>
                            <option value="MAJ BM">Major</option>
                            <option value="CAP BM">Capitão</option>
                            <option value="1º TEN BM">1º Tenente</option>
                            <option value="2º TEN BM">2º Tenente</option>
                            <option value="ASP OF BM">Aspirante-a-oficial</option>
                            <option value="CAD BM">Cadete</option>
                            <option value="AL OF BM">Aluno oficial CAOC</option>
                            <option value="ST BM" disabled>Sub-tentente</option>
                            <option value="1º SGT BM">1º Sargento</option>
                            <option value="2º SGT BM">2º Sargento</option>
                            <option value="3º SGT BM">3º Sargento</option>
                            <option value="CB BM">Cabo</option>
                            <option value="SD BM">Soldado</option>
                            <option value="AL SD BM">Aluno Soldado</option>
                        </select>
                    </div>
                    <div class="input-group mb-3">
                        <span class="input-group-text" id="quadroMilitar">Quadro</span>
                        <select class="form-select" name="criterio_quadro">
                            <option value="" selected disabled>Selecione o quadro</option>
                            <option value="QOBM">QOBM</option>
                            <option value="QCOBM">QCOBM</option>
                            <option value="QOSBM">QOSBM</option>
                            <option value="QPBM">QPBM</option>
                        </select>
                    </div>
                </div>
            </div>
            </br>
            <button class="btn btn-outline-primary active" type="submit" name="buscar">Buscar</button>
        </div>
</div>
</form>
</div>

<script>
    function validateForm() {
        // abaixo serve para Limite de Quantitativo
        let m = document.forms["formLQpromo"]["criterio_ano_promocao_futura"].value;
        if (m == "") {
            alert("O quesito ANO deve ser preenchido");
            return false;
        }
        let n = document.forms["formLQpromo"]["criterio_semestre_promocao_futura"].value;
        console.log(n);
        if (n == "") {
            alert("O quesito DIA/MêS deve ser preenchido");
            return false;
        }
        let o = document.forms["formLQpromo"]["criterio_quadro"].value;
        if (o == "") {
            alert("O quesito QUADRO deve ser preenchido");
            return false;
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        const checkbox = document.querySelector('#flexCheckPasta');

        form.addEventListener('submit', function(e) {
            if (checkbox.checked) {
                const confirmar = confirm("Você tem certeza que deseja criar as pastas promocionais?");
                if (!confirmar) {
                    e.preventDefault(); // Cancela o envio do form
                }
            }
        });
    });
</script>

<?php
include_once './footer2.php';
?>