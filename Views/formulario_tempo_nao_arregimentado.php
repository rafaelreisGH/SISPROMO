<?php
require_once '../Controllers/nivel_gestor.php';
include_once '../Views/header2.php';
require_once '../ConexaoDB/conexao.php';
$conn = Conexao::getConexao();

//Pegar o id do militar em questão e consultar BD

if (isset($_SESSION['militar_id'])) {
    $militar_id = $_SESSION['militar_id'];
    //pegar no BD dados do militar selecionado
    $stmt = $conn->prepare("SELECT nome, posto_grad_mil FROM militar WHERE id = :id");
    $stmt->bindParam(':id', $militar_id, PDO::PARAM_INT);
    $stmt->execute();
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

    if (isset($resultado['nome'])) {
        $nome = $resultado['nome'];
        $posto_grad = $resultado['posto_grad_mil'];
    }
}
/*----------------------------------------------------*/

//Sanitização
if (isset($_POST["excluir_documento"])) {
    $auxiliar = filter_input(INPUT_POST, "id_tna", FILTER_SANITIZE_NUMBER_INT);;

    try {
        // Prepara a query de exclusão
        $stmt = $conn->prepare("DELETE FROM promocao.tempo_nao_arregimentado WHERE id_tempo_nao_arregimentado = :id");
        $stmt->bindParam(':id', $auxiliar, PDO::PARAM_INT);
    } catch (PDOException $ex) {
        return $ex->getMessage();
    };

    // Executa a query
    if ($stmt->execute()) {
        include_once '../Controllers/verificar_intersticio_descontado_LTIP.php';
        $mensagem = "Registro excluído com sucesso!";

        // Log de exclusão
        require_once __DIR__ . '/../Logger/LoggerFactory.php';
        $logger = LoggerFactory::createLogger();
        $logger->info('Usuário excluiu tempo não arregimentado', [
            'id' => $_SESSION['id'],
            'usuario' => $_SESSION['nome'],
            'email' => $_SESSION['email'],
            'perfil' => $_SESSION['nivel_de_acesso'],
            'sujeito' => $militar_id
        ]);
    } else {
        $mensagem = "Erro ao excluir o registro.";
    }
} else if (!empty($_POST)) { // Este é o bloco que trata a submissão do formulário de registro

    // Validar se a categoria foi selecionada
    if (empty($_POST['categoria']) || $_POST['categoria'] == 'Selecione a categoria') {
        $mensagem = '<font style="color:#FF0000">Erro: Por favor, selecione uma categoria para o afastamento.</font>';
        // Aqui você pode redirecionar ou simplesmente parar a execução do script.
        // Se você quiser parar o processamento e exibir a mensagem, pode usar um 'return' ou 'exit'.
        exit;
    } else {
        // Se a categoria for válida, continua com o processamento
        $militar_id = filter_input(INPUT_POST, "id", FILTER_SANITIZE_NUMBER_INT);
        $categoria = filter_input(INPUT_POST, "categoria", FILTER_SANITIZE_SPECIAL_CHARS);
        $data_inicio = filter_input(INPUT_POST, "data_inicio", FILTER_SANITIZE_SPECIAL_CHARS);
        $data_fim = filter_input(INPUT_POST, "data_fim", FILTER_SANITIZE_SPECIAL_CHARS);
        /*----------------------------------------------------*/

        //pegar no BD dados do militar selecionado
        $stmt = $conn->prepare("SELECT nome, posto_grad_mil FROM militar WHERE id = :id");
        $stmt->bindParam(':id', $militar_id, PDO::PARAM_INT);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($resultado) {
            $nome = $resultado["nome"];
            $posto_grad = $resultado["posto_grad_mil"];
        }
    }
    /*----------------------------------------------------*/

    //Evitar período negativo
    //Evitar que os atestados sejam <= 30 dias (inciso I)

    function validar_periodo($data1, $data2, $categoria)
    {
        // Add your logic here
        $data_0 = date_create($data1);
        $data_1 = date_create($data2);
        $intervalo = date_diff($data_0, $data_1);
        $aux =  (int)$intervalo->format('%R%a');
        if ($aux <= 0) {
            return false;
        } else if ($categoria == "inciso1" && $aux <= 30) {
            return false;
        } else {
            return true;
        }
    }
    /*----------------------------------------------------*/
    /*----------------------------------------------------*/


    //função para pegar o intervalo
    function get_tempo($data1, $data2)
    {
        // Add your logic here
        $data_0 = date_create($data1);
        $data_1 = date_create($data2);
        $intervalo = date_diff($data_0, $data_1);
        return  (int)$intervalo->format('%a');
    }
    /*----------------------------------------------------*/
    /*----------------------------------------------------*/

    //verificação se o período é válido
    $periodo_valido = validar_periodo($data_inicio, $data_fim, $categoria);

    //SE FOR VÁLIDO, INSERE NO BANCO DE DADOS
    if ($periodo_valido) {

        //pega a diferença em dias entre o início e o fim do afastamento
        $dias = get_tempo($data_inicio, $data_fim);

        try {
            //Busca se há algum registro com o mesmo período para o mesmo militar
            $stmt = $conn->prepare("SELECT * FROM promocao.tempo_nao_arregimentado WHERE militar_id = :id AND tna_inicio = :inicio AND tna_fim = :fim AND categoria = :categoria AND posto_grad_na_epoca = :posto_grad");
            $stmt->bindParam(':id', $militar_id, PDO::PARAM_INT);
            $stmt->bindParam(':inicio', $data_inicio, PDO::PARAM_STR);
            $stmt->bindParam(':fim', $data_fim, PDO::PARAM_STR);
            $stmt->bindParam(':categoria', $categoria, PDO::PARAM_STR);
            $stmt->bindParam(':posto_grad', $posto_grad, PDO::PARAM_STR);
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC); // Obtém os dados
        } catch (PDOException $ex) {
            die("Erro no banco de dados: " . $ex->getMessage());
        };
        // Verifica se alguma linha foi afetada
        if ($resultado) {
            // echo "Há resultado(s).";
            $mensagem = '<font style="color:#FF0000">Já existe lançamento com os mesmos dados para esse evento.</font></br>';
        } else {
            //echo "Nenhum resultado obtido.";
            // Afastamento não existe, então insere um novo registro
            try {
                $stmt = $conn->prepare('INSERT INTO promocao.tempo_nao_arregimentado (categoria, tna_inicio, tna_fim, qtde_de_dias, militar_id, posto_grad_na_epoca) VALUES (:categoria, :inicio, :fim, :dias, :militar_id, :posto_grad)');
                $stmt->bindParam(':categoria', $categoria, PDO::PARAM_STR);
                $stmt->bindParam(':inicio', $data_inicio, PDO::PARAM_STR);
                $stmt->bindParam(':fim', $data_fim, PDO::PARAM_STR);
                $stmt->bindParam(':dias', $dias, PDO::PARAM_INT);
                $stmt->bindParam(':militar_id', $militar_id, PDO::PARAM_INT);
                $stmt->bindParam(':posto_grad', $posto_grad, PDO::PARAM_STR);
                $stmt->execute();
            } catch (PDOException $ex) {
                die("Erro no banco de dados: " . $ex->getMessage());
            };
            // Verifica se alguma linha foi afetada
            if ($stmt->rowCount() > 0) {

                // Log de registro
                require_once __DIR__ . '/../Logger/LoggerFactory.php';
                $logger = LoggerFactory::createLogger();
                $logger->info('Usuário inseriu tempo não arregimentado', [
                    'id' => $_SESSION['id'],
                    'usuario' => $_SESSION['nome'],
                    'email' => $_SESSION['email'],
                    'perfil' => $_SESSION['nivel_de_acesso'],
                    'sujeito' => $militar_id
                ]);

                $mensagem = '<font style="color:#FF0000">Informação inserida com sucesso.</font></br>';
            } else {
                $mensagem = '<font style="color:#FF0000">Falha na inserção do evento.</font></br>';
            }
            if ($categoria == "inciso2") {
                include_once '../Controllers/verificar_intersticio_descontado_LTIP.php';
            }
        }
    }


    /*----------------------------------------------------*/

    //consultar no banco se já há um lançamento para o mesmo evento





    /*----------------------------------------------------*/
}



