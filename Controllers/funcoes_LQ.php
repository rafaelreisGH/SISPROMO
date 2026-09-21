<?php
require_once '../ConexaoDB/conexao.php';
$conn = Conexao::getConexao();
require_once '../Controllers/date_difference.php';
require_once '../Controllers/funcoes_intersticio.php';

/**
 * Processa a lista de candidatos aptos à promoção.
 * Refatorado para permitir busca independente de quadro.
 */
function processa_lista_de_candidatos($conn, $lq_ano, $quadro = null)
{
    // Construção dinâmica da cláusula de quadro
    // Se $quadro for nulo ou vazio, não aplicamos o filtro no SQL
    $filtroQuadro = "";
    if (!empty($quadro)) {
        $filtroQuadro = " AND militar.quadro_id = :quadro ";
    }

    $sql = "SELECT registro_de_promocoes.a_contar_de, 
                   registro_de_promocoes.grau_hierarquico, 
                   registro_de_promocoes.militar_id, 
                   militar.id, 
                   militar.nome, 
                   militar.posto_grad_mil, 
                   militar.quadro_id, 
                   militar.antiguidade, 
                   militar.data_cumprimento_intersticio
            FROM registro_de_promocoes 
            INNER JOIN militar ON registro_de_promocoes.militar_id = militar.id 
            WHERE militar.posto_grad_mil NOT IN ('TC BM', 'ST BM', 'CEL BM', 'AL SD BM') 
              AND militar.status = 'ATIVO'
              $filtroQuadro
            ORDER BY militar.antiguidade";

    $stmt = $conn->prepare($sql);

    if (!empty($quadro)) {
        $stmt->bindValue(':quadro', $quadro);
    }

    $stmt->execute();
    $consulta = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($consulta)) {
        header("Location:../Views/nenhum_resultado.php");
        exit();
    }

    $alteracoes_realizadas = [];

    foreach ($consulta as $resultado) {
        $aux_a_contar_de = $resultado['a_contar_de'];
        $aux_posto_grad = $resultado['grau_hierarquico'];
        $aux_nome = $resultado['nome'];
        $aux_posto_grad_atual = $resultado['posto_grad_mil'];
        $aux_quadro = $resultado['quadro_id'];
        $aux_cumprimento_intersticio = $resultado['data_cumprimento_intersticio'];
        $aux_id = $resultado['id'];

        if (is_null($aux_a_contar_de)) {
            continue;
        }

        $data_cumprimento = null;

        if (is_null($aux_cumprimento_intersticio)) {
            $intersticio = pega_intersticio($aux_posto_grad_atual, $conn);

            if ($intersticio !== null && is_numeric($intersticio)) {
                if ($intersticio < 1 && $intersticio > 0) {
                    $meses = $intersticio * 12;
                    $data_cumprimento = date_create($aux_a_contar_de)->modify("+{$meses} months");
                } else {
                    $data_cumprimento = date_create($aux_a_contar_de)->modify("+{$intersticio} years");
                }
            }
        } else {
            $data_cumprimento = date_create($aux_cumprimento_intersticio);
        }

        // Validação contra o limite anual (31/12 do ano base)
        $data_limite_ano = date_create($lq_ano . '-12-31');

        if ($data_cumprimento && ($data_cumprimento <= $data_limite_ano) && ($aux_posto_grad == $aux_posto_grad_atual)) {
            // Retornamos os dados em string separada por vírgula para manter compatibilidade com o explode do front-end
            $alteracoes_realizadas[] = "$aux_a_contar_de,$aux_posto_grad,$aux_nome,$aux_quadro,$aux_id";
        }
    }

    if (empty($alteracoes_realizadas)) {
        header("Location:../Views/nenhum_resultado.php");
        exit();
    }

    return $alteracoes_realizadas;
}

/**
 * Processa documentos de candidatos para exibição de status.
 */
