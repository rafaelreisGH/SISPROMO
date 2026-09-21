<?php
/**
 * View: Impressão da Ata de Reunião de Comissão.
 * Método: GET ?reuniao_id=123
 */

require_once '../Controllers/nivel_gestor.php';
require_once '../vendor/autoload.php';
require_once '../ConexaoDB/conexao.php';

use App\DAO\ReuniaoComissaoDAO;

$reuniaoId = isset($_GET['reuniao_id']) ? (int)$_GET['reuniao_id'] : 0;
if (!$reuniaoId) {
    die("ID da reunião inválido.");
}

$daoReuniao = new ReuniaoComissaoDAO();
$reuniao = $daoReuniao->buscarPorId($reuniaoId);

if (!$reuniao) {
    die("Reunião não encontrada.");
}

$textoAta = $reuniao['texto_ata_final'] ?? '';
if (empty(trim($textoAta))) {
    die("A ata desta reunião ainda não foi gerada e salva.");
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Ata de Reunião - <?= htmlspecialchars($reuniao['numero_ata']) ?></title>
    <style>
        /* Estilos específicos para impressão limpa */
        body {
            font-family: Arial, sans-serif;
            font-size: 12pt;
            line-height: 1.5;
            margin: 2.5cm 2cm 2.5cm 2cm; /* Margens padrão A4 */
            color: #000;
            background-color: #fff;
        }

        /* Oculta os botões na impressão real */
        .no-print {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 30px;
            padding: 10px;
            background-color: #f5f5f5;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .btn {
            padding: 8px 16px;
            font-size: 10pt;
            font-weight: bold;
            cursor: pointer;
            border: 1px solid #333;
            background-color: #fff;
            color: #333;
            border-radius: 4px;
        }

        .btn-print {
            background-color: #212529;
            color: #fff;
            border-color: #212529;
        }

        @media print {
            .no-print {
                display: none !important;
            }
            body {
                margin: 0; /* O navegador aplica suas próprias margens de página na impressão */
            }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <button class="btn btn-print" onclick="window.print()">Imprimir Ata</button>
        <button class="btn" onclick="window.close()">Fechar Janela</button>
    </div>

    <!-- Conteúdo da Ata gravada em HTML -->
    <div class="conteudo-ata">
        <?= $textoAta ?>
    </div>

    <script>
        // Dispara o diálogo de impressão automaticamente após carregar a página
        window.addEventListener('DOMContentLoaded', () => {
            // Pequeno delay para garantir renderização correta
            setTimeout(() => {
                window.print();
            }, 500);
        });
    </script>
</body>
</html>
