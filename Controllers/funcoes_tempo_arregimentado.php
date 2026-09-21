<?php

/**
 * Helper de Regras de Negócio para Cálculo do Tempo Arregimentado do Militar.
 *
 * As operações de banco de dados foram refatoradas e isoladas no padrão DAO (TempoArregimentadoDAO),
 * mantendo este arquivo responsável exclusivamente pelas regras de cálculo e cálculo de intervalos.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/funcoes_intersticio.php';

use App\DAO\TempoArregimentadoDAO;

/**
 * Retorna o tempo mínimo arregimentado (em meses) exigido para um posto ou graduação.
 *
 * @param string $posto_grad Posto/graduação a consultar.
 * @param PDO|null $conn Conexão legada mantida por compatibilidade (opcional).
 * @return int|null Retorna a quantidade de meses ou null se não localizado.
 */
function pega_tempo_minimo_arregimentado($posto_grad, $conn = null)
{
    $dao = new TempoArregimentadoDAO();
    return $dao->obterTempoMinimoPorPostoGrad($posto_grad);
}

/**
 * Retorna o posto/graduação atual do militar.
 *
 * @param int $militar_id ID do militar.
 * @param PDO|null $conn Conexão legada mantida por compatibilidade (opcional).
 * @return string|false Retorna a string do posto/graduação ou false se não for localizado.
 */
function pega_posto_grad($militar_id, $conn = null)
{
    $dao = new TempoArregimentadoDAO();
    return $dao->obterPostoGrad((int) $militar_id);
}

/**
 * Calcula o tempo arregimentado acumulado do militar e atualiza seu status no banco de dados.
 *
 * @param int $militar_id ID do militar.
 * @param float|int $intersticio Anos ou fração de interstício exigido.
 * @param int $tempo_arregimentado_minimo Tempo mínimo em meses exigido.
 * @param PDO|null $conn Conexão legada mantida por compatibilidade (opcional).
 * @return bool|void Retorna false caso o militar não possua data de última promoção registrada.
 */
function calcularTempoArregimentado($militar_id, $intersticio, $tempo_arregimentado_minimo, $conn = null)
{
    $dao = new TempoArregimentadoDAO();
    $militar_id = (int) $militar_id;

    // Converte a exigência mínima de meses para dias (considerando mês de 30 dias)
    $tempo_arregimentado_minimo_dias = $tempo_arregimentado_minimo * 30;

    // Busca a data da última promoção registrada no banco
    $data_promocao = $dao->obterUltimaPromocao($militar_id);

    if (!$data_promocao) {
        return false; // Se não houver promoção registrada, não é possível calcular
    }

    // Calcula o período total de tempo decorrido no posto atual
    $data_inicio = new DateTime($data_promocao);
    $data_atual = clone $data_inicio;

    if (is_numeric($intersticio) && (float)$intersticio === 0.5) {
        // Interstício fracionado de meio ano (6 meses)
        $data_atual->add(new DateInterval('P6M'));
    } else {
        // Interstício em anos inteiros
        $data_atual->add(new DateInterval('P' . (int)$intersticio . 'Y'));
    }

    $intervalo = $data_inicio->diff($data_atual);
    $dias_totais = $intervalo->days;

    // Obtém o acumulado de dias em que o militar esteve afastado (não arregimentado)
    $dias_afastados = $dao->obterTotalDiasAfastados($militar_id);

    // O tempo arregimentado líquido é o total de dias descontando os afastamentos
    $tempo_arregimentado_atual = $dias_totais - $dias_afastados;

    // Atualiza a situação do militar no banco através do DAO
    $cumprido = ($tempo_arregimentado_atual >= $tempo_arregimentado_minimo_dias);
    $dao->atualizarStatusTempoArregimentado($militar_id, $cumprido);
}
