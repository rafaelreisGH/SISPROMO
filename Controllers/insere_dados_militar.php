<?php
require_once '../ConexaoDB/conexao.php';
$conn = Conexao::getConexao();
require_once __DIR__ . '/session_bootstrap.php';

// Inicialização de variáveis
$nome = isset($_POST['nome']) ? filter_input(INPUT_POST, 'nome', FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW) : "";
$posto_grad = $_POST['posto_grad'];
$quadro = $_POST['quadro'];
$cpf = isset($_POST['cpf']) ? trim($_POST['cpf']) : "";

// Variável para montar a string do header Location
$location = "Location:../Views/cadastrar_militar.php?";

// Validação do CPF
if (empty($cpf) || !preg_match('/^\d{11}$/', $cpf)) {
    header($location . 'erro=cpfInvalido');
    exit();
}

// Verifica se existe o mesmo CPF no banco de dados
$stmt = $conn->prepare('SELECT cpf FROM militar WHERE cpf = :cpf');
$stmt->bindParam(':cpf', $cpf);
$stmt->execute();
if ($stmt->fetch(PDO::FETCH_ASSOC)) {
    header($location . 'erro=cpfDuplicado');
    exit();
}

// Verifica se existe a mesma pessoa no BD (por nome, posto e quadro)
$stmt = $conn->prepare('SELECT nome, posto_grad_mil, quadro_id FROM militar WHERE nome = :nome AND posto_grad_mil = :posto_grad AND quadro_id = :quadro');
$stmt->execute(array(
    ':nome' => $nome,
    ':posto_grad' => $posto_grad,
    ':quadro' => $quadro
));
if ($stmt->fetch(PDO::FETCH_ASSOC)) {
    $location .= 'erro=1';
} else {
    // Selecionar maior antiguidade existente
    $stmt = $conn->query('SELECT MAX(antiguidade) AS max_antiguidade FROM militar');
    $antiguidade = $stmt->fetch();
    $antiguidade = $antiguidade['max_antiguidade'];

    // Inserir registro com CPF e demais dados
    $stmt = $conn->prepare('
        INSERT INTO militar (nome, posto_grad_mil, quadro_id, antiguidade, status, cpf)
        VALUES (:nome, :posto_grad, :quadro, :antiguidade, :status, :cpf)
    ');
    $resultado = $stmt->execute(array(
        ':nome' => $nome,
        ':posto_grad' => $posto_grad,
        ':quadro' => $quadro,
        ':antiguidade' => $antiguidade + 1,
        ':status' => 'ATIVO',
        ':cpf' => $cpf
    ));

    // Captura o ID recém-inserido
    $novoMilitarId = $conn->lastInsertId();

    if ($resultado) {
        // Log de cadastro de militar
        require_once __DIR__ . '/../Logger/LoggerFactory.php';
        $logger = LoggerFactory::createLogger();
        $logger->info('Usuário cadastrou militar', [
            'id' => $_SESSION['id'],
            'usuario' => $_SESSION['nome'],
            'email' => $_SESSION['email'],
            'perfil' => $_SESSION['nivel_de_acesso'],
            'sujeito' => $novoMilitarId
        ]);

        $location .= 'sucesso[]=' . urlencode($nome) . '&sucesso[]=' . urlencode($posto_grad) . '&sucesso[]=' . urlencode($quadro);
    } else {
        $location .= 'erroNoBD=1';
    }
}

header($location);
