<?php
namespace Controller;

use Model\CatalogModel;

/**
 * Class CatalogController
 * Контроллер каталога
 * @package Controller
 */
class CatalogController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Вывод списка товаров. Если передан ID товара, то выводится побробная информация об этом товаре.
     * @param null|string $id - ID товара.
     * @return array
     */
    public function actionIndex( $id = null ): array
    {
        if ( is_null( $id ) || !is_numeric( $id ) ) {
            $goods = CatalogModel::getGoodsList();

            $this->setView( 'index' );
            $this->setDataTitle( 'Кроссовки' );
            $this->setDataHeader( 'Кроссовки' );

        } else {
            $goods = CatalogModel::getGoodsById( $id );

            $name = ( isset( $goods[ 'name' ] ) ? $goods[ 'name' ] : 'Товар не найден' );

            $this->setView( 'detail' );
            $this->setDataTitle( $name );
            $this->setDataHeader( $name );
        }

        $this->setDataView( 'goods', $goods );

        return $this->getDataView();
    }
}