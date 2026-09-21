<?php

namespace App\Regras;

/**
 * Interface base para todas as regras impeditivas de promoção (Padrão Strategy).
 * Qualquer nova regra da lei deve implementar esta interface.
 */
interface RegraPromocaoInterface
{
    /**
     * Verifica se o militar está impedido de ser promovido segundo esta regra.
     *
     * @param int $militarId O ID do militar sendo avaliado
     * @param int $reuniaoId O ID da reunião/ata
     * @return bool Retorna TRUE se houver impedimento (reprovado), FALSE se estiver apto (aprovado)
     */
    public function verificarImpedimento(int $militarId, int $reuniaoId): bool;
    
    /**
     * Retorna a descrição ou justificativa formal do impedimento (ex: Inciso do Artigo 36)
     *
     * @return string
     */
    public function getDescricaoImpedimento(): string;
}
