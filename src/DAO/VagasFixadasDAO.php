<?php

namespace App\DAO;

use PDO;
use Conexao;

class VagasFixadasDAO
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = Conexao::getConexao();
    }

    /*
     * ========================================================================================
     * ATENÇÃO: REFATORAÇÃO FUTURA OBRIGATÓRIA (Módulo de Reuniões)
     * ========================================================================================
     * O que fazer: 
     * Modificar esta classe para consultar/inserir dados na tabela `vagas_promocao` 
     * em vez da tabela `vagas_fixadas` (ou trabalhar as duas em conjunto).
     *
     * Por que fazer:
     * A coluna `reuniao_comissao_id` foi removida da tabela `vagas_fixadas` no banco de dados.
     * A tabela `vagas_fixadas` agora representa APENAS o Limite Legal global de vagas 
     * definido por lei (sem relação com nenhuma reunião específica).
     * As vagas destinadas/ofertadas para uma reunião específica devem ser tratadas
     * na tabela `vagas_promocao`, que tem relação direta com a comissão.
     * 
     * Como fazer:
     * 1. Altere as consultas SQL (ex: buscarVagasPorReuniao, buscarVagasDoAgrupamento, salvarVagas)
     *    para realizar SELECT/UPDATE/INSERT na tabela `vagas_promocao` vinculando a reunião correta.
     * 2. Ajuste os parâmetros, se necessário, para receber o id correto (ex: `vagas_fixadas_id` 
     *    como FK na tabela `vagas_promocao`).
     * ========================================================================================
     */

    /**
     * Busca todas as vagas fixadas para uma determinada reunião
     *
     * @param int $reuniaoId
     * @return array Lista associativa contendo os dados de vagas por agrupamento (Contêiner)
     */
    public function buscarVagasPorReuniao(int $reuniaoId): array
    {
        $sql = "SELECT * FROM vagas_fixadas 
                WHERE reuniao_comissao_id = :reuniao_id
                ORDER BY id_vagas_fixadas ASC";
                
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':reuniao_id', $reuniaoId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca a quantidade de vagas para um agrupamento específico (Contêiner) numa reunião
     *
     * @param int $reuniaoId
     * @param string $nomeAgrupamento Ex: 'Coronel', 'Tenente-Coronel e Major'
     * @return int Quantidade de vagas
     */
    public function buscarVagasDoAgrupamento(int $reuniaoId, string $nomeAgrupamento): int
    {
        $sql = "SELECT quantidade FROM vagas_fixadas 
                WHERE reuniao_comissao_id = :reuniao_id 
                AND nome_do_agrupamento = :agrupamento";
                
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':reuniao_id', $reuniaoId, PDO::PARAM_INT);
        $stmt->bindParam(':agrupamento', $nomeAgrupamento, PDO::PARAM_STR);
        $stmt->execute();

        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $resultado ? (int) $resultado['quantidade'] : 0;
    }

    /**
     * Define ou atualiza as vagas para um agrupamento numa reunião
     *
     * @param int $quadroId
     * @param int $reuniaoId
     * @param string $nomeAgrupamento
     * @param int $quantidade
     * @return bool
     */
    public function salvarVagas(int $quadroId, int $reuniaoId, string $nomeAgrupamento, int $quantidade): bool
    {
        // Verifica se já existe para fazer UPDATE, senão faz INSERT
        $sqlCheck = "SELECT id_vagas_fixadas FROM vagas_fixadas 
                     WHERE reuniao_comissao_id = :reuniao_id 
                     AND nome_do_agrupamento = :agrupamento";
                     
        $stmtCheck = $this->conn->prepare($sqlCheck);
        $stmtCheck->bindParam(':reuniao_id', $reuniaoId, PDO::PARAM_INT);
        $stmtCheck->bindParam(':agrupamento', $nomeAgrupamento, PDO::PARAM_STR);
        $stmtCheck->execute();
        
        if ($stmtCheck->rowCount() > 0) {
            $sql = "UPDATE vagas_fixadas SET quantidade = :quantidade 
                    WHERE reuniao_comissao_id = :reuniao_id 
                    AND nome_do_agrupamento = :agrupamento";
        } else {
            $sql = "INSERT INTO vagas_fixadas (quadro_id, reuniao_comissao_id, nome_do_agrupamento, quantidade) 
                    VALUES (:quadro_id, :reuniao_id, :agrupamento, :quantidade)";
        }
        
        $stmt = $this->conn->prepare($sql);
        
        if ($stmtCheck->rowCount() == 0) {
            $stmt->bindParam(':quadro_id', $quadroId, PDO::PARAM_INT);
        }
        $stmt->bindParam(':reuniao_id', $reuniaoId, PDO::PARAM_INT);
        $stmt->bindParam(':agrupamento', $nomeAgrupamento, PDO::PARAM_STR);
        $stmt->bindParam(':quantidade', $quantidade, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
}
