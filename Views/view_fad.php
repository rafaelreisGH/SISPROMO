<?php
require_once '../Controllers/nivel_gestor.php';
include_once '../Views/header2.php';
require_once '../ConexaoDB/conexao.php';
$conn = Conexao::getConexao();

//^^^^APAGAR DAQUI PARA CIMA DEPOIS

if (isset($_POST['militar_id'])) {
    $id = (int)$_POST['militar_id'];
} else if (isset($_GET['militar_id'])) {
    $id = (int)$_GET['militar_id'];
} else {
    header('Location:../Views/acesso_restrito.php');
    exit();
}

// Busca os dados do militar com o nome do quadro via JOIN
$stmt = $conn->prepare("
    SELECT m.*, q.nome_quadro AS quadro 
    FROM militar m 
    LEFT JOIN quadro q ON m.quadro_id = q.id_quadro 
    WHERE m.id = :id
");
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$resultado = $stmt->fetch(PDO::FETCH_ASSOC);

if ($resultado && isset($resultado['nome'])) {
    $nome = $resultado['nome'];
    $posto_grad = $resultado['posto_grad_mil'];
    $quadro = $resultado['quadro'] ?? 'N/D';
    $media = $resultado['media'] ?? 0;
    $militar_id = $resultado['id'];
} else {
    header('Location:../Views/listar_militares.php');
    exit();
}
?>

<script type="text/javascript" src="../js/meus_scripts/atualiza_resumo.js"></script>
<script type="text/javascript" src="../js/meus_scripts/valida_formulario.js"></script>

<div class="container my-4">
    <div class="row mb-3">
        <div class="col-12">
            <ul class="nav nav-pills">
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="../Views/listar_militares.php"><i class="bi bi-arrow-left-circle-fill"></i> Voltar</a>
                </li>
            </ul>
            <hr>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <h3 class="fw-bold mb-2">Militar avaliado</h3>
            <div class="form-text fs-5 text-dark">
                <?= $posto_grad ?> <?= $nome ?>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Coluna da Esquerda (Formulário de Inserção) -->
        <div class="col-lg-5 col-md-12 mb-4">

            <form name="myForm" action="../Controllers/processa_fad.php" method="POST" onload="atualiza_resumo();" onchange="atualiza_resumo();" onsubmit="return validateForm()" class="mb-5 border p-4 bg-light rounded shadow-sm">
                <h3 class="fw-bold mb-4 fs-4 text-center">Inserir Ficha de Avaliação</h3>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="FormControlSelectAno" class="form-label">Ano</label>
                        <select class="form-select" id="FormControlSelectAno" name="ano">
                            <?php
                            $ano_atual = date("Y");
                            for ($i = $ano_atual; $i >= 2014; $i--) {
                                echo "<option value=" . $i . ">" . $i . "</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="FormControlSelectSemestre" class="form-label">Semestre</label>
                        <select class="form-select" id="FormControlSelectSemestre" name="semestre">
                            <option value="1">1º Semestre</option>
                            <option value="2">2º Semestre</option>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-12">
                        <label for="FormControlSelectPostoGrad" class="form-label">Posto/Graduação na época</label>
                        <select class="form-select" id="FormControlSelectPostoGrad" name="postoGradNoPerioAvaliado" required>
                            <option value="" selected>Selecione...</option>
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

                <div class="row mb-3">
                    <div class="col-md-12">
                        <label for="inputQuadro" class="form-label">Quadro</label>
                        <input class="form-control" type="text" placeholder="<?= $quadro ?>" id="inputQuadro" disabled>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-12">
                        <label for="inputPontuacao" class="form-label">Pontuação</label>
                        <input type="number" id="inputPontuacao" class="form-control" name="pontuacao" min="0" max="6" step="0.1" value="6">
                    </div>
                </div>

                <input type="hidden" name="id" value="<?= $id ?>">
                <input type="hidden" id="nota" name="notaCalculada" value="">

                <div class="row">
                    <div class="col-12 d-grid gap-2">
                        <button type="submit" class="btn btn-success"><i class="bi bi-save"></i> Salvar FAD</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Coluna da Direita (Resultados e Tabela) -->
        <div class="col-lg-7 col-md-12">

            <div class="table-responsive mb-4 shadow-sm">
                <table class="table table-bordered table-striped text-center align-middle mb-0">
                    <caption class="caption-top fw-bold text-dark mb-2">Média do militar avaliado</caption>
                    <tbody>
                        <tr>
                            <th scope="row" class="w-25 bg-light">Média</th>
                            <td class="fs-5 fw-bold text-primary">
                                <?php
                                // Cálculo da média: pega APENAS as FADs que correspondem ao posto/graduação ATUAL do militar
                                $stmtPesquisar = $conn->prepare("SELECT nota FROM fad WHERE militar_id = :id AND grau_hierarquico_na_epoca = :posto_grad");
                                $stmtPesquisar->bindParam(':id', $id, PDO::PARAM_INT);
                                $stmtPesquisar->bindParam(':posto_grad', $posto_grad, PDO::PARAM_STR);
                                $stmtPesquisar->execute();

                                $resultado = $stmtPesquisar->fetchAll(PDO::FETCH_COLUMN, 0);
                                $elementos = count($resultado);

                                if ($elementos >= 3) {
                                    sort($resultado);
                                    array_shift($resultado); // Retira a menor
                                    array_pop($resultado);   // Retira a maior
                                    $qtde = sizeof($resultado);
                                    $media = array_sum($resultado) / $qtde;

                                    // Atualiza a média no BD (Corrigido o erro de mixed named/positional parameters)
                                    $stmt3 = $conn->prepare("UPDATE militar SET media = :media WHERE id = :id");
                                    $stmt3->bindParam(':media', $media);
                                    $stmt3->bindParam(':id', $id, PDO::PARAM_INT);
                                    $stmt3->execute();
                                } else {
                                    // Se houver menos de 3 notas, zera a média
                                    $stmt4 = $conn->prepare("UPDATE militar SET media = 0 WHERE id = :id");
                                    $stmt4->bindParam(':id', $id, PDO::PARAM_INT);
                                    $stmt4->execute();
                                }

                                // Exibe a média calculada
                                $consulta = $conn->prepare("SELECT media FROM militar WHERE id = :id");
                                $consulta->bindParam(':id', $id, PDO::PARAM_INT);
                                $consulta->execute();
                                $resultadoM = $consulta->fetch(PDO::FETCH_ASSOC);

                                if (isset($resultadoM['media']) && $resultadoM['media'] != 0) {
                                    echo number_format($resultadoM['media'], 2);
                                } else {
                                    echo "<span class='fs-6 fw-normal text-muted'>Ainda não há notas suficientes para o posto atual.</span>";
                                }
                                ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Alertas de Feedback -->
            <?php
            if (isset($_GET['sucesso']) && $_GET['sucesso'] == 'upload') {
                echo '<div class="alert alert-success alert-dismissible fade show" role="alert">Link da FAD registrado com sucesso!<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
            }
            if (isset($_GET['erro'])) {
                $erroMsg = "";
                switch ($_GET['erro']) {
                    case 'invalido':
                        $erroMsg = "Dados inválidos enviados. Verifique e tente novamente.";
                        break;
                    case 'bd':
                        $erroMsg = "Erro ao salvar no banco de dados.";
                        break;
                    case 'excecao':
                        $erroMsg = "Erro inesperado. Tente novamente mais tarde.";
                        break;
                }
                if ($erroMsg) echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">' . $erroMsg . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
            }
            ?>

            <?php if (isset($_GET['msg'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php
                    switch ($_GET['msg']) {
                        case 'fad_inserida':
                            echo 'FAD inserida com sucesso!';
                            break;
                        case 'fad_excluida':
                            echo 'FAD excluída com sucesso!';
                            break;
                        case 'erro_ao_excluir':
                            echo 'Erro ao tentar excluir a FAD.';
                            break;
                        case 'erro_dados_invalidos':
                            echo 'Dados inválidos fornecidos.';
                            break;
                        case 'erro_bd':
                            echo 'Erro no banco de dados.';
                            break;
                        case 'fad_existente':
                            echo 'Já havia registro de FAD no período informado. Portanto nada foi alterado.';
                            break;
                        default:
                            echo 'Ação concluída.';
                    }
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="table-responsive shadow-sm rounded">
                <table class="table table-striped table-hover text-center align-middle mb-0">
                    <caption class="caption-top fw-bold text-dark px-2 mb-2">Registros de Ficha de Avaliação</caption>
                    <thead class="table-dark">
                        <tr>
                            <th>Semestre</th>
                            <th>Ano</th>
                            <th>Posto/Grad</th>
                            <th>Nota</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        try {
                            // Busca TODAS as FADs (independente do posto atual)
                            $stmt = $conn->prepare("SELECT id, ano, semestre, nota, caminho_do_arquivo, grau_hierarquico_na_epoca FROM fad WHERE militar_id = :id ORDER BY ano DESC, semestre DESC");
                            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                            $stmt->execute();

                            while ($resultado = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                $id_da_fad = $resultado['id'];
                                $aux_semestre = $resultado['semestre'];
                                $aux_ano = $resultado['ano'];
                                $aux_nota = $resultado['nota'];
                                $aux_url = $resultado['caminho_do_arquivo'];
                                $aux_grau = $resultado['grau_hierarquico_na_epoca'];

                                echo '<tr>'
                                    . '<td>' . $aux_semestre . 'º SEM</td>'
                                    . '<td>' . $aux_ano . '</td>'
                                    . '<td>' . $aux_grau . '</td>'
                                    . '<td>' . $aux_nota . '</td>'
                                    . '<td>
                                        <div class="d-flex justify-content-center gap-1">';

                                // Botão de Visualizar (ou badge de N/C)
                                if (empty($aux_url)) {
                                    echo '<span class="badge bg-secondary d-flex align-items-center" title="Sem documento">N/C</span>';
                                } else {
                                    echo '<a target="_blank" href="' . htmlspecialchars($aux_url) . '" class="btn btn-success btn-sm" title="Visualizar FAD">
                                            <i class="bi bi-eye-fill"></i>
                                          </a>';
                                }

                                // Botão de Upload (Chama o Modal)
                                echo '<button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalUpload" data-fadid="' . $id_da_fad . '" title="Inserir/Atualizar URL da FAD">
                                        <i class="bi bi-cloud-upload-fill"></i>
                                      </button>';

                                // Botão de Excluir
                                echo '<form action="../Controllers/exclui_fad.php" method="POST" class="m-0">
                                            <input type="hidden" name="militar_id" value="' . $id . '">
                                            <button class="btn btn-danger btn-sm" type="submit" name="id_da_fad" value="' . $id_da_fad . '" title="Excluir FAD">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        </form>';

                                echo '  </div>
                                      </td>
                                    </tr>';
                            }
                        } catch (PDOException $ex) {
                            echo '<tr><td colspan="5" class="text-danger">' . $ex->getMessage() . '</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Upload de FAD -->
<div class="modal fade" id="modalUpload" tabindex="-1" aria-labelledby="modalUploadLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalUploadLabel"><i class="bi bi-link-45deg"></i> Inserir/Atualizar URL da FAD</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="../Controllers/upload_fad_url.php" method="post">
                <div class="modal-body">
                    <!-- O value deste input é preenchido dinamicamente pelo JavaScript -->
                    <input type="hidden" id="modal_documento_id" name="documento_id" value="" required>
                    <input type="hidden" name="militar_id" value="<?= $id ?>">

                    <p class="text-muted small">A URL inserida substituirá qualquer link cadastrado anteriormente para esta FAD.</p>

                    <div class="mb-3">
                        <label for="documentoUrl" class="form-label">Endereço (URL) do Documento</label>
                        <input type="url" id="documentoUrl" name="documento_url" class="form-control" placeholder="https://..." required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-cloud-upload-fill"></i> Salvar URL</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Script do Bootstrap 5 (Garante o funcionamento do modal e os tooltips) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Lógica para injetar o ID da FAD dentro do modal quando ele for aberto
        const modalUpload = document.getElementById('modalUpload');

        if (modalUpload) {
            modalUpload.addEventListener('show.bs.modal', function(event) {
                // 'button' é o elemento exato que o usuário clicou (o botão azul na tabela)
                const button = event.relatedTarget;

                // Extrai a informação do id da FAD contida no atributo "data-fadid"
                const fadId = button.getAttribute('data-fadid');

                // Joga esse ID no input hidden que vai para o PHP processar
                const inputDocumentoId = modalUpload.querySelector('#modal_documento_id');
                if (inputDocumentoId) {
                    inputDocumentoId.value = fadId;
                }
            });

            // Limpa o campo URL quando o modal for fechado
            modalUpload.addEventListener('hidden.bs.modal', function() {
                const inputUrl = modalUpload.querySelector('#documentoUrl');
                if (inputUrl) {
                    inputUrl.value = '';
                }
            });
        }
    });
</script>

<?php
include_once '../Views/footer2.php';
?>