<?php
namespace Engine;

use Exception;

/**
 * Class App
 * Управление приложением.
 * @package Engine
 */
class App
{
    /**
     * Инициализация приложения.
     * @throws Exception
     */
    static public function init()
    {
        date_default_timezone_set( Config::get( 'TIMEZONE' ) );

        session_start();

        if ( php_sapi_name() !== 'cli' && isset( $_SERVER ) && isset( $_GET ) ) {
            $path = ( isset( $_GET[ 'path' ] ) ? $_GET[ 'path' ] : '' );
            try {
                self::routing( $path );
            } catch ( Exception $e ) {
                throw $e;
            }
        }
    }

    /**
     * Роутинг запросов пользователя.
     * http://site.ru/index.php?path=news/edit/5
     * http://site.ru/index.php?path=news/5
     * news - controller
     * edit - method
     * 5 - param method
     *
     * @param string $path - строка запроса, например, 'news/edit/5'
     * @throws Exception
     */
    static private function routing( $path )
    {
        $path = explode( '/', $path );

        $controllerName = 'index'; // контроллер по-умолчанию, если не передан
        $controllerAction = 'index'; // action по-умолчанию, если не передан
        $controllerParam = null;

        // Разбор урла.
        // Получение имени контролера, метода и параметра.
        if ( isset( $path[ 0 ] ) && !empty( $path[ 0 ] ) ) {
            $controllerName = $path[ 0 ];

            if ( isset( $path[ 1 ] ) ) {
                if ( is_numeric( $path[ 1 ] ) ) {
                    $controllerParam = $path[ 1 ];
                } else {
                    $controllerAction = $path[ 1 ];

                    if ( isset( $path[ 2 ] ) ) {
                        $controllerParam = $path[ 2 ];
                    }
                }
            }
        }

        // Вызов контроллера
        $fullControllerName = 'Controller\\' . ucfirst( $controllerName ) . 'Controller';

        $error404 = true;

        if ( class_exists( $fullControllerName ) ) {
            $controller = new $fullControllerName();

            $controllerAction = 'action' . ucfirst( $controllerAction );

            if ( method_exists( $controller, $controllerAction ) ) {
                if ( is_null( $controllerParam ) ) {
                    $content = $controller->$controllerAction();
                } else {
                    $content = $controller->$controllerAction( $controllerParam );
                }

                $template = strtolower( $controllerName ) . '/' . $controller->getView() . '.tmpl';

                $error404 = false; // контроллер и метод найдены, сбрасываем флаг ошибки
            }
        }

        if ( $error404 ) {
            $controller = new PagesError( '/' . implode( '/', $path ) . '/' );

            $content = $controller->error404();
            $template = $controller->getView() . '.tmpl';
        }

        self::render( $template,  $content);
    }

    /**
     * Отрисовка шаблона страницы.
     * @param string $view - название шаблона
     * @param array $data - данные шаблона
     */
    static private function render( $view, $data )
    {
        try {
            $loader = new \Twig_Loader_Filesystem( Config::get( 'PATH_TEMPLATES' ) );
            //        $twig = new Twig_Environment( $twigLoader, [ 'cache' => CACHE_PATH ] );
            $twig = new \Twig_Environment( $loader );

            $tmplLayout = $twig->load( 'layout.tmpl' );
            $tmplContent = $twig->load( $view );

            $content = $tmplContent->render( [
                'data' => $data
            ] );

            $page = $tmplLayout->render( [
                'data' => $data,
                'content' => $content
            ] );

            echo $page;

        } catch ( \Twig_Error $e ) {
            echo $e->getMessage();
        }
    }
}
