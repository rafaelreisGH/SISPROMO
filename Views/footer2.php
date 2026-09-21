<?php
/**
 * footer2.php - Rodapé padrão para Views que utilizam Bootstrap 5 (header2.php).
 *
 * Inclui:
 * - Bootstrap 5 JS (bundle com Popper)
 * - Toast.js centralizado (módulo de notificações)
 * - Script de verificação de sessão (AJAX periódico com toast)
 *
 * ATENÇÃO: NÃO utilize este footer em Views que ainda usam o header.php (Bootstrap 3 legado).
 */
?>

<!-- Bootstrap 5 JS Bundle (inclui Popper.js) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Módulo centralizado de notificações Toast (Bootstrap 5) -->
<script src="../js/meus_scripts/toast.js"></script>

<script>
/**
 * Verificação periódica de sessão via AJAX.
 * Se a sessão expirar, exibe um toast de aviso e redireciona para o login.
 */
setInterval(() => {
    fetch('../Controllers/verifica_sessao.php')
        .then(response => response.text())
        .then(data => {
            if (data.trim() === 'expirada') {
                showToast('Sua sessão expirou. Você será redirecionado para o login.', 'warning', 5000);
                setTimeout(() => {
                    window.location.href = '../index.php?erro=2';
                }, 5000);
            }
        })
        .catch(error => {
            console.error('Erro ao verificar sessão:', error);
        });
}, 60000); // Verifica a cada 1 minuto
</script>

</body>
</html>