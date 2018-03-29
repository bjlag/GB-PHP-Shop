<?php
namespace Controller;

/**
 * Class IndexController
 * Контроллер главной страницы.
 * @package Controller
 */
class IndexController extends Controller
{
    /**
     * IndexController constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Вывод шаблона главной старницы.
     * @return array
     */
    public function actionIndex(): array
    {
        $this->setView( 'index' );
        $this->setDataTitle( 'Здравые кроссоля' );
        $this->setDataHeader( 'Здравые кроссоля' );

        return $this->getDataView();
    }
}
