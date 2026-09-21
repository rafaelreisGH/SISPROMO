<?php
include_once '../Controllers/controle_de_sessao.php';

require_once '../ConexaoDB/conexao.php';
$conn = Conexao::getConexao();

$documento_id = filter_input(INPUT_POST, 'documento_id', FILTER_VALIDATE_INT);
$documento_url = filter_input(INPUT_POST, 'documento_url', FILTER_VALIDATE_URL);
$militar_id = filter_input(INPUT_POST, 'militar_id', FILTER_VALIDATE_INT);

if (!$documento_id || !$documento_url || !$militar_id) {
    header('Location: ../Views/view_fad.php?militar_id=' . $militar_id . '&erro=invalido');
    exit();
}

try {
    $stmt = $conn->prepare('UPDATE fad SET caminho_do_arquivo = :caminho WHERE id = :id AND militar_id = :militar_id');
    $stmt->bindParam(':caminho', $documento_url);
    $stmt->bindParam(':id', $documento_id, PDO::PARAM_INT);
    $stmt->bindParam(':militar_id', $militar_id, PDO::PARAM_INT);

    if ($stmt->execute()) {

        // Log de inserção de LINK de FAD
        require_once __DIR__ . '/../Logger/LoggerFactory.php';
        $logger = LoggerFactory::createLogger();
        $logger->info('Usuário inseriu URL da FAD', [
            'id' => $_SESSION['id'],
            'usuario' => $_SESSION['nome'],
            'email' => $_SESSION['email'],
            'perfil' => $_SESSION['nivel_de_acesso'],
            'sujeito' => $militar_id
        ]);

        header('Location: ../Views/view_fad.php?militar_id=' . $militar_id . '&sucesso=upload');
        exit();
    } else {
        header('Location: ../Views/view_fad.php?militar_id=' . $militar_id . '&erro=bd');
        exit();
    }
} catch (PDOException $e) {
    // Em produção, você pode logar esse erro ao invés de exibir
    header('Location: ../Views/view_fad.php?militar_id=' . $militar_id . '&erro=excecao');
    exit();
}
