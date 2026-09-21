<?php
require_once '../Controllers/nivel_gestor.php';
include_once './header2.php';
require_once '../ConexaoDB/conexao.php';
include_once '../Controllers/verifica_permissoes.php';
require_once '../Controllers/select_LQ.php';
$conn = Conexao::getConexao();

//GET para verificar se nada foi alterado
//ou seja, se o usuário não selecionou nenhum militar
//não é realizado nenhuma operação no BD
$nada_alterado = (isset($_GET['nada_alterado'])) ? $_GET['nada_alterado'] : 0;

//verificar se houve alterações
if (isset($_GET['alteracoes_realizadas'])) {
    $alteracoes_realizadas = $_GET['alteracoes_realizadas'];
}

//declaração de variáveis
$criterios_posto_grad;
$criterios_quadro;
$nome;
$posto_grad;
$quadro;
$militar_id;
// --------------------------- //

//verificação dos POSTs
$where = array();
$where[] = " status = 'ATIVO'";
if (isset($_POST['criterio_posto_grad'])) {
    $criterios_posto_grad = $_POST['criterio_posto_grad'];
    $where[] = " posto_grad_mil = '{$criterios_posto_grad}'";
} else if (isset($_GET['criterio_posto_grad'])) {
    $criterios_posto_grad = $_GET['criterio_posto_grad'];
    $where[] = " posto_grad_mil = '{$criterios_posto_grad}'";
}
if (isset($_POST['criterio_quadro'])) {
    $criterios_quadro = $_POST['criterio_quadro'];
    $where[] = " quadro = '{$criterios_quadro}'";
} else if (isset($_GET['criterio_quadro'])) {
    $criterios_quadro = $_GET['criterio_quadro'];
    $where[] = " quadro = '{$criterios_quadro}'";
}
// --------------------------- //

$aux = verificaSeExisteLQv2($conn, $ano_atual, $semestre_atual, $criterios_posto_grad);

// --------------------------- //

// Salva a página atual como "anterior" para ser usada na próxima requisição
$_SESSION['pagina_anterior'] = $_SERVER['REQUEST_URI'];

// --------------------------- //
?>

