<?php
/** Path */
$config[ 'PATH_ROOT' ] = dirname( dirname( __FILE__ ) );
$config[ 'PATH_CACHE' ] = $config[ 'PATH_ROOT' ] . '/cache';
$config[ 'PATH_LOGS' ] = $config[ 'PATH_ROOT' ] . '/logs';
$config[ 'PATH_PUBLIC' ] = $config[ 'PATH_ROOT' ] . '/public_html';
$config[ 'PATH_TEMPLATES' ] = $config[ 'PATH_ROOT' ] . '/templates';
$config[ 'PATH_UPLOAD' ] = $config[ 'PATH_ROOT' ] . '/upload';

/** DB */
$config[ 'DB_DRIVER' ] = 'mysql';
$config[ 'DB_HOST' ] = 'localhost';
$config[ 'DB_DATABASE' ] = 'db_shop';
$config[ 'DB_USER' ] = 'php_user';
$config[ 'DB_PASSWORD' ] = 'pass';
$config[ 'DB_CHARSET' ] = 'UTF8';

/** Time */
$config[ 'TIMEZONE' ] = 'Europe/Moscow';

/** Security */
$config[ 'SECRET_KEY' ] = 'sDf7$9!us@';
