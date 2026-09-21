<?php
// controle_de_sessao.php
require_once __DIR__ . '/session_bootstrap.php';

// tempo limite de inatividade: 15 minutos
$tempo_limite = 900;

if (isset($_SESSION['start']) && (time() - $_SESSION['start'] > $tempo_limite)) {
    // Log do logout por inatividade
    require_once __DIR__ . '/../Logger/LoggerFactory.php';
    $logger = LoggerFactory::createLogger();
    $logger->info('Deslogado por inatividade', [
        'id' => $_SESSION['id'] ?? null,
        'usuario' => $_SESSION['nome'] ?? null,
        'email' => $_SESSION['email'] ?? null,
        'perfil' => $_SESSION['nivel_de_acesso'] ?? null
    ]);

    session_unset();
    session_destroy();
    header('Location: ../index.php?erro=2');
    exit();
}

// Atualiza tempo da sessão somente se não for chamada AJAX
if ($_SERVER['REQUEST_METHOD'] !== 'GET' || !isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    $_SESSION['start'] = time();
}

// Verifica login
if (!isset($_SESSION['logado'])) {
    header('Location: ../index.php?erro=2');
    exit();
}