<?php
require_once '../Controllers/nivel_gestor.php';
include_once './header2.php';
require_once '../ConexaoDB/conexao.php';
include_once '../Controllers/inserir_registro_de_promocao.php';
$conn = Conexao::getConexao();
require_once '../Controllers/alias_quadro.php';

$nada_alterado = (isset($_GET['nada_alterado'])) ? $_GET['nada_alterado'] : 0;

$where = array();
$params = array();

// Mapeamento dos parâmetros com Prepared Statements para evitar SQL Injection
if (!empty($_POST['criterio_posto_grad'])) {
    $where[] = "posto_grad_mil = :posto_grad";
    $params[':posto_grad'] = $_POST['criterio_posto_grad'];
}

if (!empty($_POST['criterio_quadro'])) {
    $where[] = "quadro_id = :quadro_id";
    $params[':quadro_id'] = $_POST['criterio_quadro'];
}

// Inclusão da ordenação por antiguidade (1º mais antigo, 2º, 3º...)
$sql = "SELECT nome, posto_grad_mil, id, quadro_id, antiguidade FROM militar";

if (count($where) > 0) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

// Ordenação crescente por antiguidade
$sql .= " ORDER BY antiguidade ASC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);

$total_militares = $stmt->rowCount();
?>

<!-- Importação de CSS do DataTables (Caso não esteja no seu header2.php) -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<div class="container mb-4">
    <div class="col-md-12">
        <ul class="nav nav-pills">
            <li role="presentation" class="nav-item">
                <a href="selecao_criterios_de_pesquisa.php" class="nav-link">Voltar</a>
            </li>
        </ul>
        <hr>
    </div>
    <h3><strong>Resultados de pesquisa</strong></h3>
    <?php
    if (isset($total_militares)) {
        echo '<span class="badge bg-success">' . $total_militares . ' militares encontrados</span>';
    }
    ?>
    <hr>

    <form action="<?= $_SERVER['PHP_SELF'] ?>" method="POST">
        <!-- Parte do Formulário de Promoção -->
        <div class="col-md-12">
            <div class="row">
                <div class="form-group col-md-4">
                    <div class="input-group mb-3">
                        <label class="form-label"><strong>Inserir registro de promoção</strong></label>
                        <div class="input-group mb-3">
                            <span class="input-group-text">Data da promoção</span>
                            <input type="date" class="form-control" name="data_promocao" required>
                        </div>
                        <div class="input-group mb-3">
                            <span class="input-group-text">Modalidade</span>
                            <select class="form-select" name="modalidade" required>
                                <option selected disabled>Selecione</option>
                                <option value="POR ANTIGUIDADE">POR ANTIGUIDADE</option>
                                <option value="POR MERECIMENTO">POR MERECIMENTO</option>
                                <option value="POR RESSARCIMENTO DE PRETERIÇÃO">POR RESSARCIMENTO DE PRETERIÇÃO</option>
                            </select>
                        </div>
                        <div class="input-group mb-3">
                            <span class="input-group-text">Posto/Graduação</span>
                            <select class="form-select" name="promocao_posto_grad" required>
                                <option selected disabled>Selecione</option>
                                <option value="CEL BM">Coronel</option>
                                <option value="TC BM">Tenente Coronel</option>
                                <option value="MAJ BM">Major</option>
                                <option value="CAP BM">Capitão</option>
                            </select>
                        </div>
                        <button class="btn btn-danger" type="submit" name="atualizar">Atualizar data</button>
                    </div>
                </div>

                <div class="col-md-1"></div>
                <div class="col-md-6">
                    <button class="btn btn-primary" type="button" onmouseout="escondeDica()" onmouseover="mostraDica()">
                        <i class="bi bi-question-square-fill"></i>&nbsp Observação
                    </button>
                    <div class="mt-3">
                        <p id="paragrafoDica" class="text-muted"></p>
                    </div>
                </div>
            </div>
        </div>

        <div id="resultado_autalizacao" class="col-md-12 mt-3">
            <?php
            if ($nada_alterado == 1) {
                echo '<div class="alert alert-danger"><i>*Nenhum registro foi inserido. Selecione ao menos um militar.</i></div>';
            }
            if (!empty($alteracoes_realizadas)) {
                // Loop de feedback já existente no seu código...
            }
            ?>
        </div>

        <div class="col-md-12 mt-4">
            <div class="panel panel-default panel-table">
                <div class="panel-body">
                    <!-- Ajuste 1: Garantir a classe 'table-list' e id para o DataTable -->
                    <!-- Tabela HTML -->
                    <table class="table table-striped table-bordered table-list" id="minhaTabela">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 50px;">
                                    <input class="form-check-input" type="checkbox" id="btn-check">
                                    <label for="btn-check" class="d-block small">Todos</label>
                                </th>
                                <th class="text-center">Antiguidade</th> <!-- Nova coluna para o DataTables ordenar -->
                                <th>Posto/Graduação</th>
                                <th>Nome</th>
                                <th>Quadro</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            while ($resultados = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo '<tr>'
                                    . '<td align="center"><input class="form-check-input" type="checkbox" value="' . $resultados['id'] . '" name="militar_id[]"></td>'
                                    . '<td align="center">' . $resultados['antiguidade'] . '</td>'
                                    . '<td align="center">' . $resultados['posto_grad_mil'] . '</td>'
                                    . '<td align="center">' . $resultados['nome'] . '</td>'
                                    . '<td align="center">' . alias_quadro($resultados['quadro_id']) . '</td>'
                                    . '</tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Importação de JS (JQuery e DataTables) -->
<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    // JavaScript do DataTables
    $(document).ready(function() {
        var table = $('#minhaTabela').DataTable({
            "language": {
                "url": "https://cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json"
            },
            "pageLength": 25,
            "order": [
                [1, "asc"] // Alterado para índice 1 (Coluna Antiguidade - Menor para o Maior)
            ],
            "columnDefs": [{
                    "orderable": false,
                    "targets": 0
                } // Checkbox não ordena
            ]
        });

        // Lógica do Checkbox "Selecionar Todos"
        $('#btn-check').on('click', function() {
            var rows = table.rows({
                'search': 'applied'
            }).nodes();
            $('input[type="checkbox"]', rows).prop('checked', this.checked);
        });

        // Interceptar o Submit
        $('form').on('submit', function(e) {
            var form = this;
            table.$('input[type="checkbox"]:checked').each(function() {
                if (!$.contains(document, this)) {
                    $(form).append(
                        $('<input>').attr('type', 'hidden').attr('name', this.name).val(this.value)
                    );
                }
            });
        });
    });

    function mostraDica() {
        document.getElementById('paragrafoDica').innerHTML = 'Caso já exista registrada promoção para o mesmo posto/graduação, as informações serão atualizadas no banco de dados sobrescrevendo o registro anterior.'
    }

    function escondeDica() {
        document.getElementById('paragrafoDica').innerHTML = ''
    }
</script>

<?php include_once './footer3.php'; ?>