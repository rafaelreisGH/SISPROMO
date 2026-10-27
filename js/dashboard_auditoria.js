let charts = {}; // Para destruir gráficos antigos antes de criar novos

document.addEventListener("DOMContentLoaded", function() {
    carregarDashboard();
});

function carregarDashboard() {
    const dias = document.getElementById('filtro-dias').value;
    
    fetchIndicadores(dias);
    fetchMonitoramento(dias);
    fetchAuditoria(dias);
    fetchSeguranca(dias);
    fetchFeed(dias);
}

// 1. Indicadores
function fetchIndicadores(dias) {
    fetch(`../Controllers/DashboardAuditoriaController.php?acao=indicadores&dias=${dias}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('ind-volume').innerText = data.volume_total || 0;
            document.getElementById('ind-gestores').innerText = data.gestores_ativos || 0;
            document.getElementById('ind-criticas').innerText = data.acoes_criticas || 0;
            document.getElementById('ind-falhas').innerText = data.falhas || 0;
        })
        .catch(error => console.error('Erro ao carregar indicadores:', error));
}

// 2. Monitoramento
function fetchMonitoramento(dias) {
    fetch(`../Controllers/DashboardAuditoriaController.php?acao=monitoramento&dias=${dias}`)
        .then(response => response.json())
        .then(data => {
            const labels = data.map(d => `${d.data} ${d.hora}h`);
            const acoes = data.map(d => d.total_acoes);
            const erros = data.map(d => d.total_erros);

            const ctx = document.getElementById('chart-monitoramento').getContext('2d');
            
            if (charts['monitoramento']) charts['monitoramento'].destroy();
            
            charts['monitoramento'] = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        { label: 'Ações', data: acoes, borderColor: 'blue', fill: false },
                        { label: 'Erros', data: erros, borderColor: 'red', fill: false }
                    ]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
        });
}

// 3. Auditoria
function fetchAuditoria(dias) {
    fetch(`../Controllers/DashboardAuditoriaController.php?acao=auditoria&dias=${dias}`)
        .then(response => response.json())
        .then(data => {
            // Chart Ranking
            const labelsAcoes = data.ranking_acoes.map(a => a.acao);
            const totaisAcoes = data.ranking_acoes.map(a => a.total);
            
            const ctxRanking = document.getElementById('chart-ranking').getContext('2d');
            if (charts['ranking']) charts['ranking'].destroy();
            
            charts['ranking'] = new Chart(ctxRanking, {
                type: 'bar',
                data: {
                    labels: labelsAcoes,
                    datasets: [{ label: 'Frequência', data: totaisAcoes, backgroundColor: '#5bc0de' }]
                },
                options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false }
            });

            // Tabela Top Gestores
            const tbody = document.querySelector('#tabela-top-gestores tbody');
            tbody.innerHTML = '';
            data.top_gestores.forEach(g => {
                tbody.innerHTML += `<tr><td>${g.gestor || 'Sistema'}</td><td>${g.total}</td></tr>`;
            });
        });
}

// 4. Segurança
function fetchSeguranca(dias) {
    fetch(`../Controllers/DashboardAuditoriaController.php?acao=seguranca&dias=${dias}`)
        .then(response => response.json())
        .then(data => {
            const perfis = data.acoes_por_perfil.map(p => p.perfil == 1 ? 'Gestor' : (p.perfil == 2 ? 'Administrador' : (p.perfil ? p.perfil : 'Desconhecido')));
            const totais = data.acoes_por_perfil.map(p => p.total);
            
            const ctxPerfil = document.getElementById('chart-perfil').getContext('2d');
            if (charts['perfil']) charts['perfil'].destroy();
            
            charts['perfil'] = new Chart(ctxPerfil, {
                type: 'doughnut',
                data: {
                    labels: perfis,
                    datasets: [{ data: totais, backgroundColor: ['#5cb85c', '#d9534f', '#f0ad4e'] }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
        });
}

// 5. Feed
let feedData = []; // guardamos para exportar
function fetchFeed(dias) {
    fetch(`../Controllers/DashboardAuditoriaController.php?acao=feed&dias=${dias}`)
        .then(response => response.json())
        .then(data => {
            feedData = data;
            const tbody = document.querySelector('#tabela-feed tbody');
            tbody.innerHTML = '';
            data.forEach((row, index) => {
                let badge = 'bg-secondary';
                if (row.nivel === 'INFO') badge = 'bg-info text-dark';
                if (row.nivel === 'ERROR' || row.nivel === 'CRITICAL') badge = 'bg-danger';
                if (row.nivel === 'WARNING') badge = 'bg-warning text-dark';
                
                // botão para JSON
                const btnJson = `<button class="btn btn-sm btn-outline-secondary py-0 px-2" onclick="mostrarContexto(${index})"><i class="bi bi-eye"></i> Detalhes</button>`;

                tbody.innerHTML += `
                    <tr>
                        <td class="small text-muted">${row.data_log}</td>
                        <td><span class="badge ${badge}">${row.nivel}</span></td>
                        <td>${row.mensagem}</td>
                        <td class="text-center">${btnJson}</td>
                    </tr>
                `;
            });
        });
}

function mostrarContexto(index) {
    const row = feedData[index];
    let contextoObj = {};
    try {
        contextoObj = JSON.parse(row.contexto);
    } catch(e) {
        contextoObj = row.contexto;
    }
    document.getElementById('json-viewer').textContent = JSON.stringify(contextoObj, null, 4);
    
    // Bootstrap 5 Modal nativo (sem depender de jQuery)
    const modalEl = document.getElementById('modalContexto');
    const modalInstancia = bootstrap.Modal.getOrCreateInstance(modalEl);
    modalInstancia.show();
}

function exportarCSV() {
    if (feedData.length === 0) {
        alert("Não há dados para exportar.");
        return;
    }
    
    let csvContent = "data:text/csv;charset=utf-8,";
    csvContent += "Data/Hora,Nivel,Mensagem,Contexto\n";
    
    feedData.forEach(row => {
        const msg = (row.mensagem || "").replace(/"/g, '""');
        const ctx = (row.contexto || "").replace(/"/g, '""');
        csvContent += `${row.data_log},${row.nivel},"${msg}","${ctx}"\n`;
    });
    
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", "auditoria.csv");
    document.body.appendChild(link); // Requisito no firefox
    
    link.click();
    document.body.removeChild(link);
}
