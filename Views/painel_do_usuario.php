<?php
include_once './header3.php';
require_once '../ConexaoDB/conexao.php';
$conn = Conexao::getConexao();

// Verificação de alterações recebidas por GET
$nome_alterado = $_GET['nome'] ?? null;
$email_alterado = $_GET['email'] ?? null;
$senha_alterada = $_GET['senha'] ?? null;

$erroNome = $erroEmail = false;
$erroSenha = null;

$mensagemErroSenha = null;

if (isset($_GET['erro']) && is_array($_GET['erro'])) {
    foreach ($_GET['erro'] as $value) {
        if ($value == 1) $erroNome = true;
        elseif ($value == 2) $erroEmail = true;
        elseif ($value == 3) $erroSenha = true;
        elseif (!is_numeric($value)) $mensagemErroSenha = $value;
    }
}

// Buscar dados atuais do usuário
$stmt = $conn->prepare("SELECT id, nome, email FROM usuarios WHERE id = :id");
$stmt->execute([':id' => $_SESSION['id']]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

// var_dump($usuario); // Debug: Exibir dados do usuário
?>

<div class="container mt-5">
    <div class="col-md-12">
        <ul class="nav nav-pills">
            <li class="nav-item"><a class="nav-link active" aria-current="page" href="javascript:history.go(-1)">Voltar</a></li>
        </ul>
        <hr>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-6">
            <form action="../Controllers/atualiza_dados_usuario.php" method="POST">
                <label class="form-label">Dados cadastrais</label>

                <div class="input-group mb-3">
                    <span class="input-group-text" id="nomeMilitar">Nome</span>
                    <input type="text" class="form-control" placeholder="Nome completo" aria-label="Username" aria-describedby="nomeMilitar" name="nome_atualizado" value="<?= htmlspecialchars($usuario['nome']) ?>">
                </div>

                <div class="col">
                    <div class="input-group mb-3">
                        <span class="input-group-text" id="emailUsuario">E-mail</span>
                        <input type="email" class="form-control" placeholder="Email" aria-label="Email" aria-describedby="emailUsuario" name="email_atualizado" value="<?= htmlspecialchars($usuario['email']) ?>">
                    </div>
                </div>

                <div class="col">
                    <div class="input-group mb-3">
                        <span class="input-group-text" id="senhaUsuario">Senha</span>
                        <input type="password" class="form-control" placeholder="Deixe em branco para manter a atual" aria-label="Senha" aria-describedby="senhaUsuario" name="senha_atualizada">
                    </div>
                </div>

                <input type="hidden" name="id" value="<?= $usuario["id"] ?>">

                <?php

                if ($erroNome) {
                    echo '<font style="color:#ff0000">* O <strong>nome</strong> deve ser preenchido corretamente. Não são aceitos valores vazios ou numéricos. </font></br>';
                }
                if ($nome_alterado) {
                    echo '<font style="color:#FF0000">* <strong>Nome</strong> alterado para: <i>' . htmlspecialchars($nome_alterado) . '</i>. </font></br>';
                }
                if ($erroEmail) {
                    echo '<font style="color:#ff0000">* O <strong>email</strong> deve ser preenchido corretamente. Não são aceitos valores vazios ou fora do padrão de e-mail. </font></br>';
                }
                if ($email_alterado) {
                    echo '<font style="color:#FF0000">* <strong>E-mail</strong> alterado para: <i>' . htmlspecialchars($email_alterado) . '</i>. </font></br>';
                }
                if ($erroSenha === true) {
                    echo '<font style="color:#ff0000">* Erro ao salvar a <strong>senha</strong> no banco de dados. </font></br>';
                } elseif ($mensagemErroSenha) {
                    echo '<font style="color:#FF0000">* <strong>' . htmlspecialchars($mensagemErroSenha) . '</strong></font></br>';
                }
                if ($senha_alterada == 1) {
                    echo '<font style="color:#FF0000">* <strong>Senha</strong> alterada com sucesso. </font></br>';
                }

                ?>

                <hr>
                <button class="btn btn-outline-success active" type="submit">Atualizar</button>
            </form>
        </div>
    </div>

    <?php include_once './footer2.php'; ?>