?>

<div class="container">
    <div class="col-md-12">
        <ul class="nav nav-pills" style="list-style: none; display: flex; gap: 10px; padding: 0;">
            <li class="active"><a class="nav-link active" aria-current="page" href="../Views/visualizar_dados.php?militar_id=<?= $militar_id ?>">Voltar</a></li>
        </ul>
        <hr>
    </div>
    <div class="col-md-12">
        <h3>Militar Selecionado</h3>
        <div class="form-text">
            <p>
                <?= $posto_grad ?>&nbsp<?= $nome ?></p>
            <hr>
        </div>
    </div>

    <div class="row col-md-12">
        <div class="col-md-3">
            <label class="form-label">Legislação</label>
            <div class="form-text" id="basic-addon4" style="text-align: justify;">
                <p>§ 5º, art. 56 do Decreto Estadual nº 2.268/2014.</p>
                <p>I - em gozo de licença para tratamento de saúde própria ou da família, quando o atestado médico apresentar afastamento superior a 30 (trinta) dias, devidamente homologado pela Perícia Médica Oficial do Estado, exceto nos casos de afastamento decorrente de acidente de serviço;</p>
                <p>II - em gozo de licença para tratamento de interesse particular;</p>
                <p>III - em desempenho de função de natureza civil ou cargo público civil, temporário, não eletivo;</p>
                <p>IV - na situação de desertor(a) ou extraviado;</p>
                <p>V - preso preventivamente, temporariamente ou em flagrante delito, enquanto durar a prisão; ou</p>
                <p>VI - cumprindo pena restritiva de liberdade decorrente de sentença penal transitada em julgado.</p>
            </div>
        </div>

        <div class="col-md-5">
            <form id="meuFormulario" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">

                <label class="form-label">Categoria</label>
                <div class="input-group mb-3">
                    <span class="input-group-text"><i class="bi bi-file-earmark-text"></i></span>
                    <select id="opcao" class="form-select" name="categoria" required>
                        <option selected disabled>Selecione a categoria</option>
                        <option value="inciso1">I - LTSPF/LTS maior que 30 (trinta) dias</option>
                        <option value="inciso2">II - LTIP</option>
                        <option value="inciso3">III - Desempenho de função/cargo civil, não eletivo</option>
                        <option value="inciso4">IV - Desertor/extraviado</option>
                        <option value="inciso5">V - Prisão preventiva</option>
                        <option value="inciso6">VI - Cumprimento de pena restritiva de liberdade</option>
                    </select>
                </div>

                <div class="row">
                    <div class="col">
                        <div class="input-group mb-3">
                            <span class="input-group-text">Início</span>
                            <input type="date" class="form-control" name="data_inicio" required>
                        </div>
                    </div>
                    <div class="col">
                        <div class="input-group mb-3">
                            <span class="input-group-text">Fim</span>
                            <input type="date" class="form-control" name="data_fim" required>
                        </div>
                    </div>
                    <div class="form-text" id="dica"></div>
                </div>

                <div class="input-group mb-3">
                    <input type="hidden" name="id" value="<?= $militar_id ?>">
                </div>

                <?php
                if (isset($mensagem)) {
                    echo $mensagem;
                    unset($mensagem);
                }
                //}
                //if (isset($nome_alterado) && !is_null($nome_alterado)) {
                //   echo '<font style="color:#FF0000">* <strong>Nome</strong> alterado para: <i>' . $nome_alterado . '</i>. </font></br>';
                //}
                //if (isset($posto_grad_alterado) && !is_null($posto_grad_alterado)) {
                //   echo '<font style="color:#FF0000">* Posto/graduação alterado para: <strong><i>' . alias_posto_grad($posto_grad_alterado) . ' BM</i></strong>. </font></br>';
                // }
                //if (isset($quadro_alterado) && !is_null($quadro_alterado)) {
                //    echo '<font style="color:#FF0000">* Quadro alterado para: <strong><i>' . ucfirst(strtolower($quadro_alterado)) . '</i></strong>. </font></br>';
                //}
                ?>
                <hr>
                <button class="btn btn-outline-success active" type="submit">Registrar</button>
            </form>
        </div>

        <div class="col-md-4">
            <table class="table table-striped">
                <caption>Informações de afastamentos</caption>
                <thead>
                    <tr>
                        <th>Categoria</th>
                        <th>Início</th>
                        <th>Fim</th>
                        <th>Quantidade</th>
                        <th>Excluir</th>
                    </tr>
                </thead>

                <tbody>
                    <?php
                    try {
                        $stmt = $conn->prepare("SELECT id_tempo_nao_arregimentado, categoria, tna_inicio, tna_fim, qtde_de_dias FROM promocao.tempo_nao_arregimentado WHERE militar_id = :id");
                        $stmt->bindParam(':id', $militar_id, PDO::PARAM_INT);
                        $stmt->execute();
                    } catch (PDOException $ex) {
                        return $ex->getMessage();
                    };

                    // Verifica se alguma linha foi afetada
                    if ($stmt) {
                        include_once '../Controllers/alias_categoria_tna.php';
                        // Percorrer os resultados
                        while ($resultado = $stmt->fetch(PDO::FETCH_ASSOC)) {

                            $aux_id = $resultado["id_tempo_nao_arregimentado"];
                            $aux_categoria = alias_categoria($resultado["categoria"]);
                            
                            // Converte e formata a data de início
                            $data_inicio = new DateTime($resultado["tna_inicio"]);
                            $aux_inicio = $data_inicio->format('d/m/Y');

                            // Converte e formata a data de fim
                            $data_fim = new DateTime($resultado["tna_fim"]);
                            $aux_fim = $data_fim->format('d/m/Y');
                            $aux_dias = $resultado["qtde_de_dias"];

                            echo '<tr>'
                                . '<td>' . $aux_categoria . '</td>'
                                . '<td style="text-align: center;">' . $aux_inicio . '</td>'
                                . '<td style="text-align: center;">' . $aux_fim . '</td>'
                                . '<td style="text-align: center;">' . $aux_dias . '&nbspdia(s)</td>'
                                . '<td><form action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '" method="POST" onsubmit="return confirmarExclusao();"><input type="hidden" name="id_tna" value="' . $aux_id . '"><button class="btn btn-danger" type="submit" name="excluir_documento">'
                                . '<i class="bi bi-trash3-fill"></i></button></form></td></tr>';
                        }
                    } else {
                        echo '<div class="form-text"><p>Não há nenhum lançamento.</p></div>';
                    }
                    ?>
                </tbody>
            </table>
        </div>

    </div>
