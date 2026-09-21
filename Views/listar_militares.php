<?php
require_once '../Controllers/nivel_gestor.php';
include_once './header2.php'; // Usando o header novo
require_once '../ConexaoDB/conexao.php';
require_once '../Controllers/alias_quadro.php';
$conn = Conexao::getConexao();

// Removida a lógica de paginação manual do PHP para deixar o DataTable assumir
$nome_pesquisar = filter_input(INPUT_GET, 'pesquisar', FILTER_UNSAFE_RAW);
$posto_grad_pesquisar = filter_input(INPUT_GET, 'posto_grad', FILTER_UNSAFE_RAW);

// Query simplificada (o DataTable cuidará da exibição)
$query_select = "SELECT * FROM militar WHERE status = 'ATIVO' AND posto_grad_mil != 'CEL BM'";
$params = [];

if (!empty($nome_pesquisar)) {
    $query_select .= " AND nome LIKE ?";
    $params[] = "%" . $nome_pesquisar . "%";
}
if (!empty($posto_grad_pesquisar)) {
    $query_select .= " AND posto_grad_mil = ?";
    $params[] = $posto_grad_pesquisar;
}

$query_select .= " ORDER BY antiguidade";
$stmt = $conn->prepare($query_select);
$stmt->execute($params);
?>

<div class="container mt-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Resultados de pesquisa</h3>
        <a href="pagina_gestor.php" class="btn btn-secondary btn-sm">Voltar</a>
    </div>
    <table id="tabelaMilitares" class="table table-striped table-hover table-bordered w-100">
        <thead class="table-light">
            <tr>
                <th class="text-center">FAD</th>
                <th class="text-center">Pasta</th>
                <th class="text-center">Editar</th>
                <th class="text-center">Posto/Grad</th>
                <th class="text-center">Nome</th>
                <th>Quadro</th>
                <th class="text-center" class="text-center">Média</th>
            </tr>
        </thead>
        <tbody>
            <?php
            while ($resultados = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $aux_id = $resultados['id'];
                $media = ($resultados['media'] == 0) ? 'N/D' : $resultados['media'];

                echo '<tr>';
                // Botão FAD - Usando Bootstrap Icons (bi-copy)
                echo '<td class="text-center">
                                <form action="../Views/view_fad.php" method="POST">
                                    <button class="btn btn-outline-info btn-sm" type="submit" name="militar_id" value="' . $aux_id . '">
                                        <i class="bi bi-copy"></i>
                                    </button>
                                </form>
                              </td>';
                // Botão Pasta - Usando Bootstrap Icons (bi-folder2-open)
                echo '<td class="text-center">
                                <form action="../Views/pasta_promocional_home.php" method="POST">
                                    <button class="btn btn-outline-danger btn-sm" type="submit" name="militar_id" value="' . $aux_id . '">
                                        <i class="bi bi-folder2-open"></i>
                                    </button>
                                </form>
                              </td>';
                // Botão Editar - Usando Bootstrap Icons (bi-pencil-square)
                echo '<td class="text-center">
                                <form action="../Views/visualizar_dados.php" method="POST">
                                    <button class="btn btn-outline-success btn-sm" type="submit" name="militar_id" value="' . $aux_id . '">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                </form>
                              </td>';

                echo '<td class="text-center">' . $resultados['posto_grad_mil'] . '</td>';
                echo '<td class="text-center">' . $resultados['nome'] . '</td>';

                // Pega a sigla do quadro via função centralizada
                $nome_quadro = alias_quadro($resultados['quadro_id']);

                echo '<td class="text-center">' . $nome_quadro . '</td>';
                echo '<td class="text-center">' . $media . '</td>';
                echo '</tr>';
            }
            ?>
        </tbody>
    </table>

    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#tabelaMilitares').DataTable({
                "language": {
                    "url": "https://cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json"
                },
                "pageLength": 20,
                "responsive": true
            });
        });
    </script>

    <?php include_once './footer3.php'; ?>