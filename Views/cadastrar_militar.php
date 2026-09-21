<?php
require_once '../Controllers/nivel_gestor.php';
include_once '../Views/header2.php';
require_once '../ConexaoDB/conexao.php';
$conn = Conexao::getConexao();

//VERIFICAR INSERÇÃO
$sucesso_cadastro = (isset($_GET['sucesso'])) ? $_GET['sucesso'] : null;

//
//VERIFICAÇÃO SE VIERAM ERROS DO INSERE_DADOS_MILITAR.PHP
$erro = isset($_GET['erro']) ? $_GET['erro'] : null;
$erroNoBD = isset($_GET['erroNoBD']) ? $_GET['erroNoBD'] : null;

if (!is_null($erro)) {
    switch ($erro) {
        case '1':
            echo '<p class="text-danger"><i class="bi bi-exclamation-circle"></i> Militar já existe no banco de dados.</p>';
            break;
        case 'cpfInvalido':
            echo '<p class="text-danger"><i class="bi bi-exclamation-circle"></i> CPF inválido. Certifique-se de digitar exatamente 11 números.</p>';
            break;
        case 'cpfDuplicado':
            echo '<p class="text-danger"><i class="bi bi-exclamation-circle"></i> CPF já cadastrado no sistema.</p>';
            break;
    }
}

if (!is_null($erroNoBD)) {
    echo '<p class="text-danger"><i class="bi bi-database-exclamation"></i> Erro ao cadastrar no banco de dados.</p>';
}


?>

<div class="container">
    <div class="col-md-12">
        <ul class="nav nav-pills">
            <?php
            echo ('<li class="nav-item"><a class="nav-link active" aria-current="page" href="../Views/pagina_ferramentas.php">Voltar</a></li>');
            ?>
        </ul>
        <hr>
    </div>
    <div class="row justify-content-center">
        <div class="col-md-3"></div>
        <div class="col-md-6">

            <form action="../Controllers/insere_dados_militar.php" method="POST" name="formCadastro" onsubmit="return validateForm()">
                <label class="form-label">Dados cadastrais</label>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="nomeMilitar">Nome</span>
                    <input type="text" class="form-control" placeholder="Nome completo" aria-label="Username" aria-describedby="nomeMilitar" name="nome" pattern="[A-Za-zÀ-ü '-]{4,}">
                </div>
                <p id="alertaNomeVazio" style="color:#FF0000"></p>

                <div class="col">
                    <div class="input-group mb-3">
                        <span class="input-group-text" id="postoGraduacao">Posto/Graduação</span>
                        <select class="form-select" aria-label="Default select example" name="posto_grad">
                            <option disabled selected>Posto/Graduação</option>
                            <option value="CEL BM">Coronel</option>
                            <option value="TC BM">Tenente Coronel</option>
                            <option value="MAJ BM">Major</option>
                            <option value="CAP BM">Capitão</option>
                            <option value="1º TEN BM">1º Tenente</option>
                            <option value="2º TEN BM">2º Tenente</option>
                            <option value="ASP OF BM">Aspirante-a-oficial</option>
                            <option value="ST BM">Sub-tentente</option>
                            <option value="1º SGT BM">1º Sargento</option>
                            <option value="2º SGT BM">2º Sargento</option>
                            <option value="3º SGT BM">3º Sargento</option>
                            <option value="CB BM">Cabo</option>
                            <option value="SD BM">Soldado</option>
                        </select>
                    </div>
                </div>
                <p id="alertaPostoGradVazio" style="color:#FF0000"></p>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="quadroMilitar">Quadro</span>
                    <select class="form-select" name="quadro">
                        <option disabled selected>Quadro</option>
                        <option value="1">QOBM</option>
                        <option value="2">QCOBM</option>
                        <option value="3">QOSBM</option>
                        <option value="4">QPBM</option>
                    </select>
                </div>
                <p id="alertaQuadroVazio" style="color:#FF0000"></p>

                <div class="input-group mb-3">
                    <span class="input-group-text" id="cpfMilitar">CPF</span>
                    <input type="text" class="form-control" placeholder="Somente números" aria-label="CPF" aria-describedby="cpfMilitar" name="cpf" id="cpf" maxlength="11" pattern="\d{11}" required>
                </div>
                <p id="alertaCpfVazio" style="color:#FF0000"></p>

                <hr>
                <button class="btn btn-outline-primary active" type="submit">Salvar</button>
            </form>
        </div>
        <div class="col-md-3">
            <?php
            if (!is_null($erro)) {
                echo '<p><font style="color:#ff0000"><i class="bi bi-exclamation-circle" fill="currentColor"></i> Alerta!</font></p>'
                    . '<p><font style="color:#ff0000">Militar já existe no banco de dados.</font></p>';
            }
            if (!is_null($sucesso_cadastro)) {
                echo '<p><font style="color:#0000ff"><i class="bi bi-person-check" fill="currentColor"></i><strong>&nbspMilitar cadastrado com sucesso!</strong></font></p>';
                $array = ['Nome:', 'Posto/Grad.:', 'Quadro:'];
                $aux = 0;
                foreach ($sucesso_cadastro as $item) {
                    echo '<p><font style="color:#0000ff">' . $array[$aux] . '&nbsp';
                    echo $item . '</font></p>';
                    $aux++;
                }
            }
            ?>
        </div>
    </div>
</div>

<script>
    function validateForm() {
        // abaixo serve para Limite de Quantitativo
        let aux1 = document.forms["formCadastro"]["nome"].value;
        if (aux1 == "" || !isNaN(aux1)) {
            //alert("O nome deve ser preenchido.");
            document.getElementById('alertaNomeVazio').innerHTML = '* O nome não pode ser vazio ou numérico.'
            return false;
        } else {
            document.getElementById('alertaNomeVazio').innerHTML = ''
        }
        let aux2 = document.forms["formCadastro"]["posto_grad"].value;
        if (aux2 == "Posto/Graduação") {
            //alert("O campo POSTO/GRADUAÇÃO deve ser selecionado.");
            document.getElementById('alertaPostoGradVazio').innerHTML = '* O posto/graduação deve ser selecionado.'
            return false;
        } else {
            document.getElementById('alertaPostoGradVazio').innerHTML = ''
        }
        let aux3 = document.forms["formCadastro"]["quadro"].value;
        if (aux3 == "Quadro") {
            //alert("O QUADRO deve ser selecionado.");
            document.getElementById('alertaQuadroVazio').innerHTML = '* O quadro deve ser selecionado.'
            return false;
        } else {
            document.getElementById('alertaQuadroVazio').innerHTML = ''
        }
    }

    let aux4 = document.forms["formCadastro"]["cpf"].value;
    if (aux4 == "" || isNaN(aux4) || aux4.length !== 11) {
        document.getElementById('alertaCpfVazio').innerHTML = '* CPF inválido. Use apenas números (11 dígitos).';
        return false;
    } else {
        document.getElementById('alertaCpfVazio').innerHTML = ''
    }
</script>



<?php
include_once './footer2.php';
?>