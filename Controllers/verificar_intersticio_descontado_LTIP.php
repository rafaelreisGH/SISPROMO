<?php

require_once '../Controllers/funcoes_intersticio.php';

// ----------------------- Execução do Cálculo ----------------------- //
// $aux_id = 52;
// $aux_posto_grad = 'MAJ BM';

$intersticio = pega_intersticio($posto_grad, $conn);
// echo "$intersticio anos de interstício mínimo.<br>";

$dias_afastados = obter_dias_afastados($militar_id, $posto_grad, $conn);
// echo "$dias_afastados dias afastados por LTIP no posto/grad = $posto_grad<br>";

$data_promocao = obter_ultima_promocao($militar_id, $conn);
if (!$data_promocao) {
    die("Erro: Não há registro de última promoção.");
}
// echo "Última promoção: $data_promocao<br>";

$data_inicio = new DateTime($data_promocao);
$dias_totais = calcular_dias_totais($data_inicio, $intersticio);
// echo "$dias_totais dias totais para a próxima promoção.<br>";

$dias_restantes = $dias_totais + $dias_afastados;
$data_futura = calcular_data_futura($data_inicio, $dias_restantes);
// echo "Dia em que completa o interstício (considerando a LTIP): " . $data_futura->format('Y-m-d') . "<br>";

$data_promocao_oficial = calcular_data_promocao($data_futura);
// echo "Data oficial de promoção: $data_promocao_oficial<br>";

salvar_no_banco_data_cumprimento_intersticio($militar_id, $data_futura, $conn);

// ----------------------- Funções ----------------------- //

function obter_dias_afastados($militar_id, $posto_grad, $conn)
{
    $stmt = $conn->prepare("SELECT SUM(qtde_de_dias) FROM tempo_nao_arregimentado WHERE militar_id = :a AND posto_grad_na_epoca = :b AND categoria = 'inciso2'");
    $stmt->bindParam(':a', $militar_id, PDO::PARAM_INT);
    $stmt->bindParam(':b', $posto_grad, PDO::PARAM_STR);
    $stmt->execute();
    return (int) $stmt->fetchColumn() ?? 0;
}

function obter_ultima_promocao($militar_id, $conn)
{
    $stmt = $conn->prepare("SELECT ultima_promocao FROM militar WHERE id = :id");
    $stmt->bindParam(':id', $militar_id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchColumn();
}

function calcular_dias_totais($data_inicio, $intersticio)
{
    $data_atual = clone $data_inicio;
    $data_atual->add(new DateInterval("P{$intersticio}Y"));
    return $data_inicio->diff($data_atual)->days;
}

function calcular_data_futura($data_inicio, $dias_restantes)
{
    $data_futura = clone $data_inicio;
    $data_futura->add(new DateInterval("P{$dias_restantes}D"));
    return $data_futura;
}

function calcular_data_promocao($data)
{
    $data = clone $data;
    $ano = $data->format('Y');

    $julho = new DateTime("$ano-07-02");
    $dezembro = new DateTime("$ano-12-02");

    if ($data <= $julho) {
        return $julho->format('Y-m-d');
    }
    if ($data <= $dezembro) {
        return $dezembro->format('Y-m-d');
    } else {
        $proximo_julho = new DateTime(($ano + 1) . "-07-02");
        return $proximo_julho->format('Y-m-d');
    }
}

function salvar_no_banco_data_cumprimento_intersticio($militar_id, $data_futura, $conn) 
{
    $data_formatada = $data_futura->format('Y-m-d'); // Armazena o valor formatado em uma variável
    $stmt = $conn->prepare("UPDATE militar SET data_cumprimento_intersticio = :data WHERE id = :id");
    $stmt->bindParam(':data', $data_formatada, PDO::PARAM_STR);
    $stmt->bindParam(':id', $militar_id, PDO::PARAM_INT);
    $stmt->execute();
}