<?php
require_once '../Controllers/nivel_gestor.php';
include_once './header2.php';
?>

<div class="container">
    <div class="col-md-12">
        <nav class="navbar">
            <form class="container-fluid justify-content-start">
                <a class="btn btn-primary me-2" href="pagina_relatorios.php">Voltar</a>
            </form>
        </nav>
        <hr>
    </div>
    <div class="col-md-12">
        <div class="panel panel-default panel-table">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-xs-6">
                        <h3 class="panel-title">Registros de Militares com Tempo não arregimentado</h3>
                    </div>
                </div>
            </div>
            <div class="panel-body">
                <table class="table table-striped table-bordered table-list">
                    <thead>
                        <tr>
                            <th>
                                <p align="center">Nome do Militar</p>
                            </th>
                            <th>
                                <p align="center">Posto/Graduação Atual</p>
                            </th>
                            <th>
                                <p align="center">Categoria do TNA</p>
                            </th>
                            <th>
                                <p align="center">Quantidade de dias</p>
                            </th>
                            <th>
                                <p align="center">Início do afastamento</p>
                            </th>
                            <th>
                                <p align="center">Fim do afastamento</p>
                            </th>
                        </tr>
                    </thead>
                    <tbody id="tna-table-body">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    // Função para traduzir a categoria do TNA
    function getCategoryAlias(categoria) {
        switch (categoria) {
            case 'inciso1':
                return 'I - LTSPF/LTS';
            case 'inciso2':
                return 'II - LTIP';
            case 'inciso3':
                return 'III - Desempenho de função/cargo civil, não eletivo';
            case 'inciso4':
                return 'IV - Desertor/extraviado';
            case 'inciso5':
                return 'V - Prisão preventiva';
            case 'inciso6':
                return 'VI - Cumprimento de pena restritiva de liberdade';
            default:
                return categoria; // Retorna o valor original se não houver alias
        }
    }
    // Função para buscar e exibir os dados da API
    function fetchDataAndRenderTable() {
        const tableBody = document.getElementById('tna-table-body');
        tableBody.innerHTML = '<tr><td colspan="6" class="text-center">Carregando dados...</td></tr>';

        fetch('../api/tempo_nao_arregimentado_api.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erro ao buscar dados da API.');
                }
                return response.json();
            })
            .then(data => {
                tableBody.innerHTML = ''; // Limpa a mensagem de carregamento
                if (data.length === 0) {
                    tableBody.innerHTML = '<tr><td colspan="6" class="text-center">Nenhum registro encontrado.</td></tr>';
                    return;
                }

                data.forEach(item => {
                    const row = document.createElement('tr');

                    // Formata as datas para o padrão dd/mm/aaaa
                    const inicioFormatado = new Date(item.tna_inicio).toLocaleDateString('pt-BR');
                    const fimFormatado = new Date(item.tna_fim).toLocaleDateString('pt-BR');

                    // Traduz a categoria usando a nova função
                    const categoriaTraduzida = getCategoryAlias(item.categoria);
                    row.innerHTML = `
                        <td align="center">${item.nome}</td>
                        <td align="center">${item.posto_grad_mil}</td>
                        <td align="center">${categoriaTraduzida}</td>
                        <td align="center">${item.qtde_de_dias}</td>
                        <td align="center">${inicioFormatado}</td>
                        <td align="center">${fimFormatado}</td>
                    `;
                    tableBody.appendChild(row);
                });
            })
            .catch(error => {
                console.error('Erro:', error);
                tableBody.innerHTML = `<tr><td colspan="6" class="text-center">Erro ao carregar os dados.</td></tr>`;
            });
    }

    // Chama a função quando a página carregar
    document.addEventListener('DOMContentLoaded', fetchDataAndRenderTable);
</script>

<?php
include_once './footer2.php';
?>