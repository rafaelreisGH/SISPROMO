<?php
/*Este arquivo Controller serve apenas para colocar a data no formato brasileiro
*/

function alias_ultima_promocao($ultima_promocao)
{
    // Verifica se o valor é uma string válida antes de processar
    if ($ultima_promocao === null || $ultima_promocao === '') {
        return ''; // Retorna uma string vazia se o valor for nulo ou vazio
    }

    // converter a data vinda do BD MYSQL para formato brasileiro
    return implode("/", array_reverse(explode("-", $ultima_promocao)));
}