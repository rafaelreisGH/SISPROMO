<?php

namespace App\DAO;

use PDO;
use Conexao;
use Exception;

require_once __DIR__ . '/../../ConexaoDB/conexao.php';

/**
 * DAO responsável pela gestão e persistência da tabela `militar`.
 */
class MilitarDAO
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = Conexao::getConexao();
    }

    /**
     * Busca um militar pelo ID.
     */
    public function buscarPorId(int $id, bool $forUpdate = false): ?array
    {
        $sql = "SELECT m.*, q.nome_quadro 
                FROM militar m
                LEFT JOIN quadro q ON m.quadro_id = q.id_quadro
                WHERE m.id = :id";

        if ($forUpdate) {
            $sql .= " FOR UPDATE";
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ?: null;
    }

    /**
     * Busca todos os militares (Ativos e Inativos).
     * Os Ativos vêm no topo ordenados por antiguidade; os Inativos ao final.
     */
    public function buscarTodosMilitares(): array
    {
        $sql = "SELECT m.*, q.nome_quadro 
                FROM militar m
                LEFT JOIN quadro q ON m.quadro_id = q.id_quadro
                ORDER BY 
                    CASE WHEN m.status = 'ATIVO' THEN 0 ELSE 1 END ASC,
                    m.antiguidade ASC,
                    m.id ASC";

        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca apenas os militares ativos (inclusive CEL BM), ordenados por antiguidade.
     */
    public function buscarAtivos(?string $nome = null, ?string $posto = null): array
    {
        $sql = "SELECT m.*, q.nome_quadro 
                FROM militar m
                LEFT JOIN quadro q ON m.quadro_id = q.id_quadro
                WHERE m.status = 'ATIVO'";

        $params = [];

        if (!empty($nome)) {
            $sql .= " AND m.nome LIKE :nome";
            $params[':nome'] = "%{$nome}%";
        }

        if (!empty($posto)) {
            $sql .= " AND m.posto_grad_mil = :posto";
            $params[':posto'] = $posto;
        }

        $sql .= " ORDER BY m.antiguidade ASC, m.id ASC";

        $stmt = $this->conn->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retorna a conexão PDO utilizada pelo DAO.
     */
    public function getConexao(): PDO
    {
        return $this->conn;
    }

    /**
     * Altera diretamente o status de um militar ('ATIVO' ou 'INATIVO').
     * Permite passar conexão transacional aberta.
     */
    public function alterarStatus(int $id, string $novoStatus, ?PDO $conn = null): bool
    {
        $conexao = $conn ?? $this->conn;
        $sql = "UPDATE militar SET status = :status WHERE id = :id";
        $stmt = $conexao->prepare($sql);
        $stmt->bindValue(':status', $novoStatus, PDO::PARAM_STR);
        $stmt->bindValue(':id',     $id,         PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Inativa o militar e reordena a antiguidade dos militares subsequentes
     * utilizando a função legada `liberarAntiguidade`.
     *
     * @param int $militarId
     * @return array
     * @throws Exception
     */
    public function inativarEReordenar(int $militarId): array
    {
        try {
            $this->conn->beginTransaction();

            // 1. Busca o militar com trava de linha
            $militar = $this->buscarPorId($militarId, true);

            if (!$militar) {
                throw new Exception("Militar não encontrado (ID: {$militarId}).");
            }

            if ($militar['status'] === 'INATIVO') {
                $this->conn->rollBack();
                return [
                    'ja_inativo' => true,
                    'militar'    => $militar
                ];
            }

            $antiguidade = !empty($militar['antiguidade']) ? (int)$militar['antiguidade'] : 0;
            $numeroLiberado = $antiguidade;

            // 2. Se possuir antiguidade ativa, reordena a fila posterior
            if ($antiguidade > 0) {
                $stmtFila = $this->conn->prepare("
                    SELECT id, antiguidade 
                    FROM militar 
                    WHERE antiguidade >= :antiguidade 
                      AND status = 'ATIVO' 
                    ORDER BY antiguidade ASC
                ");
                $stmtFila->bindValue(':antiguidade', $antiguidade, PDO::PARAM_INT);
                $stmtFila->execute();
                $militaresPosteriores = $stmtFila->fetchAll(PDO::FETCH_ASSOC);

                $vetor = [];
                foreach ($militaresPosteriores as $row) {
                    $vetor[(string)$row['id']] = (int)$row['antiguidade'];
                }

                // Inativa o militar e limpa sua antiguidade
                $stmtInativa = $this->conn->prepare("UPDATE militar SET antiguidade = NULL, status = 'INATIVO' WHERE id = :id");
                $stmtInativa->bindValue(':id', $militarId, PDO::PARAM_INT);
                $stmtInativa->execute();

                // Inclui a função de liberação de antiguidade
                if (file_exists(__DIR__ . '/../../Controllers/libera_antiguidade.php')) {
                    require_once __DIR__ . '/../../Controllers/libera_antiguidade.php';
                    $vetorReordenado = liberarAntiguidade($vetor, $numeroLiberado);

                    $stmtUpd = $this->conn->prepare("UPDATE militar SET antiguidade = :antiguidade WHERE id = :id");
                    foreach ($vetorReordenado as $idItem => $novaAntiguidade) {
                        $stmtUpd->bindValue(':antiguidade', $novaAntiguidade, PDO::PARAM_INT);
                        $stmtUpd->bindValue(':id', (int)$idItem, PDO::PARAM_INT);
                        $stmtUpd->execute();
                    }
                }
            } else {
                // Inativação sem necessidade de reordenação numérica
                $stmtInativa = $this->conn->prepare("UPDATE militar SET antiguidade = NULL, status = 'INATIVO' WHERE id = :id");
                $stmtInativa->bindValue(':id', $militarId, PDO::PARAM_INT);
                $stmtInativa->execute();
            }

            $this->conn->commit();

            return [
                'ja_inativo' => false,
                'militar'    => $militar
            ];

        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Alterna a condição de excedente de um militar (1 = Excedente, 0 = Efetivo Regular).
     * O militar excedente continua ativo e preserva sua antiguidade, mas libera sua vaga no contêiner.
     * Permite conexão transacional aberta.
     *
     * @param int $militarId ID do militar a ser atualizado.
     * @param int $isExcedente 1 para marcar como excedente, 0 para marcar como efetivo regular.
     * @param PDO|null $conn Conexão PDO opcional.
     * @return bool True em caso de sucesso na atualização, False caso contrário.
     */
    public function alternarExcedente(int $militarId, int $isExcedente, ?PDO $conn = null): bool
    {
        $conexao = $conn ?? $this->conn;
        $valor = ($isExcedente === 1) ? 1 : 0;
        $sql = "UPDATE militar SET is_excedente = :is_excedente WHERE id = :id";
        $stmt = $conexao->prepare($sql);
        $stmt->bindValue(':is_excedente', $valor, PDO::PARAM_INT);
        $stmt->bindValue(':id',           $militarId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Alias para alternarExcedente.
     */
    public function alterarExcedente(int $militarId, int $isExcedente, ?PDO $conn = null): bool
    {
        return $this->alternarExcedente($militarId, $isExcedente, $conn);
    }
}