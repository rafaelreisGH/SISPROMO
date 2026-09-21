<?php
// Garante que não haja saída anterior
ob_start();

// Inicia a sessão, se ainda não estiver ativa
require_once __DIR__ . '/session_bootstrap.php';

// Verifica se a sessão expirou
if (!isset($_SESSION['start']) || (time() - $_SESSION['start'] > 900)) {
    // Faz o log da inatividade
    if (isset($_SESSION['id'])) {
        require_once __DIR__ . '/../Logger/LoggerFactory.php';
        $logger = LoggerFactory::createLogger();
        $logger->info('Deslogado por inatividade (verificação AJAX)', [
            'id' => $_SESSION['id'],
            'usuario' => $_SESSION['nome'] ?? '',
            'email' => $_SESSION['email'] ?? '',
            'perfil' => $_SESSION['nivel_de_acesso'] ?? ''
        ]);
    }

    session_unset();
    session_destroy();
    echo "expirada";
    exit();
}

// Caso ainda esteja ativa
echo "ok";
exit();
