<?php
require_once '../Controllers/nivel_gestor.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST)) {
    header('Location:../Views/acesso_restrito.php');
    exit;
} elseif (isset($_POST["excluir_documento"])) {
    $aux_id = filter_var($_POST["excluir_documento"], FILTER_SANITIZE_NUMBER_INT);

    if (!filter_var($aux_id, FILTER_VALIDATE_INT)) {
        return false;
    }

    $resultado = ExcluiDoBancoDocPromo($aux_id);
    if ($resultado) {
        header('Location:../Views/edicao_documentos_pasta_promo.php?id_da_pasta=' . $_POST["id_da_pasta"]);
        exit;
    } else {
        echo "Erro ao resetar o documento.";
    }
} else {
    // Sanitização dos inputs
    $id_da_pasta_promo = filter_input(INPUT_POST, "id_da_pasta", FILTER_SANITIZE_NUMBER_INT);
    $militar_id = filter_input(INPUT_POST, "militar_id", FILTER_SANITIZE_NUMBER_INT);
    $nome_documento = filter_input(INPUT_POST, "nome_do_documento", FILTER_SANITIZE_SPECIAL_CHARS);
    $caminho_do_arquivo = filter_input(INPUT_POST, "caminho_do_arquivo", FILTER_SANITIZE_URL);
    $status_documento = filter_input(INPUT_POST, "status_documento", FILTER_SANITIZE_NUMBER_INT);

    $erros = array();

    // Validações
    if (!filter_var($id_da_pasta_promo, FILTER_VALIDATE_INT)) {
        $erros[] = "O id da pasta promocional deve ser um número inteiro.";
    }
    if (!filter_var($militar_id, FILTER_VALIDATE_INT)) {
        $erros[] = "O id do militar deve ser um número inteiro.";
    }
    if (!filter_var($caminho_do_arquivo, FILTER_VALIDATE_URL)) {
        $erros[] = "URL do arquivo inválida.";
    }

    // TRATAMENTO DO 4º CASO: Agora aceita de 1 a 4 conforme sua tabela documento_status
    if (!$status_documento || $status_documento < 1 || $status_documento > 4) {
        $erros[] = "Situação inválida. Selecione uma das 4 opções disponíveis.";
    }

    // Verificação de erros (Correção da exibição da variável $erro)
    if (!empty($erros)) {
        echo '<!DOCTYPE html><html lang="pt-br"><head><meta charset="UTF-8">';
        echo '<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css"></head><body>';
        echo '<div class="container" style="margin-top: 50px;">';
        echo '<div class="alert alert-danger"><h4><i class="glyphicon glyphicon-exclamation-sign"></i> Atenção!</h4><hr><ul>';

        foreach ($erros as $mensagem) {
            // Usamos a variável $mensagem para evitar confusão com o símbolo de string literal
            echo "<li><strong>" . htmlspecialchars($mensagem) . "</strong></li>";
        }

        echo '</ul><br>';
        echo '<a class="btn btn-primary" href="../Views/edicao_documentos_pasta_promo.php?id_da_pasta=' . $id_da_pasta_promo . '">Voltar</a>';
        echo '</div></div></body></html>';
        exit;
    } else {
        $resultado = salvaNoBancoDocPromo($id_da_pasta_promo, $militar_id, $nome_documento, $caminho_do_arquivo, $status_documento);
        if ($resultado) {
            header('Location:../Views/edicao_documentos_pasta_promo.php?id_da_pasta=' . $id_da_pasta_promo);
            exit;
        } else {
            echo "Erro ao salvar os dados no banco.";
        }
    }
}

function salvaNoBancoDocPromo($id_pasta, $militar_id, $nome, $caminho, $status)
{
    require_once '../ConexaoDB/conexao.php';
    $conn = Conexao::getConexao();

    try {
        $stmt = $conn->prepare('SELECT id_doc_promo FROM documento_promocao WHERE doc_promo_nome = :nome AND pasta_promocional_id = :pasta');
        $stmt->execute([':nome' => $nome, ':pasta' => $id_pasta]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        if (empty($resultado)) {
            // INSERT com o novo status (aceita o valor 4)
            $stmt = $conn->prepare('INSERT INTO documento_promocao (doc_promo_nome, doc_promo_url, doc_status_id, pasta_promocional_id, militar_id) VALUES (:nome, :url, :status, :pasta, :militar)');
            $stmt->execute([
                ':nome' => $nome,
                ':url' => $caminho,
                ':status' => $status,
                ':pasta' => $id_pasta,
                ':militar' => $militar_id
            ]);
        } else {
            // UPDATE com o novo status
            $stmt = $conn->prepare('UPDATE documento_promocao SET doc_promo_url = :url, doc_status_id = :status WHERE id_doc_promo = :id');
            $stmt->execute([
                ':url' => $caminho,
                ':status' => $status,
                ':id' => $resultado['id_doc_promo']
            ]);
        }
        return true;
    } catch (PDOException $ex) {
        error_log("Erro SQL: " . $ex->getMessage());
        return false;
    }
}

function ExcluiDoBancoDocPromo($id)
{
    require_once '../ConexaoDB/conexao.php';
    $conn = Conexao::getConexao();

    try {
        // Reset para o status "NE" (ID 2 conforme sua imagem)
        $stmt = $conn->prepare('UPDATE documento_promocao SET doc_promo_url = NULL, doc_status_id = 2 WHERE id_doc_promo = :id');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    } catch (PDOException $ex) {
        return false;
    }
}
