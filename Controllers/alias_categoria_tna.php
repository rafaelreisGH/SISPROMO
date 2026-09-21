<?php
/*Este arquivo Controller serve apenas para renomear os postos e graduações
de modo que o VARCHAR do BD apareça no HTML mais amigavelmente
*/

function alias_categoria($categoria)
{
    switch ($categoria) {
        case 'inciso1':
            return 'I - LTSPF/LTS';
            break;
        case 'inciso2':
            return 'II - LTIP';
            break;
        case 'inciso3':
            return 'III - Desempenho de função/cargo civil, não eletivo';
            break;
        case 'inciso4':
            return 'IV - Desertor/extraviado';
            break;
        case 'inciso5':
            return 'V - Prisão preventiva';
            break;
        case 'inciso6':
            return 'VI - Cumprimento de pena restritiva de liberdade';
            break;
    }
}
