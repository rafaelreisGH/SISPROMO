<?php
include_once '../Controllers/controle_de_sessao.php';

require_once '../ConexaoDB/conexao.php';
$conn = Conexao::getConexao();

//verifica a página de onde veio a requisição
//para poder mudar o header location
if (isset($_SESSION['pagina_anterior']) and $_SESSION['pagina_anterior'] == '/Views/listar_militares_promocao_em_lote_v2.php') {
    $altera_header = '_v2';
} else {
    $altera_header = '';
}
//---------------------------//

if (isset($_POST['data_promocao'])) {
    $data_promocao = $_POST['data_promocao'];
}
if (isset($_POST['modalidade'])) {
    $modalidade = $_POST['modalidade'];
}
if (isset($_POST['promocao_posto_grad'])) {
    $promocao_posto_grad = $_POST['promocao_posto_grad'];
}

$criterio_posto_grad = $_POST['criterio_posto_grad'];
$criterio_quadro = $_POST['criterio_quadro'];

if (isset($_POST['militar_id'])) {
    $militar_id = $_POST['militar_id'];
} else { // se não for selecionado nenhum militar
    header('Location:../Views/listar_militares_promocao_em_lote' . $altera_header . '.php?nada_alterado=1&criterio_posto_grad=' . $criterio_posto_grad . '&criterio_quadro=' . $criterio_quadro . '');
}
//---------------------------//

//ANTES DE SE REALIZAR A ALTERAÇÃO DO POSTO/GRADUAÇÃO
//PEGAR O MILITAR MAIS MODERNO ATUALMENTO NO POSTO/GRADUAÇÃO EM QUESTÃO
$vetor_antiguidade_mais_moderno_anteriormente = array();
$stmt = $conn->prepare("SELECT antiguidade FROM militar WHERE posto_grad_mil = '$promocao_posto_grad'");
$resultado = $stmt->execute();
while ($resultado = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $vetor_antiguidade_mais_moderno_anteriormente[] = $resultado['antiguidade'];
}
if (!empty($vetor_antiguidade_mais_moderno_anteriormente)) {
    $mais_moderno_anteriormente = (int)max($vetor_antiguidade_mais_moderno_anteriormente);
} else { //se vazio, ou seja, se não houver ninguém no posto/grad imediatamente superior
    $stmt = $conn->query("SELECT min(antiguidade) FROM militar WHERE posto_grad_mil = '$criterio_posto_grad'")->fetch();
    $mais_moderno_anteriormente = $stmt['min(antiguidade)'] - 1;
}
//---------------------------//

// vetor para armazenar os militares e sua antiguidade
$vetor_antiguidade_anterior = array();

foreach ($militar_id as $item) {
    $stmt = $conn->prepare("SELECT id, nome, antiguidade FROM militar WHERE id = '$item'");
    $resultado = $stmt->execute();
    while ($resultado = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $valor = $resultado['antiguidade'];
        $chave = $resultado['id'];
        $vetor_antiguidade_anterior += ["{$chave}" => $valor];
    }
}
$max = (int)max($vetor_antiguidade_anterior); //pega a antiguidade do militar de maior antiguidade nominal -- militar mais moderno promovido

$vetor_antiguidade = array();
//LISTAR OS MILITARES QUE JÁ ESTAVAM NO POSTO/GRADUAÇÃO, E OS QUE ACABARAM DE ENTRAR, E TAMBÉM AQUELES QUE NÃO FORAM PROMOVIDOS ORDENANDO-OS PELA ANTIGUIDADE 
$stmt = $conn->prepare("SELECT id, nome, posto_grad_mil, antiguidade FROM militar WHERE antiguidade >= '$mais_moderno_anteriormente' AND antiguidade <= '$max' ORDER BY antiguidade");
$resultado = $stmt->execute();
while ($resultado = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $valor = $resultado['antiguidade'];
    $key = $resultado['id'];
    $vetor_antiguidade += ["{$key}" => $valor];
}

//-------------------------------------------------------------------//
// Forma segura de remover o primeiro elemento mantendo a associação ID => Antiguidade
if (count($vetor_antiguidade) > 0) {
    // Pegamos as chaves (IDs) e os valores (Antiguidades)
    $ids_temporarios = array_keys($vetor_antiguidade);
    $valores_temporarios = array_values($vetor_antiguidade);

    // Removemos o primeiro de cada um (o "deslocamento" que você deseja)
    array_shift($ids_temporarios);
    array_shift($valores_temporarios);

    // Remontamos o vetor garantindo que os tamanhos batem
    $vetor_antiguidade = array_combine($ids_temporarios, $valores_temporarios);
}
//-------------------------------------------------------------------//

foreach ($vetor_antiguidade_anterior as $item => $value) {
    $antiguidade = $value;
    if (($antiguidade - $mais_moderno_anteriormente) > 1) {
        unset($vetor_antiguidade[$item]);
    } else {
        break;
    }
}

