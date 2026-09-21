<?php
function pega_intersticio($posto_grad, $conn)
{
    $consulta =  $conn->query("SELECT * FROM intersticio")->fetch(PDO::FETCH_ASSOC); 
    switch ($posto_grad) {
        case 'CEL BM':
            return 0;
        case 'TC BM':
            return $consulta['tc_cel'];
        case 'MAJ BM':
            return $consulta['maj_tc'];
        case 'CAP BM':
            return $consulta['cap_maj'];
        case '1º TEN BM':
            return $consulta['1ten_cap'];
        case '2º TEN BM':
            return $consulta['2ten_1ten'];
        case 'ASP OF BM':
            return $consulta['asp_2ten'];
        case 'AL OF BM':
            return 0;
        case 'ST BM':
            return $consulta['st_2ten'];
        case '1º SGT BM':
            return $consulta['1sgt_st'];
        case '2º SGT BM':
            return $consulta['2sgt_1sgt'];
        case '3º SGT BM':
            return $consulta['3sgt_2sgt'];
        case 'CB BM':
            return $consulta['cb_3sgt'];
        case 'SD BM':
            return $consulta['sd_cb'];
    }
}

function tem_intersticio($lq_ano, $aux_cumprimento_intersticio)
{
    // Converte as strings para objetos DateTime
    $lq_ano = new DateTime($lq_ano);
    $aux_cumprimento_intersticio = new DateTime($aux_cumprimento_intersticio);

    if ($aux_cumprimento_intersticio <= $lq_ano) {
        return true;
    } else {
        return false;
    }
}