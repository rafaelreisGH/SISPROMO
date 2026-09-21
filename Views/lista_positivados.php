<?php
require_once '../Controllers/nivel_gestor.php';
include_once './header2.php';
?>

<div class="container">
    <div class="col-md-12">
        <ul class="nav nav-pills">
            <li role="presentation" class="nav-item">
                <a href="pesquisa_positivados.php" class="nav-link">Voltar</a>
            </li>
        </ul>
        <hr>
        <h3>
            <strong>Documentação:</strong>
        </h3>

        <p id="descricaoPositivados"></p>

        <hr>

        <div class="panel-body">
            <table class="table table-striped table-bordered table-list" id="tabelaPositivados">
                <thead>
                    <tr>
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
                            <p align="center">Documentos Positivados</p>
                        </th>
                    </tr>
                </thead>

                <tbody>
                </tbody>
            </table>
            <div class="col-md-12">
            </div>
        </div>
    </div>
</div>

<script>
    // Função para traduzir os nomes dos documentos, replicando a lógica da sua função PHP.
    function aliasNomeDocumentoJS(nome_documento) {
        switch (nome_documento) {
            case 'cert_1JE':
                return '1º J.E. - Cert. Neg. Crim. - 1º grau - TJ/MT';
            case 'cert_2JE':
                return '2º J.E. - Cert. Neg. Crim. - 2º grau - TJ/MT';
            case 'cert_1JF':
                return '1º J.F. - Cert. Neg. Crim. - TRF-1 - Sç. Jud. MT';
            case 'cert_2JF':
                return '2º J.F. - Cert. Neg. Crim. - TRF-1';
            case 'cert_tse':
                return 'C.E. - Cert. Neg. Crim. - Justiça Eleitoral';
            case 'fad':
                return 'F.A.D. - Ficha de Avaliação de Desempenho';
            case 'rta':
                return 'R.T.A. - Relatório de Tempo Arregimentado';
            case 'ais':
                return 'A.I.S. - Ata de Inspeção de Saúde';
            case 'fp':
                return 'F.P. - Ficha Profissional';
            default:
                return nome_documento; // Retorna o nome original se não houver alias
        }
    }

    async function fetchPositivados() {
        const urlParams = new URLSearchParams(window.location.search);
        const ano = urlParams.get('ano');
        const semestre = urlParams.get('semestre');

        const descricao = document.getElementById('descricaoPositivados');
        descricao.textContent = `Relação dos militares que possuem certidões da justiça positivadas, referente ao processo promocional do ${semestre}º semestre de ${ano}.`;

        if (!ano || !semestre) {
            console.error("Parâmetros 'ano' ou 'semestre' não encontrados na URL.");
            return;
        }

        try {
            const response = await fetch(`../api/positivados_api.php?ano=${ano}&semestre=${semestre}`);
            const data = await response.json();

            const tableBody = document.querySelector('#tabelaPositivados tbody');
            tableBody.innerHTML = '';

            if (data.success && data.data.length > 0) {
                data.data.forEach(item => {
                    const row = document.createElement('tr');
                    const documentos = item.documentos_positivados ? item.documentos_positivados.split(', ') : [];
                    const urls = item.urls_documentos ? item.urls_documentos.split(', ') : [];

                    let linksHtml = '';
                    for (let i = 0; i < documentos.length; i++) {
                        const nomeTratado = aliasNomeDocumentoJS(documentos[i]); // Chama a nova função
                        linksHtml += `<a href="${urls[i]}" target="_blank">${nomeTratado}</a><br>`;
                    }

                    row.innerHTML = `
                    <td align="center">${item.posto_grad_mil}</td>
                    <td align="center">${item.nome}</td>
                    <td align="center">${item.quadro}</td>
                    <td align="center">${linksHtml}</td>
                `;
                    tableBody.appendChild(row);
                });
            } else {
                tableBody.innerHTML = '<tr><td colspan="4">Nenhum resultado encontrado para os critérios de pesquisa.</td></tr>';
            }
        } catch (error) {
            console.error("Erro na requisição:", error);
            const tableBody = document.querySelector('#tabelaPositivados tbody');
            tableBody.innerHTML = '<tr><td colspan="4">Erro ao carregar os dados. Verifique a conexão.</td></tr>';
        }
    }

    window.onload = fetchPositivados;
</script>

<?php
include_once './footer.php';
?>