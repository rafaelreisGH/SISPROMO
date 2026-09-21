<?php
require_once '../Controllers/nivel_gestor.php';
include_once '../Views/header2.php';
require_once '../ConexaoDB/conexao.php';
include_once '../Controllers/funcoes_tempo_arregimentado.php';
include_once '../Controllers/funcoes_intersticio.php';
require_once '../Controllers/alias_quadro.php';

$conn = Conexao::getConexao();
/*
erro[0] é referente a $erroNome
erro[1] é referente a $erroPostoGrad
erro[2] é referente a $erroQuadro
*/
//VERIFICAR ALTERAÇÕES
$nome_alterado = (isset($_GET['nome'])) ? $_GET['nome'] : null;
$posto_grad_alterado = (isset($_GET['posto_grad'])) ? $_GET['posto_grad'] : null;
$quadro_alterado = (isset($_GET['quadro'])) ? $_GET['quadro'] : null;

//
//VERIFICAÇÃO SE VIERAM ERROS DO ATUALIZA_DADOS_MILITAR.PHP
if (isset($_GET['erro'])) {
    foreach ($_GET['erro'] as $value) {
        switch ($value) {
            case 1:
                $erroNome = 1;
                break;
            case 2:
                $erroPostoGrad = 1;
                break;
            case 3:
                $erroQuadro = 1;
                break;
        }
    }
}

if (isset($_POST['militar_id'])) {
    $id = $_POST['militar_id'];

    $aux_posto_grad = pega_posto_grad($id, $conn);
    $inter = pega_intersticio($aux_posto_grad, $conn);
    $tempo_arregimentado_minimo = pega_tempo_minimo_arregimentado($aux_posto_grad, $conn);
    calcularTempoArregimentado($id, $inter, $tempo_arregimentado_minimo, $conn);

    //pegar no BD dados do militar selecionado
    $stmt = $conn->query("SELECT militar.nome, militar.posto_grad_mil, militar.id, militar.media, militar.quadro_id, militar.antiguidade, militar.tempo_arregimentado FROM militar WHERE militar.id = '" . $id . "'");
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    if (isset($resultado['nome'])) {
        $militar_id = $resultado['id'];
        $nome = $resultado['nome'];
        $posto_grad = $resultado['posto_grad_mil'];
        $quadro = $resultado['quadro_id'];
        $media = $resultado['media'];
        $antiguidade = $resultado['antiguidade'];
        $arregimentado = $resultado['tempo_arregimentado'];
    }
    // Prepara a consulta
    $stmt = $conn->prepare("SELECT id, a_contar_de, modalidade FROM registro_de_promocoes WHERE militar_id = :id ORDER BY a_contar_de DESC LIMIT 1");
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($resultado) {
        $ultima_promocao = $resultado['a_contar_de'];
        $modalidade = $resultado['modalidade'];
    }
} else if (isset($_GET['militar_id'])) {
    $id = $_GET['militar_id'];

    $aux_posto_grad = pega_posto_grad($id, $conn);
    $inter = pega_intersticio($aux_posto_grad, $conn);
    $tempo_arregimentado_minimo = pega_tempo_minimo_arregimentado($aux_posto_grad, $conn);
    calcularTempoArregimentado($id, $inter, $tempo_arregimentado_minimo, $conn);

    //pegar no BD dados do militar selecionado
    $stmt = $conn->query("SELECT militar.nome, militar.posto_grad_mil, militar.id, militar.media, militar.quadro_id, militar.antiguidade, militar.tempo_arregimentado FROM militar WHERE militar.id = '" . $id . "'");
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    if (isset($resultado['nome'])) {
        $militar_id = $resultado['id'];
        $nome = $resultado['nome'];
        $posto_grad = $resultado['posto_grad_mil'];
        $quadro = $resultado['quadro_id'];
        $media = $resultado['media'];
        $antiguidade = $resultado['antiguidade'];
        $arregimentado = $resultado['tempo_arregimentado'];
    }
    // Prepara a consulta
    $stmt = $conn->prepare("SELECT id, a_contar_de, modalidade FROM registro_de_promocoes WHERE militar_id = :id ORDER BY a_contar_de DESC LIMIT 1");
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($resultado) {
        $ultima_promocao = $resultado['a_contar_de'];
        $modalidade = $resultado['modalidade'];
    }
}

