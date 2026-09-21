<?php

/**
 * API: Gera o texto HTML da Ata de Reunião da Comissão (CPO / CPP).
 * Segue estritamente a minuta oficial do CBMMT e nomenclaturas institucionais.
 * Método: GET ?reuniao_id=123
 */

require_once __DIR__ . '/../Controllers/session_bootstrap.php';

ob_start();

require_once '../Controllers/nivel_gestor.php';
require_once '../vendor/autoload.php';
require_once '../ConexaoDB/conexao.php';

use App\DAO\ReuniaoComissaoDAO;
use App\DAO\CriterioFalhoDAO;
use App\DAO\QuadroAcessoDAO;
use App\Config\CatalogoImpedimentos;

if (ob_get_length()) ob_clean();
header('Content-Type: application/json; charset=utf-8');

try {
    $reuniaoId = isset($_GET['reuniao_id']) ? (int)$_GET['reuniao_id'] : 0;
    if (!$reuniaoId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID da reunião inválido.']);
        exit();
    }

    $conn = Conexao::getConexao();
    $daoReuniao = new ReuniaoComissaoDAO();
    $reuniao = $daoReuniao->buscarPorId($reuniaoId);
    if (!$reuniao) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Reunião não encontrada.']);
        exit();
    }

    $processoId   = (int)$reuniao['processo_promocional_id'];
    $tipoComissao = $reuniao['tipo_comissao'];

    // Datas
    $dtReuniao = new DateTime($reuniao['data_reuniao']);
    $dia = $dtReuniao->format('d');
    $ano = $dtReuniao->format('Y');
    $meses = [
        '01' => 'janeiro',
        '02' => 'fevereiro',
        '03' => 'março',
        '04' => 'abril',
        '05' => 'maio',
        '06' => 'junho',
        '07' => 'julho',
        '08' => 'agosto',
        '09' => 'setembro',
        '10' => 'outubro',
        '11' => 'novembro',
        '12' => 'dezembro'
    ];
    $mesExtenso = $meses[$dtReuniao->format('m')];
    $dataExtenso = "{$dia} dias do mês de {$mesExtenso} de {$ano}";

    $dataPromocaoFormatada = !empty($reuniao['processo_data_promocao'])
        ? date('d/m/Y', strtotime($reuniao['processo_data_promocao']))
        : "02/12/{$ano}";

    $siglaComissao = htmlspecialchars($tipoComissao);
    $nomeComissaoExtenso = ($tipoComissao === 'CPO')
        ? 'Comissão de Promoção de Oficiais'
        : 'Comissão de Promoção de Praças';

    $numeroAta = htmlspecialchars($reuniao['numero_ata']);
    $catalogo = CatalogoImpedimentos::getCatalogo();
    $letras = range('a', 'z');

    // =========================================================================
    // MONTAGEM DO HTML DA ATA
    // =========================================================================
    $html = "";

    // 1. Cabeçalho Institucional
    $html .= "<div style='text-align: center; font-weight: bold; line-height: 1.4; margin-bottom: 20px;'>";
    $html .= "ESTADO DE MATO GROSSO<br>";
    $html .= "SECRETARIA DE ESTADO DE SEGURANÇA PÚBLICA<br>";
    $html .= "CORPO DE BOMBEIROS MILITAR<br>";
    $html .= "{$nomeComissaoExtenso} ({$siglaComissao})";
    $html .= "</div>";

    // 2. Título
    $html .= "<div style='text-align: center; font-weight: bold; margin-bottom: 20px;'>";
    $html .= "ATA DE REUNIÃO DA {$siglaComissao}<br>";
    $html .= "ATA Nº. {$numeroAta}/{$siglaComissao}";
    $html .= "</div>";

    // 3. Abertura
    $html .= "<p style='text-align: justify; text-indent: 2cm;'>";
    $html .= "Ao(s) {$dataExtenso}, na sala do Comandante-Geral do Corpo de Bombeiros Militar, localizada no Quartel do Comando Geral em Cuiabá, Estado de Mato Grosso, reuniu-se presencialmente a {$nomeComissaoExtenso} ({$siglaComissao}), a fim de deliberar sobre assuntos relativos às promoções de {$dataPromocaoFormatada}.";
    $html .= "</p>";

    $html .= "<p style='text-align: justify; text-indent: 2cm;'>";
    $html .= "Aberta a reunião, o presidente passou a palavra ao secretário, que passou os assuntos da pauta:";
    $html .= "</p>";

    // =========================================================================
    // PRIMEIRA PAUTA: IMPEDIMENTOS LEGAIS (ALTERAÇÃO #1 E ALTERAÇÃO #2)
    // =========================================================================
    $html .= "<h4 style='font-size: 11pt; font-weight: bold; margin-top: 25px; margin-bottom: 10px; text-transform: uppercase;'>";
    $html .= "PRIMEIRA PAUTA: SITUAÇÕES LEGAIS QUE IMPEDEM A INCLUSÃO EM QUADRO DE ACESSO, CONFORME LEGISLAÇÃO VIGENTE.";
    $html .= "</h4>";

    // Alteração #1: Texto de Abertura Oficial
    $html .= "<p style='text-align: justify; text-indent: 2cm;'>";
    $html .= "A Secretaria das Comissões de Promoções da Diretoria de Gestão de Pessoas realizou estudos e levantamento das situações que impedem a inclusão em Quadro de Acesso conforme legislação vigente de militares relacionados no Limite Quantitativo publicado no BGE nº XXXX, de XX/XX/20XX.";
    $html .= "</p>";

    $html .= "<p style='text-align: justify; text-indent: 2cm;'>";
    $html .= "Verificou-se que os militares, pelos motivos abaixo, não preenchiam, em sua plenitude, os requisitos para ingresso no Quadro de Acesso nos termos do Art. 36 Lei 10.076/14, e consequentemente para concorrer à promoção na data pertinente.";
    $html .= "</p>";

    $html .= "<p style='text-align: justify; text-indent: 2cm;'>";
    $html .= "Com base no exposto, proferiu-se a seguinte decisão colegiada:";
    $html .= "</p>";

    $criterioDao = new CriterioFalhoDAO();
    $impedidosIds = $criterioDao->buscarTodosImpedidosPorProcesso($processoId, $tipoComissao);

    // Alteração #2: Decisão Nominal e Alíneas a), b), c)
    if (!empty($impedidosIds)) {
        foreach ($impedidosIds as $mid) {
            $stmtMil = $conn->prepare("SELECT nome, posto_grad_mil FROM militar WHERE id = :mid");
            $stmtMil->execute([':mid' => $mid]);
            $mil = $stmtMil->fetch(PDO::FETCH_ASSOC);
            $nomeMilitarCompleto = mb_strtoupper("{$mil['posto_grad_mil']} {$mil['nome']}", 'UTF-8');

            $html .= "<p style='margin-top: 15px; margin-bottom: 5px; text-align: justify;'>";
            $html .= "NÃO INCLUIR no quadro de Acesso o <strong>" . htmlspecialchars($nomeMilitarCompleto) . "</strong> por:";
            $html .= "</p>";

            $html .= "<div style='margin-left: 1cm;'>";
            $falhas = $criterioDao->buscarFalhasPorMilitarEProcesso($mid, $processoId, $tipoComissao);

            foreach ($falhas as $idx => $falha) {
                $letraAtual = $letras[$idx] ?? '-';
                $codigo = $falha['inciso_artigo_36'];
                $itemInfo = $catalogo[$codigo] ?? null;
                $detalhe = trim((string)$falha['descricao_impedimento']);

                // Texto exato da option / título normativo
                $tituloOption = $itemInfo['titulo_ata'] ?? $itemInfo['label'] ?? "Restrição normativa {$codigo}";

                if ($codigo === 'L21_III') {
                    // REGRA ESPECIAL: CONCEITO MORAL (Fusão Art. 21, III + Art. 36, I + 3 blocos)
                    $comissaoCargo = ($tipoComissao === 'CPO') ? 'A Comissão de Promoção de Oficiais' : 'A Comissão de Promoção de Praças';
                    $postoTratamento = ($tipoComissao === 'CPO') ? 'o Oficial' : 'a Praça';

                    $html .= "<p style='text-align: justify; margin-bottom: 6px;'>";
                    $html .= "<strong>{$letraAtual}) - " . htmlspecialchars($tituloOption) . "</strong>";
                    $html .= "</p>";

                    $html .= "<p style='text-align: justify; margin-bottom: 6px;'>";
                    $html .= "{$comissaoCargo} ao analisar a vida funcional pregressa do " . htmlspecialchars($nomeMilitarCompleto) . " constatou que {$postoTratamento} possui:";
                    $html .= "</p>";

                    if (!empty($detalhe) && !str_starts_with($detalhe, 'Incidiu na restrição') && $detalhe !== 'Sem conceito moral cadastrado.') {
                        $html .= "<p style='text-align: justify; margin-bottom: 6px;'>";
                        $html .= nl2br(htmlspecialchars($detalhe));
                        $html .= "</p>";
                    } else {
                        // Linha em branco para livre edição no TinyMCE
                        $html .= "<p style='text-align: justify; margin-bottom: 6px;'>";
                        $html .= "01 - <br>";
                        $html .= "02 - ";
                        $html .= "</p>";
                    }

                    $html .= "<p style='text-align: justify; margin-bottom: 12px;'>";
                    $html .= "Não reunindo, portanto, as qualidades e atributos, caracterizados pela honra, dignidade, honestidade e seriedade que o militar estadual deve possuir no desempenho de suas funções e no convívio social, de modo a lhe conferir respeitabilidade perante a sociedade, seus superiores, pares e subordinados.";
                    $html .= "</p>";
                } else {
                    // DEMAIS IMPEDIMENTOS: Texto da Option + Detalhe
                    $html .= "<p style='text-align: justify; margin-bottom: 8px;'>";
                    $html .= "<strong>{$letraAtual}) - " . htmlspecialchars($tituloOption) . "</strong>";

                    if (!empty($detalhe) && !str_starts_with($detalhe, 'Incidiu na restrição')) {
                        $html .= "<br>" . nl2br(htmlspecialchars($detalhe));
                    }
                    $html .= "</p>";
                }
            }
            $html .= "</div>";
        }
    } else {
        $html .= "<p style='text-align: justify; text-indent: 2cm;'>";
        $html .= "Verificou-se que todos os militares relacionados no Limite Quantitativo preenchiam os requisitos regulamentares para ingresso no Quadro de Acesso.";
        $html .= "</p>";
    }

    // =========================================================================
    // SEGUNDA PAUTA: MONTAGEM DO QUADRO DE ACESSO (QA)
    // =========================================================================
    $html .= "<h4 style='font-size: 11pt; font-weight: bold; margin-top: 30px; margin-bottom: 10px; text-transform: uppercase;'>";
    $html .= "SEGUNDA PAUTA: MONTAGEM DO QUADRO DE ACESSO";
    $html .= "</h4>";

    $html .= "<p style='text-align: justify; text-indent: 2cm;'>";
    $html .= "A {$nomeComissaoExtenso} passou a realizar a montagem do Quadro de Acesso, observando os requisitos exigidos nos Arts. 21 e 36 da Lei nº 10.076 de 31 de Março de 2014 c/c o Decreto nº 2.268 de 10 de Abril de 2014, resultando na seguinte composição:";
    $html .= "</p>";

    $qaDao = new QuadroAcessoDAO();
    $dadosQA = $qaDao->buscarDadosQA($reuniaoId);

    if (!empty($dadosQA['qa_agrupado'])) {
        // 1. QAM (Merecimento) - Tabela Compacta com a Média
        $gruposQAM = array_filter($dadosQA['qa_agrupado'], fn($g) => $g['criterio'] === 'Merecimento');
        if (!empty($gruposQAM)) {
            foreach ($gruposQAM as $gQam) {
                $html .= "<div style='margin-top: 15px; margin-bottom: 5px; font-weight: bold;'>";
                $html .= htmlspecialchars($gQam['quadro']) . "<br>";
                $html .= "Quadro de Acesso por Merecimento (QAM)<br>";
                $html .= "Ao Posto de " . htmlspecialchars($gQam['destino']);
                $html .= "</div>";

                $html .= "<table border='1' cellpadding='4' cellspacing='0' style='width: 100%; border-collapse: collapse; margin-top: 6px; margin-bottom: 15px;'>";
                $html .= "<thead><tr style='background-color: #f2f2f2;'><th style='width: 10%; text-align: center;'>Ordem</th><th>Posto / Nome Completo</th><th style='width: 18%; text-align: center;'>Média¹</th></tr></thead><tbody>";

                foreach ($gQam['membros'] as $idx => $m) {
                    $ordem = ($idx + 1) . "º";
                    $media = ($m['pontuacao'] !== null) ? number_format((float)$m['pontuacao'], 3, ',', '.') : '—';
                    $html .= "<tr>";
                    $html .= "<td style='text-align: center; font-weight: bold;'>{$ordem}</td>";
                    $html .= "<td><strong>" . htmlspecialchars($m['posto_atual']) . "</strong> " . htmlspecialchars($m['nome']) . "</td>";
                    $html .= "<td style='text-align: center; font-weight: bold;'>{$media}</td>";
                    $html .= "</tr>";
                }

                $html .= "</tbody></table>";
                $html .= "<small style='display: block; margin-top: -10px; margin-bottom: 15px;'>[1] Análise do Conceito Profissional através da média aritmética das fichas (FAD + FP + FEO).</small>";
            }
        }

        // 2. QAA (Antiguidade) - Texto Corrido
        $gruposQAA = array_filter($dadosQA['qa_agrupado'], fn($g) => $g['criterio'] === 'Antiguidade');
        if (!empty($gruposQAA)) {
            $html .= "<div style='margin-top: 20px; font-weight: bold;'>Quadro de Acesso por Antiguidade (QAA)</div>";

            foreach ($gruposQAA as $gQaa) {
                $html .= "<p style='margin-top: 15px; margin-bottom: 5px; font-weight: bold;'>";
                $html .= htmlspecialchars($gQaa['quadro']) . "<br>";
                $html .= "Ao Posto/Graduação de " . htmlspecialchars($gQaa['destino']);
                $html .= "</p>";

                $html .= "<div style='margin-left: 0.5cm; margin-bottom: 15px;'>";
                foreach ($gQaa['membros'] as $m) {
                    $html .= "<p style='margin-bottom: 3px;'>" . htmlspecialchars($m['posto_atual']) . " " . htmlspecialchars($m['nome']) . "</p>";
                }
                $html .= "</div>";
            }
        }
    }

    // =========================================================================
    // TERCEIRA PAUTA: PROPOSTA DE PROMOÇÃO
    // =========================================================================
    $html .= "<h4 style='font-size: 11pt; font-weight: bold; margin-top: 30px; margin-bottom: 10px; text-transform: uppercase;'>";
    $html .= "TERCEIRA PAUTA: PROPOSTA DE PROMOÇÃO";
    $html .= "</h4>";

    $html .= "<p style='text-align: justify; text-indent: 2cm;'>";
    $html .= "De acordo com o que foi processado pela {$nomeComissaoExtenso} e conforme as vagas existentes para cada posto e graduação, ficam assim definidas as seguintes propostas de Promoção, que serão consubstanciadas por Decreto do Governador do Estado de Mato Grosso:";
    $html .= "</p>";

    $dadosProposta = $qaDao->buscarDadosProposta($reuniaoId);

    if (!empty($dadosProposta['proposta_agrupada'])) {
        foreach ($dadosProposta['proposta_agrupada'] as $gProp) {
            $isMerecimento = ($gProp['criterio'] === 'Merecimento');

            $html .= "<p style='margin-top: 15px; margin-bottom: 5px; font-weight: bold;'>";
            $html .= htmlspecialchars($gProp['quadro']) . "<br>";
            $html .= "Pelo critério de " . htmlspecialchars($gProp['criterio']) . "<br>";
            $html .= "Promoção ao Posto/Graduação de " . htmlspecialchars($gProp['destino']);
            $html .= "</p>";

            if (!$gProp['ha_vagas']) {
                $html .= "<p style='margin-left: 0.5cm; font-style: italic; color: #555;'>Não há vagas disponíveis para este posto/graduação neste processo promocional.</p>";
            } else {
                if ($isMerecimento) {
                    $html .= "<table border='1' cellpadding='4' cellspacing='0' style='width: 100%; border-collapse: collapse; margin-top: 6px; margin-bottom: 10px;'>";
                    $html .= "<thead><tr style='background-color: #f2f2f2;'><th style='width: 10%; text-align: center;'>Ordem</th><th>Posto / Nome Completo</th><th style='width: 18%; text-align: center;'>Média¹</th></tr></thead><tbody>";

                    foreach ($gProp['membros_propostos'] as $idx => $m) {
                        $ordem = ($idx + 1) . "º";
                        $media = ($m['pontuacao'] !== null) ? number_format((float)$m['pontuacao'], 3, ',', '.') : '—';
                        $html .= "<tr>";
                        $html .= "<td style='text-align: center; font-weight: bold;'>{$ordem}</td>";
                        $html .= "<td><strong>" . htmlspecialchars($m['posto_atual']) . "</strong> " . htmlspecialchars($m['nome']) . "</td>";
                        $html .= "<td style='text-align: center; font-weight: bold;'>{$media}</td>";
                        $html .= "</tr>";
                    }

                    $html .= "</tbody></table>";
                    $html .= "<small style='display: block; margin-top: -5px; margin-bottom: 10px;'>[1] Análise do Conceito Profissional através da média aritmética das fichas (FAD + FP + FEO).</small>";

                    $qtdCandidatos = count($gProp['membros_propostos']);
                    $numFormatado = str_pad($qtdCandidatos, 2, '0', STR_PAD_LEFT);

                    $html .= "<p style='text-align: justify; text-indent: 2cm; margin-top: 10px;'>";
                    $html .= "Entre esses <strong>{$numFormatado}</strong> candidatos acima relacionados, o Governador do Estado de Mato Grosso apreciará livremente o mérito dos oficiais contemplados nesta proposta e decidir-se-á pelos nomes a serem promovidos, tendo em consideração ao que se encontra previsto no Artigo 40 da Lei nº 10.076 de 31 de março de 2014, c/c os Artigos 40 e 41 do Decreto nº 2.268 de 10 de abril de 2014.";
                    $html .= "</p>";
                } else {
                    $html .= "<div style='margin-left: 0.5cm; margin-bottom: 15px;'>";
                    foreach ($gProp['membros_propostos'] as $m) {
                        $html .= "<p style='margin-bottom: 3px;'>" . htmlspecialchars($m['posto_atual']) . " " . htmlspecialchars($m['nome']) . "</p>";
                    }
                    $html .= "</div>";
                }
            }
        }
    }

    $html .= "<p style='text-align: justify; text-indent: 2cm; margin-top: 20px;'>";
    $html .= "Diante das propostas, caberá a Secretaria das Comissões de Promoções da Diretoria de Gestão de Pessoas do CBMMT confeccionar as minutas dos Decretos e encaminhá-las ao Governador do Estado para assinatura e publicação em Diário Oficial do Estado.";
    $html .= "</p>";

    // =========================================================================
    // ENCERRAMENTO E ASSINATURAS
    // =========================================================================
    $html .= "<p style='text-align: justify; text-indent: 2cm; margin-top: 25px;'>";
    $html .= "E como nada mais houve a tratar, mandou o Senhor Presidente da {$siglaComissao} encerrar os trabalhos e lavrar a presente ata, que lida e achada conforme, segue assinada pelos membros presentes.";
    $html .= "</p>";

    $html .= "<div style='text-align: center; margin-top: 30px; margin-bottom: 40px;'>";
    $html .= "Quartel do Comando Geral em Cuiabá-MT, {$dataExtenso}.";
    $html .= "</div>";

    $html .= "<table style='width: 100%; border: none; text-align: center; margin-top: 20px;'>";
    $html .= "<tr>";
    $html .= "<td style='width: 50%; padding-bottom: 30px;'>__________________________________________<br><strong>Presidente da {$siglaComissao}</strong><br>Membro Nato / Presidente</td>";
    $html .= "<td style='width: 50%; padding-bottom: 30px;'>__________________________________________<br><strong>Vice-Presidente da {$siglaComissao}</strong><br>Membro Nato / Vice-Presidente</td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td style='width: 50%; padding-bottom: 30px;'>__________________________________________<br><strong>Secretário da {$siglaComissao}</strong><br>Membro Nato / Secretário</td>";
    $html .= "<td style='width: 50%; padding-bottom: 30px;'>__________________________________________<br><strong>Membro Efetivo</strong></td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td colspan='2' style='padding-bottom: 20px;'>__________________________________________<br><strong>Membro Efetivo</strong></td>";
    $html .= "</tr>";
    $html .= "</table>";

    echo json_encode(['success' => true, 'html' => $html]);
} catch (Throwable $e) {
    if (ob_get_length()) ob_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno ao gerar ata: ' . $e->getMessage()]);
}
