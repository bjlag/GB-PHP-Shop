<?php
namespace Model;

use Engine\Db;
use Engine\Logger;

/**
 * Class BasketModel
 * Работа с корзинок.
 * @package Model
 */
class BasketModel
{
    /**
     * Получить корзину.
     * Возвращается ассоциативный массив с ключами:
     *   id - ID товара
     *   name - Название товара
     *   picture - Ссылка на превьюшное изображение
     *   price - Цена товара
     *   count - Количество товара
     *
     * @return array
     */
    static public function getBasket(): array
    {
        $basket = [];

        if ( UserModel::isUserAuth()
            && ( !isset( $_SESSION[ 'basket' ][ 'sync' ] )
                || $_SESSION[ 'basket' ][ 'sync' ] !== true ) )
        {
            // Проверяем есть ли для авторизованного пользователя сохраненная корзина в БД
            $userId = UserModel::getUserId();
            $basket = self::getSaveBasket( $userId );
            if ( !empty( $basket ) ) {
                // Есть сохраненная корзина в БД
                // Восстанавливаем корзину и записываем ее в сессию
                $_SESSION[ 'basket' ][ 'items' ] = $basket;
            } else if ( isset( $_SESSION[ 'basket' ][ 'items' ] ) && !empty( $_SESSION[ 'basket' ][ 'items' ] ) ) {
                // В сессии есть корзина
                // Сохраняем корзину в БД
                foreach( $_SESSION[ 'basket' ][ 'items' ] as $item ) {
                    self::addGoodsInDb( $userId, $item[ 'id' ], $item[ 'count' ] );
                }
            }

            // Делаем пометку, что корзина в актуальном состоянии и сохранена в сессии,
            // чтобы в следующий раз не делать запросы к БД
            $_SESSION[ 'basket' ][ 'sync' ] = true;
        }

        if ( isset( $_SESSION[ 'basket' ] ) ) {
            $basket = $_SESSION[ 'basket' ];
        }

        $basket[ 'total_count' ] = 0;
        $basket[ 'total_amount' ] = 0;
        $basket[ 'total_discount' ] = 0;

        if ( isset( $basket[ 'items' ] ) && !empty( $basket[ 'items' ] ) ) {
            foreach ( $basket[ 'items' ] as $item ) {
                $basket[ 'total_count' ] += $item[ 'count' ];
                $basket[ 'total_amount' ] += $item[ 'price' ] * $item[ 'count' ];
                $basket[ 'total_discount' ] += $item[ 'discount_size' ] * $item[ 'count' ];
            }
        }

        return $basket;
    }

    /**
     * Добавляем товар в корзину.
     * Если пользователь авторизован, то сохраняем данные в БД с обновлением информации в сессии.
     * Если пользователь не авторизован, то данные корзины сохраняются только в сессию.
     *
     * @param string $idGoods - ID товара
     * @param int $count - Количество товара
     * @return bool
     */
    static public function add( string $idGoods, int $count = 1 ): bool
    {
        $goods = CatalogModel::getGoodsById( $idGoods );
        if ( empty( $goods ) ) {
            return false;
        }

        // Если пользователь авторизован, корзину сохраняем в БД
        if ( UserModel::isUserAuth() ) {
            self::addGoodsInDb( UserModel::getUserId(), $idGoods, $count );
        }

        // Обновляем информацию о корзине в сессии
        $isGoodsFound = false;
        if ( isset( $_SESSION[ 'basket' ][ 'items' ] ) ) {
            // Если товар уже есть в корзине, то обновить информацию о количестве
            foreach ( $_SESSION[ 'basket' ][ 'items' ] as $key => $item ) {
                if ( $item[ 'id' ] == $idGoods ) {
                    $_SESSION[ 'basket' ][ 'items' ][ $key ][ 'count' ] += $count;
                    $isGoodsFound = true;
                    break;
                }
            }
        }

        if ( !$isGoodsFound ) {
            $_SESSION[ 'basket' ][ 'items' ][] = [
                'id' => $idGoods,
                'name' => $goods[ 'name' ],
                'picture' => $goods[ 'image_preview' ],
                'price' => $goods[ 'price' ],
                'count' => $count,
                'discount' => $goods[ 'discount' ],
                'price_old' => $goods[ 'price_old' ],
                'discount_size' => $goods[ 'discount_size' ]
            ];
        }

        return true;
    }

