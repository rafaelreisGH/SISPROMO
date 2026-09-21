<?php
require_once __DIR__ . '/session_bootstrap.php';
require_once '../ConexaoDB/conexao.php';
$conn = Conexao::getConexao();
require_once '../Controllers/validar_senha.php';

$senhaNova = filter_input(INPUT_POST, 'senhaNova', FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW);

// Valida a senha
$erroValidacao = validarsenha($senhaNova);
if ($erroValidacao !== true) {
    $_SESSION['mensagem_erro'] = $erroValidacao;
    header('Location: ../Views/pagina_muda_senha.php');
    exit;
}

$senhaHash = password_hash($senhaNova, PASSWORD_DEFAULT);
$user_email = $_SESSION['email'];

try {
    $stmt = $conn->prepare("UPDATE usuarios SET senha = ?, senha_reset = true WHERE email = ?");
    $stmt->bindParam(1, $senhaHash, PDO::PARAM_STR);
    $stmt->bindParam(2, $user_email, PDO::PARAM_STR);
    $stmt->execute();

    // Se a atualização for bem-sucedida
    if ($stmt) {
        // Destrói a sessão atual para forçar novo login
        session_unset();
        session_destroy();

        // Cria uma nova sessão só para exibir a mensagem de sucesso na página de login
        require_once __DIR__ . '/session_bootstrap.php';
        $_SESSION['mensagem_sucesso'] = "Senha alterada com sucesso. Faça login com a nova senha.";

        header('Location: ../index.php'); // index.php é a tela de login
        exit;
    }
} catch (PDOException $ex) {
    echo "Erro ao alterar senha: " . $ex->getMessage();
}


