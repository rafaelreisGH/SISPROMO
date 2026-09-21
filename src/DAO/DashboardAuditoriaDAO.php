<?php

namespace App\DAO;

use PDO;
use Conexao;

// Inclusão da conexão (ajuste de diretório)
require_once __DIR__ . '/../../ConexaoDB/conexao.php';

/**
 * DAO responsável pelas métricas e extrações analíticas da tabela de logs.
 * Segue restritamente o padrão arquitetural de isolamento de banco de dados.
 */
class DashboardAuditoriaDAO
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = Conexao::getConexao();
    }

    /**
     * Módulo 1: Painel de Indicadores Principais
     */
    public function getIndicadoresPrincipais(int $dias = 30): array
    {
        $sql = "SELECT 
            COUNT(*) as volume_total,
            COUNT(DISTINCT JSON_UNQUOTE(JSON_EXTRACT(contexto, '$.id'))) as gestores_ativos,
            SUM(CASE WHEN nivel = 'INFO' THEN 1 ELSE 0 END) as sucesso,
            SUM(CASE WHEN nivel IN ('ERROR', 'CRITICAL') THEN 1 ELSE 0 END) as falhas,
            SUM(CASE WHEN mensagem LIKE '%inativado%' OR mensagem LIKE '%excedente%' OR mensagem LIKE '%vagas%' OR mensagem LIKE '%finalizada%' THEN 1 ELSE 0 END) as acoes_criticas
        FROM logs 
        WHERE data_log >= DATE_SUB(NOW(), INTERVAL :dias DAY)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':dias', $dias, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Módulo 2: Monitoramento de Uso e Picos Temporais
     */
    public function getMonitoramentoUso(int $dias = 30): array
    {
        $sql = "SELECT 
            DATE(data_log) as data,
            HOUR(data_log) as hora,
            COUNT(*) as total_acoes,
            SUM(CASE WHEN nivel IN ('ERROR', 'CRITICAL') THEN 1 ELSE 0 END) as total_erros
        FROM logs 
        WHERE data_log >= DATE_SUB(NOW(), INTERVAL :dias DAY)
        GROUP BY DATE(data_log), HOUR(data_log)
        ORDER BY data_log ASC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':dias', $dias, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Módulo 3: Auditoria e Rastreabilidade Operacional
     */
    public function getAuditoriaRastreabilidade(int $dias = 30): array
    {
        // Ranking ações
        $sqlAcoes = "SELECT mensagem as acao, COUNT(*) as total
                     FROM logs
                     WHERE data_log >= DATE_SUB(NOW(), INTERVAL :dias DAY)
                     GROUP BY mensagem
                     ORDER BY total DESC
                     LIMIT 10";
                     
        $stmtAcoes = $this->conn->prepare($sqlAcoes);
        $stmtAcoes->bindParam(':dias', $dias, PDO::PARAM_INT);
        $stmtAcoes->execute();
        $acoes = $stmtAcoes->fetchAll(PDO::FETCH_ASSOC);
        
        // Top gestores
        $sqlGestores = "SELECT JSON_UNQUOTE(JSON_EXTRACT(contexto, '$.usuario')) as gestor, COUNT(*) as total
                        FROM logs
                        WHERE data_log >= DATE_SUB(NOW(), INTERVAL :dias DAY)
                        GROUP BY gestor
                        HAVING gestor IS NOT NULL
                        ORDER BY total DESC
                        LIMIT 10";
                        
        $stmtGestores = $this->conn->prepare($sqlGestores);
        $stmtGestores->bindParam(':dias', $dias, PDO::PARAM_INT);
        $stmtGestores->execute();
        $gestores = $stmtGestores->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'ranking_acoes' => $acoes,
            'top_gestores' => $gestores
        ];
    }

    /**
     * Módulo 4: Gestão de Segurança e Níveis de Acesso
     */
    public function getSegurancaAcesso(int $dias = 30): array
    {
        $sqlPerfil = "SELECT JSON_UNQUOTE(JSON_EXTRACT(contexto, '$.perfil')) as perfil, COUNT(*) as total
                      FROM logs
                      WHERE data_log >= DATE_SUB(NOW(), INTERVAL :dias DAY)
                      GROUP BY perfil
                      HAVING perfil IS NOT NULL";
                      
        $stmtPerfil = $this->conn->prepare($sqlPerfil);
        $stmtPerfil->bindParam(':dias', $dias, PDO::PARAM_INT);
        $stmtPerfil->execute();
        $perfil = $stmtPerfil->fetchAll(PDO::FETCH_ASSOC);
        
        $sqlForaExpediente = "SELECT *
                              FROM logs
                              WHERE data_log >= DATE_SUB(NOW(), INTERVAL :dias DAY)
                              AND (HOUR(data_log) < 8 OR HOUR(data_log) > 18 OR DAYOFWEEK(data_log) IN (1, 7))
                              ORDER BY data_log DESC
                              LIMIT 50";
        $stmtForaExpediente = $this->conn->prepare($sqlForaExpediente);
        $stmtForaExpediente->bindParam(':dias', $dias, PDO::PARAM_INT);
        $stmtForaExpediente->execute();
        $foraExpediente = $stmtForaExpediente->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'acoes_por_perfil' => $perfil,
            'fora_expediente' => $foraExpediente
        ];
    }

    /**
     * Módulo 5: Central de Consulta Analítica (Feed de Auditoria)
     */
    public function getFeed(int $dias = 30, ?string $nivel = null, ?string $gestor = null, ?string $keyword = null): array
    {
        $sql = "SELECT * FROM logs WHERE data_log >= DATE_SUB(NOW(), INTERVAL :dias DAY)";
        
        if (!empty($nivel)) {
            $sql .= " AND nivel = :nivel";
        }
        if (!empty($keyword)) {
            $sql .= " AND mensagem LIKE :keyword";
        }
        if (!empty($gestor)) {
            // Filtrando via JSON_EXTRACT na query
            $sql .= " AND JSON_UNQUOTE(JSON_EXTRACT(contexto, '$.usuario')) LIKE :gestor";
        }
        
        $sql .= " ORDER BY data_log DESC LIMIT 1000";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':dias', $dias, PDO::PARAM_INT);
        
        if (!empty($nivel)) {
            $stmt->bindParam(':nivel', $nivel, PDO::PARAM_STR);
        }
        if (!empty($keyword)) {
            $kw = '%' . $keyword . '%';
            $stmt->bindParam(':keyword', $kw, PDO::PARAM_STR);
        }
        if (!empty($gestor)) {
            $gest = '%' . $gestor . '%';
            $stmt->bindParam(':gestor', $gest, PDO::PARAM_STR);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
