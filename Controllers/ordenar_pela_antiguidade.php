<?php

function ordenar(array $vetor, $antiguidade_atual, $nova_antiguidade)
{
    //-------------------------------------------------------------------//
    //Encontrar a chave (id do militar) correspondente à antiguidade atual
    $militar_id = array_search($antiguidade_atual, $vetor);

    //declaração de vetor auxiliar para guardar informações
    $auxiliar = array();
    $auxiliar += [$militar_id => $antiguidade_atual];

    //remove-se o militar da primeira posição do vetor original
    unset($vetor[$militar_id]);

    //declaração do array final (que será dado o retorno)
    $vetor2 = array();
    //-------------------------------------------------------------------//

    //-------------------------------------------------------------------//
    if ($antiguidade_atual > $nova_antiguidade) {
        //Joga-se os dados dos dois vetores no vetor final. Um após o outro.
        foreach ($auxiliar as $key => $valor) {
            $vetor2 += ["{$key}" => $valor];
        }
        foreach ($vetor as $key => $valor) {
            $vetor2 += ["{$key}" => $valor];
        }
        //unset em variável que não se utiliza mais
        unset($auxiliar);
        unset($vetor);
        //-------------------------------------------------------------------//
        //!!!!!!!!!!!!!!!!!!!!!!!CÓDIGOS IGUAIS ATÉ AQUI !!!!!!!!!!!!!!!!!!!!
        //-------------------------------------------------------------------//
        //REORDENAÇÃO DA ANTIGUIDADE
        foreach ($vetor2 as $key => &$valor) {
            $valor = $nova_antiguidade;
            $nova_antiguidade++;
        }
    } else {
        //-------------------------------------------------------------------//
        //Joga-se os dados dos dois vetores no vetor final. Um após o outro.
        foreach ($vetor as $key => $valor) {
            $vetor2 += ["{$key}" => $valor];
        }
        foreach ($auxiliar as $key => $valor) {
            $vetor2 += ["{$key}" => $valor];
        }
        //unset em variável que não se utiliza mais
        unset($auxiliar);
        unset($vetor);
        //-------------------------------------------------------------------//
        //!!!!!!!!!!!!!!!!!!!!!!!CÓDIGOS IGUAIS ATÉ AQUI !!!!!!!!!!!!!!!!!!!!
        //-------------------------------------------------------------------//
        //REORDENAÇÃO DA ANTIGUIDADE
        foreach ($vetor2 as $key => &$valor) {
            $valor = $antiguidade_atual;
            $antiguidade_atual++;
        }
    }
    return $vetor2;
}
