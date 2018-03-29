<?php
use Engine\App;

try {
    require_once 'autoloader.php';

    App::init();

} catch ( Exception $e ) {
    die(  $e->getMessage() );
}
