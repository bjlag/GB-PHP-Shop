<?php
namespace Engine;

use Controller\Controller;

/**
 * Class PagesError
 * Страницы ошиблок.
 * @package Engine
 */
class PagesError extends Controller
{
    /** @var string $url - адрес страницы, на которой возникла ошибка */
    private $url;

    /**
     * PagesError constructor.
     * @param string $url - адрес страницы, на которой возникла ошибка.
     */
    public function __construct( string $url )
    {
        parent::__construct();

        $this->url = $url;
    }

    /**
     * 404 ошибка.
     * @return array
     */
    public function error404()
    {
        header( $_SERVER[ 'SERVER_PROTOCOL' ] . ' 404 Not Found' );

        $this->setView( 'error_404' );
        $this->setDataTitle( 'Страница не найдена' );
        $this->setDataHeader( 'Страница не найдена' );

        $this->setDataView( 'url', $this->url );

        return $this->getDataView();
    }
}