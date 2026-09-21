<?php
/*Este arquivo Controller serve apenas para renomear os postos e graduações
de modo que o VARCHAR do BD apareça no HTML mais amigavelmente
*/

function alias_posto_grad($posto_grad)
{
    switch ($posto_grad) {
        case 'CEL BM':
            return 'Coronel';
            break;
        case 'TC BM':
            return 'Tenente Coronel';
            break;
        case 'MAJ BM':
            return 'Major';
            break;
        case 'CAP BM':
            return 'Capitão';
            break;
        case '1º TEN BM':
            return '1º Tenente';
            break;
        case '2º TEN BM':
            return '2º Tenente';
            break;
        case 'ASP OF BM':
            return 'Aspirante-a-oficial';
            break;
        case 'CAD BM':
            return 'Cadete';
            break;
        case 'AL OF BM':
            return 'Aluno oficial CAOC';
            break;
        case 'ST BM':
            return 'Subtenente';
            break;
        case '1º SGT BM':
            return '1º Sargento ';
            break;
        case '2º SGT BM':
            return '2º Sargento';
            break;
        case '3º SGT BM':
            return '3º Sargento';
            break;
        case 'CB BM':
            return 'Cabo';
            break;
        case 'SD BM':
            return 'Soldado';
            break;
        case 'AL SD BM':
            return 'Aluno Soldado';
            break;
    }
}
