<?php
require_once '../Controllers/nivel_gestor.php';
include_once '../Views/header2.php';
require_once '../ConexaoDB/conexao.php';
$conn = Conexao::getConexao();

if (isset($_GET['militar_id'])) {
    $militar_id = $_GET['militar_id'];
    //pegar no BD dados do militar selecionado
    $stmt = $conn->query("SELECT * FROM militar WHERE id = '" . $militar_id . "'");
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    if (isset($resultado['nome'])) {
        $nome = $resultado['nome'];
        $posto_grad = $resultado['posto_grad_mil'];
    }
}
if (isset($_POST['militar_id'])) {
    $militar_id = $_POST['militar_id'];
    //pegar no BD dados do militar selecionado
    $stmt = $conn->query("SELECT * FROM militar WHERE id = '" . $militar_id . "'");
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    if (isset($resultado['nome'])) {
        $nome = $resultado['nome'];
        $posto_grad = $resultado['posto_grad_mil'];
    }
}

?>

<div class="container">
    <div class="col-md-12">
        <ul class="nav nav-pills" style="list-style: none; display: flex; gap: 10px; padding: 0;">
            <li class="active"><a class="nav-link active" aria-current="page" href="../Views/pagina_gestor.php">Voltar</a></li>
            <!-- <li class="active"><a class="nav-link active" aria-current="page" href="../Views/cadastro_de_pasta.php?militar_id=<?= $militar_id ?>">Criar Pasta Promocional</a></li> -->
        </ul>
        <hr>
    </div>
    <div class="col-md-12">
        <form>
            <h3>Militar Selecionado</h3>
            <div class="form-text">
                <p>
                    <?php
                    if (isset($_GET['erro'])) {
                        $erro = $_GET['erro'];
                        if ($erro == 1) {
                            echo '<br><font style="color:#ff0000"><i>*Erro! Favor cadastrar pasta promocional.<br>Ainda não há pasta promocional referente ao ' . $_GET['semestre'] . 'º de ' . $_GET['ano'] . ' para:&nbsp</i></font>';
                        }
                    }
                    ?>
                    <?= $posto_grad ?>&nbsp<?= $nome ?></p>
            </div>
        </form>
    </div>

    <div class="clearfix"></div>
    <br />

    <div class="col-md-12">

        <div class="row">
            <div class="col col-md-12">
                <h3 class="panel-title"><strong>Registros de pastas promocionais</strong></h3>
                <hr>
                <?php
                if (isset($_GET['sucesso'])) {
                    echo '<br><font style="color:#ff0000"><i>*Pasta promocional atualizada com sucesso!</i></font>';
                }
                if (isset($_GET['sucesso_exclusão'])) {
                    $i = $_GET['sucesso_exclusão'];
                    switch ($i) {
                        case 1:
                            echo '<br><font style="color:#ff0000"><i>*Documento excluído com sucesso!</i></font>';
                            break;
                        case 0:
                            echo '<br><font style="color:#ff0000"><i>*Nada foi alterado! O documento pode já ter sido excluído ou foi informado um período para o qual não há pasta promocional criada. </i></font>';
                            break;
                    }
                }
                ?>
            </div>
        </div>

        <div class="card" style="width: auto;">
            <ul class="list-group list-group-flush">
                <?php
                try {
                    //PROCURA REGISTRO DE DOCUMENTOS CONFORME ID DO MILITAR
                    $consulta = $conn->query("SELECT id, ano_processo_promocional, semestre_processo_promocional FROM pasta_promocional WHERE militar_id = '$militar_id'");
                    $consulta2 = $conn->query("SELECT id, ano_processo_promocional, semestre_processo_promocional FROM pasta_promocional WHERE militar_id = '$militar_id'");
                    //percorrer os resultados
                    if (($consulta->fetch(PDO::FETCH_ASSOC)) == false) {
                        echo "Nenhum registro encontrado.";
                    }
                    while ($res = $consulta2->fetch(PDO::FETCH_ASSOC)) {
                        $id_da_pasta = $res['id'];
                        $aux_semestre_promocional = $res['semestre_processo_promocional'];
                        $aux_ano_promocional = $res['ano_processo_promocional'];

                        echo '<li class="list-group-item">'
                            . '<p><strong>Processo promocional</br></strong>' . $aux_semestre_promocional . 'º semestre/' . $aux_ano_promocional . '</p>'
                            . '<ul class="nav nav-pills" style="list-style: none; display: flex; gap: 10px; padding: 0;">'
                            .'<li role="presentation"><form action="edicao_documentos_pasta_promo.php" method="POST" style="display: inline;"><input type="hidden" name="id_da_pasta" value="' . $id_da_pasta . '"><button type="submit" class="btn btn-outline-success"><i class="bi bi-pencil-square">&nbsp</i>Editar</button></form></li>'
                            . '<li role="presentation"><a class="btn btn-outline-primary" href="pasta_promocional_resumo.php?id_da_pasta=' . $id_da_pasta . '" role="button" target="_blank"><i class="bi bi-list-check"></i>&nbspResumo</a></li>'
                            //.'<li role="presentation"><form action="../Controllers/exclui_pasta_v2.php" method="POST" style="display: inline;" onsubmit="return confirmarExclusao();"><input type="hidden" name="id_da_pasta" value="' . $id_da_pasta . '"><input type="hidden" name="militar_id" value="' . $militar_id . '"><button type="submit" class="btn btn-outline-danger"><i class="bi bi-trash3"></i>&nbsp;Excluir</button></form></li>'
                            . '</ul><br>';
                    }
                } catch (PDOException $ex) {
                    return $ex->getMessage();
                }
                ?>
            </ul>
        </div>
    </div>

    <script>
        function confirmarExclusao() {
            return confirm("Tem certeza que deseja excluir esta pasta? Esta ação não pode ser desfeita!");
        }
    </script>

    <div class="clearfix"></div>
    <br />
    <?php
    include_once '../Views/footer.php';
    ?>