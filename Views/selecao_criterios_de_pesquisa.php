<?php
require_once '../Controllers/nivel_gestor.php';
include_once './header2.php';
require_once '../ConexaoDB/conexao.php';
$conn = Conexao::getConexao();

$alteracao_antiguidade = isset($_GET['alteracao_antiguidade']) ? $_GET['alteracao_antiguidade'] : null;
$promover = isset($_GET['promover']) ? $_GET['promover'] : null;

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
    <form action="<?php
                    if (!is_null($alteracao_antiguidade)) {
                        echo 'listar_militares_atualizar_antiguidade.php';
                    } else if(!is_null($promover)){
                        echo 'listar_militares_promocao_em_lote.php';
                    } else {
                        echo 'listar_militares_data_em_lote.php';
                    }
                    ?>" method="POST">
        <h3>
            <strong>Seleção de critérios de pesquisa</strong>
        </h3>
        <hr>
        <div class="form-row">
            <div class="form-group col-md-4">
                <div class="col">
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
                            <option value="ST BM">Sub-tentente</option>
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
                            <option selected disabled>Selecione o quadro</option>
                            <option value="1">QOBM</option>
                            <option value="2">QCOBM</option>
                            <option value="3">QOSBM</option>
                            <option value="4">QPBM</option>
                        </select>
                    </div>
                </div>
                <button class="btn btn-outline-primary active" type="submit" name="buscar">Buscar</button>
            </div>
        </div>
    </form>
</div>

<?php
include_once './footer2.php';
?>