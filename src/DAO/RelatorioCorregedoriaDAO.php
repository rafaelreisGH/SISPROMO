<?php

namespace App\DAO;

use PDO;
use Conexao;

// Inclusão da conexão (ajuste de diretório)
require_once __DIR__ . '/../../ConexaoDB/conexao.php';

/**
 * DAO responsável pela gestão dos Relatórios da Corregedoria.
 */
class RelatorioCorregedoriaDAO
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = Conexao::getConexao();
    }

    public function salvar(int $ano, int $semestre, string $urlArquivo, ?string $descricao = null): int
    {
        $sql = "INSERT INTO relatorios_corregedoria (ano, semestre, descricao, url_arquivo)
                VALUES (:ano, :semestre, :descricao, :url_arquivo)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':ano',         $ano,        PDO::PARAM_INT);
        $stmt->bindParam(':semestre',    $semestre,   PDO::PARAM_INT);
        $stmt->bindParam(':descricao',   $descricao,  PDO::PARAM_STR);
        $stmt->bindParam(':url_arquivo', $urlArquivo, PDO::PARAM_STR);
        $stmt->execute();

        return (int) $this->conn->lastInsertId();
    }

    public function atualizar(int $relatorioId, string $novaUrl, ?string $descricao = null): bool
    {
        $sql = "UPDATE relatorios_corregedoria
                SET url_arquivo = :url_arquivo, descricao = :descricao
                WHERE id_relatorio = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':url_arquivo', $novaUrl,     PDO::PARAM_STR);
        $stmt->bindParam(':descricao',   $descricao,   PDO::PARAM_STR);
        $stmt->bindParam(':id',          $relatorioId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function buscarTodos(): array
    {
        $sql = "SELECT * FROM relatorios_corregedoria ORDER BY ano DESC, semestre DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorPeriodo(int $ano, int $semestre): ?array
    {
        $sql = "SELECT * FROM relatorios_corregedoria
                WHERE ano = :ano AND semestre = :semestre
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':ano',      $ano,      PDO::PARAM_INT);
        $stmt->bindParam(':semestre', $semestre, PDO::PARAM_INT);
        $stmt->execute();

        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado ?: null;
    }

    public function buscarPorId(int $id): ?array
    {
        $sql = "SELECT * FROM relatorios_corregedoria WHERE id_relatorio = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado ?: null;
    }

    /**
     * Exclui um relatório do banco de dados pelo seu ID.
     */
    public function excluir(int $id): bool
    {
        $sql = "DELETE FROM relatorios_corregedoria WHERE id_relatorio = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
