<?php
namespace Engine;

/**
 * Class Logger
 * Лог.
 * @package Engine
 */
class Logger
{
    /**
     * Записываем сообщение в лог файл.
     * @param string|array $message - информация, которую нужно добавить в лог
     */
    public static function write( $message )
    {
        $pathLogs = Config::get( 'PATH_LOGS' );
        if ( !is_dir( $pathLogs ) ) {
            mkdir( $pathLogs );
        }

        if ( is_array( $message ) ) {
            $message = json_encode( $message );
        }

        $string = date( 'Y-m-d H:i:s' ) . ":  $message\n";
        file_put_contents( $pathLogs . '/log.txt', $string, FILE_APPEND );
    }
}
