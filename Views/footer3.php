<script src="https://code.jquery.com/jquery-3.7.0.js"></script>

<!-- 2º Carregar Bootstrap 5 JS Bundle (inclui Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- 3º Carregar Scripts do DataTables -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
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
    }, 60000); // Verifica a cada 1 minuto
</script>

</body>

</html>