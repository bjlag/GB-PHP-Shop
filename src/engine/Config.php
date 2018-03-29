<?php
namespace Engine;

use AppExceptions\ConfigException;

/**
 * Class Config
 * Работа с конфигурациями.
 * @package Engine
 */
class Config
{
    /** @var array $configCache */
    private static $configCache = [];

    /**
     * Получить указанный параметр конфигурации.
     *
     * @param string $param - название параметра
     * @return mixed
     */
    public static function get( $param )
    {
        if ( empty( self::$configCache ) ) {
            $config = null;
            require_once __DIR__ . '/../config/config.main.php';
            self::$configCache = $config;
        }

        try {
            if ( !isset( self::$configCache[ $param ] ) ) {
                throw new ConfigException( "Parameter $param does not exists" );
            }
        } catch( ConfigException $e ) {
            echo $e->getError();
        }

        return self::$configCache[ $param ];
    }
}
