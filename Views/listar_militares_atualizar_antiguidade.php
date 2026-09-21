<?php
require_once '../Controllers/nivel_gestor.php';
include_once './header2.php';
require_once '../ConexaoDB/conexao.php';
$conn = Conexao::getConexao();
require_once '../Controllers/alias_quadro.php';

// verificar se houve alterações
$alteracoes_realizadas = isset($_GET['sucesso']) ? $_GET['sucesso'] : 0;

// declaração de variáveis
$criterio_posto_grad = "";
$criterio_quadro = "";

// verificação dos POSTs e GETs para filtros
$where = array();
if (!empty($_POST['criterio_posto_grad'])) {
    $criterio_posto_grad = $_POST['criterio_posto_grad'];
    $where[] = " posto_grad_mil = '{$criterio_posto_grad}'";
} else if (!empty($_GET['criterio_posto_grad'])) {
    $criterio_posto_grad = $_GET['criterio_posto_grad'];
    $where[] = " posto_grad_mil = '{$criterio_posto_grad}'";
}

if (!empty($_POST['criterio_quadro'])) {
    $criterio_quadro = $_POST['criterio_quadro'];
    $where[] = " quadro_id = '{$criterio_quadro}'";
} else if (!empty($_GET['criterio_quadro'])) {
    $criterio_quadro = $_GET['criterio_quadro'];
    $where[] = " quadro_id = '{$criterio_quadro}'";
}

// Construção da Query
$sql = "SELECT nome, posto_grad_mil, id, quadro_id, antiguidade FROM militar WHERE 1=1";

if (count($where) > 0) {
    $sql .= " AND " . implode(" AND ", $where);
}

$sql .= " AND status = 'ATIVO' ORDER BY antiguidade ASC";

$stmt = $conn->query($sql);

// Contador de militares encontrados
$total_militares = $stmt->rowCount();
?>

<div class="container mb-5">
    <div class="col-md-12">
        <ul class="nav nav-pills">
            <li role="presentation" class="nav-item">
                <a href="selecao_criterios_de_pesquisa.php?alteracao_antiguidade=1" class="nav-link">Voltar</a>
            </li>
        </ul>
        <hr>
    </div>
    
    <div class="d-flex align-items-center justify-content-between">
        <h3><strong>Resultados de pesquisa</strong></h3>
        <?php if (isset($total_militares)): ?>
            <span class="badge bg-success" style="font-size: 1rem;"><?= $total_militares ?> militares encontrados</span>
        <?php endif; ?>
    </div>
    <hr>
    
    <form action="../Controllers/atualiza_antiguidade.php" method="POST" name="formAntiguidade" id="formAntiguidade" onsubmit="return validateForm()">
        <div class="col-md-12">
            <div class="row">
                <div class="form-group col-md-5">
                    <label class="form-label">Inserir a antiguidade correta do militar selecionado</label>
                    <div class="input-group mb-2">
                        <span class="input-group-text">Posição a ser ocupada</span>
                        <input type="number" class="form-control" name="antiguidade_informada" id="antiguidade_informada" min="1" required oninput="validarAntiguidade(this)">
                    </div>
                    
                    <p id="alertaErroAntiguidadeIgual" style="color:#FF0000; font-weight: bold; min-height: 24px;"></p>

                    <input type="hidden" name="criterio_posto_grad" value="<?= htmlspecialchars($criterio_posto_grad) ?>">
                    <input type="hidden" name="criterio_quadro" value="<?= htmlspecialchars($criterio_quadro) ?>">
                    <input type="hidden" name="militar_id" id="militar_id_hidden" value="">

                    <button class="btn btn-outline-danger active" type="submit" name="buscar">Atualizar antiguidade</button>
                </div>
                
                <div class="col-md-7">
                    <div class="alert alert-info py-2 mb-2">
                        <strong>Dica:</strong> Selecione o militar na tabela abaixo, informe a nova posição e clique em atualizar. 
                        A reordenação dos demais militares será feita automaticamente.
                    </div>
                    <?php if ($alteracoes_realizadas == 1): ?>
                        <div class="alert alert-success py-2"><i class="bi bi-person-check"></i> Alteração realizada com sucesso!</div>
                    <?php elseif (isset($_GET['msg']) && $_GET['msg'] == 'erro'): ?>
                        <div class="alert alert-danger py-2">Erro ao processar alteração.</div>
                    <?php endif; ?>
                </div>
            </div>
            <br>
        </div>

        <div class="col-md-12">
            <div class="panel panel-default panel-table">
                <div class="panel-body">
                    <table class="table table-striped table-bordered table-list" id="tabelaAntiguidade">
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
                                    <td align="center">
                                        <input class="form-check-input mt-0 militar-radio" type="radio" 
                                               name="antiguidade_atual" 
                                               value="<?= $resultados['antiguidade'] ?>" 
                                               data-id="<?= $resultados['id'] ?>" 
                                               required>
                                    </td>
                                    <td align="center"><?= $resultados['posto_grad_mil'] ?></td>
                                    <td align="center"><?= $resultados['nome'] ?></td>
                                    <td align="center"><?= alias_quadro($resultados['quadro_id'])  ?></td>
                                    <td align="center"><?= $resultados['antiguidade'] ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Scripts Necessários -->
