<?php
/*Este arquivo Controller serve apenas para renomear os postos e graduações
de modo que o VARCHAR do BD apareça no HTML mais amigavelmente
*/

function alias_quadro($quadro_id)
{
    switch ((string)$quadro_id) {
        case '1':
            return 'QOBM';
        case '2':
            return 'QCOBM';
        case '3':
            return 'QOSBM';
        case '4':
            return 'QPBM';
        default:
            return 'N/D';
    }
}
