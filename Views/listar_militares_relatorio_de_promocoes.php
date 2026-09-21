<?php
require_once '../Controllers/nivel_gestor.php';
include_once './header2.php';
require_once '../ConexaoDB/conexao.php';
include_once '../Controllers/verifica_permissoes.php';
require_once '../Controllers/alias_quadro.php';

$conn = Conexao::getConexao();

// Removida a lógica de $pagina manual, pois o DataTables cuidará disso.

$pesquisar = (isset($_GET['pesquisar'])) ? filter_input(INPUT_GET, 'pesquisar') : "";

// Prepara a consulta base sem LIMIT
$sql = "SELECT * FROM militar WHERE status = 'ATIVO'";
if (!empty($pesquisar)) {
    $sql .= " AND nome LIKE :pesquisar";
}
// Opcional: manter a regra do CEL BM se desejar
// $sql .= " AND posto_grad_mil != 'CEL BM'";
$sql .= " ORDER BY antiguidade";

$stmt = $conn->prepare($sql);
if (!empty($pesquisar)) {
    $term = "%$pesquisar%";
    $stmt->bindParam(':pesquisar', $term);
}
$stmt->execute();

// Agora o total_militares reflete o total real da consulta
$total_militares = $stmt->rowCount();
?>

<div class="container pb-4">
    <div class="col-md-12">
        <ul class="nav nav-pills">
            <li role="presentation" class="nav-item">
                <a href="pagina_relatorios.php" class="nav-link">Voltar</a>
            </li>
        </ul>
        <hr>
    </div>
    
    <h3><strong>Registros de promoção</strong></h3>
    
    <?php if ($total_militares > 0): ?>
        <span class="badge bg-success"><?= $total_militares ?> militares encontrados no total</span>
    <?php else: ?>
        <span class="badge bg-danger">Nenhum militar encontrado</span>
    <?php endif; ?>
    <hr>

    <div class="col-md-12">
        <form action="" method="GET">
            <div class="row align-items-end">
                <div class="col-md-4">
                    <label for="nome" class="form-label">Nome</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input class="form-control" name="pesquisar" id="nome" placeholder="Digite um nome" value="<?= htmlspecialchars($pesquisar) ?>">
                    </div>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary" type="submit">Pesquisar no Banco</button>
                </div>
            </div>
        </form>
    </div>
    <hr>

    <div class="col-md-12">
        <div class="panel panel-default panel-table">
            <div class="panel-body">
                <!-- FORMULÁRIO DE SELEÇÃO PARA RELATÓRIO -->
                <form id="formRelatorio" action="../Views/relatorio_de_promocoes.php" method="POST">
                    <table class="table table-striped table-bordered table-list">
                        <thead>
                            <tr>
                                <th class="text-center">Selecionar</th>
                                <th class="text-center">Posto/Graduação</th>
                                <th class="text-center">Nome</th>
                                <th class="text-center">Quadro</th>
                                <th class="text-center">Antiguidade</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($resultados = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                <tr>
                                    <td class="text-center">
                                        <input class="form-check-input" type="radio" name="militar_id" value="<?= $resultados['id'] ?>" required>
                                    </td>
                                    <td class="text-center"><?= $resultados['posto_grad_mil'] ?></td>
                                    <td><?= $resultados['nome'] ?></td>
                                    <td class="text-center"><?= alias_quadro($resultados['quadro_id']) ?></td>
                                    <td class="text-center"><?= $resultados['antiguidade'] ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>

                    <?php if ($total_militares > 0): ?>
                        <div class="mt-3"> <!-- Adicionado mb-5 para margem inferior -->
                            <button id="btnRelatorio" class="btn btn-danger" type="submit">
                                <i class="bi bi-file-earmark-pdf"></i> Gerar relatório
                            </button>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include_once './footer3.php'; ?>

<script>
$(document).ready(function() {
    // Inicializa o DataTable (ele agora cuidará de filtrar os 10, 25, 50 resultados na tela)
    var table = $('.table-list').DataTable({
        "language": {
            "url": "https://cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json"
        },
        "pageLength": 10, // Quantidade inicial por página na tela
        "order": [[4, "asc"]], // Ordena por antiguidade
        "columnDefs": [
            { "orderable": false, "targets": 0 } // Desativa ordenação no Radio Button
        ]
    });

    // Caso queira garantir o envio do rádio mesmo em outras páginas (segurança extra):
    $('#formRelatorio').on('submit', function(e) {
        var form = this;
        var checkedRadio = table.$('input[name="militar_id"]:checked');
        
        if (checkedRadio.length > 0) {
            // Se o rádio selecionado não estiver na página atual, injeta ele como hidden
            if (!$.contains(document, checkedRadio[0])) {
                $(form).append(
                    $('<input>')
                        .attr('type', 'hidden')
                        .attr('name', 'militar_id')
                        .val(checkedRadio.val())
                );
            }
        } else {
            alert("Por favor, selecione um militar para gerar o relatório.");
            e.preventDefault();
        }
    });
});
</script>