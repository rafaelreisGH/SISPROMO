<?php

namespace App\DAO;

use PDO;
use Conexao;

class CriterioFalhoDAO
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = Conexao::getConexao();
    }

    public function registrarFalha(int $militarId, int $reuniaoId, string $descricaoImpedimento, string $incisoArtigo36): bool
    {
        $sql = "INSERT INTO criterio_falho (militar_id, reuniao_comissao_id, descricao_impedimento, inciso_artigo_36) 
                VALUES (:militar_id, :reuniao_id, :descricao, :inciso)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':militar_id', $militarId, PDO::PARAM_INT);
        $stmt->bindParam(':reuniao_id', $reuniaoId, PDO::PARAM_INT);
        $stmt->bindParam(':descricao', $descricaoImpedimento, PDO::PARAM_STR);
        $stmt->bindParam(':inciso', $incisoArtigo36, PDO::PARAM_STR);

        return $stmt->execute();
    }

    /**
     * Busca falhas de um militar em TODAS as reuniões do mesmo Processo e Comissão.
     */
    public function buscarFalhasPorMilitarEProcesso(int $militarId, int $processoId, string $tipoComissao): array
    {
        $sql = "SELECT cf.*, rc.numero_ata 
                FROM criterio_falho cf
                INNER JOIN reuniao_comissao rc ON rc.id_reuniao_comissao = cf.reuniao_comissao_id
                WHERE cf.militar_id = :militar_id 
                  AND rc.processo_promocional_id = :processo_id 
                  AND rc.tipo_comissao = :tipo_comissao
                ORDER BY cf.id_criterio_falho ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':militar_id', $militarId, PDO::PARAM_INT);
        $stmt->bindParam(':processo_id', $processoId, PDO::PARAM_INT);
        $stmt->bindParam(':tipo_comissao', $tipoComissao, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retorna a lista de IDs de todos os militares que possuem algum impedimento 
     * registrado em qualquer reunião do mesmo Processo Promocional e Comissão.
     *
     * @param int $processoId
     * @param string $tipoComissao
     * @return array Lista de IDs (inteiros) dos militares
     */
    public function buscarTodosImpedidosPorProcesso(int $processoId, string $tipoComissao): array
    {
        $sql = "SELECT DISTINCT cf.militar_id 
                FROM criterio_falho cf
                INNER JOIN reuniao_comissao rc ON rc.id_reuniao_comissao = cf.reuniao_comissao_id
                WHERE rc.processo_promocional_id = :processo_id 
                  AND rc.tipo_comissao = :tipo_comissao";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':processo_id', $processoId, PDO::PARAM_INT);
        $stmt->bindParam(':tipo_comissao', $tipoComissao, PDO::PARAM_STR);
        $stmt->execute();

        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    public function buscarFalhasPorMilitar(int $militarId, int $reuniaoId): array
    {
        $sql = "SELECT * FROM criterio_falho 
                WHERE militar_id = :militar_id 
                AND reuniao_comissao_id = :reuniao_id
                ORDER BY id_criterio_falho ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':militar_id', $militarId, PDO::PARAM_INT);
        $stmt->bindParam(':reuniao_id', $reuniaoId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarTodosImpedidos(int $reuniaoId): array
    {
        $sql = "SELECT DISTINCT militar_id FROM criterio_falho 
                WHERE reuniao_comissao_id = :reuniao_id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':reuniao_id', $reuniaoId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}