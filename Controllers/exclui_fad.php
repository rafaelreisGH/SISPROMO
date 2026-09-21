<?php
include_once '../Controllers/controle_de_sessao.php';

require_once '../ConexaoDB/conexao.php';
$conn = Conexao::getConexao();

// Verifica se os dados foram enviados corretamente
if (!isset($_POST['id_da_fad'], $_POST['militar_id'])) {
    header('Location: ../Views/view_fad.php?msg=erro_dados_invalidos');
    exit();
}

$id_da_fad = filter_input(INPUT_POST, 'id_da_fad', FILTER_VALIDATE_INT);
$militar_id = filter_input(INPUT_POST, 'militar_id', FILTER_VALIDATE_INT);

if (!$id_da_fad || !$militar_id) {
    header('Location: ../Views/view_fad.php?msg=erro_dados_invalidos');
    exit();
}

try {
    $stmt = $conn->prepare("DELETE FROM fad WHERE id = :id_da_fad AND militar_id = :militar_id");
    $stmt->bindParam(':id_da_fad', $id_da_fad, PDO::PARAM_INT);
    $stmt->bindParam(':militar_id', $militar_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        header('Location: ../Views/view_fad.php?militar_id=' . $militar_id .'&msg=fad_excluida');
    } else {
        header('Location: ../Views/view_fad.php?msg=erro_ao_excluir');
    }
    exit();

} catch (PDOException $e) {
    // Aqui você pode fazer log do erro se desejar
    header('Location: ../Views/pagina_usuario.php?msg=erro_bd');
    exit();
}