//-------------------------------------------------------------------//
//COMBINAR O VETOR DOS PROMOVIDOS COM OS NÃO PROMOVIDOS, QUE FICAM NO FINAL DA FILA
$aux = array();
foreach ($vetor_antiguidade_anterior as $key => $valor) {
    $aux += ["{$key}" => $valor];
}
foreach ($vetor_antiguidade as $key => $valor) {
    $aux += ["{$key}" => $valor];
}
//-------------------------------------------------------------------//

//-------------------------------------------------------------------//
//REORDENAÇÃO DA ANTIGUIDADE
$mais_moderno_anteriormente += 1;
foreach ($aux as $key => &$valor) {
    $valor = $mais_moderno_anteriormente;
    $mais_moderno_anteriormente++;
}
//-------------------------------------------------------------------//

//-------------------------------------------------------------------//
//ALTERAR O POSTO/GRADUAÇÃO NO BANCO DE DADOS
foreach ($militar_id as $item) {
    $stmt = $conn->prepare("UPDATE militar SET posto_grad_mil = :promocao_posto_grad, 
                                data_cumprimento_intersticio = NULL WHERE id = :id");
                                //Quando a promoção é processada, o militar entra em um novo ciclo/posto, portanto o campo data_cumprimento_intersticio deve obrigatoriamente ser resetado para NULL. Dessa forma, nas próximas consultas de candidatos, o sistema utilizará o cálculo regular com base na nova data de promoção (a_contar_de).
    $stmt->execute(array(
        ':id' => $item,
        ':promocao_posto_grad' => $promocao_posto_grad
    ));
}
//-------------------------------------------------------------------//

//-------------------------------------------------------------------//
//ALTERAR A ANTIGUIDADE NO BANCO DE DADOS
foreach ($aux as $item => $value) {
    $stmt = $conn->prepare("UPDATE militar SET antiguidade = :antiguidade  WHERE id = :id");
    $stmt->execute(array(
        ':id' => $item,
        ':antiguidade' => $value
    ));
}
//-------------------------------------------------------------------//

//-------------------------------------------------------------------//
//INSERIR O REGISTRO DE PROMOÇÃO NO BANCO DE DADOS
// *** CORREÇÃO: O loop agora itera sobre '$militar_id', que contém apenas os militares selecionados ***

//array para gravar os militares que tiveram os registros alterados
$alteracoes = array();

foreach ($militar_id as $item) { // <--- CORRIGIDO!
    // O ID do militar agora é a variável '$item' diretamente.

    //SELECT para buscar no BD resultado igual ao informado
    //A query aqui precisa ser parametrizada para evitar SQL Injection
    $stmt = $conn->prepare("SELECT * FROM registro_de_promocoes WHERE militar_id = :militar_id AND  grau_hierarquico = :grau_hierarquico");
    $stmt->execute([
        ':militar_id' => $item,
        ':grau_hierarquico' => $promocao_posto_grad
    ]);

    //se encontra registro com o mesmo grau_hierarquico, atualiza o registo no BD
    if ($stmt->fetch(PDO::FETCH_ASSOC)) {
        $stmt_update = $conn->prepare("UPDATE registro_de_promocoes SET a_contar_de = :data_promocao, modalidade = :modalidade WHERE militar_id = :id AND grau_hierarquico = :promocao_posto_grad");
        $stmt_update->execute(array(
            ':id' => $item,
            ':data_promocao' => $data_promocao,
            ':modalidade' => $modalidade,
            ':promocao_posto_grad' => $promocao_posto_grad,
        ));
    } else {
        //se não encontrar nada, faz o insert na tabela registro_de_promocoes
        $stmt_insert = $conn->prepare("INSERT INTO registro_de_promocoes (a_contar_de, grau_hierarquico, modalidade, militar_id) VALUES (:data_promocao, :promocao_posto_grad, :modalidade, :id)");
        $stmt_insert->execute(array(
            ':id' => $item,
            ':data_promocao' => $data_promocao,
            ':modalidade' => $modalidade,
            ':promocao_posto_grad' => $promocao_posto_grad,
        ));
    }

    $alteracoes[] = $item; // Adiciona o ID do militar processado

    // Log de registro de promoção
    require_once __DIR__ . '/../Logger/LoggerFactory.php';
    $logger = LoggerFactory::createLogger();
    $logger->info('Usuário promoveu em lote', [
        'id' => $_SESSION['id'],
        'usuario' => $_SESSION['nome'],
        'email' => $_SESSION['email'],
        'perfil' => $_SESSION['nivel_de_acesso'],
        'sujeito' => $item
    ]);
}
//-------------------------------------------------------------------//


//se a promocão for POR REQUERIMENTO automaticamente é liberada a antiguidade e inativado o militar.
if ($modalidade == 'POR REQUERIMENTO') {
    $aux_id = $militar_id[0];
    header('Location:../Controllers/inativa_militar.php?id=' . $aux_id . '');
} else {

    //variável para montar a string do header Location
    $location = 'Location:../Views/listar_militares_promocao_em_lote' . $altera_header . '.php?criterio_posto_grad=' . $criterio_posto_grad . '&criterio_quadro=' . $criterio_quadro . '&';
    if (sizeof($alteracoes)) {
        $location .= "alteracoes_realizadas[]=" . implode("&alteracoes_realizadas[]=", $alteracoes);
    }
    header($location);
}
