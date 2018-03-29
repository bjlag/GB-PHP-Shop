<?php
namespace Model;

/**
 * Class OrderPaymentModel
 * Работа с оплатой заказа
 * @package Model
 */
class OrderPaymentModel
{
    /** @var array $methods - способы оплаты */
    private $methods;

    /**
     * OrderPaymentModel constructor.
     */
    public function __construct()
    {
        // todo: хранить информацию в базе
        $methods = [
            'cash' => 'Наличными',
            'card' => 'Картой'
        ];

        $this->methods = $methods;
    }

    /**
    * Получить все способы оплаты заказа или какой-то конкретный, если передать его код.
    * @param string|null $code - код метода
    * @return array|string
    */
    public function getMethods( string $code = null )
    {
        if ( is_null( $code ) ) {
            return $this->methods;
        }

        return ( isset( $this->methods[ $code ] ) ? $this->methods[ $code ] : '' );
    }
}
