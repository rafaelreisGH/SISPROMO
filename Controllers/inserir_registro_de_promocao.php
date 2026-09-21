<?php
include_once '../Controllers/controle_de_sessao.php';

require_once '../ConexaoDB/conexao.php';
$conn = Conexao::getConexao();

//verificação se foram settados os critérios de pesquisa
if (isset($_POST['data_promocao'])) {
    $data_promocao = $_POST['data_promocao'];
}

if (isset($_POST['modalidade'])) {
    $modalidade = $_POST['modalidade'];
}

if (isset($_POST['promocao_posto_grad'])) {
    $promocao_posto_grad = $_POST['promocao_posto_grad'];
}
//---------------------------//

//array para gravar os militares que tiveram os registros alterados
$alteracoes = array();

//variável para montar a string do header Location
$location = "Location:../Views/listar_militares_data_em_lote.php?";

if (isset($_POST['atualizar'])) {
    if (isset($_POST['militar_id'])) {
        $militar_id = $_POST['militar_id'];
        $aux = (int)$militar_id[0]; //incluído por causa do if PROMOÇÃO POR REQUERIMENTO

        foreach ($militar_id as $item) {
            //SELECT para buscar no BD resultado igual ao informado
            $stmt = $conn->prepare("SELECT * FROM registro_de_promocoes WHERE militar_id = " . $item . " AND  grau_hierarquico = '" . $promocao_posto_grad . "'");
            $resultado = $stmt->execute();

            //se encontra registro com o mesmo grau_hierarquico, atualiza o registo no BD
            //isso porque não pode haver duas promoções para o mesmo grau_hierarquico
            if ($resultado = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $stmt = $conn->prepare("UPDATE registro_de_promocoes SET a_contar_de = :data_promocao, modalidade = :modalidade, grau_hierarquico = :promocao_posto_grad  WHERE militar_id = :id AND grau_hierarquico = :promocao_posto_grad");
                $stmt->execute(array(
                    ':id' => $item,
                    ':data_promocao' => $data_promocao,
                    ':modalidade' => $modalidade,
                    ':promocao_posto_grad' => $promocao_posto_grad,
                ));

                //atualiza a tabela militar
                $stmt = $conn->prepare("UPDATE militar SET ultima_promocao = :data_promocao  WHERE id = :id");
                $stmt->execute(array(
                    ':id' => $item,
                    ':data_promocao' => $data_promocao,
                ));

                // A LINHA REDUNDANTE FOI REMOVIDA DAQUI

            } else {
                //se não encontrar nada, faz o insert na tabela registro_de_promocoes
                //FAZER INSERT NO BD

                $stmt = $conn->prepare("INSERT INTO registro_de_promocoes (a_contar_de, grau_hierarquico, modalidade, militar_id) VALUES (:data_promocao, :promocao_posto_grad, :modalidade, :id)");

                $stmt->execute(array(
                    ':id' => $item,
                    ':data_promocao' => $data_promocao,
                    ':modalidade' => $modalidade,
                    ':promocao_posto_grad' => $promocao_posto_grad,
                ));

                //atualiza a tabela militar
                $stmt = $conn->prepare("UPDATE militar SET ultima_promocao = :data_promocao  WHERE id = :id");
                $stmt->execute(array(
                    ':id' => $item,
                    ':data_promocao' => $data_promocao,
                ));

            }
            // ESTA LINHA GARANTE QUE O ID SEJA ADICIONADO APENAS UMA VEZ, SEJA EM CASO DE UPDATE OU INSERT.
            $alteracoes_realizadas[] = $item;
            
            // Log de registro de promoção passada
            require_once __DIR__ . '/../Logger/LoggerFactory.php';
            $logger = LoggerFactory::createLogger();
            $logger->info('Usuário registrou promoção passada', [
                'id' => $_SESSION['id'],
                'usuario' => $_SESSION['nome'],
                'email' => $_SESSION['email'],
                'perfil' => $_SESSION['nivel_de_acesso'],
                'sujeito' => $item
            ]);
        }
    } else {
        header('Location:../Views/listar_militares_data_em_lote.php?nada_alterado=1');
    }
}