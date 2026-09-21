<?php
require_once '../Controllers/nivel_gestor.php';
include_once './header2.php';
include_once '../Controllers/funcoes_LQ.php';
require_once '../Controllers/alias_quadro.php';

$conn = Conexao::getConexao();

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

/*-------------------------------------------------------------------*/
//função para verificar quem pode ser promovido no respectivo processo promocional
$alteracoes_realizadas = processa_lista_de_candidatos_TC($conn, $lq_ano);

//função para criar em lote as pastas promocionais dos militares
//só cria a pasta se o checkbox estiver marcado e se houver militares a serem promovidos
if (isset($_POST['criar_pasta']) && $_POST['criar_pasta'] == 1) {
    if (!empty($alteracoes_realizadas)) {
        $pastas_criadas = criarPastaPromocionalEmLote($alteracoes_realizadas, $lq_ano, $conn);

        foreach ($pastas_criadas as $item) {
            // Log de criação de pasta promocional
            require_once __DIR__ . '/../Logger/LoggerFactory.php';
            $logger = LoggerFactory::createLogger();
            $logger->info('Usuário criou pasta promocional', [
                'id' => $_SESSION['id'],
                'usuario' => $_SESSION['nome'],
                'email' => $_SESSION['email'],
                'perfil' => $_SESSION['nivel_de_acesso'],
                'sujeito' => $item["id_militar"]
            ]);
        }

        if (!empty($pastas_criadas)) {
            $aux = criaDocumentosVazios($pastas_criadas, $conn);
        } else $aux = false;
    }
} else {
    $aux = false;
}
/*-------------------------------------------------------------------*/

$parametro_data = (isset($_GET['data'])) ? $_GET['data'] : null;

list($ano, $mes, $dia) = explode("-", $lq_ano);
$lq_ano = $dia . '/' . $mes . '/' . $ano;
?>

<div class="container">
    <div class="col-md-12">
        <ul class="nav nav-pills">
            <li role="presentation" class="nav-item">
                <a href="selecao_criterios_de_pesquisa_LQ_TC.php" class="nav-link">Voltar</a>
            </li>
        </ul>
        <hr>
        <h3>
            <strong>Tenentes Coronéis com insterstício</strong>
        </h3>
        <p>Data considerada:&nbsp
            <?= $lq_ano; ?></p>
        <p><em>São relacionados os candidatos que completarão o interstício mínimo previsto para cada posto ou graduação até a data considerada, bem como aqueles que já haviam completado.</em></p>
        <p>
            <font style="color:#FF0000">
                <?php
                if (!empty($pastas_criadas)) {
                    echo "Pastas promocionais criadas com sucesso!</br>";
                }
                if (isset($aux) && (!empty($aux))) {
                    echo "Documentos promocionais criados com sucesso!";
                }
                ?>
            </font>
        </p>
        <hr>

        <?php
        require_once '../Controllers/alias_ultima_promocao.php';
        if (!is_null($parametro_data)) {
            $parametro_data = alias_ultima_promocao($parametro_data);
            echo "<p><i>Promoção em {$parametro_data}.</i></p>";
        }
        ?>
        <div class="panel-body">
            <table class="table table-striped table-bordered table-list">
                <thead>
                    <tr>
                        <th>
                            <p align="center">Ordem</p>
                        </th>
                        <th>
                            <p align="center">Última promoção</p>
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
                            <p align="center">Opções</p>
                        </th>
                    </tr>
                </thead>

                <tbody>
                    <?php
                    require_once '../Controllers/alias_posto_grad.php';
                    // require_once '../Controllers/alias_ultima_promocao.php';

                    $ordem = 1;
                    if (isset($alteracoes_realizadas)) {
                        foreach ($alteracoes_realizadas as $item) {
                            $auxiliar = explode(",", $item);
                            //deixar no formato de data brasileiro
                            //$auxiliar[0] = alias_ultima_promocao($auxiliar[0]);
                            /*****************/
                            echo '<tr>'
                                . '<td align="center">' . $ordem . '</td>'
                                . '<td align="center">' . alias_ultima_promocao($auxiliar[0]) . '</td>'
                                . '<td align="center">' . alias_posto_grad($auxiliar[1]) . '</td>'
                                . '<td align="center">' . $auxiliar[2] . '</td>'
                                . '<td align="center">' . alias_quadro($auxiliar[3]) . '</td>'
                                //adicionado o link abaixo para levar à página de listagem de militares
                                . '<td align="center"><a href="listar_militares.php?pesquisar=' . $auxiliar[2] . '" target="_blank">Acessar</a></td>';
                            $ordem++;
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