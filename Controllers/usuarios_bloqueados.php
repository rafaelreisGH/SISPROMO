<?php

require_once '../ConexaoDB/conexao.php';
$conn = Conexao::getConexao();

/*
LEGENDA
status:
0 = USUÁRIO BLOQUEADO
1 = USUÁRIO DESBLOQUEADO

senha_reset:
0 = USUÁRIO PRECISA DEFINIR NOVA SENHA
1 = USUÁRIO JÁ DEFINIU SENHA
*/

// Query de seleção de usuários bloqueados
$consulta = $conn->query("SELECT * FROM usuarios WHERE status = 0");

while ($resultado = $consulta->fetch(PDO::FETCH_ASSOC)) {
    $aux_id = $resultado['id'];
    $aux_nome = $resultado['nome'];
    $aux_email = $resultado['email'];
    $senha_reset = $resultado['senha_reset'];
    $perfil = $resultado['nivel_de_acesso'];
    $posto_grad = $resultado['posto_grad_usuario'];

    $tipo_bloqueio = ($senha_reset == 1) ? 'Novo Cadastro' : 'Esqueceu a Senha';
    $badge_class = ($senha_reset == 1) ? 'bg-primary' : 'bg-warning text-dark';

    echo '<tr>'
        . '<td class="text-center">'
        . '<form action="../Controllers/desbloquear_usuario.php" method="POST" class="d-inline">'
        . '<button class="btn btn-sm btn-outline-danger" type="submit" name="desbloquear" value="' . $aux_id . '" title="Enviar chave de desbloqueio">'
        . '<i class="bi bi-send-fill"></i>'
        . '</button>'
        . '</td>'
        . '<td>' . htmlspecialchars($aux_nome) . '</td>'
        . '<td><span class="text-muted small">' . htmlspecialchars($aux_email) . '</span></td>';

    if ($senha_reset == 1) {
        // Novo cadastro — admin precisa selecionar perfil e posto
        echo '<td><select class="form-select form-select-sm" name="perfil" required>'
            . '<option selected disabled>Perfil</option>'
            . '<option value="1">Gestor</option>'
            . '<option value="2">Administrador</option>'
            . '<option value="3">Usuário</option>'
            . '</select></td>'
            . '<td><select class="form-select form-select-sm" name="posto_grad" required>'
            . '<option disabled selected>Posto/Graduação</option>'
            . '<option value="CEL BM">Coronel</option>'
            . '<option value="TC BM">Tenente Coronel</option>'
            . '<option value="MAJ BM">Major</option>'
            . '<option value="CAP BM">Capitão</option>'
            . '<option value="1º TEN BM">1º Tenente</option>'
            . '<option value="2º TEN BM">2º Tenente</option>'
            . '<option value="ASP OF BM">Aspirante-a-oficial</option>'
            . '<option value="ST BM">Sub-tentente</option>'
            . '<option value="1º SGT BM">1º Sargento</option>'
            . '<option value="2º SGT BM">2º Sargento</option>'
            . '<option value="3º SGT BM">3º Sargento</option>'
            . '<option value="CB BM">Cabo</option>'
            . '<option value="SD BM">Soldado</option>'
            . '</select></td>';
    } else {
        // Esqueceu a senha — já tem perfil e posto definidos
        echo '<td>' . ($perfil == "1" ? "Gestor" : ($perfil == "2" ? "Administrador" : "Usuário")) . '</td>';
        echo '<td>' . htmlspecialchars($posto_grad) . '</td>';
    }

    echo '</form>'
        . '<td><span class="badge ' . $badge_class . '">' . $tipo_bloqueio . '</span></td>'
        . '</tr>';
}
