<?php
require_once '../Controllers/nivel_gestor.php';
include_once './header2.php';
include_once '../Controllers/funcoes_LQ.php';
require_once '../ConexaoDB/conexao.php';
require_once '../Controllers/alias_quadro.php';

$conn = Conexao::getConexao();

// Definições de filtro
$quadro = null;
$lq_ano_base = isset($_POST['criterio_ano_promocao_futura']) ? $_POST['criterio_ano_promocao_futura'] : null;
$lq_dia_mes = isset($_POST['criterio_dia_mes_promocao_futura']) ? $_POST['criterio_dia_mes_promocao_futura'] : null;

if (is_null($lq_ano_base)) {
    header("Location:../Views/nenhum_resultado.php");
    exit;
}

$lq_data_formatada = $lq_ano_base . '-' . $lq_dia_mes;

/*-------------------------------------------------------------------*/
// Processamento da lista
$alteracoes_realizadas = processa_lista_de_candidatos($conn, $lq_data_formatada, $quadro);

// Lógica de criação de pastas em lote
$pastas_criadas = [];
if (isset($_POST['criar_pasta']) && $_POST['criar_pasta'] == 1 && !empty($alteracoes_realizadas)) {
    $pastas_criadas = criarPastaPromocionalEmLote($alteracoes_realizadas, $lq_data_formatada, $conn);

    if (!empty($pastas_criadas)) {
        require_once __DIR__ . '/../Logger/LoggerFactory.php';
        $logger = LoggerFactory::createLogger();
        foreach ($pastas_criadas as $item) {
            $logger->info('Usuário criou pasta promocional em lote', [
                'id' => $_SESSION['id'],
                'sujeito' => $item["id_militar"]
            ]);
        }
        $aux = criaDocumentosVazios($pastas_criadas, $conn);
    }
}
/*-------------------------------------------------------------------*/

// Includes para formatação dos dados na tabela
require_once '../Controllers/alias_posto_grad.php';
require_once '../Controllers/alias_ultima_promocao.php';

$data_exibicao = date('d/m/Y', strtotime($lq_data_formatada));
?>

<div class="container mt-4 pb-5">
    <!-- Cabeçalho Informativo (Mantido para contexto, mas fora da estrutura da tabela solicitada) -->
    <div class="col-md-12">
        <ul class="nav nav-pills">
            <li role="presentation" class="nav-item">
                <a href="selecao_criterios_de_pesquisa_LQ.php" class="nav-link">
                    Voltar
                </a>
            </li>
        </ul>
        <hr>
    </div>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3>Limite de Quantitativo (LQ)</h3>
            <p class="text-muted">Data considerada: <strong><?= $data_exibicao; ?></strong></p>
            <!-- INÍCIO DA BADGE DE CONTAGEM -->
            <?php
            if (!empty($alteracoes_realizadas)) {
                $total_militares = count($alteracoes_realizadas);
                echo '<span class="badge bg-success mb-2">' . $total_militares . ' militares encontrados</span>';
            }
            ?>
            <!-- FIM DA BADGE DE CONTAGEM -->
        </div>
        <!-- <a href="selecao_criterios_de_pesquisa_LQ.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a> -->
    </div>

    <?php if (!empty($pastas_criadas)): ?>
        <div class="alert alert-success">
            <i class="bi bi-check-circle"></i> Pastas e documentos promocionais criados com sucesso!
        </div>
    <?php endif; ?>
    <hr>
    <!-- INÍCIO DO MODELO VISUAL PADRONIZADO -->
    <form action="" method="POST" id="formListaCandidatos">
        <div class="col-md-12">
            <div class="panel panel-default panel-table">
                <div class="panel-body">
                    <table class="table table-striped table-bordered table-list" id="tabelaLQ">
                        <thead>
                            <tr>
                                <th>
                                    <p align="center"></br>
                                        <input class="form-check-input" type="checkbox" id="btn-check" onclick="checkUncheck(this)">
                                    </p>
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
                                    <p align="center">Antiguidade</p>
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                            $ordem = 1;
                            if (!empty($alteracoes_realizadas)) {
                                foreach ($alteracoes_realizadas as $item) {
                                    $auxiliar = explode(",", $item);
                                    // Mapeamento baseado no seu código anterior:
                                    // $auxiliar[0] = Data
                                    // $auxiliar[1] = Posto ID
                                    // $auxiliar[2] = Nome
                                    // $auxiliar[3] = Quadro

                                    $aux_posto_grad = alias_posto_grad($auxiliar[1]);
                                    $aux_nome = $auxiliar[2];
                                    $aux_quadro = alias_quadro($auxiliar[3] ?? 'N/A');
                                    // Usando a ordem da lista como antiguidade, já que o array vem ordenado
                                    $aux_antiguidade = $ordem;

                                    // Nota: O value do checkbox precisa ser o ID do militar. 
                                    // Como o array $alteracoes_realizadas original é string explodida e não parece ter o ID explicitamente,
                                    // coloquei $aux_nome como fallback, mas o ideal é passar o ID real se disponível.
                                    $valor_checkbox = $aux_nome;

                                    echo '<tr>'
                                        . '<td align="center"><input class="form-check-input mt-0" type="checkbox" value="' . $valor_checkbox . '" name="militar_id[]"></td>'
                                        . '<td align="center">' . $aux_posto_grad . '</td>'
                                        . '<td align="center">' . $aux_nome . '</td>'
                                        . '<td align="center">' . $aux_quadro . '</td>'
                                        . '<td align="center">' . $aux_antiguidade . 'º</td>';
                                    echo '</tr>';

                                    $ordem++;
                                }
                            } else {
                                echo '<tr><td colspan="5" align="center">Nenhum registro encontrado.</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </form>
    <!-- FIM DO MODELO VISUAL PADRONIZADO -->
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    // Função para marcar/desmarcar todos os checkboxes (Requerido pelo modelo visual)
    function checkUncheck(mainCheck) {
        var checkboxes = document.getElementsByName('militar_id[]');
        for (var i = 0; i < checkboxes.length; i++) {
            checkboxes[i].checked = mainCheck.checked;
        }
    }

    $(document).ready(function() {
        $('#tabelaLQ').DataTable({
            "language": {
                "url": "https://cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json"
            },
            // Define a quantidade padrão inicial (ex: 25 como na sua imagem)
            "pageLength": 25,

            // Define as opções que aparecerão no menu (10, 25, 50, 100, Todos)
            "lengthMenu": [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, "Todos"]
            ],

            "responsive": true,

            /* 
               AQUI ESTÁ A MÁGICA:
               'l' = length (o filtro de quantidade)
               'f' = filtering (o campo de busca)
               'r' = processing (carregando)
               't' = table (a tabela em si)
               'i' = information (mostrando 1 de X registros)
               'p' = pagination (paginação)
               
               A classe d-flex justify-content-between coloca um na esquerda e outro na direita.
            */
            "dom": '<"d-flex justify-content-between align-items-center mb-3"lf>rtip',
        });
    });
</script>

<?php include_once './footer.php'; ?>