    /**
     * Удалить из корзины товар с указанным ID.
     * @param $idGoods
     */
    static public function remove( $idGoods )
    {
        if ( UserModel::isUserAuth() ) {
            $filter = [
                'id_user' => UserModel::getUserId(),
                'id_goods' => $idGoods
            ];
            Db::getInstance()->delete( 'basket', $filter );
        }

        foreach ( $_SESSION[ 'basket' ][ 'items' ] as $key => $item ) {
            if ( $item[ 'id' ] == $idGoods ) {
                unset( $_SESSION[ 'basket' ][ 'items' ][ $key ] );
                break;
            }
        }
    }

    /**
     * Изменить количество для указанного товара.
     *
     * @param string $idGoods - ID товара, у которого меняем количество
     * @param int $newCount - новое количество товара
     */
    static public function changeCount( string $idGoods, int $newCount )
    {
        if ( UserModel::isUserAuth() ) {
            $valuesSet = [
                'count' => $newCount
            ];
            $valuesWhere = [
                'id_user' => UserModel::getUserId(),
                'id_goods' => $idGoods
            ];

            if ( $newCount > 0 ) {
                Db::getInstance()->update( 'basket', $valuesSet, 'id_user = :id_user AND id_goods = :id_goods', $valuesWhere );
            } else {
                self::remove( $idGoods );
            }
        }

        foreach ( $_SESSION[ 'basket' ][ 'items' ] as $key => $item ) {
            if ( $item[ 'id' ] == $idGoods ) {
                $_SESSION[ 'basket' ][ 'items' ][ $key ][ 'count' ] = $newCount;
                break;
            }
        }
    }

    /**
     * Очистка корзины.
     */
    static public function clean(): void
    {
        if ( UserModel::isUserAuth() ) {
            $filter = [
                'id_user' => UserModel::getUserId()
            ];
            Db::getInstance()->delete( 'basket', $filter );
        }

        $_SESSION[ 'basket' ] = [];
    }

    /**
     * Добавить товар в корзину в БД.
     *
     * @param $idUser - ID пользователя
     * @param $idGoods - ID товара
     * @param $count - количество добавляемого товара
     */
    static private function addGoodsInDb( string $idUser, string $idGoods, int $count ): void
    {
        // Проверяем, если ли у пользователя в корзине этот товар
        $result = self::getSaveBasket( $idUser, $idGoods );

        if ( empty( $result ) ) {
             // Товара нет, добавляем новую запись в БД
            $values = [
                'id_user' => $idUser,
                'id_goods' => $idGoods,
                'count' => $count
            ];

            Db::getInstance()->insert( 'basket', $values );
        } else {
            // Товар есть, обновляем количество
            $valuesSet = [
                'count' => $count + $result[ 0 ][ 'count' ]
            ];
            $valuesWhere = [
                'id_user' => $idUser,
                'id_goods' => $idGoods
            ];

            Db::getInstance()->update( 'basket', $valuesSet, 'id_user = :id_user AND id_goods = :id_goods', $valuesWhere );
        }
    }

    /**
     * Получить сохраненную корзину в БД для указанного пользователя.
     * Так же можно узнать, есть ли у пользователя в корзине конкретный товар.
     *
     * @param string $idUser
     * @param string $idGoods
     * @return array
     */
    static private function getSaveBasket( string $idUser, string $idGoods = null ): array
    {
        $params[ 'id_user' ] = $idUser;
        $query = "SELECT b.id_goods AS id, g.name AS name, g.image_preview AS picture, g.price AS price, 
                         g.discount AS discount, b.count AS count 
                  FROM basket AS b INNER JOIN catalog_goods AS g USING( id_goods ) 
                  WHERE b.id_user = :id_user";

        if ( !is_null( $idGoods ) ) {
            $params[ 'id_goods' ] = $idGoods;
            $query .= " AND b.id_goods = :id_goods";
        }

        $basket = Db::getInstance()->select( $query, $params );
        foreach ( $basket as $key => $item ) {
            $arPrice = CatalogModel::getPrice( $item[ 'price' ], $item[ 'discount' ] );

            $basket[ $key ][ 'price_old' ] = $arPrice[ 'price_old' ];
            $basket[ $key ][ 'discount_size' ] = $arPrice[ 'discount_size' ];
            $basket[ $key ][ 'price' ] = $arPrice[ 'price' ];
        }

        return $basket;
    }
}
