<?php
require_once '../Controllers/nivel_gestor.php';
include_once './header2.php';
require_once '../ConexaoDB/conexao.php';
$conn = Conexao::getConexao();

//sucesso;
$sucesso_exclusao = isset($_GET['sucesso_exclusao']) ? $_GET['sucesso_exclusao'] : null;
//nada excluído
$nada_excluido = isset($_GET['nada_excluido']) ? $_GET['nada_excluido'] : null;

//id do militar em questão (selecionado anteriormente)
if (isset($_POST['militar_id'])) {
    $militar_id = $_POST['militar_id'];
} else if (isset($_GET['militar_id'])) {
    $militar_id = $_GET['militar_id'];
}

//SELECT para buscar no BD
$stmt2 = $conn->prepare("SELECT nome, posto_grad_mil FROM militar WHERE id = :id");
$resultado = $stmt2->execute(array(
    ':id' => $militar_id,
));;
$dados = $stmt2->fetchAll(PDO::FETCH_ASSOC);
foreach ($dados as $key => $valor) {
    $nome = $valor['nome'];
    $posto_grad = $valor['posto_grad_mil'];
}
unset($dados);

//array para gravar os resultados que vem do BD
$consulta = array();

//SELECT para buscar no BD
$stmt = $conn->prepare("SELECT * FROM registro_de_promocoes WHERE militar_id = :id");
$resultado = $stmt->execute(array(
    ':id' => $militar_id,
));

//percorrer os resultados e salvar no $consulta
while ($resultado = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $consulta[] = $resultado;
}

?>

<div class="container">
    <div class="col-md-12">
        <ul class="nav nav-pills">
            <li role="presentation" class="nav-item">
                <a href="listar_militares_relatorio_de_promocoes.php?pesquisar=" class="nav-link">Voltar</a>
            </li>
        </ul>
        <hr>
        <h3>
            <strong>Relatório de Registros de promoção</strong>
        </h3>
        <p><font style="color:#ff0000">
            <?php
            if (isset($sucesso_exclusao)) {
                echo 'Registro excluído: </br> Promoção em ' . $sucesso_exclusao[0] . ' a ' . $sucesso_exclusao[1] . ' ' . strtolower($sucesso_exclusao[2]);
            } else if (!is_null($nada_excluido)) {
                echo 'Nenhum registro foi excluído.';
            }
            ?>
        </font></p>
    </div>

    <div class="col-md-12">
        <hr>
        <h6><?php echo '<p align="center">' . $nome . ' - ' . $posto_grad . '</p>'; ?></h6>
        <hr>
    </div>
    <div class="col-md-12">
        <div class="panel panel-default panel-table">
            <div class="panel-body">
                <table class="table table-striped table-bordered table-list">
                    <thead>
                        <tr>
                            <th>
                                <p align="center">Excluir registro</p>
                            </th>
                            <th>
                                <p align="center">Data</p>
                            </th>
                            <th>
                                <p align="center">Promovido(a) a</p>
                            </th>
                            <th>
                                <p align="center">Modalidade</p>
                            </th>
                        </tr>
                    </thead>

                    <form action="../Controllers/excluir_registro_promocao.php" method="POST" onsubmit="return confirmarExclusao()">
                        <tbody>
                            <?php
                            //arquivos chamados para fazer os alias
                            require_once '../Controllers/alias_posto_grad.php';
                            require_once '../Controllers/alias_ultima_promocao.php';

                            //só mostra a tabela se houver resultados na pesquisa
                            if (isset($stmt) && !empty($consulta)) {
                                foreach ($consulta as $key => $valor) {
                                    $aux_a_contar_de = $valor['a_contar_de'];
                                    $aux_posto_grad = $valor['grau_hierarquico'];
                                    $aux_modalidade = strtolower($valor['modalidade']);
                                    $aux_id = $valor['id'];

                                    echo '<tr>'
                                        . '<td align="center"><button class="btn btn-outline-secondary" type="submit" name="registro_id" value="' . $aux_id . '" title="Excluir"><i class="bi bi-x-circle-fill"></i></td>'
                                        . '<td align="center">' . alias_ultima_promocao($aux_a_contar_de) . '</td>'
                                        . '<td align="center">' . alias_posto_grad($aux_posto_grad) . '</td>'
                                        . '<td align="center">' . ucfirst($aux_modalidade) . '</td>';
                                }
                            } else {
                                echo "Nenhum resultado encontrado!</br></br>";
                            }
                            ?>
                        </tbody>
                        <input type="hidden" value="<?= $militar_id; ?>" name="militar_id">
                    </form>

                </table>
            </div>
        </div>
    </div>
</div>

<script>
    function confirmarExclusao() {
        var confirma = window.confirm("Confirma a exclusão do registro?");
        if (confirma == true) return true
        else return false
    }
</script>

<?php
include_once './footer2.php';
?>