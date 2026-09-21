<?php
require_once '../ConexaoDB/conexao.php';
$conn = Conexao::getConexao();

/*
Obter o ano atual e o semestre atual para poder definir qual LQ estamos tratando.
*/
$ano_atual = date("Y");
$semestre_atual = date("m");
$semestre_atual = ($semestre_atual <= 7) ? "1" : "2";
//-----------------------------------------------------------------------------//

// $aux = verificaSeExisteLQv2($conn, $ano_atual, $semestre_atual, "SD BM");
// echo '<pre>';
// print_r($aux);
// echo '</pre>';


//posto/grad
//quadro
function verificaSeExisteLQv1($conn, $ano_atual, $semestre_atual)
{
    // Verifica se já existe LQ para o ano e semestre atuais
    $stmt = $conn->prepare("SELECT count(id) FROM pasta_promocional WHERE ano_processo_promocional = :ano_atual AND semestre_processo_promocional = :semestre_atual");

    $stmt->bindParam(':ano_atual', $ano_atual, PDO::PARAM_INT);
    $stmt->bindParam(':semestre_atual', $semestre_atual, PDO::PARAM_INT);

    $stmt->execute();
    $consulta = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($consulta[0]['count(id)'] == 0) {
        return null;
    } else {
        return $vetor = [
            'ano_atual' => $ano_atual,
            'semestre_atual' => $semestre_atual,
        ];
    }
}
function verificaSeExisteLQv2($conn, $ano_atual, $semestre_atual, $posto_grad)
{
    // Verifica se já existe LQ para o ano e semestre atuais
    $stmt = $conn->prepare("SELECT militar_id, militar.nome, militar.posto_grad_mil, militar.antiguidade, militar.quadro_id FROM pasta_promocional INNER JOIN militar ON pasta_promocional.militar_id = militar.id WHERE ano_processo_promocional = :ano_atual AND semestre_processo_promocional = :semestre_atual AND militar.posto_grad_mil = :posto_grad");

    $stmt->bindParam(':ano_atual', $ano_atual, PDO::PARAM_INT);
    $stmt->bindParam(':semestre_atual', $semestre_atual, PDO::PARAM_INT);
    $stmt->bindParam(':posto_grad', $posto_grad, PDO::PARAM_STR);

    $stmt->execute();
    $consulta = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($consulta)) {
        return null;
    } else {
        return $consulta;
    }
}
