<?php

namespace App\DAO;

require_once __DIR__ . '/../../ConexaoDB/conexao.php';

use Conexao;
use PDO;
use PDOException;

/**
 * DAO responsável pelo acesso e manipulação da tabela 'processo_promocional'.
 *
 * O Processo Promocional representa um ciclo semestral UNIFICADO da corporação,
 * englobando tanto os processos de oficiais (CPO) quanto os de praças (CPP).
 * Portanto, NÃO há distinção de tipo de comissão neste nível; a separação
 * ocorre apenas nas reuniões e no snapshot do LQ.
 */
class ProcessoPromocionalDAO
{
    private PDO $conexao;

    public function __construct()
    {
        $this->conexao = Conexao::getConexao();
    }

    /**
     * Cria um novo processo promocional para um dado ano/semestre.
     *
     * @param int    $ano          Ano do processo (ex: 2026).
     * @param int    $semestre     Semestre do processo (1 ou 2).
     * @param string $dataPromocao Data oficial da promoção (YYYY-MM-DD). Ex: 2026-12-02.
     * @return int ID do processo recém-criado.
     */
    public function criar(int $ano, int $semestre, string $dataPromocao): int
    {
        try {
            $sql = "INSERT INTO processo_promocional (ano, semestre, data_promocao) 
                    VALUES (:ano, :semestre, :data_promocao)";
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(':ano', $ano, PDO::PARAM_INT);
            $stmt->bindValue(':semestre', $semestre, PDO::PARAM_INT);
            $stmt->bindValue(':data_promocao', $dataPromocao, PDO::PARAM_STR);
            $stmt->execute();

            return (int) $this->conexao->lastInsertId();
        } catch (PDOException $e) {
            error_log("Erro em ProcessoPromocionalDAO->criar: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Busca o processo promocional ativo (status diferente de CONCLUIDO).
     * Como regra de negócio, só pode haver um processo em aberto por vez.
     *
     * @return array|null Dados do processo ou null se não houver nenhum ativo.
     */
    public function buscarAtivo(): ?array
    {
        try {
            $sql = "SELECT * FROM processo_promocional 
                    WHERE status != 'CONCLUIDO' 
                    ORDER BY ano DESC, semestre DESC LIMIT 1";
            $stmt = $this->conexao->prepare($sql);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("Erro em ProcessoPromocionalDAO->buscarAtivo: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Busca um processo promocional pelo ano e semestre.
     *
     * @param int $ano      Ano do processo.
     * @param int $semestre Semestre (1 ou 2).
     * @return array|null
     */
    public function buscarPorAnoSemestre(int $ano, int $semestre): ?array
    {
        try {
            $sql = "SELECT * FROM processo_promocional 
                    WHERE ano = :ano AND semestre = :semestre 
                    LIMIT 1";
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(':ano', $ano, PDO::PARAM_INT);
            $stmt->bindValue(':semestre', $semestre, PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("Erro em ProcessoPromocionalDAO->buscarPorAnoSemestre: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Busca um processo promocional pelo seu ID.
     *
     * @param int $id ID do processo.
     * @return array|null
     */
    public function buscarPorId(int $id): ?array
    {
        try {
            $sql = "SELECT * FROM processo_promocional WHERE id = :id";
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("Erro em ProcessoPromocionalDAO->buscarPorId: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Altera o status de um processo promocional.
     * Valores válidos: 'EM_PREPARACAO', 'LQ_PUBLICADO', 'EM_REUNIAO', 'CONCLUIDO'.
     *
     * @param int    $id     ID do processo.
     * @param string $status Novo status.
     * @return bool
     */
    public function alterarStatus(int $id, string $status): bool
    {
        try {
            $sql = "UPDATE processo_promocional SET status = :status WHERE id = :id";
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(':status', $status, PDO::PARAM_STR);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro em ProcessoPromocionalDAO->alterarStatus: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Lista todos os processos promocionais em ordem decrescente de ano/semestre.
     *
     * @return array Lista de todos os processos.
     */
    public function listarTodos(): array
    {
        try {
            $sql = "SELECT * FROM processo_promocional 
                    ORDER BY ano DESC, semestre DESC";
            $stmt = $this->conexao->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro em ProcessoPromocionalDAO->listarTodos: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Exclui um processo promocional e seus limites quantitativos associados.
     *
     * @param int $id ID do processo a ser excluído.
     * @return bool True se a exclusão foi bem-sucedida.
     * @throws PDOException Em caso de erro de integridade referencial.
     */
    public function deletar(int $id): bool
    {
        try {
            $this->conexao->beginTransaction();

            // 1. Limpa snapshots do LQ associados ao processo
            $sqlLq = "DELETE FROM lq_snapshot WHERE processo_promocional_id = :id";
            $stmtLq = $this->conexao->prepare($sqlLq);
            $stmtLq->bindValue(':id', $id, PDO::PARAM_INT);
            $stmtLq->execute();

            // 2. Remove o processo promocional
            $sqlProc = "DELETE FROM processo_promocional WHERE id = :id";
            $stmtProc = $this->conexao->prepare($sqlProc);
            $stmtProc->bindValue(':id', $id, PDO::PARAM_INT);
            $stmtProc->execute();

            $this->conexao->commit();
            return true;
        } catch (PDOException $e) {
            if ($this->conexao->inTransaction()) {
                $this->conexao->rollBack();
            }
            error_log("Erro em ProcessoPromocionalDAO->deletar: " . $e->getMessage());
            throw $e;
        }
    }
}
