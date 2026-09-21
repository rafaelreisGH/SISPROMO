<?php

namespace App\Config;

/**
 * Catálogo centralizado de fundamentações normativas para impedimentos
 * Lei nº 10.076/2014 e Decreto Estadual nº 2.268/2014.
 */
class CatalogoImpedimentos
{
    public static function getCatalogo(): array
    {
        return [
            // =========================================================================
            // GRUPO 1: LEI Nº 10.076/2014 - ARTIGO 21 (REQUISITOS POSITIVOS)
            // =========================================================================
            'L21_I' => [
                'grupo'       => 'Lei 10.076/14 - Art. 21 (Requisitos Positivos)',
                'label'       => 'Art. 21, I - Ter interstício mínimo previsto no posto ou graduação',
                'titulo_ata'  => 'Não ter interstício mínimo previsto no posto ou graduação; (Art. 21, Inciso I, Lei 10.076/14)'
            ],
            'L21_II' => [
                'grupo'       => 'Lei 10.076/14 - Art. 21 (Requisitos Positivos)',
                'label'       => 'Art. 21, II - Estar no mínimo no conceito disciplinar "bom" (Praças)',
                'titulo_ata'  => 'Não estar no mínimo no conceito disciplinar "bom"; (Art. 21, Inciso II, Lei 10.076/14)'
            ],
            'L21_III' => [
                'grupo'       => 'Lei 10.076/14 - Art. 21 (Requisitos Positivos)',
                'label'       => 'Art. 21, III - Ser considerado possuidor de Conceito Moral',
                'titulo_ata'  => 'Não ser considerado possuidor de Conceito Moral; (Art. 21, Inciso III, Artigo 24 e Artigo 36, Inciso I da Lei 10.076/14)'
            ],
            'L21_IV' => [
                'grupo'       => 'Lei 10.076/14 - Art. 21 (Requisitos Positivos)',
                'label'       => 'Art. 21, IV - Ser considerado apto em Inspeção de Saúde',
                'titulo_ata'  => 'Não Ser considerado Apto em Inspeção de Saúde; (Art. 21, Inciso IV, Lei 10.076/14)'
            ],
            'L21_V' => [
                'grupo'       => 'Lei 10.076/14 - Art. 21 (Requisitos Positivos)',
                'label'       => 'Art. 21, V - Ser considerado apto na ADF ou TAF',
                'titulo_ata'  => 'Não Ser considerado apto na Avaliação de Desempenho Físico (ADF) ou Teste de Aptidão Física (TAF); (Art. 21, Inciso V, Lei 10.076/14)'
            ],
            'L21_VI' => [
                'grupo'       => 'Lei 10.076/14 - Art. 21 (Requisitos Positivos)',
                'label'       => 'Art. 21, VI - Ter avaliação de desempenho individual satisfatória (FAD)',
                'titulo_ata'  => 'Não ter avaliação de desempenho individual satisfatória; (Art. 21, Inciso VI, Lei 10.076/14)'
            ],
            'L21_VII' => [
                'grupo'       => 'Lei 10.076/14 - Art. 21 (Requisitos Positivos)',
                'label'       => 'Art. 21, VII - Ter conceito profissional satisfatório (FP/FEO)',
                'titulo_ata'  => 'Não ter conceito profissional satisfatório; (Art. 21, Inciso VII, Lei 10.076/14)'
            ],
            'L21_VIII' => [
                'grupo'       => 'Lei 10.076/14 - Art. 21 (Requisitos Positivos)',
                'label'       => 'Art. 21, VIII - Haver vaga',
                'titulo_ata'  => 'Não haver vaga; (Art. 21, Inciso VIII, Lei 10.076/14)'
            ],
            'L21_IX' => [
                'grupo'       => 'Lei 10.076/14 - Art. 21 (Requisitos Positivos)',
                'label'       => 'Art. 21, IX - Possuir os cursos ou estágios exigidos para promoção',
                'titulo_ata'  => 'Não possuir os cursos ou estágios exigidos para promoção; (Art. 21, Inciso IX, Lei 10.076/14)'
            ],
            'L21_X' => [
                'grupo'       => 'Lei 10.076/14 - Art. 21 (Requisitos Positivos)',
                'label'       => 'Art. 21, X - Ter tempo de serviço arregimentado regulamentar',
                'titulo_ata'  => 'Não ter tempo de serviço arregimentado, nos termos do regulamento desta lei; (Art. 21, Inciso X, Lei 10.076/14)'
            ],

            // =========================================================================
            // GRUPO 2: LEI Nº 10.076/2014 - ARTIGO 36 (IMPEDIMENTOS LEGAIS)
            // =========================================================================
            'L36_I' => [
                'grupo'       => 'Lei 10.076/14 - Art. 36 (Impedimentos Legais)',
                'label'       => 'Art. 36, I - Deixar de satisfazer as condições exigidas no Art. 21',
                'titulo_ata'  => 'Deixar de satisfazer as condições exigidas no Art. 21 desta lei; (Art. 36, Inciso I, Lei 10.076/14)'
            ],
            'L36_II' => [
                'grupo'       => 'Lei 10.076/14 - Art. 36 (Impedimentos Legais)',
                'label'       => 'Art. 36, II - Atingir idade limite para permanência no serviço ativo',
                'titulo_ata'  => 'Atingir, até a data das promoções, a idade limite para permanência no serviço ativo; (Art. 36, Inciso II, Lei 10.076/14)'
            ],
            'L36_III' => [
                'grupo'       => 'Lei 10.076/14 - Art. 36 (Impedimentos Legais)',
                'label'       => 'Art. 36, III - Cumprindo sentença penal ou preso à disposição da Justiça',
                'titulo_ata'  => 'Estiver cumprindo sentença penal ou estiver preso à disposição da justiça; (Art. 36, Inciso III, Lei 10.076/14)'
            ],
            'L36_IV' => [
                'grupo'       => 'Lei 10.076/14 - Art. 36 (Impedimentos Legais)',
                'label'       => 'Art. 36, IV - Condenação criminal definitiva durante cumprimento da pena',
                'titulo_ata'  => 'Sofrer condenação criminal definitiva nos termos da lei; (Art. 36, Inciso IV, Lei 10.076/14)'
            ],
            'L36_V' => [
                'grupo'       => 'Lei 10.076/14 - Art. 36 (Impedimentos Legais)',
                'label'       => 'Art. 36, V - Submetido a processo administrativo de natureza demissória',
                'titulo_ata'  => 'Estar Submetido a Processo Administrativo de Natureza Demissória; (Art. 36, Inciso V, Lei 10.076/14)'
            ],
            'L36_VI' => [
                'grupo'       => 'Lei 10.076/14 - Art. 36 (Impedimentos Legais)',
                'label'       => 'Art. 36, VI - Em gozo de Licença para Tratamento de Interesse Particular (LTIP)',
                'titulo_ata'  => 'Esteja em gozo de licença para tratamento de interesse particular (LTIP); (Art. 36, Inciso VI, Lei 10.076/14)'
            ],
            'L36_VII' => [
                'grupo'       => 'Lei 10.076/14 - Art. 36 (Impedimentos Legais)',
                'label'       => 'Art. 36, VII - Licença saúde de pessoa da família > 6 meses nos últimos 12 meses',
                'titulo_ata'  => 'Por motivo de gozo de licença para tratamento de saúde de pessoa da família por prazo superior a 06 meses; (Art. 36, Inciso VII, Lei 10.076/14)'
            ],
            'L36_VIII' => [
                'grupo'       => 'Lei 10.076/14 - Art. 36 (Impedimentos Legais)',
                'label'       => 'Art. 36, VIII - Privado ou suspenso do exercício de cargo ou função',
                'titulo_ata'  => 'For privado ou suspenso do exercício do cargo ou função; (Art. 36, Inciso VIII, Lei 10.076/14)'
            ],
            'L36_IX' => [
                'grupo'       => 'Lei 10.076/14 - Art. 36 (Impedimentos Legais)',
                'label'       => 'Art. 36, IX - Considerado ausente, extraviado ou desertor',
                'titulo_ata'  => 'For considerado ausente, extraviado ou desertor; (Art. 36, Inciso IX, Lei 10.076/14)'
            ],
            'L36_X' => [
                'grupo'       => 'Lei 10.076/14 - Art. 36 (Impedimentos Legais)',
                'label'       => 'Art. 36, X - Interditado judicialmente',
                'titulo_ata'  => 'Estiver interditado judicialmente; (Art. 36, Inciso X, Lei 10.076/14)'
            ],
            'L36_XI' => [
                'grupo'       => 'Lei 10.076/14 - Art. 36 (Impedimentos Legais)',
                'label'       => 'Art. 36, XI - Incapaz definitivamente para o serviço militar em JIS',
                'titulo_ata'  => 'Tenha sido considerado incapaz definitivamente para o serviço militar, em inspeção de saúde; (Art. 36, Inciso XI, Lei 10.076/14)'
            ],

            // =========================================================================
            // GRUPO 3: DECRETO ESTADUAL Nº 2.268/2014 - ARTIGO 78 (DOCUMENTAÇÃO)
            // =========================================================================
            'D78_I' => [
                'grupo'       => 'Decreto 2.268/14 - Art. 78 (Documentação Obrigatória)',
                'label'       => 'Art. 78, I - Não apresentar Ata de Inspeção de Saúde ou Certificado de Capacidade Física',
                'titulo_ata'  => 'Não apresentar documentação; (Art. 78, Inciso I c/c Inciso VIII, Decreto Estadual nº 2.268/14)'
            ],
            'D78_II' => [
                'grupo'       => 'Decreto 2.268/14 - Art. 78 (Documentação Obrigatória)',
                'label'       => 'Art. 78, II - Não apresentar Ficha Individual da ADF ou TAF',
                'titulo_ata'  => 'Não apresentar documentação; (Art. 78, Inciso II c/c Inciso VIII, Decreto Estadual nº 2.268/14)'
            ],
            'D78_V' => [
                'grupo'       => 'Decreto 2.268/14 - Art. 78 (Documentação Obrigatória)',
                'label'       => 'Art. 78, V - Não apresentar Ficha de Avaliação de Desempenho - FAD',
                'titulo_ata'  => 'Não apresentar documentação; (Art. 78, Inciso V c/c Inciso VIII, Decreto Estadual nº 2.268/14)'
            ],
            'D78_VI' => [
                'grupo'       => 'Decreto 2.268/14 - Art. 78 (Documentação Obrigatória)',
                'label'       => 'Art. 78, VI - Não apresentar Ficha Profissional (somente Tenentes-Coronéis)',
                'titulo_ata'  => 'Não apresentar documentação; (Art. 78, Inciso VI c/c Inciso VIII, Decreto Estadual nº 2.268/14)'
            ],
            'D78_P5' => [
                'grupo'       => 'Decreto 2.268/14 - Art. 78 (Documentação Obrigatória)',
                'label'       => 'Art. 78, § 5º - Não apresentar Certidões de Antecedentes Criminais',
                'titulo_ata'  => 'Não apresentar documentação; (Art. 78, § 5º c/c Inciso VIII, Decreto Estadual nº 2.268/14)'
            ]
        ];
    }

    public static function getOpcoesParaJs(): array
    {
        $catalogo = self::getCatalogo();
        $lista = [];
        foreach ($catalogo as $codigo => $item) {
            $lista[] = [
                'codigo' => $codigo,
                'grupo'  => $item['grupo'],
                'desc'   => $item['label']
            ];
        }
        return $lista;
    }
}