function processa_documentos_de_candidatos($conn, $semestre, $ano, $lista)
{
    $id_dos_militares = isola_id_candidatos($lista);
    $resultados = [];

    foreach ($id_dos_militares as $militar_id) {
        $stmt = $conn->prepare("SELECT DISTINCT
            militar.id, militar.posto_grad_mil, militar.nome, militar.antiguidade, militar.quadro_id,
            documento_promocao.doc_promo_nome, documento_promocao.doc_status_id
        FROM militar
        INNER JOIN pasta_promocional ON pasta_promocional.militar_id = militar.id
        LEFT JOIN documento_promocao ON documento_promocao.pasta_promocional_id = pasta_promocional.id
        WHERE pasta_promocional.ano_processo_promocional = :ano 
          AND pasta_promocional.semestre_processo_promocional = :semestre
          AND militar.id = :militar_id
          AND militar.posto_grad_mil NOT IN ('TC BM', 'ST BM', 'CEL BM', 'AL SD BM')
          AND militar.status = 'ATIVO'
        ORDER BY militar.antiguidade;");

        $stmt->bindValue(':ano', $ano, PDO::PARAM_INT);
        $stmt->bindValue(':semestre', $semestre, PDO::PARAM_INT);
        $stmt->bindValue(':militar_id', $militar_id, PDO::PARAM_INT);
        $stmt->execute();

        $consulta = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($consulta)) {
            foreach ($consulta as &$dado) {
                $dado['doc_status_id'] = traduzStatusDocumento($dado['doc_status_id']);
            }
            $resultados[$militar_id] = $consulta;
        }
    }
    return $resultados;
}

/*--------------------------------------------------------------------------*/
/*
- Funções específicas para os TC
*/

function processa_documentos_de_candidatos_TC($conn, $semestre, $ano, $lista)
{
    $lista = isola_id_candidatos($lista);
    $id_dos_militares = array_map('intval', $lista);

    foreach ($id_dos_militares as $item) {
        $id_dos_militares[$item] = $item;
    }

    $resultados = []; // Array para armazenar os resultados
    foreach ($id_dos_militares as $item) {
        $stmt = $conn->prepare("SELECT DISTINCT
            militar.id,
            militar.posto_grad_mil, 
            militar.nome,
            militar.antiguidade,
            militar.quadro_id,
            documento_promocao.doc_promo_nome,
            documento_promocao.doc_status_id
        FROM militar
            INNER JOIN pasta_promocional 
                ON pasta_promocional.militar_id = militar.id
            LEFT JOIN documento_promocao 
                ON documento_promocao.pasta_promocional_id = pasta_promocional.id
        WHERE
            pasta_promocional.ano_processo_promocional = :ano 
            AND pasta_promocional.semestre_processo_promocional = :semestre
            AND militar.id = :militar_id
            AND militar.posto_grad_mil = 'TC BM'
            AND militar.status = 'ATIVO'
        ORDER BY militar.antiguidade;");

        $stmt->bindValue(':ano', $ano, PDO::PARAM_INT);
        $stmt->bindValue(':semestre', $semestre, PDO::PARAM_INT);
        $stmt->bindValue(':militar_id', $item, PDO::PARAM_INT);

        $stmt->execute();
        $consulta = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($consulta)) {
            $resultados[$item] = $consulta; // Armazena os resultados para cada militar
        } else {
            return false; // Retorna falso se não houver resultados
        }
    }

    foreach ($resultados as $militar_id => &$dados) {
        foreach ($dados as &$dado) {
            // Agora, você simplesmente chama a função que já existe
            $dado['doc_status_id'] = traduzStatusDocumento($dado['doc_status_id']);
        }
        unset($dados);
    }
    return $resultados;
}

