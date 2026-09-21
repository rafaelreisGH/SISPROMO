<?php

namespace App\DAO;

require_once __DIR__ . '/../../ConexaoDB/conexao.php';
use Conexao;
use PDO;
use PDOException;

class ContainerPostoMapDAO
{
    private PDO $conexao;

    public function __construct()
    {
        $this->conexao = Conexao::getConexao();
    }

    public function buscarConteinerPorPosto(string $postoGradMil): ?array
    {
        try {
            $sql = "SELECT cpm.*, vf.nome_do_agrupamento, vf.quantidade as limite_vagas, vf.ajuste_manual 
                    FROM container_posto_map cpm
                    INNER JOIN vagas_fixadas vf ON cpm.vagas_fixadas_id = vf.id_vagas_fixadas
                    WHERE cpm.posto_grad_mil = :posto";
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(':posto', $postoGradMil, PDO::PARAM_STR);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("Erro em ContainerPostoMapDAO->buscarConteinerPorPosto: " . $e->getMessage());
            throw $e;
        }
    }

    public function buscarPostosPorConteiner(int $vagasFixadasId): array
    {
        try {
            $sql = "SELECT * FROM container_posto_map WHERE vagas_fixadas_id = :id";
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(':id', $vagasFixadasId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro em ContainerPostoMapDAO->buscarPostosPorConteiner: " . $e->getMessage());
            throw $e;
        }
    }

    public function listarTodos(): array
    {
        try {
            $sql = "SELECT cpm.*, vf.nome_do_agrupamento 
                    FROM container_posto_map cpm
                    INNER JOIN vagas_fixadas vf ON cpm.vagas_fixadas_id = vf.id_vagas_fixadas
                    ORDER BY cpm.vagas_fixadas_id, cpm.id";
            $stmt = $this->conexao->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro em ContainerPostoMapDAO->listarTodos: " . $e->getMessage());
            throw $e;
        }
    }
}