<div class="container">
    <div class="col-md-12">
        <ul class="nav nav-pills">
            <li role="presentation" class="nav-item">
                <a href="selecao_criterios_de_pesquisa_LQ_promocao.php" class="nav-link">Voltar</a>
            </li>
        </ul>
        <hr>
    </div>
    <h3><strong>Resultados de pesquisa</strong></h3>
    <hr>
    <?php
    if (is_null($aux)) {
        echo '<p>Não há militares no posto (' . $criterios_posto_grad . ') dentro do LQ considerando o ano e semestre atuais.</p>';
        exit;
    }
    ?>
    <form action="../Controllers/processa_promocao.php" method="POST">
        <div class="col-md-12">
            <div class="row">
                <div class="form-group col-md-4">
                    <div class="input-group mb-3">
                        <label for="basic-addon3" class="form-label">Processar promoção</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="basic-addon3">Data da promoção</span>
                            <input type="date" class="form-control" name="data_promocao" required>
                        </div>
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="basic-addon3">Modalidade</span>
                            <select class="form-select" name="modalidade" required>
                                <option selected disabled>Selecione</option>
                                <option value="POR ANTIGUIDADE">POR ANTIGUIDADE</option>
                                <option value="POR MERECIMENTO">POR MERECIMENTO</option>
                                <option value="POR RESSARCIMENTO DE PRETERIÇÃO">POR RESSARCIMENTO DE PRETERIÇÃO</option>
                                <option value="POR REQUERIMENTO">POR REQUERIMENTO</option>
                                <option value="POR ATO DE BRAVURA">POR ATO DE BRAVURA</option>
                                <option value="POR INVALIDEZ">POR INVALIDEZ</option>
                            </select>
                        </div>
                        <div class="input-group mb-3">
                            <span class="input-group-text">Posto/Graduação</span>
                            <select class="form-select" name="promocao_posto_grad" id="FormControlSelectPosto" required>
                                <option selected disabled>Selecione</option>
                                <option value="CEL BM">Coronel</option>
                                <option value="TC BM">Tenente Coronel</option>
                                <option value="MAJ BM">Major</option>
                                <option value="CAP BM">Capitão</option>
                                <option value="1º TEN BM">1º Tenente</option>
                                <option value="2º TEN BM">2º Tenente</option>
                                <option value="ASP OF BM">Aspirante-a-oficial</option>
                                <option value="ST BM">Sub-tentente</option>
                                <option value="1º SGT BM">1º Sargento</option>
                                <option value="2º SGT BM">2º Sargento</option>
                                <option value="3º SGT BM">3º Sargento</option>
                                <option value="CB BM">Cabo</option>
                                <option value="SD BM">Soldado</option>
                            </select>
                        </div>
                        <input type="hidden" name="criterio_posto_grad" value="<?= $criterios_posto_grad ?>">
                        <input type="hidden" name="criterio_quadro" value="<?= $criterios_quadro ?>">
                    </div>

                    <button class="btn btn-outline-danger active" type="submit" name="buscar">Promover militares</button>

                </div>
                <div class="col-md-4">
                    <?php
                    if ($nada_alterado == 1) {
                        echo '<br><font style="color:#ff0000"><i>*Nenhum registro foi inserido, pois não foi selecionado nenhum militar.</i><br>';
                    }
                    if (!empty($alteracoes_realizadas)) {
                        echo '<font style="color:#ff0000">Alterações realizadas no cadastro dos seguintes militares:<br>';
                        foreach ($alteracoes_realizadas as $item) {
                            //$stmt2 = $conn->prepare("SELECT nome, posto_grad_mil FROM militar WHERE id = " . $item . "");
                            $stmt2 = $conn->prepare("SELECT nome, posto_grad_mil, antiguidade FROM militar WHERE id = '" . $item . "' ORDER BY antiguidade");
                            $resultado = $stmt2->execute();
                            while ($resultado = $stmt2->fetch(PDO::FETCH_ASSOC)) {
                                $_nome = $resultado['nome'];
                                $_posto_grad = $resultado['posto_grad_mil'];
                            }
                            echo '<br><font style="color:#ff0000"><i>*' . $_posto_grad . ' ' . $_nome . '.</i>';
                        }
                        echo '<br><br><font style="color:#ff0000"><i class="bi bi-exclamation-circle" fill="currentColor"></i>&nbspOs militares não promovidos acima listados são exibidos pelo fato de a antiguidade ter sido alterada.';
                    }
                    ?>
                </div>
            </div>
            </br>
        </div>

        <div class="col-md-12">
            <div class="panel panel-default panel-table">
                <div class="panel-body">
                    <table class="table table-striped table-bordered table-list">
                        <thead>
                            <tr>
                                <th>
                                    <p align="center"></br>

                                        <input type="checkbox" class="btn-check" id="btn-check" autocomplete="off" onclick="checkUncheck(this)">
                                        <label class="btn btn-light" for="btn-check"><strong>Selecionar todos</strong></label>
                                    </p>
                                </th>
                                <th>
                                    <p align="center">Ordem</p>
                                </th>
                                <th>
                                    <p align="center">Posto/Graduação</p>
                                </th>
                                <th>
                                    <p align="center">Nome</p>
                                </th>
                                <th>
                                    <p align="center">Quadro</p>
                                </th>
                                <th>
                                    <p align="center">Antiguidade</p>
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                            $ordem = 1;
                            foreach ($aux as $item) {
                                $aux_id = $item['militar_id'];
                                $aux_nome = $item['nome'];
                                $aux_posto_grad = $item['posto_grad_mil'];
                                $aux_quadro = $item['quadro'];
                                $aux_antiguidade = $item['antiguidade'];

                                echo '<tr>'
                                    . '<td align="center"><input class="form-check-input mt-0" type="checkbox" value="' . $aux_id . '" name="militar_id[]"></td>'
                                    . '<td align="center">' . $ordem . '</td>'
                                    . '<td align="center">' . $aux_posto_grad . '</td>'
                                    . '<td align="center">' . $aux_nome . '</td>'
                                    . '<td align="center">' . $aux_quadro . '</td>'
                                    . '<td align="center">' . $aux_antiguidade . '</td>';
                                $ordem++;
                            }
                            ?>
                        </tbody>
                    </table>

                </div>

            </div>
        </div>
    </form>
</div>

<script>
    function checkUncheck(main) {
        all = document.getElementsByName('militar_id[]');
        for (var a = 0; a < all.length; a++) {
            all[a].checked = main.checked;
        }
    }
</script>

<?php
include_once './footer.php';
?>