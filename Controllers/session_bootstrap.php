<?php
/**
 * Bootstrap centralizado de sessão do SAP.
 * 
 * Este arquivo DEVE ser incluído no topo de qualquer script que precise
 * de sessão. Ele substitui chamadas diretas a session_start().
 * 
 * Responsabilidades:
 * - Configura cookies de sessão com flags de segurança (HttpOnly, SameSite)
 * - Inicia a sessão de forma segura (evitando session_start() duplicado)
 * - Valida o fingerprint do navegador (anti-hijacking via User-Agent)
 */

if (session_status() === PHP_SESSION_NONE) {
    // Configura flags de segurança no cookie de sessão ANTES de iniciar
    session_set_cookie_params([
        'lifetime' => 0,              // Cookie expira ao fechar o navegador
        'path'     => '/',             // Disponível em todo o domínio
        'domain'   => '',              // Domínio atual
        'secure'   => isset($_SERVER['HTTPS']),  // Transmite só via HTTPS quando disponível
        'httponly'  => true,           // Impede acesso via JavaScript (anti-XSS)
        'samesite'  => 'Strict'        // Bloqueia envio cross-site (anti-CSRF)
    ]);

    session_start();
}

// Validação do fingerprint do navegador (anti-hijacking)
// Verifica se o User-Agent atual é idêntico ao registrado no login
if (isset($_SESSION['_fingerprint'])) {
    $fingerprintAtual = hash('sha256', $_SERVER['HTTP_USER_AGENT'] ?? '');

    if (!hash_equals($_SESSION['_fingerprint'], $fingerprintAtual)) {
        // Fingerprint divergente — possível sessão sequestrada
        session_unset();
        session_destroy();
        
        // Trata requisição AJAX para não quebrar contrato da API
        if (
            (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
            (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
        ) {
            http_response_code(401);
            echo json_encode(['error' => 'Sessão inválida ou expirada.']);
            exit();
        }

        header('Location: ../index.php?erro=2');
        exit();
    }
}
