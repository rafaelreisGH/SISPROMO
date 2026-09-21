<?php
require_once '../ConexaoDB/conexao.php';
$conn = Conexao::getConexao();
require_once '../Controllers/validar_senha.php';

//inicialização de variáveis
$militar_id = $_POST['id'];
$nome_atualizado =  $_POST['nome_atualizado'];
$email_atualizado =  $_POST['email_atualizado'];
$senha_atualizada =  $_POST['senha_atualizada'];

$erroNome = '';
$erroEmail = '';
$erroSenha = '';

/*CÓDIGOS DOS ERROS
1 = ERRO DE INSERÇÃO DE NOME
2 = ERRO DE INSERÇÃO DE EMAIL
3 = ERRO DE INSERÇÃO DE SENHA
*/

//variável para montar a string do header Location
$location = "Location:../Views/painel_do_usuario.php?";

try {
    //verifica se foi preenchido o nome e se o nome é diferente de vazio
    if (isset($nome_atualizado) && ($nome_atualizado != "") && (!is_numeric($nome_atualizado))) {
        $stmt = $conn->prepare('SELECT nome FROM usuarios WHERE id = :id');
        $stmt->execute(array(
            ':id' => $militar_id
        ));
        if ($resultado = $stmt->fetch(PDO::FETCH_ASSOC)) { //se econtrou nome no BD
            $nome_anterior = $resultado['nome'];
            //verifica se nome_anterior e nome_atualizado são iguais
            if ($nome_anterior != $nome_atualizado) {
                $stmt = $conn->prepare('UPDATE usuarios SET nome = :nome WHERE id = :id');
                $stmt->bindParam(':id', $militar_id, PDO::PARAM_INT);
                $stmt->bindParam(':nome', $nome_atualizado, PDO::PARAM_STR);
                $stmt->execute();

                if (!$stmt) {
                    $erroNome = 1;
                } else {
                    $erroNome = 0;
                    $location .= 'nome=' . $nome_atualizado . '&';
                }
            }
        }
    } else {
        $erroNome = 1;
    }

    //verificar se o posto/graduação no BD é igual ao informado no formulário
    if (isset($email_atualizado) && ($email_atualizado != "")) {
        $stmt = $conn->prepare('SELECT email FROM usuarios WHERE id = :id');
        $stmt->execute(array(
            ':id' => $militar_id
        ));
        if ($resultado = $stmt->fetch(PDO::FETCH_ASSOC)) { //se encontrou posto/grad no BD
            $email_anterior = $resultado['email'];
            //verifica se email_anterior e posto_grad_atualizado são iguais
            if ($email_anterior != $email_atualizado) {
                $stmt = $conn->prepare('UPDATE usuarios SET email = :email WHERE id = :id');
                $stmt->execute(array(
                    ':id' => $militar_id,
                    ':email' => $email_atualizado
                ));
                if (!$stmt) {
                    $erroEmail = 2;
                } else {
                    $erroEmail = 0;
                    $location .= 'email=' . $email_atualizado . '&';
                }
            }
        }
    } else {
        $erroEmail = 0;
    }

    $erroSenha = null;

    if (!empty($senha_atualizada)) {
        $aux = validarSenha($senha_atualizada);

        if ($aux === true) {
            $senha_hash = password_hash($senha_atualizada, PASSWORD_DEFAULT);
            $stmt = $conn->prepare('UPDATE usuarios SET senha = :senha WHERE id = :id');
            $stmt->bindParam(':id', $militar_id, PDO::PARAM_INT);
            $stmt->bindParam(':senha', $senha_hash, PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                $erroSenha = 3; // Nenhuma linha atualizada
            } else {
                $erroSenha = 0;
                $location .= 'senha=1&';
            }
        } else {
            $erroSenha = $aux; // Código de erro retornado pela função validarSenha
        }
    }

    // Redirecionar com erros (apenas os definidos)
    $params = [];
    if ($erroNome !== '') $params[] = "erro[]=$erroNome";
    if ($erroEmail !== '') $params[] = "erro[]=$erroEmail";
    if ($erroSenha !== '' && $erroSenha !== null) $params[] = "erro[]=$erroSenha";

    header($location . implode('&', $params));
    exit;
} catch (PDOException $ex) {
    return $ex->getMessage();
}
