<?php
ob_start();
include_once '../Controllers/controle_de_sessao.php';
require_once '../ConexaoDB/conexao.php';

try {
    $conn = Conexao::getConexao();
    $conn->beginTransaction();

    // 1. Dados de Entrada
    $criterio_posto_grad = isset($_POST['criterio_posto_grad']) ? strip_tags($_POST['criterio_posto_grad']) : '';
    $criterio_quadro = isset($_POST['criterio_quadro']) ? strip_tags($_POST['criterio_quadro']) : '';
    $militar_id = (int)($_POST['militar_id'] ?? 0);
    $nova_antiguidade = (int)($_POST['antiguidade_informada'] ?? 0);

    if ($nova_antiguidade <= 0 || $militar_id <= 0) {
        throw new Exception("Dados inválidos.");
    }

    // 2. Descobrir dados atuais do militar (para garantir o filtro correto)
    $stmt_mil = $conn->prepare("SELECT posto_grad_mil, quadro_id, antiguidade FROM militar WHERE id = :id");
    $stmt_mil->execute([':id' => $militar_id]);
    $militar_atual = $stmt_mil->fetch(PDO::FETCH_ASSOC);

    if (!$militar_atual) throw new Exception("Militar não encontrado.");

    // Se os critérios do POST vierem vazios, usa os do próprio militar
    $pg_filtro = $criterio_posto_grad ?: $militar_atual['posto_grad_mil'];
    $quadro_filtro = $criterio_quadro ?: $militar_atual['quadro_id'];

    // Filtro para afetar apenas o grupo correto
    $where_group = " WHERE posto_grad_mil = :pg AND quadro_id = :q AND status = 'ATIVO' ";
    $params_group = [':pg' => $pg_filtro, ':q' => $quadro_filtro];

    // --- ETAPA 1: COLOCAR O MILITAR NA POSIÇÃO DESEJADA (EMPURA A FILA) ---
    
    // A. Move militar para área temporária (evita erro de Unique Key)
    $stmt_temp = $conn->prepare("UPDATE militar SET antiguidade = :temp WHERE id = :id");
    $stmt_temp->execute([':temp' => -1 * $militar_id, ':id' => $militar_id]);

    // B. Abre espaço na nova posição (Empurra todos que estão >= nova posição para baixo)
    // Isso garante que o 1335 fique livre, jogando quem estava lá (ou depois) para frente temporariamente.
    $sql_abre = "UPDATE militar SET antiguidade = antiguidade + 1 
                 $where_group AND antiguidade >= :nova AND id != :id";
    $stmt_abre = $conn->prepare($sql_abre);
    $stmt_abre->execute(array_merge($params_group, [':nova' => $nova_antiguidade, ':id' => $militar_id]));

    // C. Define a nova posição do militar
    $stmt_set = $conn->prepare("UPDATE militar SET antiguidade = :nova WHERE id = :id");
    $stmt_set->execute([':nova' => $nova_antiguidade, ':id' => $militar_id]);

    // --- ETAPA 2: NORMALIZAÇÃO (FECHAR BURACOS) ---
    // Agora que o militar está no 1335, pode haver buracos antes ou depois dele, 
    // ou o "empurrão" acima criou lacunas indesejadas. Vamos renumerar tudo sequencialmente.

    // A. Descobrir qual é o menor número de antiguidade deste grupo.
    // (Ex: se a lista começa em 1329, queremos manter o início em 1329 e não resetar para 1)
    $stmt_min = $conn->prepare("SELECT MIN(antiguidade) as menor FROM militar $where_group");
    $stmt_min->execute($params_group);
    $row_min = $stmt_min->fetch(PDO::FETCH_ASSOC);
    $inicio_sequencia = $row_min['menor'] ? (int)$row_min['menor'] : 1;
    
    // Se a nova antiguidade for menor que o início atual, usamos ela como novo início
    if ($nova_antiguidade < $inicio_sequencia) {
        $inicio_sequencia = $nova_antiguidade;
    }

    // B. Reordena todo o grupo sequencialmente baseando-se na ordem atual
    // Usamos variáveis de sessão do MySQL para iterar e atualizar numa única query rápida.
    $sql_compactar = "
        SET @contador = :inicio - 1;
        UPDATE militar 
        SET antiguidade = (@contador := @contador + 1)
        $where_group
        ORDER BY antiguidade ASC;
    ";
    
    $stmt_compactar = $conn->prepare($sql_compactar);
    // Injeta o parametro :inicio manualmente na query ou via bind se o driver suportar multiplas queries
    // Para compatibilidade PDO, faremos em passos separados se necessário, mas o SET + UPDATE costuma funcionar.
    
    // Alternativa compatível com qualquer driver PDO (Executando separado):
    $conn->exec("SET @contador = " . ($inicio_sequencia - 1));
    $stmt_run_update = $conn->prepare("
        UPDATE militar 
        SET antiguidade = (@contador := @contador + 1)
        $where_group
        ORDER BY antiguidade ASC
    ");
    $stmt_run_update->execute($params_group);

    $conn->commit();
    $sucesso = true;

} catch (Exception $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log("ERRO REORDENACAO: " . $e->getMessage());
    $sucesso = false;
}

// Redirecionamento
$url = "../Views/listar_militares_atualizar_antiguidade.php?criterio_posto_grad=" . urlencode($criterio_posto_grad) . "&criterio_quadro=" . urlencode($criterio_quadro);
header("Location: $url&sucesso=" . ($sucesso ? '1' : '0'));
exit();
?>