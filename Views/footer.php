<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
<script>
setInterval(() => {
    fetch('../Controllers/verifica_sessao.php')
        .then(response => response.text())
        .then(data => {
            if (data.trim() === 'expirada') {
                alert('Sua sessão expirou. Você será redirecionado para o login.');
                window.location.href = '../index.php?erro=2';
            }
        })
        .catch(error => {
            console.error('Erro ao verificar sessão:', error);
        });
}, 5000); // Verifica a cada 1 minuto
</script>

</body>
</html>