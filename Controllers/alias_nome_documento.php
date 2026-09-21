<?php
/*Este arquivo Controller serve apenas para renomear os postos e graduações
de modo que o VARCHAR do BD apareça no HTML mais amigavelmente
*/

function alias_nome_documento($nome_documento)
{
    switch ($nome_documento) {
        case 'cert_1JE':
            return '1º J.E. - Cert. Neg. Crim. - 1º grau - TJ/MT';
            break;
        case 'cert_2JE':
            return '2º J.E. - Cert. Neg. Crim. - 2º grau - TJ/MT';
            break;
        case 'cert_1JF':
            return '1º J.F. - Cert. Neg. Crim. - TRF-1 - Sç. Jud. MT';
            break;
        case 'cert_2JF':
            return '2º J.F. - Cert. Neg. Crim. - TRF-1';
            break;
        case 'cert_tse':
            return 'C.E. - Cert. Neg. Crim. - Justiça Eleitoral';
            break;
        case 'fad':
            return 'F.A.D. - Ficha de Avaliação de Desempenho';
            break;
        case 'rta':
            return 'R.T.A. - Relatório de Tempo Arregimentado';
            break;
        case 'ais':
            return 'A.I.S. - Ata de Inspeção de Saúde';
            break;
        case 'fp':
            return 'F.P. - Ficha Profissional';
            break;
    }
}
