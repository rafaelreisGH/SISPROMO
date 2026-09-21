<?php

require_once '../ConexaoDB/conexao.php';
$conn = Conexao::getConexao();
// Função de validação de senha
require_once '../Controllers/validar_senha.php';

// Captura dos dados
$nome  = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS);
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$senha_original = filter_input(INPUT_POST, 'senha', FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW);
$cpf = filter_input(INPUT_POST, 'cpf', FILTER_SANITIZE_NUMBER_INT);

// Inicialização de erros
$erros = [];

// Validação da senha
$senha_valida = validarSenha($senha_original);
if ($senha_valida !== true) {
    $erros['erro_senha'] = $senha_valida;
}

// Validação do e-mail
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $erros['email_invalido'] = 1;
}

// Verifica se o nome já existe
$stmt_nome = $conn->prepare("SELECT 1 FROM usuarios WHERE nome = :nome LIMIT 1");
$stmt_nome->bindParam(':nome', $nome);
$stmt_nome->execute();
if ($stmt_nome->fetch()) {
    $erros['erro_nome'] = 1;
}

// Verifica se o e-mail já existe
$stmt_email = $conn->prepare("SELECT 1 FROM usuarios WHERE email = :email LIMIT 1");
$stmt_email->bindParam(':email', $email);
$stmt_email->execute();
if ($stmt_email->fetch()) {
    $erros['erro_email'] = 1;
}

function validarCPF($cpf) {
    // Remove tudo que não é número
    $cpf = preg_replace('/[^0-9]/', '', $cpf);

    // Verifica tamanho e repetições
    if (strlen($cpf) != 11 || preg_match('/(\d)\1{10}/', $cpf)) {
        return false;
    }

    // Valida dígitos verificadores
    for ($t = 9; $t < 11; $t++) {
        for ($d = 0, $c = 0; $c < $t; $c++) {
            $d += $cpf[$c] * (($t + 1) - $c);
        }
        $d = ((10 * $d) % 11) % 10;
        if ($cpf[$c] != $d) {
            return false;
        }
    }

    return true;
}

// Validação
if (!validarCPF($cpf)) {
    $erros['cpf_invalido'] = 1;
}

// Verifica se o CPF já existe
$stmt_cpf = $conn->prepare("SELECT 1 FROM usuarios WHERE cpf_usuario = :cpf LIMIT 1");
$stmt_cpf->bindParam(':cpf', $cpf);
$stmt_cpf->execute();
if ($stmt_cpf->fetch()) {
    $erros['erro_cpf'] = 1;
}

// Redireciona com os erros, se houver
if (!empty($erros)) {
    $query_string = http_build_query($erros);
    header("Location:../Views/inscrevase.php?$query_string");
    exit;
}

// Se chegou aqui, está tudo certo — cadastrar usuário

// Gera hash seguro da senha
$senha_hash = password_hash($senha_original, PASSWORD_DEFAULT);

// Insere no banco explicitando status como 0 (bloqueado) e senha_reset como 1
$stmt = $conn->prepare("
    INSERT INTO usuarios (nome, email, cpf_usuario, senha, senha_reset, status) 
    VALUES (:nome, :email, :cpf, :senha, :reset, :status)
");
$stmt->bindValue(':nome', $nome);
$stmt->bindValue(':email', $email);
$stmt->bindValue(':cpf', $cpf);
$stmt->bindValue(':senha', $senha_hash);
$stmt->bindValue(':reset', 1);   // senha já definida
$stmt->bindValue(':status', 0);  // bloqueado até aprovação do admin

if ($stmt->execute()) {
    header('Location:../Views/recem_cadastrado.php');
    exit;
} else {
    // Caso haja erro inesperado no insert (opcionalmente logar isso)
    header('Location:../Views/inscrevase.php?erro_interno=1');
    exit;
}
