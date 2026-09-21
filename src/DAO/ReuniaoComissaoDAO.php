<?php

namespace App\DAO;

use PDO;
use Conexao;

// Inclusão da conexão com o banco de dados
require_once __DIR__ . '/../../ConexaoDB/conexao.php';

/**
 * DAO responsável pela gestão das Reuniões das Comissões de Promoção (CPO e CPP).
 *
 * Uma reunião pode, opcionalmente, referenciar um Relatório da Corregedoria
 * já cadastrado. O relatório existe de forma independente e é vinculado
 * à reunião conforme o entendimento do usuário — não o contrário.
 */
class ReuniaoComissaoDAO
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = Conexao::getConexao();
    }

    /**
     * Cria uma nova sessão (reunião) de comissão.
     *
     * Uma reunião deve, obrigatoriamente, estar vinculada ao processo
     * promocional ativo vigente — não é possível criar reuniões retroativas.
     *
     * @param string   $numeroAta               Número da ata.
     * @param string   $dataReuniao             Data da reunião (YYYY-MM-DD).
     * @param string   $tipoComissao            'CPO' ou 'CPP'.
     * @param int      $processoPromocionalId   FK obrigatória para o processo ativo.
     * @param string|null $observacao           Observação livre.
     * @param int|null $relatorioCorregedoriaId FK opcional para o relatório vigente.
     * @return int O ID da reunião recém-criada.
     */
    public function criarSessao(
        string  $numeroAta,
        string  $dataReuniao,
        string  $tipoComissao,
        int     $processoPromocionalId,
        ?string $observacao = null,
        ?int    $relatorioCorregedoriaId = null
    ): int {
        $sql = "INSERT INTO reuniao_comissao
                    (numero_ata, data_reuniao, tipo_comissao, status, processo_promocional_id, observacao, relatorio_corregedoria_id)
                VALUES
                    (:numero_ata, :data_reuniao, :tipo_comissao, 'PREPARACAO', :processo_id, :observacao, :relatorio_id)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':numero_ata',    $numeroAta,    PDO::PARAM_STR);
        $stmt->bindValue(':data_reuniao',  $dataReuniao,  PDO::PARAM_STR);
        $stmt->bindValue(':tipo_comissao', $tipoComissao, PDO::PARAM_STR);
        $stmt->bindValue(':processo_id',   $processoPromocionalId, PDO::PARAM_INT);
        // Campos nullable: o tipo é determinado em tempo de execução para garantir envio correto do NULL
        $stmt->bindValue(':observacao',    $observacao,              is_null($observacao)              ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':relatorio_id',  $relatorioCorregedoriaId, is_null($relatorioCorregedoriaId) ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->execute();

        return (int) $this->conn->lastInsertId();
    }

    /**
     * Busca todas as reuniões abertas de uma determinada comissão, incluindo
     * dados do processo promocional vinculado e do relatório da Corregedoria.
     *
     * @param string $tipoComissao 'CPO' ou 'CPP'
     * @return array Lista de reuniões em preparação ou andamento.
     */
    public function buscarReunioesAbertas(string $tipoComissao): array
    {
        $sql = "SELECT r.*,
                       rc.ano AS relatorio_ano, rc.semestre AS relatorio_semestre,
                       rc.descricao AS relatorio_descricao, rc.url_arquivo AS relatorio_url,
                       pp.ano AS processo_ano, pp.semestre AS processo_semestre, pp.status AS processo_status
                FROM reuniao_comissao r
                LEFT JOIN relatorios_corregedoria rc ON rc.id_relatorio = r.relatorio_corregedoria_id
                LEFT JOIN processo_promocional pp    ON pp.id           = r.processo_promocional_id
                WHERE r.tipo_comissao = :tipo_comissao
                  AND r.status IN ('PREPARACAO', 'EM_ANDAMENTO')
                ORDER BY r.data_reuniao DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':tipo_comissao', $tipoComissao, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Vincula um relatório da Corregedoria a uma reunião já existente.
     * Substitui qualquer vínculo anterior.
     *
     * @param int $reuniaoId    ID da reunião.
     * @param int $relatorioId  ID do relatório da Corregedoria.
     * @return bool True se atualizado com sucesso.
     */
    public function vincularRelatorio(int $reuniaoId, int $relatorioId): bool
    {
        $sql = "UPDATE reuniao_comissao
                SET relatorio_corregedoria_id = :relatorio_id
                WHERE id_reuniao_comissao = :reuniao_id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':relatorio_id', $relatorioId, PDO::PARAM_INT);
        $stmt->bindParam(':reuniao_id',   $reuniaoId,   PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Desvincula o relatório da Corregedoria de uma reunião, setando a FK para NULL.
     *
     * @param int $reuniaoId ID da reunião que terá o relatório removido.
     * @return bool True se atualizado com sucesso.
     */
    public function desvincularRelatorio(int $reuniaoId): bool
    {
        $sql = "UPDATE reuniao_comissao
                SET relatorio_corregedoria_id = NULL
                WHERE id_reuniao_comissao = :reuniao_id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':reuniao_id', $reuniaoId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Busca uma reunião pelo seu ID, incluindo dados do relatório e do processo.
     *
     * @param int $id ID da reunião.
     * @return array|null Dados da reunião ou null se não encontrada.
     */
    public function buscarPorId(int $id): ?array
    {
        $sql = "SELECT r.*,
                       rc.ano AS relatorio_ano, rc.semestre AS relatorio_semestre,
                       rc.descricao AS relatorio_descricao, rc.url_arquivo AS relatorio_url,
                       pp.ano AS processo_ano, pp.semestre AS processo_semestre, pp.status AS processo_status
                FROM reuniao_comissao r
                LEFT JOIN relatorios_corregedoria rc ON rc.id_relatorio = r.relatorio_corregedoria_id
                LEFT JOIN processo_promocional pp    ON pp.id           = r.processo_promocional_id
                WHERE r.id_reuniao_comissao = :id
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ?: null;
    }

    /**
     * Busca o histórico completo de reuniões de uma determinada comissão (todas as situações),
     * incluindo dados do processo promocional vinculado.
     *
     * @param string $tipoComissao 'CPO' ou 'CPP'
     * @return array Lista de reuniões ordenadas por data decrescente.
     */
    public function buscarTodasPorComissao(string $tipoComissao): array
    {
        $sql = "SELECT r.*,
                       rc.ano AS relatorio_ano, rc.semestre AS relatorio_semestre,
                       rc.descricao AS relatorio_descricao, rc.url_arquivo AS relatorio_url,
                       pp.ano AS processo_ano, pp.semestre AS processo_semestre
                FROM reuniao_comissao r
                LEFT JOIN relatorios_corregedoria rc ON rc.id_relatorio = r.relatorio_corregedoria_id
                LEFT JOIN processo_promocional pp    ON pp.id           = r.processo_promocional_id
                WHERE r.tipo_comissao = :tipo_comissao
                ORDER BY r.data_reuniao DESC, r.id_reuniao_comissao DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':tipo_comissao', $tipoComissao, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Atualiza os dados cadastrais de uma reunião.
     * O processo promocional não é alterável após a criação (é fixado ao processo ativo).
     *
     * @param int         $id                      ID da reunião.
     * @param string      $numeroAta               Número da ata.
     * @param string      $dataReuniao             Data da reunião (YYYY-MM-DD).
     * @param string|null $observacao              Observação.
     * @param int|null    $relatorioCorregedoriaId FK do relatório ou null.
     * @return bool
     */
    public function atualizarSessao(
        int     $id,
        string  $numeroAta,
        string  $dataReuniao,
        ?string $observacao = null,
        ?int    $relatorioCorregedoriaId = null
    ): bool {
        $sql = "UPDATE reuniao_comissao
                SET numero_ata                = :numero_ata,
                    data_reuniao              = :data_reuniao,
                    observacao                = :observacao,
                    relatorio_corregedoria_id = :relatorio_id
                WHERE id_reuniao_comissao = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':numero_ata',    $numeroAta,   PDO::PARAM_STR);
        $stmt->bindValue(':data_reuniao',  $dataReuniao, PDO::PARAM_STR);
        $stmt->bindValue(':id',            $id,          PDO::PARAM_INT);
        // Campos nullable
        $stmt->bindValue(':observacao',    $observacao,              is_null($observacao)              ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':relatorio_id',  $relatorioCorregedoriaId, is_null($relatorioCorregedoriaId) ? PDO::PARAM_NULL : PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Altera o status da reunião ('PREPARACAO', 'EM_ANDAMENTO', 'CONCLUIDA').
     *
     * @param int    $id         ID da reunião.
     * @param string $novoStatus Novo status.
     * @return bool
     */
    public function alterarStatus(int $id, string $novoStatus): bool
    {
        $sql = "UPDATE reuniao_comissao
                SET status = :status
                WHERE id_reuniao_comissao = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':status', $novoStatus, PDO::PARAM_STR);
        $stmt->bindParam(':id',     $id,         PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Exclui uma reunião do banco de dados e limpa todas as suas tabelas vinculadas
     * (Conceito Moral - T1, Impedimentos Legais - T2 e Quadro de Acesso - T3).
     */
    public function excluirSessao(int $id): bool
    {
        try {
            $this->conn->beginTransaction();

            // 1. Limpa Tópico 1 (Conceito Moral)
            $stmt1 = $this->conn->prepare("DELETE FROM conceito_moral WHERE reuniao_comissao_id = :id");
            $stmt1->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt1->execute();

            // 2. Limpa Tópico 2 (Impedimentos Legais)
            $stmt2 = $this->conn->prepare("DELETE FROM criterio_falho WHERE reuniao_comissao_id = :id");
            $stmt2->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt2->execute();

            // 3. Limpa Tópico 3 (Quadro de Acesso / Proposta) -> ADICIONADO AQUI
            $stmt3 = $this->conn->prepare("DELETE FROM quadro_acesso_proposta WHERE reuniao_comissao_id = :id");
            $stmt3->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt3->execute();

            // 4. Exclui a reunião
            $stmt4 = $this->conn->prepare("DELETE FROM reuniao_comissao WHERE id_reuniao_comissao = :id");
            $stmt4->bindValue(':id', $id, PDO::PARAM_INT);
            $resultado = $stmt4->execute();

            $this->conn->commit();
            return $resultado;
        } catch (\PDOException $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log("Erro ao excluir reunião no DAO: " . $e->getMessage());
            throw $e;
        }
    }
}
