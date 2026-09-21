<?php

namespace App\DAO;

use PDO;
use Conexao;
use Exception;

require_once __DIR__ . '/../../ConexaoDB/conexao.php';

/**
 * Classe DAO para gestão do histórico imutável de atos administrativos do militar.
 * 
 * Registra formalmente publicações (DOE, BGE, Portarias, Decretos) referentes a
 * inativação, reativação e condição de excedente, garantindo rastreabilidade jurídica.
 */
class MilitarHistoricoEventosDAO
{
    private PDO $conn;

    public function __construct(?PDO $conn = null)
    {
        $this->conn = $conn ?? Conexao::getConexao();
    }

    /**
     * Registra um novo evento administrativo no histórico.
     * Pode ser executado em uma conexão transacional já aberta ($connTransacao)
     * para garantir atomicidade com a tabela principal de militares.
     *
     * @param array $dados Metadados do evento administrativo.
     * @param PDO|null $connTransacao Conexão PDO opcional (para transações abertas).
     * @return int ID do evento registrado.
     * @throws Exception Em caso de parâmetros inválidos ou falha na inserção.
     */
    public function registrarEvento(array $dados, ?PDO $connTransacao = null): int
    {
        $conexao = $connTransacao ?? $this->conn;

        $militarId        = !empty($dados['militar_id']) ? (int)$dados['militar_id'] : 0;
        $tipoEvento       = trim((string)($dados['tipo_evento'] ?? ''));
        $urlDocumento     = !empty($dados['url_documento']) ? trim((string)$dados['url_documento']) : null;
        $numeroPublicacao = !empty($dados['numero_publicacao']) ? trim((string)$dados['numero_publicacao']) : null;
        $tipoPublicacao   = !empty($dados['tipo_publicacao']) ? trim((string)$dados['tipo_publicacao']) : null;
        $dataVigencia     = !empty($dados['data_vigencia']) ? trim((string)$dados['data_vigencia']) : null;
        $observacao       = !empty($dados['observacao']) ? trim((string)$dados['observacao']) : null;
        $usuarioId        = !empty($dados['usuario_id']) ? (int)$dados['usuario_id'] : null;

        $tiposValidos = ['INATIVACAO', 'REATIVACAO', 'EXCEDENTE_ATIVADO', 'EXCEDENTE_DESATIVADO'];
        if (!$militarId || !in_array($tipoEvento, $tiposValidos, true)) {
            throw new Exception("Dados obrigatórios inválidos para registro de evento administrativo.");
        }

        $sql = "INSERT INTO militar_historico_eventos (
                    militar_id,
                    tipo_evento,
                    url_documento,
                    numero_publicacao,
                    tipo_publicacao,
                    data_vigencia,
                    observacao,
                    usuario_id
                ) VALUES (
                    :militar_id,
                    :tipo_evento,
                    :url_documento,
                    :numero_publicacao,
                    :tipo_publicacao,
                    :data_vigencia,
                    :observacao,
                    :usuario_id
                )";

        $stmt = $conexao->prepare($sql);
        $stmt->bindValue(':militar_id',        $militarId,        PDO::PARAM_INT);
        $stmt->bindValue(':tipo_evento',       $tipoEvento,       PDO::PARAM_STR);
        $stmt->bindValue(':url_documento',     $urlDocumento,     is_null($urlDocumento)     ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':numero_publicacao', $numeroPublicacao, is_null($numeroPublicacao) ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':tipo_publicacao',   $tipoPublicacao,   is_null($tipoPublicacao)   ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':data_vigencia',     $dataVigencia,     is_null($dataVigencia)     ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':observacao',       $observacao,       is_null($observacao)       ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':usuario_id',        $usuarioId,        is_null($usuarioId)        ? PDO::PARAM_NULL : PDO::PARAM_INT);

        $stmt->execute();

        return (int)$conexao->lastInsertId();
    }

    /**
     * Busca o histórico completo de eventos de um militar em ordem cronológica decrescente.
     *
     * @param int $militarId
     * @return array Lista de eventos com dados do usuário que fez o registro.
     */
    public function buscarHistoricoPorMilitar(int $militarId): array
    {
        $sql = "SELECT h.*, 
                       u.nome AS usuario_nome,
                       u.posto_grad_usuario
                FROM militar_historico_eventos h
                LEFT JOIN usuarios u ON h.usuario_id = u.id
                WHERE h.militar_id = :militar_id
                ORDER BY h.criado_em DESC, h.id_evento DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':militar_id', $militarId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca o último evento registrado para um militar específico.
     *
     * @param int $militarId
     * @return array|null Dados do último evento ou null se não houver.
     */
    public function buscarUltimoEventoPorMilitar(int $militarId): ?array
    {
        $sql = "SELECT h.*, 
                       u.nome AS usuario_nome,
                       u.posto_grad_usuario
                FROM militar_historico_eventos h
                LEFT JOIN usuarios u ON h.usuario_id = u.id
                WHERE h.militar_id = :militar_id
                ORDER BY h.criado_em DESC, h.id_evento DESC
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':militar_id', $militarId, PDO::PARAM_INT);
        $stmt->execute();

        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ?: null;
    }

    /**
     * Retorna os últimos eventos de todos os militares agrupados por militar_id.
     * Otimizado para alimentar a tabela sem consultas repetidas (N+1).
     *
     * @return array Mapa no formato [militar_id => dados_evento]
     */
    public function buscarUltimosEventosMilitares(): array
    {
        $sql = "SELECT h.*
                FROM militar_historico_eventos h
                INNER JOIN (
                    SELECT militar_id, MAX(id_evento) as max_id
                    FROM militar_historico_eventos
                    GROUP BY militar_id
                ) ultimos ON h.id_evento = ultimos.max_id";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $mapa = [];
        foreach ($registros as $row) {
            $mapa[(int)$row['militar_id']] = $row;
        }

        return $mapa;
    }
}
