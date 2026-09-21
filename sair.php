<?php
require_once __DIR__ . '/Controllers/session_bootstrap.php';

// Log do logout
require_once __DIR__ . '/Logger/LoggerFactory.php';
$logger = LoggerFactory::createLogger(); // Criação do logger
$logger->info('Usuário saiu do sistema', [
    'id' => $_SESSION['id'],
    'usuario' => $_SESSION['nome'],
    'email' => $_SESSION['email'],
    'perfil' => $_SESSION['nivel_de_acesso']
]);

session_unset();            // Limpa todas as variáveis de sessão
session_destroy();          // Destroi a sessão
//redirecionar o usuario para a página de login
header("Location: index.php");
exit();
