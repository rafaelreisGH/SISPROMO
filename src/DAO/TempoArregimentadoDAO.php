<?php

namespace App\DAO;

use PDO;
use Conexao;
use Exception;

require_once __DIR__ . '/../../ConexaoDB/conexao.php';

/**
 * DAO responsável pela gestão e consulta dos dados de tempo arregimentado
 * e registros de afastamento dos militares.
 */
class TempoArregimentadoDAO
{
    private PDO $conn;

    /**
     * Inicializa a conexão PDO com o banco de dados.
     */
    public function __construct()
    {
        $this->conn = Conexao::getConexao();
    }

    /**
     * Busca o tempo mínimo arregimentado exigido de acordo com o posto ou graduação.
     *
     * @param string $postoGrad Posto ou graduação do militar (ex: 'CAP BM', '1º SGT BM').
     * @return int|null Retorna a quantidade de tempo mínimo em meses ou null se não encontrado.
     */
    public function obterTempoMinimoPorPostoGrad(string $postoGrad): ?int
    {
        $sql = "SELECT * FROM tempo_arregimentado LIMIT 1";
        $stmt = $this->conn->query($sql);
        $consulta = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$consulta) {
            return null;
        }

        $mapeamentoCampos = [
            'TC BM'     => 'tempo_tc',
            'MAJ BM'    => 'tempo_maj',
            'CAP BM'    => 'tempo_cap',
            '1º TEN BM' => 'tempo_1ten',
            '2º TEN BM' => 'tempo_2ten',
            'ST BM'     => 'tempo_st',
            '1º SGT BM' => 'tempo_1sgt',
            '2º SGT BM' => 'tempo_2sgt',
            '3º SGT BM' => 'tempo_3sgt',
            'CB BM'     => 'tempo_cb',
            'SD BM'     => 'tempo_sd',
        ];

        $coluna = $mapeamentoCampos[$postoGrad] ?? null;

        if ($coluna && isset($consulta[$coluna])) {
            return (int) $consulta[$coluna];
        }

        return null;
    }

    /**
     * Busca o posto/graduação atual de um militar pelo seu ID.
     *
     * @param int $militarId ID do militar.
     * @return string|false Retorna o posto/graduação ou false se não for localizado.
     */
    public function obterPostoGrad(int $militarId)
    {
        $sql = "SELECT posto_grad_mil FROM militar WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $militarId, PDO::PARAM_INT);
        $stmt->execute();

        $postoGrad = $stmt->fetchColumn();

        return $postoGrad ?: false;
    }

    /**
     * Busca a data da última promoção de um militar.
     *
     * @param int $militarId ID do militar.
     * @return string|null Retorna a data no formato Y-m-d ou null se não houver.
     */
    public function obterUltimaPromocao(int $militarId): ?string
    {
        $sql = "SELECT ultima_promocao FROM militar WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $militarId, PDO::PARAM_INT);
        $stmt->execute();

        $dataPromocao = $stmt->fetchColumn();

        return $dataPromocao ?: null;
    }

    /**
     * Retorna a soma total de dias de afastamento (tempo não arregimentado) do militar.
     *
     * @param int $militarId ID do militar.
     * @return int Total de dias de afastamento descontados.
     */
    public function obterTotalDiasAfastados(int $militarId): int
    {
        $sql = "SELECT SUM(qtde_de_dias) FROM tempo_nao_arregimentado WHERE militar_id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $militarId, PDO::PARAM_INT);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    /**
     * Atualiza o indicador booleano de tempo arregimentado cumprido na tabela do militar.
     *
     * @param int $militarId ID do militar.
     * @param bool $cumprido Indica se o militar atingiu o tempo mínimo arregimentado (true/false).
     * @return bool Sucesso da operação.
     */
    public function atualizarStatusTempoArregimentado(int $militarId, bool $cumprido): bool
    {
        $sql = "UPDATE militar SET tempo_arregimentado = :status WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':status', $cumprido, PDO::PARAM_BOOL);
        $stmt->bindValue(':id', $militarId, PDO::PARAM_INT);

        return $stmt->execute();
    }
}