</div>


<div class="clearfix"></div>
<br />

<script>
    document.querySelector('form').addEventListener('input', function() {
        const categoriaSelecionada = document.getElementById("opcao").value;
        const dataInicio = document.querySelector('input[name="data_inicio"]').value;
        const dataFim = document.querySelector('input[name="data_fim"]').value;
        const dicaElement = document.getElementById('dica');

        // Se a categoria padrão ainda estiver selecionada, exibe a dica para selecionar.
        if (categoriaSelecionada === "Selecione a categoria") {
            dicaElement.textContent = "Por favor, selecione a categoria do afastamento.";
        }
        // Se as datas estiverem preenchidas e a categoria não for a padrão, calcula os dias.
        else if (dataInicio && dataFim) {
            const data1 = new Date(dataInicio);
            const data2 = new Date(dataFim);

            // Verifica se as datas são válidas
            if (!isNaN(data1) && !isNaN(data2)) {
                const diffTime = Math.abs(data2 - data1);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                dicaElement.textContent = "Quantidade de dias no período informado: " + diffDays + " dia(s).";
            } else {
                dicaElement.textContent = "";
            }
        } else {
            // Limpa a dica se nem a categoria nem as datas foram preenchidas
            dicaElement.textContent = "";
        }
    });

    document.getElementById("meuFormulario").addEventListener("submit", function(event) {
        let opcaoSelecionada = document.getElementById("opcao").value;
        let dataInicioInput = document.querySelector('input[name="data_inicio"]').value;
        let dataFimInput = document.querySelector('input[name="data_fim"]').value;

        // Adiciona a validação no lado do cliente para a categoria
        if (opcaoSelecionada === "Selecione a categoria") {
            alert("Por favor, selecione uma categoria para o afastamento.");
            event.preventDefault();
            return;
        }

        if (!dataInicioInput || !dataFimInput) {
            alert("Por favor, preencha ambas as datas.");
            event.preventDefault();
            return;
        }

        let dataInicio = new Date(dataInicioInput);
        let dataFim = new Date(dataFimInput);
        let diferencaDias = (dataFim - dataInicio) / (1000 * 60 * 60 * 24);

        if (opcaoSelecionada === "inciso1" && diferencaDias <= 30) {
            alert("Para a categoria 'Inciso 1', a diferença entre as datas deve ser maior que 30 dias.");
            event.preventDefault();
            return;
        } else if (diferencaDias <= 0) {
            alert("A data de fim deve ser maior que a data de início.");
            event.preventDefault();
            return;
        }
    });

    function confirmarExclusao() {
        return confirm("Tem certeza que deseja excluir este documento? Esta ação não pode ser desfeita!");
    }
</script>

<?php
include_once '../Views/footer.php';
?>