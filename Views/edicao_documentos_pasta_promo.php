<?php
// /views/edicao_documentos_pasta_promo.php

require_once '../Controllers/nivel_gestor.php';
include_once './header2.php';
require_once '../ConexaoDB/conexao.php';
$conn = Conexao::getConexao();

// --------------------------- //
if (isset($_POST['id_da_pasta'])) {
    $id_da_pasta = $_POST['id_da_pasta'];
} else if (isset($_GET['id_da_pasta'])) {
    $id_da_pasta = $_GET['id_da_pasta'];
} else {
    //se não foi preenchido o form com o id_da_pasta, redireciona.
    header('Location:../Views/acesso_restrito.php');
}
// --------------------------- //

// --------------------------- //
//pegar no BD dados do militar selecionado
$stmt = $conn->prepare("SELECT * FROM pasta_promocional WHERE id = :id");
$stmt->bindParam(':id', $id_da_pasta, PDO::PARAM_INT);
$stmt->execute();
$resultado = $stmt->fetch(PDO::FETCH_ASSOC);
if (isset($resultado['militar_id'])) {
    $militar_id = $resultado['militar_id'];
    $semestre_pasta = $resultado['semestre_processo_promocional'];
    $ano_pasta = $resultado['ano_processo_promocional'];

    $stmt = $conn->prepare("SELECT * FROM militar WHERE id = :id");
    $stmt->bindParam(':id', $militar_id, PDO::PARAM_INT);
    $stmt->execute();
    $res = $stmt->fetch(PDO::FETCH_ASSOC);
    if (isset($res['nome'])) {
        $nome = $res['nome'];
        $posto_grad = $res['posto_grad_mil'];
    }
}
// --------------------------- //

?>

<div class="container">
    <div class="col-md-12">
        <div>
            <ul class="nav nav-pills">
                <li role="presentation" class="nav-item">
                    <a href="pasta_promocional_home.php?militar_id=<?= $militar_id ?>" class="nav-link">Voltar</a>
                </li>
            </ul>
            <hr>
        </div>

        <div>
            <h4>Militar Selecionado</h4>
            <div class="form-text">
                <p><?= $posto_grad ?>&nbsp<?= $nome ?></p>
            </div>
        </div>
        <hr>
        <h3><strong>Edição de documentos da pasta</strong></h3>
        <h6>Referência:&nbsp<?= $semestre_pasta ?>º semestre de <?= $ano_pasta ?></h6>
        <hr>
    </div>

    <div class="row">
        <div id="documentos" class="col-md-5">

            <div class="col">

                <div class="row">

                    <div class="col-md-12">
                        <form action="../Controllers/atualiza_tb_documentos.php" method="post">
                            <label class="form-label">Dados do documento</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text"><i class="bi bi-file-earmark-text"></i></span>
                                <select id="select_doc" class="form-select" name="nome_do_documento" required>
                                    <option selected disabled>Selecione o documento</option>
                                    <option value="cert_1JE">1º J.E. - Cert. Neg. Crim. - 1º grau - TJ/MT</option>
                                    <option value="cert_2JE">2º J.E. - Cert. Neg. Crim. - 2º grau - TJ/MT</option>
                                    <option value="cert_1JF">1º J.F. - Cert. Neg. Crim. - TRF-1 - Sç. Jud. MT</option>
                                    <option value="cert_2JF">2º J.F. - Cert. Neg. Crim. - TRF-1</option>
                                    <option value="cert_tse">C.E. - Cert. Neg. Crim. - Justiça Eleitoral</option>
                                    <option value="fad">F.A.D. - Ficha de Avaliação de Desempenho</option>
                                    <option value="rta">R.T.A. - Relatório de Tempo Arregimentado</option>
                                    <option value="ais">A.I.S. - Ata de Inspeção de Saúde</option>
                                    <?php
                                    if ($posto_grad == "TC BM") {
                                        echo '<option value="fp">F.P. - Ficha Profissional</option>';
                                    }
                                    ?>
                                </select>
                                <input type="hidden" name="id_da_pasta" value="<?= $id_da_pasta ?>">
                                <input type="hidden" name="militar_id" value="<?= $militar_id ?>">
                            </div>

                            <div class="input-group mb-3">
                                <span class="input-group-text"><i class="bi bi-link"></i></span>
                                <input id="url_doc" class="form-control" type="url" name="caminho_do_arquivo" placeholder="Informe o link do arquivo" required>
                            </div>

                            <div class="input-group mb-3">
                                <span class="input-group-text"><i class="bi bi-file-earmark-check"></i></span>
                                <select id="status_documento" class="form-select" name="status_documento" required>
                                    <option selected disabled>Selecione a situação do documento</option>
                                    <option value="1">O.K. - Documento entregue</option>
                                    <option value="2">N.E. - Não entregue dentro do prazo</option>
                                    <option value="3">E.R. - Entregue no prazo, necessidade de retificação</option>
                                    <option value="4">C.P. - Certidão Positivada</option>
                                </select>
                            </div>

                            <div id="container_fad_selecao" class="mt-2" style="display:none;">
                                <label class="form-label text-primary">FADs encontradas no sistema:</label>
                                <select id="select_fad_existente" class="form-select border-primary">
                                    <option value="">-- Selecione uma FAD cadastrada --</option>
                                </select>
                            </div>

                            <hr>
                            <button class="btn btn-success" type="submit">Salvar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div id="resumo" class="col-md-6">
            <table class="table table-striped">
                <caption>Documentos salvos na pasta</caption>
                <thead>
                    <tr>
                        <th>Documento</th>
                        <th>Validação/Status</th>
                        <th>Excluir</th>
                        <th>Visualizar</th>
                    </tr>
                </thead>

                <tbody>
                    <?php
                    try {
                        //PROCURA REGISTRO DE DOCUMENTOS CONFORME A PASTA PROMOCIONAL DO MILITAR
                        $stmt = $conn->prepare("SELECT id_doc_promo, doc_promo_nome, doc_promo_url, doc_status_id FROM documento_promocao WHERE pasta_promocional_id = :id");
                        $stmt->bindParam(':id', $id_da_pasta, PDO::PARAM_INT);
                        $stmt->execute();

                        include_once '../Controllers/alias_nome_documento.php';
                        // Percorrer os resultados
                        while ($resultado = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            $aux_id_documento = $resultado['id_doc_promo'];
                            $aux_nome = alias_nome_documento($resultado['doc_promo_nome']);
                            $aux_url = $resultado['doc_promo_url'];
                            $aux_status = $resultado['doc_status_id'];
                            switch ($aux_status) {
                                case 1:
                                    $aux_status = "O.K.";
                                    break;
                                case 2:
                                    $aux_status = "N.E.";
                                    break;
                                case 3:
                                    $aux_status = "E.R.";
                                    break;
                                case 4:
                                    $aux_status = '<button class="btn btn-danger"><i class="bi bi-exclamation-triangle-fill"></i>&nbspPositivada</button>';
                                    break;
                            }
                            echo '<tr>'
                                . '<td>' . $aux_nome . '</td>'
                                . '<td style="text-align: center;">' . $aux_status . '</td>';
                            if ($aux_url == null) echo '<td align="center">N/C</td>';
                            else echo '<td><form action="../Controllers/atualiza_tb_documentos.php" method="POST" onsubmit="return confirmarExclusao();"><button class="btn btn-danger" type="submit" name="excluir_documento" value="' . $aux_id_documento . '"><input type="hidden" name="id_da_pasta" value="' . $id_da_pasta . '"><i class="bi bi-trash3-fill"></i></button></form></td>';
                            if ($aux_url == null) echo '<td align="center">N/C</td>';
                            else echo '<td align="center"><a target="_blank" href="' . $aux_url . '"><button class="btn btn-success" type="button"><i class="bi bi-eye-fill"></i></button></a>&nbsp</td>';
                        }
                    } catch (PDOException $ex) {
                        return $ex->getMessage();
                    }
                    ?>
                </tbody>
            </table>
        </div>

    </div>



