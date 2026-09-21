<?php
include_once '../Controllers/controle_de_sessao.php';

require_once '../ConexaoDB/conexao.php';
$conn = Conexao::getConexao();

$id_militar = $_POST['id'];
$semestre = $_POST['semestre'];
$ano = $_POST['ano'];
$postoGradNoPerioAvaliado = filter_input(INPUT_POST, 'postoGradNoPerioAvaliado', FILTER_UNSAFE_RAW);
$postoGradNoPerioAvaliado = htmlspecialchars($postoGradNoPerioAvaliado, ENT_QUOTES | ENT_HTML5);
$pontuacao = filter_input(INPUT_POST, 'pontuacao', FILTER_VALIDATE_FLOAT);

$consulta = $conn->query("SELECT id, semestre, ano FROM fad WHERE militar_id = '" . $id_militar . "'"
    . "AND semestre = '" . $semestre . "' "
    . "AND ano = '" . $ano . "'");
$resultado = $consulta->fetch(PDO::FETCH_ASSOC);
if (!empty($resultado)) {
    $aux_ano = $resultado['ano'];
    $aux_sem = $resultado['semestre'];
    $id_militar_fad = $resultado['id'];
    if (($aux_ano == $ano) && ($aux_sem == $semestre)) {
        // $stmt = $conn->prepare("UPDATE fad SET ano = :ano, semestre = :semestre, nota = :nota, militar_id = :militar_id WHERE id = :id");
        // $stmt->execute(array(
        //     ':id' => $id_militar_fad,
        //     ':ano' => $ano,
        //     ':semestre' => $semestre,
        //     ':nota' => $pontuacao,
        //     ':militar_id' => $id_militar,
        // ));

        header('Location:../Views/view_fad.php?militar_id=' . $id_militar . '&msg=fad_existente');
        exit();
    }
} else {
    $stmt = $conn->prepare("INSERT INTO fad (ano, semestre, nota, militar_id, grau_hierarquico_na_epoca) VALUES (?,?,?,?,?)");
    $stmt->bindParam(1, $ano);
    $stmt->bindParam(2, $semestre);
    $stmt->bindParam(3, $pontuacao);
    $stmt->bindParam(4, $id_militar);
    $stmt->bindParam(5, $postoGradNoPerioAvaliado);
    $stmt->execute();
    if ($stmt) {

        // Log de inserção de FAD
        require_once __DIR__ . '/../Logger/LoggerFactory.php';
        $logger = LoggerFactory::createLogger();
        $logger->info('Usuário inseriu FAD', [
            'id' => $_SESSION['id'],
            'usuario' => $_SESSION['nome'],
            'email' => $_SESSION['email'],
            'perfil' => $_SESSION['nivel_de_acesso'],
            'sujeito' => $id_militar
        ]);

        header('Location:../Views/view_fad.php?&militar_id=' . $id_militar . '&msg=fad_inserida');
        exit();
    }
}
