<?php
require_once '../Controllers/nivel_gestor.php';
include_once './header2.php';
require_once '../ConexaoDB/conexao.php';
$conn = Conexao::getConexao();
?>

<div class="container">
    <div class="col-md-12">
        <ul class="nav nav-pills">
            <li role="presentation" class="nav-item">
                <a href="pagina_relatorios.php" class="nav-link">Voltar</a>
            </li>
        </ul>
        <hr>
    </div>
    <form name="form" action="lista_positivados.php" method="GET">
        <h3><strong>Selecione o processo promocional</strong></h3>
        <hr>
        <div class="form-row">
            <div class="form-group col-md-3">
                <div class="col">
                    <div class="input-group mb-3">
                        <span class="input-group-text">Ano</span>
                        <select class="form-select" name="ano" id="FormControlSelectAno">
                            <?php
                            $ano_atual = date("Y");
                            for ($i = $ano_atual; $i <= ($ano_atual + 4); $i++) {
                                echo "<option value=" . $i . ">" . $i . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="input-group mb-3">
                        <span class="input-group-text">Dia/mês</span>
                        <select class="form-select" name="semestre">
                            <option value="" selected disabled>Selecione o semestre</option>
                            <option value="1">1º semestre</option>
                            <option value="2">2º semestre</option>
                        </select>
                    </div>
                </div>
            </div>
            </br>
            <button class="btn btn-outline-primary active" type="submit" name="buscar">Buscar</button>
        </div>
    </form>
</div>

<script>
    function validateForm() {
        let ano = document.forms["form"]["ano"].value;
        if (ano == "") {
            alert("O quesito ANO deve ser preenchido");
            return false;
        }
        let semestre = document.forms["form"]["semestre"].value;
        if (semestre == "") {
            alert("O quesito DIA/MêS deve ser preenchido");
            return false;
        }
    }
</script>

<?php
include_once './footer2.php';
?>