<?php
namespace Controller;

use Engine\Logger;
use Model\BasketModel;

/**
 * Class BasketController
 * Контроллер корзины.
 * @package Controller
 */
class BasketController extends Controller
{
    /**
     * BasketController constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->setView( 'index' );
        $this->setDataTitle( 'Корзина' );
        $this->setDataHeader( 'Корзина' );
    }

    /**
     * Вывод корзины.
     * @return array
     */
    public function actionIndex(): array
    {
        return $this->getDataView();
    }

    /**
     * Добавление товара в корзину.
     */
    public function actionAdd(): void
    {
        if ( $_SERVER[ 'REQUEST_METHOD' ] == 'POST' && isset( $_POST[ 'goods_id' ] ) ) {
            $goodsId = $_POST[ 'goods_id' ];

            if ( is_numeric( $goodsId ) && $goodsId > 0 ) {
                BasketModel::add( $goodsId );
            }
        }

        if ( isset( $_SERVER[ 'HTTP_REFERER' ] ) ) {
            header( 'Location: ' . $_SERVER[ 'HTTP_REFERER' ] );
        } else {
            header( 'Location: /' );
        }
    }

    /**
     * Удаление товара из корзины.
     */
    public function actionRemove(): void
    {
        if ( $_SERVER[ 'REQUEST_METHOD' ] === 'POST' && isset( $_POST[ 'goods_id' ] ) ) {

            $goodsId = $_POST[ 'goods_id' ];

            if ( is_numeric( $goodsId ) && $goodsId > 0 ) {
                BasketModel::remove( $goodsId );
            }
        }

        if ( isset( $_SERVER[ 'HTTP_REFERER' ] ) ) {
            header( 'Location: ' . $_SERVER[ 'HTTP_REFERER' ] );
        } else {
            header( 'Location: /' );
        }
    }

    /**
     * Изменение количества у конкретного товара в корзине.
     */
    public function actionChange(): void
    {
        if ( $_SERVER[ 'REQUEST_METHOD' ] === 'POST' && isset( $_POST[ 'goods_id' ] ) && isset( $_POST[ 'new_count' ] ) ) {
            $goodsId = $_POST[ 'goods_id' ];
            $newCount = $_POST[ 'new_count' ];

            if ( is_numeric( $goodsId ) && $goodsId > 0 && is_numeric( $newCount ) ) {
                BasketModel::changeCount( $goodsId, $newCount );
            }
        }

        if ( isset( $_SERVER[ 'HTTP_REFERER' ] ) ) {
            header( 'Location: ' . $_SERVER[ 'HTTP_REFERER' ] );
        } else {
            header( 'Location: /' );
        }
    }

    /**
     * Удалить все товары из корзины.
     */
    public function actionClean()
    {
        BasketModel::clean();

        if ( isset( $_SERVER[ 'HTTP_REFERER' ] ) ) {
            header( 'Location: ' . $_SERVER[ 'HTTP_REFERER' ] );
        } else {
            header( 'Location: /' );
        }
    }
}