</div>

<script>
    function confirmarExclusao() {
        return confirm("Tem certeza que deseja excluir este documento? Esta ação não pode ser desfeita!");
    }

    document.getElementById('select_doc').addEventListener('change', function() {
        const valor = this.value;
        const militarId = "<?= $militar_id ?>";
        const containerFad = document.getElementById('container_fad_selecao');
        const selectFad = document.getElementById('select_fad_existente');
        const inputUrl = document.getElementById('url_doc');

        if (valor === 'fad') {
            // Busca via AJAX as FADs do militar
            fetch(`../Controllers/buscar_fads_json.php?militar_id=${militarId}`)
                .then(response => response.json())
                .then(data => {
                    selectFad.innerHTML = '<option value="">-- Selecione uma FAD cadastrada --</option>';
                    if (data.length > 0) {
                        data.forEach(fad => {
                            let option = document.createElement('option');
                            option.value = fad.caminho_do_arquivo;
                            option.textContent = `${fad.semestre}º Sem / ${fad.ano} - Nota: ${fad.nota}`;
                            selectFad.appendChild(option);
                        });
                        containerFad.style.display = 'block';
                    } else {
                        alert('Nenhuma FAD com arquivo digital foi encontrada para este militar no histórico.');
                    }
                });
        } else {
            containerFad.style.display = 'none';
        }
    });

    // Quando o usuário selecionar uma FAD da lista, preenche o campo de URL automaticamente
    document.getElementById('select_fad_existente').addEventListener('change', function() {
        if (this.value !== "") {
            document.getElementById('url_doc').value = this.value;
        }
    });

    // Dentro do seu script existente:
    document.getElementById('select_fad_existente').addEventListener('change', function() {
        if (this.value !== "") {
            document.getElementById('url_doc').value = this.value;

            // Preenchimento automático do status para "O.K."
            const selectStatus = document.getElementById('status_documento');
            if (selectStatus) {
                selectStatus.value = "1"; // Ajuste o "1" para o ID correspondente ao status O.K.
            }
        }
    });

    // Adicione validação nativa nos campos para evitar o envio vazio
    document.getElementById('select_doc').required = true;
    document.getElementById('url_doc').required = true;
    document.getElementById('status_documento').required = true;
</script>
<?php
include_once './footer.php';
?>