function processa_lista_de_candidatos_TC($conn, $lq_ano)
{
    $consulta = $conn->query(
        "SELECT registro_de_promocoes.a_contar_de, registro_de_promocoes.grau_hierarquico, registro_de_promocoes.militar_id, militar.id, militar.nome, militar.posto_grad_mil, militar.quadro_id, militar.antiguidade, militar.data_cumprimento_intersticio, militar.id 
    FROM registro_de_promocoes
    CROSS JOIN militar
            WHERE registro_de_promocoes.militar_id = militar.id
            AND militar.posto_grad_mil = 'TC BM'
            AND militar.status = 'ATIVO'
            ORDER BY militar.antiguidade"
    )->fetchAll();

    if (empty($consulta)) {
        header("Location:../Views/nenhum_resultado.php");
        exit();
    }

    require_once 'funcoes_intersticio.php';
    $alteracoes_realizadas = [];

    foreach ($consulta as $resultado) {
        $aux_a_contar_de = $resultado['a_contar_de'];
        $aux_posto_grad = $resultado['grau_hierarquico'];
        $aux_nome = $resultado['nome'];
        $aux_posto_grad_atual = $resultado['posto_grad_mil'];
        $aux_quadro = $resultado['quadro_id'];
        $aux_cumprimento_intersticio = $resultado['data_cumprimento_intersticio'];
        $aux_id = $resultado['id'];

        if ($aux_a_contar_de == null) {
            continue;
        } else {
            if (is_null($aux_cumprimento_intersticio)) {
                $intervalo = dateDifference($lq_ano, $aux_a_contar_de);
                $intersticio = pega_intersticio($aux_posto_grad_atual, $conn);

                if (($intervalo >= $intersticio) && ($aux_posto_grad == $aux_posto_grad_atual)) {
                    $alteracoes_realizadas[] = "$aux_a_contar_de,$aux_posto_grad,$aux_nome,$aux_quadro,$aux_id";
                }
            } else {
                if ((tem_intersticio($lq_ano, $aux_cumprimento_intersticio)) && ($aux_posto_grad == $aux_posto_grad_atual)) {
                    $alteracoes_realizadas[] = "$aux_cumprimento_intersticio,$aux_posto_grad,$aux_nome,$aux_quadro,$aux_id";
                }
            }
        }
    }
    if (empty($alteracoes_realizadas)) {
        header("Location:../Views/nenhum_resultado.php");
        exit();
    }

    return $alteracoes_realizadas;
}


/*--------------------------------------------------------------------------*/

function isola_id_candidatos($lista)
{
    $id_dos_militares = [];
    if (isset($lista) && is_array($lista)) {
        foreach ($lista as $item) {
            $partes = explode(",", $item);
            if (isset($partes[4])) {
                $id_dos_militares[] = (int)$partes[4];
            }
        }
    }
    return $id_dos_militares;
}

function obterAnoESemestre($data)
{
    $dataObj = DateTime::createFromFormat("Y-m-d", $data);
    if (!$dataObj) {
        $dataObj = DateTime::createFromFormat("d/m/Y", $data);
    }

    if (!$dataObj) return ["erro" => "Data inválida"];

    $ano = $dataObj->format("Y");
    $mesDia = $dataObj->format("m-d");

    // Lógica de semestres baseada em datas específicas do CBM
    $semestre = ($mesDia <= "07-02") ? 1 : 2;

    return ["ano" => $ano, "semestre" => $semestre];
}

function traduzStatusDocumento($doc_status_id)
{
    $status = [
        1 => 'O.K.',
        2 => 'N.E.',
        3 => 'E.R.',
        4 => 'C.P.'
    ];
    return $status[$doc_status_id] ?? 'N.E.';
}

function filtrar_sd($lista)
{
    $resultado = [];
    $contador_sd = 0;

    if (isset($lista)) {
        foreach ($lista as $item) {
            $dados = explode(",", $item);

            if (!isset($dados[1])) {
                continue; // ignora se não houver posto/graduação
            }

            $posto_grad = trim($dados[1]);

            if ($posto_grad === 'SD BM') {
                if ($contador_sd >= 100) {
                    continue; // ignora SD BM excedentes
                }
                $contador_sd++;
            }

            // Adiciona o item completo ao resultado
            $resultado[] = $item;
        }
    }

    return $resultado;
}

