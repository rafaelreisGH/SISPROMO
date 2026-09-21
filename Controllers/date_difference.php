<?php
//https://www.php.net/manual/en/function.date-diff.php

/**
 * Calcula a diferença entre duas datas e retorna o resultado em anos (formato fracionado).
 *
 * @param string $date_1 Data mais recente (ex: data da lista de promoção)
 * @param string $date_2 Data mais antiga (ex: data da última promoção)
 * @return float A diferença em anos.
 */
function dateDifference($date_1, $date_2)
{
    $datetime1 = date_create($date_1);
    $datetime2 = date_create($date_2);
   
    $interval = date_diff($datetime1, $datetime2);
   
    // Pega a diferença total em dias. A propriedade "days" do objeto DateInterval
    // já calcula o total de dias transcorridos.
    $total_dias = $interval->days;

    // Converte o total de dias para anos (formato float)
    $diferenca_em_anos = $total_dias / 365.25;
   
    return $diferenca_em_anos;
}