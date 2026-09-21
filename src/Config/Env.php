<?php

namespace App\Config;

use Dotenv\Dotenv;

/**
 * Classe responsável por inicializar as variáveis de ambiente (.env).
 */
class Env
{
    /**
     * Carrega as variáveis do arquivo .env localizado na raiz do projeto.
     */
    public static function init(): void
    {
        static $loaded = false;
        if (!$loaded) {
            $path = dirname(__DIR__, 2);
            if (file_exists($path . '/.env')) {
                $dotenv = Dotenv::createImmutable($path);
                $dotenv->safeLoad();
            }
            $loaded = true;
        }
    }
}
