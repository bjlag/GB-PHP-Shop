<?php

namespace Model;

use Engine\Db;

/**
 * Class CatalogModel
 * Работа с каталогом.
 * @package Model
 */
class CatalogModel
{
    /**
     * Получить массив товаров.
     * @return array
     */
    static public function getGoodsList(): array
    {
        // получаем все товары
        $query = "SELECT c.id_goods AS id, c.name AS name, b.name AS brand, c.image_preview AS image_preview, 
                         c.image_full AS image_full, c.text_preview AS text_preview, c.text_full AS text_full, 
                         c.rating AS rating, c.price AS price, c.discount AS discount 
                  FROM catalog_goods c INNER JOIN brands b USING (id_brands)";
        $goods = Db::getInstance()->select( $query );

        // добавляем в массив дополнительные данные: размеры, URL детальной страницы и др.
        foreach ( $goods as $key => $item ) {
            $id = $item[ 'id' ];
            $arPrice = self::getPrice( $item[ 'price' ], $item[ 'discount' ] );

            $goods[ $key ][ 'price_old' ] = $arPrice[ 'price_old' ];
            $goods[ $key ][ 'discount_size' ] = $arPrice[ 'discount_size' ];
            $goods[ $key ][ 'price' ] = $arPrice[ 'price' ];
            $goods[ $key ][ 'page_detail' ] = "/catalog/{$id}/";
            $goods[ $key ][ 'sizes' ] = self::getSizesGoodsById( $id );
        }

        return $goods;
    }

    /**
     * Получить информацию о товаре по его ID.
     * @param string $id - ID товара
     * @return array
     */
    static public function getGoodsById( string $id ): array
    {
        $query = "SELECT c.id_goods AS id, c.name AS name, b.name AS brand, c.image_preview AS image_preview, 
                         c.image_full AS image_full, c.text_preview AS text_preview, c.text_full AS text_full, 
                         c.rating AS rating, c.price AS price, c.discount AS discount 
                  FROM catalog_goods c INNER JOIN brands b USING (id_brands)
                  WHERE c.id_goods=:id_goods";

        $params = [
            'id_goods' => $id
        ];

        $goods = Db::getInstance()->select( $query, $params, true );
        if ( !empty( $goods ) ) {
            $arPrice = self::getPrice( $goods[ 'price' ], $goods[ 'discount' ] );

            $goods[ 'price_old' ] = $arPrice[ 'price_old' ];
            $goods[ 'discount_size' ] = $arPrice[ 'discount_size' ];
            $goods[ 'price' ] = $arPrice[ 'price' ];
            $goods[ 'sizes' ] = self::getSizesGoodsById( $id );
        }

        return ( $goods ? $goods : [] );
    }

    /**
     * Получить массив доступных размеров у товара.
     *
     * @param string $id - ID товара, для которого нужно найти размеры.
     * @return array
     */
    static public function getSizesGoodsById( $id ): array
    {
        /** @var array $sizes */
        static $sizes = null;

        if ( is_null( $sizes ) ) {
            $query = "SELECT c.id_goods AS id_goods, s.name AS name 
                  FROM catalog_goods_size c INNER JOIN catalog_size s USING (id_size)
                  ORDER BY c.id_goods";
            $sizes = Db::getInstance()->select( $query );
        }

        $sizesGoods = [];
        $find = false;
        foreach ( $sizes as $size ) {
            if ( $size[ 'id_goods' ] != $id ) {
                if ( $find ) {
                    // Массив размеров отсортирован по ID товара.
                    // Если до этого товар был найден и мы оказались здесь,
                    // значит начался другой товар и продолжать нет смысла
                    break;
                }
                continue;
            }

            $sizesGoods[] = $size[ 'name' ];
            $find = true;
        }

        return $sizesGoods;
    }

    /**
     * Получить информацию о цене на товар.
     *
     * @param float $price - цена на товар без скидки
     * @param float $discount - размер скидки в процентах
     * @return array
     */
    static public function getPrice( float $price, float $discount ): array
    {
        $result = [];

        $priceOld = 0;
        $discountSize = 0;
        $priceNew = $price;

        if ( is_numeric( $discount ) && $discount > 0 ) {
            $priceOld = $price;
            $discountSize = $priceOld * $discount / 100;
            $priceNew = $priceOld - $discountSize;
        }

        $result[ 'price_old' ] = $priceOld;
        $result[ 'discount_size' ] = $discountSize;
        $result[ 'price' ] = $priceNew;

        return $result;
    }
}
