<?php
require_once '../Controllers/nivel_gestor.php';
include_once '../Views/header2.php';
require_once '../ConexaoDB/conexao.php';
require_once '../Controllers/funcoes_LQ.php';
$conn = Conexao::getConexao();

if (isset($_GET['id_da_pasta'])) {

    //verificação de a variável $_GET é um inteiro
    $aux_id_da_pasta = filter_var($_GET['id_da_pasta'], FILTER_SANITIZE_NUMBER_INT);

    //se não for um INT, para a execução.
    if (!filter_var($aux_id_da_pasta, FILTER_VALIDATE_INT)) {
        echo "Operação não permitida.";
        exit();
    } else {

        //array com os nomes dos documentos necessários

        $documentos_nomes = array('cert_1JE', 'cert_2JE', 'cert_1JF', 'cert_2JF', 'cert_tse', 'fad', 'rta', 'ais', 'fp');

        //???????????????
        $documentos = [];

        $resultado = consultaDocumentosPelaPasta($aux_id_da_pasta, $conn);
        if ($resultado == false) {
            echo "A pasta não tem nenhum documento registrado.";
            exit();
        } else {
            // echo "<pre>";
            // print_r($resultado);
            // echo "</pre>";
        }

        // Criar um array apenas com os nomes dos documentos da consulta
        $documentos_encontrados = array_column($resultado, "doc_promo_nome");

        //aqui o objetivo é buscar os dados do militar (nome e posto) sabendo apenas o id da pasta promocional
        try {
            $stmt = $conn->prepare('SELECT nome, posto_grad_mil, pasta_promocional.ano_processo_promocional, pasta_promocional.semestre_processo_promocional FROM militar JOIN pasta_promocional ON militar_id = militar.id WHERE pasta_promocional.id = :id');
            $stmt->bindParam(':id', $aux_id_da_pasta, PDO::PARAM_INT);
            $stmt->execute();
            $consulta = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!empty($consulta)) {
                $nome = $consulta['nome'];
                $posto_grad = $consulta['posto_grad_mil'];
                $aux_ano_promocional = $consulta['ano_processo_promocional'];
                $aux_semestre_promocional = $consulta['semestre_processo_promocional'];
            }
        } catch (PDOException $ex) {
            return $ex->getMessage();
        }
    }
}

?>

<div class="container">
    <div class="col-md-12">
        <h4>Militar Selecionado</h4>
        <div class="form-text">
            <p><?= $posto_grad ?>&nbsp<?= $nome ?></p>
        </div>
    </div>

    <hr>

    <div class="col-md-12">
        <h4><strong>Documentos constantes na pasta promocional</strong></h4>
        <p><Strong>Referência:&nbsp</Strong>
            <?php
            echo $aux_semestre_promocional . 'º semestre de ';
            echo $aux_ano_promocional;
            ?>
        </p>
        <hr>
        <div class="col-md-12">
            <div class="panel panel-default panel-table">
                <div class="panel-body">
                    <div class="row justify-content-center">
                        <table class="table table-striped table-bordered table-list">
                            <thead>
                                <tr>
                                    <th>
                                        <p align="center">Ordem</p>
                                    </th>
                                    <th>
                                        <p align="center"></br>
                                            <label><strong>Documento</strong></label>
                                        </p>
                                    </th>
                                    <th>
                                        <p align="center">Registro</p>
                                    </th>
                                    <th>
                                        <p align="center">Link</p>
                                    </th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php

                                $ordem = 1;

                                foreach ($resultado as $item) {
                                    include_once '../Controllers/alias_nome_documento.php';

                                    echo '<tr><td align="center">' . $ordem . '</td>'
                                        . '<td align="center">' . alias_nome_documento($item["doc_promo_nome"]) . '</td>'
                                        . '<td align="center">' . traduzStatusDocumento($item["doc_status_id"]) . '</td>';
                                    if ($item["doc_promo_url"] == "") echo '<td align="center">N/C</td>';
                                    else echo '<td align="center"><a target="_blank" href="' . $item['doc_promo_url'] . '"><button class="btn btn-success" type="button"><i class="bi bi-eye-fill"></i></button></a>&nbsp</td>';
                                    $ordem++;
                                }
                                ?>
                            </tbody>
                        </table>

                    </div>


                </div>
            </div>
        </div>

        <div class="clearfix"></div>
        <br />
        <?php
        include_once '../Views/footer.php';
        ?>