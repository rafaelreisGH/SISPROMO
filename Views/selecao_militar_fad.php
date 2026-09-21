<?php
require_once '../Controllers/nivel_gestor.php';
include_once './header2.php';
require_once '../ConexaoDB/conexao.php';
$conn = Conexao::getConexao();

$pesquisar = isset($_POST['pesquisar']) ? filter_input(INPUT_POST, 'pesquisar', FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW) : null;

if (!is_null($pesquisar)) {
    switch ($pesquisar) {
        case "":
            $stmt = $conn->prepare("SELECT * FROM militar WHERE nome LIKE '%$pesquisar%' AND status = 'ATIVO' AND posto_grad_mil != 'CEL BM'  ORDER BY antiguidade");
            $stmt->execute();
            break;
        default:
            $stmt = $conn->prepare("SELECT * FROM militar WHERE nome LIKE '%$pesquisar%' AND status = 'ATIVO' AND posto_grad_mil != 'CEL BM'  ORDER BY antiguidade");
            $stmt->execute();
            break;
    }
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
    <h3>
        <strong>Seleção de critérios de pesquisa</strong>
    </h3>
    <hr>
    <div class="form-row">
        <div class="form-group col-md-4">
            <div class="col">
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                    <div class="input-group mb-3">
                        <span class="input-group-text" id="postoGraduacao">Nome</span>
                        <input type="text" class="form-control" placeholder="Digite o nome" name="pesquisar">
                    </div>
            </div>
            <button class="btn btn-outline-primary active" type="submit" name="buscar">Buscar</button>
            </form>
        </div>
    </div>
    <hr>
    <div class="col-md-12">
        <div class="d-flex justify-content-left">
            <div class="panel-body">
                <form action="insere_fad.php" method="POST" name="formName" onsubmit="return validate()">
                    <?php
                    if (!is_null($pesquisar)) {
                        echo '<table class="table table-striped table-bordered table-list">'
                            . '<thead><tr>'
                            . '<th><p align="center">Seleção</p></th>'
                            . '<th><p align="center">Nome</p></th>'
                            . '<th><p align="center">Posto/Grad</p>'
                            . '</th></tr></thead>'
                            . '<tbody>';

                        while ($resultados = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            $aux_id = $resultados['id'];
                            $aux_nome = $resultados['nome'];
                            $aux_posto_grad = $resultados['posto_grad_mil'];

                            echo '<tr>'
                                . '<td align="center"><input type="radio" class="btn-radio" name="militar_id" value="' . $aux_id . '" id="radio"></td>'
                                . '<td align="center">' . $aux_posto_grad . '</td>'
                                . '<td align="center">' . $aux_nome . '</td>';
                        }
                        echo '</tbody></table>';
                        echo '<button class="btn btn-outline-primary active" type="submit" name="buscar">Selecionar</button>';
                    }
                    ?>
                </form>
            </div>
        </div>
    </div>
    </br>
</div>

<script>
    function validate() {
        let x = document.getElementById('radio').checked
        if (x == true) {
            return true;
        } else {
            alert("Selecione um militar.");
            return false;
        }
    }
</script>

<?php
include_once './footer2.php';
?>