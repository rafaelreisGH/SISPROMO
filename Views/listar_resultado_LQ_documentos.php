<?php
require_once '../Controllers/nivel_gestor.php';
include_once './header2.php';
include_once '../Controllers/funcoes_LQ.php';
require_once '../Controllers/alias_quadro.php';

//preparação de variáveis
$lq_ano = isset($_POST['criterio_ano_promocao_futura']) ? $_POST['criterio_ano_promocao_futura'] : null;
$lq_dia_mes = isset($_POST['criterio_dia_mes_promocao_futura']) ? $_POST['criterio_dia_mes_promocao_futura'] : null;
$quadro = isset($_POST['criterio_quadro']) ? $_POST['criterio_quadro'] : null;

//se não chegar nada no POST, então não tem como continuar o processamento
if (is_null($lq_ano)) {
    header("Location:../Views/nenhum_resultado.php");
    exit;
}
$lq_ano .= '-' . $lq_dia_mes; //concatena as variáveis numa string apenas
unset($lq_dia_mes); //destrói a variável

//função para verificar quem pode ser promovido no respectivo processo promocional
/** @var mysqli|PDO $conn */
$lista = processa_lista_de_candidatos($conn, $lq_ano, $quadro);

/*--------------------------------------------------------------------------*/

//$parametro_data = (isset($_GET['data'])) ? $_GET['data'] : null;

list($ano, $mes, $dia) = explode("-", $lq_ano);
$lq_ano = $dia . '/' . $mes . '/' . $ano;

//obtem o ano e semestre para poder ser usado na próxima função
$resultado = obterAnoESemestre($lq_ano); //verifica o semestre de acordo com a data de promoção

//consulta a documentação entregue pelos militares
$documentacao_entregue = processa_documentos_de_candidatos($conn, $resultado['semestre'], $resultado['ano'], $lista);

//flag para auxiliar a exibição e checagem
$flag = false;
if ($documentacao_entregue != false) {
    /*--------------------------------------------------------------------------*/
    $teste = [];
    $teste = extrairDadosRecursivo($documentacao_entregue);

    // echo "<pre>";
    // print_r($teste);
    // echo "</pre>";
    /*--------------------------------------------------------------------------*/
    $flag = true;
}

?>

<div class="container">
    <div class="col-md-12">
        <ul class="nav nav-pills">
            <li role="presentation" class="nav-item">
                <a href="pesquisa_docs_entregues.php" class="nav-link">Voltar</a>
            </li>
        </ul>
        <hr>
        <h3>
            <strong>Documentação:</strong>
        </h3>

        <p>Relação dos militares que concorrem à promoção na data de <?= $lq_ano ?> quanto à entrega da Certidão Negativa de Antecedentes Criminais, Relatório de Tempo Arregimentado, Ficha de Avaliação de Desempenho, Ficha Profissional e Ata de Inspeção de Saúde.</p>
        <p><em>(Prazo Limite - Portaria de Normatização nº 022/GSB/CMTEGERAL/2021).</em></p>
        <hr>
        <?php
        if($flag == false){
            echo '<div class="alert alert-danger" role="alert"><p><strong>Erro no processamento:</strong></p>'
            .'<p>O Limite de Quantitativo para a data selecionada ainda não foi gerado.</p>'
            .'<p>Consequentemente não há pasta promocional, nem documentos cadastrados.</p>'
            .'</div>';
            exit;
        } 
        ?>

        <div class="panel-body">
            <table class="table table-striped table-bordered table-list">
                <thead>
                    <tr>
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
                            <p align="center">1º J.E.</p>
                        </th>
                        <th>
                            <p align="center">2º J.E.</p>
                        </th>
                        <th>
                            <p align="center">1º J.F.</p>
                        </th>
                        <th>
                            <p align="center">2º J.F.</p>
                        </th>
                        <th>
                            <p align="center">C.E.</p>
                        </th>
                        <th>
                            <p align="center">F.A.D.</p>
                        </th>
                        <th>
                            <p align="center">R.T.A.</p>
                        </th>
                        <th>
                            <p align="center">A.I.S.</p>
                        </th>
                    </tr>
                </thead>

                <tbody>
                    <?php
                    require_once '../Controllers/alias_posto_grad.php';

                    if (isset($teste)) {
                        foreach ($teste as $linha) {
                            list($id, $posto, $nome, $quadro, $d1, $d2, $d3, $d4, $d5, $d6, $d7, $d8) = $linha;
                            echo '<tr>'
                                . '<td align="center">' . alias_posto_grad($posto) . '</td>'
                                . '<td align="center">' . $nome . '</td>'
                                . '<td align="center">' . alias_quadro($quadro) . '</td>'
                                . '<td align="center">' . $d1 . '</td>'
                                . '<td align="center">' . $d2 . '</td>'
                                . '<td align="center">' . $d3 . '</td>'
                                . '<td align="center">' . $d4 . '</td>'
                                . '<td align="center">' . $d5 . '</td>'
                                . '<td align="center">' . $d6 . '</td>'
                                . '<td align="center">' . $d7 . '</td>'
                                . '<td align="center">' . $d8 . '</td>';
                        }
                    }
                    ?>
                </tbody>
            </table>
            <div class="col-md-12">
            </div>

        </div>
    </div>
</div>


<?php
include_once './footer.php';
?>