function criarPastaPromocionalEmLote($lista, $lq_ano, $conn)
{
    $id = isola_id_candidatos($lista);
    $data = obterAnoESemestre($lq_ano);
    $ano_processo_promocional = $data['ano'];
    $semestre_processo_promocional = $data['semestre'];

    $sucesso = []; // array para armazenar os id das pastas promocionais

    foreach ($id as $item) {
        // Verifica se já existe o registro
        $stmt = $conn->prepare("SELECT COUNT(*) FROM pasta_promocional 
                                WHERE militar_id = :militar_id 
                                AND ano_processo_promocional = :ano 
                                AND semestre_processo_promocional = :semestre");

        $stmt->bindValue(':militar_id', $item, PDO::PARAM_INT);
        $stmt->bindValue(':ano', $ano_processo_promocional, PDO::PARAM_INT);
        $stmt->bindValue(':semestre', $semestre_processo_promocional, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->fetchColumn() == 0) { // Se não existir, insere
            $stmt = $conn->prepare("INSERT INTO pasta_promocional (militar_id, ano_processo_promocional, semestre_processo_promocional) 
                                    VALUES (:militar_id, :ano_processo_promocional, :semestre_processo_promocional)");

            $stmt->bindValue(':militar_id', $item, PDO::PARAM_INT);
            $stmt->bindValue(':ano_processo_promocional', $ano_processo_promocional, PDO::PARAM_INT);
            $stmt->bindValue(':semestre_processo_promocional', $semestre_processo_promocional, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $id = $conn->lastInsertId(); // Obtém o ID da pasta promocional criada
                $sucesso[] = ['id_militar' => (int)$item, 'id_pasta' => (int)$id]; // Adiciona o ID ao array de sucesso
            }
        }
    }
    return $sucesso;
}

function criaDocumentosVazios($pastas_id, $conn)
{
    $sucesso = []; // Alterado de false para []

    $query = "INSERT INTO documento_promocao (militar_id, pasta_promocional_id, doc_promo_nome, doc_status_id) VALUES (:militar_id, :pasta, 'cert_1JE', 2), (:militar_id, :pasta, 'cert_2JE', 2), (:militar_id, :pasta, 'cert_1JF', 2), (:militar_id, :pasta, 'cert_2JF', 2), (:militar_id, :pasta, 'cert_tse', 2), (:militar_id, :pasta, 'fad', 2), (:militar_id, :pasta, 'rta', 2), (:militar_id, :pasta, 'ais', 2)";
    $complemento = "";

    try {
        $stmt = $conn->prepare("SELECT posto_grad_mil FROM militar WHERE id = :militar_id");
        $stmt->bindValue(':militar_id', $pastas_id[0]["id_militar"], PDO::PARAM_INT);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($resultado && $resultado["posto_grad_mil"] == "TC BM") {
            $complemento = ", (:militar_id, :pasta, 'fp', 2)";
        }
    } catch (PDOException $e) {
        echo "Erro ao consultar: " . $e->getMessage();
    }

    $query .= $complemento;
    unset($stmt);

    foreach ($pastas_id as $item) {
        try {
            $stmt = $conn->prepare($query);

            $stmt->bindValue(':militar_id', $item["id_militar"], PDO::PARAM_INT);
            $stmt->bindValue(':pasta', $item["id_pasta"], PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $id = $conn->lastInsertId();
                $sucesso[] = ['id_militar' => (int)$item["id_militar"], 'id_pasta' => (int)$id];
            }
        } catch (PDOException $e) {
            echo "Erro ao inserir: " . $e->getMessage();
        }
    }

    return $sucesso; // Retorna um array, mesmo que esteja vazio
}


function extrairDadosRecursivo($documentacao, &$resultado = [])
{
    foreach ($documentacao as $key => $item) {
        $resultado[] = array_merge([$key, $item[0]["posto_grad_mil"], $item[0]["nome"], $item[0]["quadro_id"]], extrairStatus($item, 0));
    }
    return $resultado;
}

function extrairStatus($item, $index)
{
    if (!isset($item[$index]["doc_status_id"])) {
        return []; // Base da recursão: retorna um array vazio se o índice não existir
    }
    return array_merge([$item[$index]["doc_status_id"]], extrairStatus($item, $index + 1));
}

function consultaDocumentosPelaPasta($id_da_pasta, $conn)
{
    $stmt = $conn->prepare("SELECT * FROM documento_promocao WHERE pasta_promocional_id = :pasta_id");
    $stmt->bindParam(':pasta_id', $id_da_pasta, PDO::PARAM_INT);
    $stmt->execute();

    $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

    //se não haver resultados, para a execução do código.
    if (!empty($resultado)) {
        return $resultado; // Retorna falso se não houver resultados
    } else {
        return false; // Retorna falso se não houver resultados
    }
}
