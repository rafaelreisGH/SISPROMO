<?php

if (!empty($_POST)) {
    $militar_id = filter_input(INPUT_POST, "id", FILTER_SANITIZE_NUMBER_INT);
    $categoria = filter_input(INPUT_POST, "categoria", FILTER_SANITIZE_SPECIAL_CHARS);
    $data_inicio = filter_input(INPUT_POST, "data_inicio", FILTER_SANITIZE_SPECIAL_CHARS);
    $data_fim = filter_input(INPUT_POST, "data_fim", FILTER_SANITIZE_SPECIAL_CHARS);

    /*----------------------------------------------------*/
    //pegar no BD dados do militar selecionado
    $stmt = $conn->prepare("SELECT nome, posto_grad_mil FROM militar WHERE id = :id");
    $stmt->bindParam(':id', $militar_id, PDO::PARAM_INT);
    $stmt->execute();
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($resultado) {
        $nome = $resultado["nome"];
        $posto_grad = $resultado["posto_grad_mil"];
    }
    /*----------------------------------------------------*/

    /*----------------------------------------------------*/
    //
    function validar_periodo($data1, $data2, $categoria)
    {
        // Add your logic here
        $data_0 = date_create($data1);
        $data_1 = date_create($data2);
        $intervalo = date_diff($data_0, $data_1);
        $aux =  (int)$intervalo->format('%R%a');
        if ($aux <= 0) {
            return false;
        } else if ($categoria == "inciso1") {
            if ($aux <= 30) {
                return false;
            }
        } else {

            return true;
        }
    }

    $periodo_valido = validar_periodo($data_inicio, $data_fim, $categoria);
    if($periodo_valido){
        echo "Período válido!</BR>";
        
    } else {
        echo "Período INválido!</BR>";

    }





    //consultar no banco se já há um lançamento para o mesmo evento

}