<?php include_once './footer3.php'; ?>

<script>
    $(document).ready(function() {
        // 1. Inicializa o DataTable
        var table = $('#tabelaAntiguidade').DataTable({
            "language": {
                "url": "https://cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json"
            },
            "pageLength": 25,
            "order": [[4, "asc"]], // Ordena pela coluna de Antiguidade
            "columnDefs": [
                { "orderable": false, "targets": 0 } // Desabilita ordenação na coluna de rádio
            ]
        });

        // 2. Lógica para atualizar o ID oculto quando o rádio for clicado
        // Usamos delegação de evento porque o DataTables recria os elementos ao mudar de página
        $('#tabelaAntiguidade tbody').on('change', '.militar-radio', function() {
            var militarId = $(this).attr('data-id');
            $('#militar_id_hidden').val(militarId);
            $('#alertaErroAntiguidadeIgual').html(''); // Limpa alertas ao trocar seleção
        });

        // 3. Garantir que o valor do rádio seja enviado mesmo se estiver em outra página do DataTable
        $('#formAntiguidade').on('submit', function(e) {
            var rádioSelecionado = table.$('.militar-radio:checked');
            
            if (rádioSelecionado.length === 0) {
                $('#alertaErroAntiguidadeIgual').html('* Selecione um militar na tabela.');
                e.preventDefault();
                return false;
            }

            // Se o rádio não estiver no DOM atual (em outra página), injetamos um hidden para garantir o envio
            if (!$.contains(document, rádioSelecionado[0])) {
                $(this).append(
                    $('<input>').attr('type', 'hidden').attr('name', 'antiguidade_atual').val(rádioSelecionado.val())
                );
            }
            
            return validateForm();
        });
    });

    function validateForm() {
        let antiguidade_informada = document.getElementById("antiguidade_informada").value;
        // Busca o rádio selecionado dentro de todo o DataTable (todas as páginas)
        let rádioSelecionado = $('#tabelaAntiguidade').DataTable().$('.militar-radio:checked');
        
        if (rádioSelecionado.length === 0) {
            document.getElementById('alertaErroAntiguidadeIgual').innerHTML = '* Selecione um militar na tabela.';
            return false;
        }

        let antiguidade_atual = rádioSelecionado.val();

        if (parseInt(antiguidade_informada) === parseInt(antiguidade_atual)) {
            document.getElementById('alertaErroAntiguidadeIgual').innerHTML = '* A nova antiguidade deve ser diferente da atual.';
            return false;
        } else if (antiguidade_informada <= 0) {
            document.getElementById('alertaErroAntiguidadeIgual').innerHTML = '* A antiguidade informada deve ser maior que 0.';
            return false;
        }
        
        return true;
    }

    function validarAntiguidade(input) {
        if (input.value <= 0) {
            input.setCustomValidity("A antiguidade deve ser maior que 0.");
        } else {
            input.setCustomValidity("");
        }
    }
</script>