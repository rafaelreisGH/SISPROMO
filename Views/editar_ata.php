<?php

/**
 * Views/editar_ata.php
 * Tela dedicada para geração, redação e impressão da Ata.
 */
require_once '../Controllers/nivel_gestor.php';
require_once '../ConexaoDB/conexao.php';
require_once '../vendor/autoload.php';

use App\DAO\ReuniaoComissaoDAO;

$reuniaoId = isset($_GET['reuniao_id']) ? (int)$_GET['reuniao_id'] : 0;
if (!$reuniaoId) {
    header('Location: gestao_reunioes.php');
    exit();
}

$daoReuniao = new ReuniaoComissaoDAO();
$reuniao = $daoReuniao->buscarPorId($reuniaoId);
if (!$reuniao) {
    die("Reunião não encontrada.");
}

$isConcluida = ($reuniao['status'] === 'CONCLUIDA');

include_once './header2.php';
?>


<div class="container mt-3">
    <!-- Breadcrumb e Ações -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <a href="painel_comissao.php?reuniao_id=<?= $reuniaoId ?>" class="btn btn-outline-secondary btn-sm mb-2">
                <i class="bi bi-arrow-left"></i> Voltar ao Painel da Reunião
            </a>
            <h4 class="mb-0">
                <i class="bi bi-file-text text-primary"></i>
                Redação da Ata Nº <?= htmlspecialchars($reuniao['numero_ata']) ?> – <?= $reuniao['tipo_comissao'] ?>
                <?php if ($isConcluida): ?>
                    <span class="badge bg-success fs-6 ms-2"><i class="bi bi-lock-fill"></i> CONCLUÍDA</span>
                <?php else: ?>
                    <span class="badge bg-primary fs-6 ms-2"><?= htmlspecialchars($reuniao['status']) ?></span>
                <?php endif; ?>
            </h4>
        </div>
        <div class="d-flex gap-2">
            <?php if (!$isConcluida): ?>
                <button class="btn btn-outline-secondary" onclick="gerarAta()" id="btnGerar">
                    <i class="bi bi-magic"></i> Gerar Modelo Automático
                </button>
                <button class="btn btn-success" onclick="salvarAta()" id="btnSalvar">
                    <i class="bi bi-save"></i> Salvar Ata
                </button>
            <?php endif; ?>
            <a href="imprimir_ata.php?reuniao_id=<?= $reuniaoId ?>" target="_blank" class="btn btn-dark">
                <i class="bi bi-printer"></i> Imprimir / PDF
            </a>
        </div>
    </div>

    <?php if ($isConcluida): ?>
        <div class="alert alert-info d-flex align-items-center mb-3" role="alert">
            <i class="bi bi-shield-lock-fill fs-4 me-3"></i>
            <div>
                <strong>Ata arquivada em modo somente leitura:</strong> Esta reunião já foi concluída e homologada, portanto seu texto não permite mais edições ou regerações.
            </div>
        </div>
    <?php endif; ?>

    <!-- Área de Alertas / Feedback -->
    <div id="areaAlerta"></div>

    <!-- Container da Folha / Editor -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-3 bg-light">
            <textarea id="editorAta"><?= htmlspecialchars($reuniao['texto_ata_final'] ?? '') ?></textarea>
        </div>
    </div>
</div>

<script>
    const reuniaoId = <?= $reuniaoId ?>;
    const statusConcluida = <?= $isConcluida ? 'true' : 'false' ?>;

    document.addEventListener('DOMContentLoaded', function() {
        tinymce.init({
            selector: '#editorAta',
            language: 'pt_BR',
            height: '75vh',
            menubar: !statusConcluida, // Desativa menus em modo leitura
            plugins: 'lists table autolink link code wordcount preview fullscreen searchreplace',
            toolbar: statusConcluida ?
                'preview fullscreen' : 'undo redo | blocks | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist | table | code fullscreen preview',
            content_style: `
            body { 
                font-family: Arial, Helvetica, sans-serif; 
                font-size: 12pt; 
                margin: 2cm 2.5cm; 
                line-height: 1.6; 
                background-color: #fff;
                color: #000;
            }
            p { margin-bottom: 0.8em; }
            table { border-collapse: collapse; width: 100%; margin: 1em 0; }
            table, th, td { border: 1px solid #333; padding: 6px; }
        `,
            readonly: statusConcluida,
            promotion: false,
            branding: false
        });
    });

    function exibirAviso(mensagem, tipo = 'success') {
        const area = document.getElementById('areaAlerta');
        if (!area) return;
        area.innerHTML = `
        <div class="alert alert-${tipo} alert-dismissible fade show" role="alert">
            ${mensagem}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>`;
    }

    async function gerarAta() {
        if (statusConcluida) return;

        const btn = document.getElementById('btnGerar');
        if (btn) btn.disabled = true;

        try {
            const res = await fetch(`../api/gerar_ata_api.php?reuniao_id=${reuniaoId}`);
            const data = await res.json();
            if (data.success) {
                tinymce.get('editorAta').setContent(data.html);
                tinymce.get('editorAta').focus();
                exibirAviso('Modelo da ata carregado com sucesso. Edite como desejar e depois clique em Salvar.', 'success');
            } else {
                exibirAviso('Erro ao gerar ata: ' + data.message, 'danger');
            }
        } catch (err) {
            exibirAviso('Erro ao comunicar com o servidor.', 'danger');
        } finally {
            if (btn) btn.disabled = false;
        }
    }

    async function salvarAta() {
        if (statusConcluida) return;

        const btn = document.getElementById('btnSalvar');
        if (btn) btn.disabled = true;

        try {
            const conteudo = tinymce.get('editorAta').getContent();
            if (!conteudo.trim()) {
                exibirAviso('O texto da ata está vazio.', 'warning');
                if (btn) btn.disabled = false;
                return;
            }

            const res = await fetch('../api/salvar_ata_api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    reuniao_id: reuniaoId,
                    texto_ata: conteudo
                })
            });

            const data = await res.json(); // <-- CORRIGIDO AQUI

            if (data.success) {
                exibirAviso(data.message || 'Ata salva com sucesso!', 'success');
            } else {
                exibirAviso('Erro ao salvar ata: ' + data.message, 'danger');
            }
        } catch (err) {
            exibirAviso('Erro ao comunicar com o servidor.', 'danger');
        } finally {
            if (btn) btn.disabled = false;
        }
    }
</script>

<?php include_once './footer2.php'; ?>