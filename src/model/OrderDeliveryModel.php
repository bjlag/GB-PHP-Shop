<?php
namespace Model;

/**
 * Class OrderDeliveryModel
 * Работа с доставкой.
 * @package Model
 */
class OrderDeliveryModel
{
    /** @var array $methods - способы доставки */
    private $methods;

    /**
     * OrderDeliveryModel constructor.
     */
    public function __construct()
    {
        // todo: хранить информацию в базе
        $methods = [
            'pickup' => [
                'name' => 'Самовывоз',
                'cost' => 0
            ],
            'courier' => [
                'name' => 'Курьером',
                'cost' => 200
            ]
        ];

        $this->methods = $methods;
    }

    /**
     * Получить все способы доставки или какой-то конкретный, если передать его код.
     * @param string|null $code - код метода
     * @return array
     */
    public function getMethods( string $code = null ): array
    {
        if ( is_null( $code ) ) {
            return $this->methods;
        }

        return ( isset( $this->methods[ $code ] ) ? $this->methods[ $code ] : [] );
    }
}
