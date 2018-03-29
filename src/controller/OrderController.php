<?php
namespace Controller;

use Engine\Logger;
use Model\BasketModel;
use Model\OrderDeliveryModel;
use Model\OrderModel;
use Model\OrderPaymentModel;
use Model\UserModel;

/**
 * Class OrderController
 * Работа с заказами.
 * @package Controller
 */
class OrderController extends Controller
{
    /** @var array $formFields - описание полей формы оформления заказа */
    private $formFields = [];

    /** @var array $error - массив ошибок */
    private $error = [];

    /**
     * OrderController constructor.
     */
    public function __construct()
    {
        parent::__construct();

        if ( !UserModel::isUserAuth() ) {
            header( 'Location: /user/auth/' );
            exit();
        }

        $this->setFormFields();
    }

    /**
     * Установить поля формы оформления заказа.
     */
    private function setFormFields(): void
    {
        $value = [
            'order-city' => [
                'required' => true,
                'error' => 'Укажите город'
            ],
            'order-method-delivery' => [
                'required' => true,
                'error' => 'Укажите метод доставки'
            ],
            'order-address-delivery' => [
                'required' => true,
                'error' => 'Укажите адрес доставки'
            ],
            'order-method-payment' => [
                'required' => true,
                'error' => 'Укажите способ оплаты'
            ],
            'order-user-name' => [
                'required' => true,
                'error' => 'Укажите ваше имя'
            ],
            'order-user-email' => [
                'required' => true,
                'error' => 'Укажите ваш адрес электронной почты'
            ],
            'order-user-phone' => [
                'required' => true,
                'error' => 'Укажите ваш контактный телефон'
            ],
            'order-user-comment' => [
                'required' => false,
                'error' => ''
            ]
        ];

        $this->formFields = $value;
    }

    /**
     * Получить описание полей формы оформления заказа.
     * @return array
     */
    private function getFormFields(): array
    {
        return $this->formFields;
    }

    /**
     * Страница с формой оформления заказа
     * @return array
     * @throws \Exception
     */
    public function actionIndex(): array
    {
        $delivery = new OrderDeliveryModel();
        $payment = new OrderPaymentModel();

        // Обработка формы
        if ( $_SERVER[ 'REQUEST_METHOD' ] == 'POST' && isset( $_POST[ 'order-submit' ] ) ) {
            $data = [];

            foreach ( $this->getFormFields() as $key => $value ) {
                if ( $value[ 'required' ] === true
                    && ( !isset( $_POST[ $key ] ) || empty( $_POST[ $key ] ) ) )
                {
                    $this->error[] = $value[ 'error' ];
                }

                if ( isset( $_POST[ $key ] ) ) {
                    $data[ str_replace( '-', '_', $key ) ] = $_POST[ $key ];
                }
            }

            if ( !empty( $this->error ) ) {
                $this->setDataView( 'form', $data );
                $this->setDataView( 'error', $this->error );
            } else {
                $basket = $this->getBasket();

                $deliveryMethod = $delivery->getMethods( $_POST[ 'order-method-delivery' ] );

                $orderParams = [
                    'user_id' => UserModel::getUserId(),
                    'user_name' => $_POST[ 'order-user-name' ],
                    'user_email' => $_POST[ 'order-user-email' ],
                    'user_phone' => $_POST[ 'order-user-phone' ],
                    'city' => $_POST[ 'order-city' ],
                    'address' => $_POST[ 'order-address-delivery' ],
                    'delivery' => [
                        'code' => $_POST[ 'order-method-delivery' ],
                        'cost' => $deliveryMethod[ 'cost' ]
                    ],
                    'payment' => [
                        'code' => $_POST[ 'order-method-payment' ]
                    ],
                    'comment' => $_POST[ 'order-user-comment' ],
                    'basket' => $basket[ 'items' ]
                ];

                if ( $orderId = OrderModel::create( $orderParams ) ) {
                    BasketModel::clean();
                    header( "Location: /order/thanks/$orderId/" );
                }
            }
        }

        $this->setView( 'index' );
        $this->setDataTitle( 'Оформление заказа' );
        $this->setDataHeader( 'Оформление заказа' );

        $this->setDataView( 'method_delivery', $delivery->getMethods() );
        $this->setDataView( 'method_payment', $payment->getMethods() );
        $this->setDataView( 'goods', $this->basket );

        return $this->getDataView();
    }

    /**
     * Страница спасибо за заказ.
     * @param $orderId - ID заказа
     * @return array
     */
    public function actionThanks( string $orderId ): array
    {
        $this->setView( 'thanks' );
        $this->setDataTitle( 'Спасибо за ваш заказ' );
        $this->setDataHeader( 'Спасибо за ваш заказ' );

        $this->setDataView( 'order_id', $orderId );

        return $this->getDataView();
    }
}
