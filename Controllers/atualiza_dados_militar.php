<?php
require_once '../ConexaoDB/conexao.php';
$conn = Conexao::getConexao(); // Obtém a conexão ativa

//inicialização de variáveis
$militar_id = $_POST['id'];
$nome_atualizado =  $_POST['nome_atualizado'];
$posto_grad_atualizado = $_POST['posto_grad_atualizado'];
$quadro_atualizado = $_POST['quadro_atualizado'];
$data_atualizada =  isset($_POST['data_atualizada']) ? $_POST['data_atualizada'] : null;

$erroNome = '';
$erroData = '';
$erroPostoGrad = '';
$erroQuadro = '';

/*CÓDIGOS DOS ERROS
1 = ERRO DE INSERÇÃO DE NOME
2 = ERRO DE INSERÇÃO DA DATA
3 = ERRO DE ALTERAÇÃO DO POSTO/GRADUAÇÃO
4 = ERRO DE ALTERAÇÃO DO QUADRO
*/

//array para gravar os militares que tiveram os registros alterados
$alteracoes = array();

//variável para montar a string do header Location
$location = "Location:../Views/visualizar_dados.php?";

try {
    //verifica se foi preenchido o nome e se o nome é diferente de vazio
    if (isset($nome_atualizado) && ($nome_atualizado != "") && (!is_numeric($nome_atualizado))) {
        $stmt = $conn->prepare('SELECT nome FROM militar WHERE id = :id');
        $stmt->execute(array(
            ':id' => $militar_id
        ));
        if ($resultado = $stmt->fetch(PDO::FETCH_ASSOC)) { //se econtrou nome no BD
            $nome_anterior = $resultado['nome'];
            //verifica se nome_anterior e nome_atualizado são iguais
            if ($nome_anterior != $nome_atualizado) {
                $stmt = $conn->prepare('UPDATE militar SET nome = :nome WHERE id = :id');
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
    if (isset($posto_grad_atualizado)) {
        $stmt = $conn->prepare('SELECT posto_grad_mil FROM militar WHERE id = :id');
        $stmt->execute(array(
            ':id' => $militar_id
        ));
        if ($resultado = $stmt->fetch(PDO::FETCH_ASSOC)) { //se encontrou posto/grad no BD
            $posto_grad_anterior = $resultado['posto_grad_mil'];
            //verifica se posto_grad_anterior e posto_grad_atualizado são iguais
            if ($posto_grad_anterior != $posto_grad_atualizado) {
                $stmt = $conn->prepare('UPDATE militar SET posto_grad_mil = :posto_grad_mil_atualizado WHERE id = :id');
                $stmt->execute(array(
                    ':id' => $militar_id,
                    ':posto_grad_mil_atualizado' => $posto_grad_atualizado
                ));
                if (!$stmt) {
                    $erroPostoGrad = 2;
                } else {
                    $erroPostoGrad = 0;
                    $location .= 'posto_grad=' . $posto_grad_atualizado . '&';
                }
            }
        }
    } else {
        $erroPostoGrad = 0;
    }

    //verificar se o quadro no BD é igual ao informado no formulário
    if (isset($quadro_atualizado)) {
        $stmt = $conn->prepare('SELECT quadro_id FROM militar WHERE id = :id');
        $stmt->execute(array(
            ':id' => $militar_id
        ));
        if ($resultado = $stmt->fetch(PDO::FETCH_ASSOC)) { //se encontrou quadro no BD
            $quadro_anterior = $resultado['quadro_id'];
            //verifica se quadro_anterior e quadro_atualizado são iguais
            if ($quadro_anterior != $quadro_atualizado) {
                $stmt = $conn->prepare('UPDATE militar SET quadro_id = :quadro_atualizado WHERE id = :id');
                $stmt->execute(array(
                    ':id' => $militar_id,
                    ':quadro_atualizado' => $quadro_atualizado
                ));
                if (!$stmt) {
                    $erroQuadro = 3;
                } else {
                    $erroQuadro = 0;
                    $location .= 'quadro=' . $quadro_atualizado . '&';
                }
            }
        }
    } else {
        $erroQuadro = 0;
    }
    header($location . 'militar_id=' . $militar_id . '&erro[]=' . $erroNome . '&erro[]=' . $erroPostoGrad . '&erro[]=' . $erroQuadro . '');
} catch (PDOException $ex) {
    return $ex->getMessage();
}

/*<?php
require_once '../ConexaoDB/conexao.php';





//inicialização de variáveis
$militar_id = $_POST['id'];
$nome_atualizado = filter_input(INPUT_POST, 'nome_atualizado', FILTER_SANITIZE_STRING);
$posto_grad_atualizado = $_POST['posto_grad_atualizado'];
$quadro_atualizado = $_POST['quadro_atualizado'];
$data_atualizada =  isset($_POST['data_atualizada']) ? $_POST['data_atualizada'] : null;

$erroNome;
$erroData;
$erroPostoGrad;
$erroQuadro;

/*CÓDIGOS DOS ERROS
1 = ERRO DE INSERÇÃO DE NOME
2 = ERRO DE INSERÇÃO DA DATA
3 = ERRO DE ALTERAÇÃO DO POSTO/GRADUAÇÃO
4 = ERRO DE ALTERAÇÃO DO QUADRO
*/
/*
try {

    //verifica se foi preenchido o nome e se o nome é diferente de vazio
    if (isset($nome_atualizado) && ($nome_atualizado != "") && (!is_numeric($nome_atualizado))) {
        $stmt = $conn->prepare('UPDATE militar SET nome = :nome WHERE id = :id');
        $stmt->execute(array(
            ':id' => $militar_id,
            ':nome' => $nome_atualizado
        ));
        if (!$stmt) {
            $erroNome = 1;
        } else {
            $erroNome = 0;
        }
    } else {
        $erroNome = 1;
    }

    if (isset($data_atualizada) && $data_atualizada != '') {
        $stmt = $conn->prepare('UPDATE registro_de_promocoes SET a_contar_de = :ultima_promocao WHERE militar_id = :id');
        $stmt->execute(array(
            ':id' => $militar_id,
            ':ultima_promocao' => $data_atualizada
        ));
        if (!$stmt) {
            $erroData = 2;
        } else {
            $erroData = 0;
        }
    } else {
        $erroData = 0;
    }

    if (isset($posto_grad_atualizado)) {
        $stmt = $conn->prepare('UPDATE militar SET posto_grad_mil = :posto_grad_mil_atualizado WHERE id = :id');
        $stmt->execute(array(
            ':id' => $militar_id,
            ':posto_grad_mil_atualizado' => $posto_grad_atualizado
        ));
        if (!$stmt) {
            $erroPostoGrad = 3;
        } else {
            $erroPostoGrad = 0;
        }
    } else {
        $erroPostoGrad = 0;
    }
    if (isset($quadro_atualizado)) {
        $stmt = $conn->prepare('UPDATE militar SET quadro = :quadro_atualizado WHERE id = :id');
        $stmt->execute(array(
            ':id' => $militar_id,
            ':quadro_atualizado' => $quadro_atualizado
        ));
        if (!$stmt) {
            $erroQuadro = 4;
        } else {
            $erroQuadro = 0;
        }
    } else {
        $erroQuadro = 0;
    }
} catch (PDOException $ex) {
    return $ex->getMessage();
}
//header('Location:../Views/visualizar_dados.php?militar_id=' . $militar_id . '&erro[]=' . $erroNome . '&erro[]=' . $erroData . '&erro[]=' . $erroPostoGrad . '&erro[]=' . $erroQuadro . '');
header('Location:../Views/visualizar_dados.php?&militar_id=' . $militar_id . '&erro[]=' . $erroNome . '&erro[]=' . $erroData . '&erro[]=' . $erroPostoGrad . '&erro[]=' . $erroQuadro . '');*/