if (isset($posto_grad)) {
    require_once '../Controllers/alias_posto_grad.php';
    $alias_posto_grad = alias_posto_grad($posto_grad);
}

if (isset($ultima_promocao)) {
    require_once '../Controllers/alias_ultima_promocao.php';
    $alias_ultima_promocao = alias_ultima_promocao($ultima_promocao);
}
// if (isset($arregimentado)) {
//     require_once '../Controllers/atualizar_tempo_arregimentado.php';
//     calcularTempoArregimentado($militar_id, $inter, $tempo_arregimentado_minimo, $conn);
// }

$_SESSION['militar_id'] = $id ?? null;
?>

<!-- BOTÃO VOLTA PARA A PÁGINA DE PESQUISA JOGANDO NA URL O NOME DO MILITAR EM QUESTÃO-->
<div class="container">
    <div class="col-md-12">
        <ul class="nav nav-pills">
            <li class="nav-item"><a class="nav-link active" aria-current="page" href="../Views/listar_militares.php?pesquisar=<?=$nome?>">Voltar</a></li>
        </ul>
        <hr>
    </div>
    <!-- BOTÃO VOLTA PARA A PÁGINA DE PESQUISA JOGANDO NA URL O NOME DO MILITAR EM QUESTÃO-->

    <div class="row justify-content-center">
        <div class="col-md-3">
            <label class="form-label">Ação Opcional</label>
            <ul class="nav nav-pills">
                <li class="nav-item"><a class="nav-link active" aria-current="page" href="formulario_tempo_nao_arregimentado.php">Tempo não arregimentado</a></li>
            </ul>
        </div>

        <div class="col-md-6">

            <form action="../Controllers/atualiza_dados_militar.php" method="POST">
                <label class="form-label">Dados cadastrais</label>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="nomeMilitar">Nome</span>
                    <input type="text" class="form-control" placeholder="Nome completo" aria-label="Username" aria-describedby="nomeMilitar" name="nome_atualizado" value="<?php echo ($nome); ?>">
                </div>
                <!-- FAZER VALIDAÇÃO DO CAMPO NOME SÓ ACEITANDO TEXTO E NÃO VAZIO -->


                <div class="col">
                    <div class="input-group mb-3">
                        <span class="input-group-text" id="postoGraduacao">Posto/Graduação</span>
                        <select class="form-select" aria-label="Default select example" name="posto_grad_atualizado">
                            <option value="<?php echo ($posto_grad); ?>"><?php echo $alias_posto_grad ?? 'N/D'; ?></option disabled selected>
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
                        </select>
                    </div>
                </div>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="quadroMilitar">Quadro</span>
                    <select class="form-select" name="quadro_atualizado">
                        <option value="<?php echo ($quadro); ?>"><?php echo (alias_quadro($quadro)); ?></option disabled selected>
                        <option value="1">QOBM</option>
                        <option value="2">QCOBM</option>
                        <option value="3">QOSBM</option>
                        <option value="4">QPBM</option>
                    </select>
                </div>

                <label for="basic-addon3" class="form-label">Dados referente à data de promoção - último registro</label>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon3">Data</span>
                    <input type="text" class="form-control" aria-describedby="basic-addon3" value="<?php
                                                                                                    $alias_ultima_promocao = (isset($alias_ultima_promocao)) ? $alias_ultima_promocao : "N/D";
                                                                                                    echo $alias_ultima_promocao; ?>" disabled>
                    <span class="input-group-text" id="basic-addon3">Modalidade</span>
                    <input type="text" class="form-control" aria-describedby="basic-addon3" value="<?php
                                                                                                    $modalidade = (isset($modalidade)) ? $modalidade : "N/D";
                                                                                                    echo ($modalidade); ?>" disabled>
                </div>

                <label for="mediaMilitar" class="form-label">Média das avaliações de desempenho no posto/graduação atual</label>

                <div class="input-group mb-3">
                    <span class="input-group-text" id="mediaMilitar">Média</span>
                    <input type="text" class="form-control" aria-describedby="mediaMilitar" value="<?php $media = (isset($media)) ? $media : 'Não disponível.';
                                                                                                    echo ($media); ?>" disabled>
                    <span class="input-group-text" id="TempoArregimentado">Tempo Arregimentado</span>
                    <input type="text" class="form-control" aria-describedby="TempoArregimentado" value="<?php $arregimentado = (isset($arregimentado)) ? $arregimentado : 'Não disponível';
                                                                                                            switch ($arregimentado) {
                                                                                                                case 0:
                                                                                                                    $arregimentado = "Incompleto";
                                                                                                                    break;
                                                                                                                case 1:
                                                                                                                    $arregimentado = "Completo";
                                                                                                                    break;
                                                                                                            }
                                                                                                            echo ($arregimentado); ?>" disabled>
                </div>

                <div class="input-group mb-3">
                    <input type="hidden" name="id" value="<?= $militar_id ?>">
                </div>
                <?php
                if (isset($erroNome) && $erroNome == 1) {
                    echo '<font style="color:#ff0000">* O <strong>nome</strong> deve ser preenchido corretamente. Não são aceitos valores vazios ou numéricos. </font></br>';
                }
                if (isset($nome_alterado) && !is_null($nome_alterado)) {

                    // Log de edição de dados de militar
                    require_once __DIR__ . '/../Logger/LoggerFactory.php';
                    $logger = LoggerFactory::createLogger();
                    $logger->info('Usuário alterou nome de militar', [
                        'id' => $_SESSION['id'],
                        'usuario' => $_SESSION['nome'],
                        'email' => $_SESSION['email'],
                        'perfil' => $_SESSION['nivel_de_acesso'],
                        'sujeito' => $militar_id
                    ]);

                    echo '<font style="color:#FF0000">* <strong>Nome</strong> alterado para: <i>' . $nome_alterado . '</i>. </font></br>';
                }
                if (isset($posto_grad_alterado) && !is_null($posto_grad_alterado)) {

                    // Log de edição de dados de militar
                    require_once __DIR__ . '/../Logger/LoggerFactory.php';
                    $logger = LoggerFactory::createLogger();
                    $logger->info('Usuário alterou posto/grad de militar', [
                        'id' => $_SESSION['id'],
                        'usuario' => $_SESSION['nome'],
                        'email' => $_SESSION['email'],
                        'perfil' => $_SESSION['nivel_de_acesso'],
                        'sujeito' => $militar_id
                    ]);

                    echo '<font style="color:#FF0000">* Posto/graduação alterado para: <strong><i>' . alias_posto_grad($posto_grad_alterado) . ' BM</i></strong>. </font></br>';
                }
                if (isset($quadro_alterado) && !is_null($quadro_alterado)) {

                    // Log de edição de dados de militar
                    require_once __DIR__ . '/../Logger/LoggerFactory.php';
                    $logger = LoggerFactory::createLogger();
                    $logger->info('Usuário alterou quadro do militar', [
                        'id' => $_SESSION['id'],
                        'usuario' => $_SESSION['nome'],
                        'email' => $_SESSION['email'],
                        'perfil' => $_SESSION['nivel_de_acesso'],
                        'sujeito' => $militar_id
                    ]);

                    echo '<font style="color:#FF0000">* Quadro alterado para: <strong><i>' . ucfirst(strtolower(alias_quadro($quadro_alterado))) . '</i></strong>. </font></br>';
                }
                ?>
                <hr>
                <button class="btn btn-outline-success active" type="submit">Atualizar</button>
            </form>
        </div>
        <div class="col-md-3">

            <form name="formInativar" action="../Controllers/inativa_militar.php" method="POST" onsubmit="confirmarInativacao()">
                <label class="form-label">Ação Opcional</label>
                <ul class="nav nav-pills">
                    <input type="hidden" name="antiguidade" value="<?= $antiguidade  ?>">
                    <button class="btn btn-outline-danger " type="submit">Inativar militar</button>
                    <small id="InativacaoHelp" class="form-text text-muted">Esta opção faz com que o militar não seja mais listado, e libera o número de antiguidade, porém não o exclui preservando seus registros no banco de dados.</small>
                </ul>
            </form>
        </div>
    </div>

    <script>
        function confirmarInativacao() {
            confirm("Confirma a inativação do militar?")
        }
    </script>
    <?php
    include_once './footer2.php';
    ?>