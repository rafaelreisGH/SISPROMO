<?php

namespace App\DAO;

use PDO;
use Conexao;

/**
 * DAO responsável pela gestão dos registros de conceito moral na ata de promoção.
 * Lida com o Tópico 1: militares sem conceito moral julgados pela comissão.
 */
class ConceitoMoralDAO
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = Conexao::getConexao();
    }

    /**
     * Marca um militar como "sem conceito moral" para uma determinada reunião.
     */
    public function marcarSemConceitoMoral(int $militarId, int $reuniaoId, ?string $parecer = null): bool
    {
        $sqlCheck = "SELECT id_conceito_moral FROM conceito_moral 
                     WHERE militar_id = :militar_id AND reuniao_comissao_id = :reuniao_id";
        $stmtCheck = $this->conn->prepare($sqlCheck);
        $stmtCheck->bindParam(':militar_id', $militarId, PDO::PARAM_INT);
        $stmtCheck->bindParam(':reuniao_id', $reuniaoId, PDO::PARAM_INT);
        $stmtCheck->execute();

        if ($stmtCheck->rowCount() > 0) {
            $sql = "UPDATE conceito_moral SET favoravel = 0, parecer = :parecer 
                    WHERE militar_id = :militar_id AND reuniao_comissao_id = :reuniao_id";
        } else {
            $sql = "INSERT INTO conceito_moral (militar_id, reuniao_comissao_id, favoravel, parecer) 
                    VALUES (:militar_id, :reuniao_id, 0, :parecer)";
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':militar_id', $militarId, PDO::PARAM_INT);
        $stmt->bindParam(':reuniao_id', $reuniaoId, PDO::PARAM_INT);
        $stmt->bindParam(':parecer', $parecer, PDO::PARAM_STR);

        return $stmt->execute();
    }

    /**
     * Remove a marcação de "sem conceito moral" de um militar no escopo de todo o Processo e Comissão.
     */
    public function desmarcarPendenciaNoProcesso(int $militarId, int $processoId, string $tipoComissao): bool
    {
        $sql = "DELETE cm FROM conceito_moral cm
                INNER JOIN reuniao_comissao rc ON rc.id_reuniao_comissao = cm.reuniao_comissao_id
                WHERE cm.militar_id = :militar_id 
                  AND rc.processo_promocional_id = :processo_id 
                  AND rc.tipo_comissao = :tipo_comissao";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':militar_id', $militarId, PDO::PARAM_INT);
        $stmt->bindParam(':processo_id', $processoId, PDO::PARAM_INT);
        $stmt->bindParam(':tipo_comissao', $tipoComissao, PDO::PARAM_STR);

        return $stmt->execute();
    }

    /**
     * Mantido por retrocompatibilidade (remove apenas por reunião específica).
     */
    public function desmarcarPendencia(int $militarId, int $reuniaoId): bool
    {
        $sql = "DELETE FROM conceito_moral 
                WHERE militar_id = :militar_id AND reuniao_comissao_id = :reuniao_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':militar_id', $militarId, PDO::PARAM_INT);
        $stmt->bindParam(':reuniao_id', $reuniaoId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Busca todos os militares marcados como sem conceito moral em TODAS as reuniões do mesmo Processo e Comissão.
     */
    public function buscarSemConceitoPorProcesso(int $processoId, string $tipoComissao): array
    {
        $sql = "SELECT cm.*, m.nome, m.posto_grad_mil, m.cpf, rc.numero_ata 
                FROM conceito_moral cm
                INNER JOIN militar m ON m.id = cm.militar_id
                INNER JOIN reuniao_comissao rc ON rc.id_reuniao_comissao = cm.reuniao_comissao_id
                WHERE rc.processo_promocional_id = :processo_id 
                  AND rc.tipo_comissao = :tipo_comissao 
                  AND cm.favoravel = 0
                ORDER BY m.antiguidade ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':processo_id', $processoId, PDO::PARAM_INT);
        $stmt->bindParam(':tipo_comissao', $tipoComissao, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca os militares sem conceito moral em uma reunião específica (usado na geração da Ata daquela sessão).
     */
    public function buscarSemConceitoMoral(int $reuniaoId): array
    {
        $sql = "SELECT cm.*, m.nome, m.posto_grad_mil, m.cpf 
                FROM conceito_moral cm
                INNER JOIN militar m ON m.id = cm.militar_id
                WHERE cm.reuniao_comissao_id = :reuniao_id AND cm.favoravel = 0
                ORDER BY m.antiguidade ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':reuniao_id', $reuniaoId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
