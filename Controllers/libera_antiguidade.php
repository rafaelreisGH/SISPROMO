<?php

function liberarAntiguidade(array $vetor, int $numero_liberado)
{
    /*
    (1) Cria-se um vetor ($chaves) para armazenar todas as chaves presentes no $vetor.
    (2) Descarta-se o primeiro elemento de $chaves. Assim tanto $chaves quanto $vetor vão possuir a mesma quantidade de elementos, requisito para o array_combine.
    */

    $chaves = array_keys($vetor);//----->>(1)
    array_shift($chaves);//----->>(2)
    array_shift($vetor);//descarta-se o primeiro elemento (militar que será inativado)
    foreach ($vetor as &$value) {
        $value = $numero_liberado;
        $numero_liberado++;
    }
    $vetor = array_combine($chaves, $vetor);

    return $vetor;
}
