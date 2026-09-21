<?php

require_once '../ConexaoDB/conexao.php';
$conn = Conexao::getConexao();

$id_militar = $_POST['id']; //id do militar
$semestre = $_POST['semestre']; //semestre da fad
$ano = $_POST['ano']; //ano da fad
$nota = $_POST['notaCalculada']; //nota atribuída
$funcaoDesempenhada = $_POST['funcaoDesempenhada']; //função desempenhada
$postoGradNoPerioAvaliado = $_POST['postoGradNoPerioAvaliado']; //posto/graduação no período avaliado

//quesitos
//
$produtividade = $_POST['produtividade']; //quesito produtividade
$lideranca = $_POST['lideranca']; //quesito lideranca
$decisao = $_POST['decisao']; //quesito decisao
$interpessoal = $_POST['interpessoal']; //quesito interpessoal
$saude = $_POST['saude']; //quesito saude
$planejamento = $_POST['planejamento']; //quesito planejamento
$disciplina = $_POST['disciplina']; //quesito disciplina
$disposicao = $_POST['disposicao']; //quesito disposicao
$assiduidade = $_POST['assiduidade']; //quesito assiduidade
$preparo = $_POST['preparo']; //quesito preparo
//
//quesitos
$justificativa = $_POST['textoJustificativa']; //justificativa
//********************************************** */

$stmt = $conn->prepare("SELECT id, semestre, ano FROM fad WHERE militar_id = '" . $id_militar . "'"
    . "AND semestre = '" . $semestre . "' "
    . "AND ano = '" . $ano . "'");
$resultado = $stmt->execute();

//declaração de variáveis auxiliares
$aux_ano;
$aux_sem;

if ($resultado = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $aux_ano = $resultado['ano'];
    $aux_sem = $resultado['semestre'];
    $id_fad = $resultado['id'];
    // atualizar o sql com os novos campos
    if (($aux_ano == $ano) && ($aux_sem == $semestre)) {
        $stmt = $conn->prepare("UPDATE fad SET ano = :ano, semestre = :semestre, nota = :nota, militar_id = :militar_id, funcao_desempenhada = :funcao, grau_hierarquico_na_epoca = :posto, produtividade = :produtividade, lideranca = :lideranca, decisao = :decisao, relacionamento_interpessoal = :interpessoal, saude_fisica = :saude, planejamento = :planejamento, disciplina = :disciplina, disposicao_para_o_trabalho = :disposicao, assiduidade = :assiduidade, preparo_intelectual = :preparo, justificativa = :justificativa WHERE id = :id");
        $stmt->execute(array(
            ':id' => $id_fad,
            ':ano' => $ano,
            ':semestre' => $semestre,
            ':nota' => $nota,
            ':militar_id' => $id_militar,
            ':funcao' => $funcaoDesempenhada,
            ':posto' => $postoGradNoPerioAvaliado,
            ':produtividade' => $produtividade,
            ':lideranca' => $lideranca,
            ':decisao' => $decisao,
            ':interpessoal' => $interpessoal,
            ':saude' => $saude,
            ':planejamento' => $planejamento,
            ':disciplina' => $disciplina,
            ':disposicao' => $disposicao,
            ':assiduidade' => $assiduidade,
            ':preparo' => $preparo,
            ':justificativa' => $justificativa,
        ));
        header('Location:../Views/teste_fad.php?militar_id=' . $id_militar . '&erro=1&nota=' . $nota . '&semestre=' . $semestre . '&id=' . $semestre . '&ano=' . $ano . '');
    }
} else {
    $stmt = $conn->prepare("INSERT INTO fad (ano, semestre, nota, militar_id, funcao_desempenhada, grau_hierarquico_na_epoca, produtividade, lideranca, decisao, relacionamento_interpessoal, saude_fisica, planejamento, disciplina, disposicao_para_o_trabalho, assiduidade, preparo_intelectual, justificativa) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $stmt->bindParam(1, $ano);
    $stmt->bindParam(2, $semestre);
    $stmt->bindParam(3, $nota);
    $stmt->bindParam(4, $id_militar);
    $stmt->bindParam(5, $funcaoDesempenhada);
    $stmt->bindParam(6, $postoGradNoPerioAvaliado);
    $stmt->bindParam(7, $produtividade);
    $stmt->bindParam(8, $lideranca);
    $stmt->bindParam(9, $decisao);
    $stmt->bindParam(10, $interpessoal);
    $stmt->bindParam(11, $saude);
    $stmt->bindParam(12, $planejamento);
    $stmt->bindParam(13, $disciplina);
    $stmt->bindParam(14, $disposicao);
    $stmt->bindParam(15, $assiduidade);
    $stmt->bindParam(16, $preparo);
    $stmt->bindParam(17, $justificativa);
    $stmt->execute();

    if ($stmt) {
        header('Location:../Views/teste_fad.php?nota=' . $nota . '&militar_id=' . $id_militar . '&semestre=' . $semestre . '&id=' . $semestre . '&ano=' . $ano . '');
    }
}
