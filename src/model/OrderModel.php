<?php
namespace Model;

use Engine\Db;
use PDOException;
use Exception;

/**
 * Class OrderModel
 * Обработка заказов
 * @package Model
 */
class OrderModel
{
    /**
     * Записать созданный заказ в БД.
     * @param array $params - информация о заказе
     * @return string - ID созданного заказа в таблице ORDERS
     * @throws Exception
     */
    static public function create( array $params ): string
    {
        try {
            Db::getInstance()->transactionStart();

            // добавляем данные в таблицу order
            $values = [
                'id_user' => $params[ 'user_id' ],
                'date_create' => date( 'Y-m-d H:i:s' ),
                'name' => $params[ 'user_name' ],
                'email' => $params[ 'user_email' ],
                'phone' => $params[ 'user_phone' ],
                'city' => $params[ 'city' ],
                'address_delivery' => $params[ 'address' ],
                'method_delivery' => $params[ 'delivery' ][ 'code' ],
                'delivery_cost' => $params[ 'delivery' ][ 'cost' ],
                'method_payment' => $params[ 'payment' ][ 'code' ],
                'comment' => $params[ 'comment' ]
            ];

            $orderId = Db::getInstance()->insert( 'orders', $values );

            // добавляем данные в таблицу order_goods
            $basket = $params[ 'basket' ];
            foreach ( $basket as $item ) {
                $values = [
                    'id_order' => $orderId,
                    'id_goods' => $item[ 'id' ],
                    'count' => $item[ 'count' ],
                    'price' => $item[ 'price' ],
                    'discount_amount' => $item[ 'discount_size' ]
                ];

                Db::getInstance()->insert( 'orders_goods', $values );
            }

        } catch ( PDOException $e ) {
            Db::getInstance()->transactionRollBack();
            throw new Exception( 'Ошибка при добавлении заказа в БД' );
        }

        Db::getInstance()->transactionCommit();

        return $orderId;
    }

    /**
     * Получить массив с информацией о товарах в указанном заказе.
     * @param string $orderId - ID заказа
     * @return array
     */
    static public function getGoodsInOrder( string $orderId ): array
    {
        $query = "SELECT o.id_order AS order_id, o.id_goods AS goods_id, g.name AS goods_name, 
                         g.image_preview AS goods_image, o.count AS count, o.price AS price, 
                         ( o.price * o.count ) AS sum, ( o.discount_amount * o.count ) AS discount 
                  FROM orders_goods o INNER JOIN catalog_goods g USING ( id_goods )
                  WHERE o.id_order = :id_order";

        $row = Db::getInstance()->select( $query, [ 'id_order' => $orderId ] );

        return is_array( $row ) ? $row : [];
    }

    /**
     * Получить список заказов для пользователя.
     * Если передать ID заказа, то получим конкретный заказ.
     *
     * @param string $userId - ID пользователя, для которого получаем список заказов
     * @param string|null $orderId - ID заказа
     * @return array
     */
    static public function getOrdersList( string $userId, string $orderId = null ): array
    {
        $params = [ 'id_user' => $userId ];

        $query = "SELECT o.id_order AS id, DATE_FORMAT( o.date_create, '%d.%m.%Y %H:%i' ) AS date_create,
                         o.id_user AS customer_id, o.name AS customer_name, o.email AS customer_email, o.phone AS customer_phone,
                         o.address_delivery AS delivery_address, o.method_delivery AS delivery_method, o.delivery_cost AS delivery_cost, 
                         o.method_payment AS payment_method, o.comment AS comment, g.count AS count, g.sum AS sum
                  FROM `orders` AS o INNER JOIN
                       ( SELECT id_order, sum( count ) as count, sum( price * count ) as sum
                         FROM orders_goods
                         GROUP BY id_order ) AS g USING ( id_order )
                  WHERE o.id_user = :id_user";

        if ( !is_null( $orderId ) ) {
            $query .= ' AND o.id_order = :id_order';
            $params[ 'id_order' ] = $orderId;
        }

        $query .= "\nORDER BY o.date_create DESC";

        $row = Db::getInstance()->select( $query, $params, ( is_null( $orderId ) ? false : true ) );

        return is_array( $row ) ? $row : [];
    }

    /**
     * Получить заказ по его ID для указанного пользоватея.
     * @param string $userId - ID пользователя
     * @param string $orderId - ID заказа
     * @return array
     */
    static public function getOrderById( string $userId, string $orderId ): array
    {
        $result = [];

        // общая информация о заказе
        $order = self::getOrdersList( $userId, $orderId );
        if ( empty( $order ) ) {
            return $result;
        }

        $result[ 'id' ] = $order[ 'id' ];
        $result[ 'date_create' ] = $order[ 'date_create' ];
        $result[ 'total_count' ] = $order[ 'count' ];
        $result[ 'total_sum' ] = $order[ 'sum' ];

        $result[ 'customer' ] = [
            'id' => $order[ 'customer_id' ],
            'name' => $order[ 'customer_name' ],
            'email' => $order[ 'customer_email' ],
            'phone' => $order[ 'customer_phone' ],
        ];

        // информация о доставке
        $delivery = new OrderDeliveryModel();
        $deliveryMethod = $delivery->getMethods( $order[ 'delivery_method' ] );

        $result[ 'delivery' ] = [
            'method' => $deliveryMethod[ 'name' ],
            'address' => $order[ 'delivery_address' ],
            'cost' => $order[ 'delivery_cost' ]
        ];

        // информация об оплате
        $payment = new OrderPaymentModel();
        $paymentMethod = $payment->getMethods( $order[ 'payment_method' ] );

        $result[ 'payment' ] = [
            'method' => $paymentMethod
        ];

        $result[ 'comment' ] = $order[ 'comment' ];

        // товары в заказе
        $goods = self::getGoodsInOrder( $orderId );
        foreach ( $goods as $key => $item ) {
            $result[ 'goods' ][ $key ] = $item;
        }

        return $result;
    }
}
