<?php
namespace Controller;

use Engine\Db;
use Model\OrderModel;
use Model\UserModel;
use Exception;
use PDOException;

/**
 * Class AccountController
 * Контроллер личного кабинета пользователя.
 * @package Controller
 */
class AccountController extends Controller
{
    /**
     * AccountController constructor.
     */
    public function __construct()
    {
        parent::__construct();

        if ( !UserModel::isUserAuth() ) {
            header( 'Location: /user/auth/' );
            exit();
        }
    }

    /**
     * Главная страница раздела.
     * @return array
     */
    public function actionIndex(): array
    {
        $orders = OrderModel::getOrdersList( UserModel::getUserId() );

        $this->setView( 'index' );
        $this->setDataTitle( 'Личный кабинет пользователя' );
        $this->setDataHeader( 'Личный кабинет' );

        $this->setDataView( 'orders', $orders );

        return $this->getDataView();
    }

    /**
     * Просмотр подробной информации о заказе.
     * @param $orderId - ID заказа, для котого вывести подробную информацию
     * @return array
     */
    public function actionOrder( $orderId ): array
    {
        $this->setView( 'order' );

        $order = OrderModel::getOrderById( UserModel::getUserId(), $orderId );
        if ( !empty( $order ) ) {

            $this->setDataTitle( "Заказ №{$orderId} от {$order[ 'date_create' ]}" );
            $this->setDataHeader( "Заказ №{$orderId} от {$order[ 'date_create' ]}" );

            $this->setDataView( 'customer', $order[ 'customer' ] );
            $this->setDataView( 'delivery', $order[ 'delivery' ] );
            $this->setDataView( 'payment', $order[ 'payment' ] );
            $this->setDataView( 'goods', $order[ 'goods' ] );
            $this->setDataView( 'comment', $order[ 'comment' ] );
            $this->setDataView( 'total_count', $order[ 'total_count' ] );
            $this->setDataView( 'total_sum', $order[ 'total_sum' ] );
        } else {
            $this->setDataTitle( "Заказ №{$orderId} не найден" );
            $this->setDataHeader( "Заказ №{$orderId} не найден" );
        }

        return $this->getDataView();
    }

    /**
     * Изменение личных данных пользователя.
     * @return array
     * @throws Exception
     */
    public function actionUser(): array
    {
        if ( $_SERVER[ 'REQUEST_METHOD' ] == 'POST' && isset( $_POST[ 'user-data-submit' ] ) ) {

            if ( !isset( $_POST[ 'user-data-name' ] )
                || !isset( $_POST[ 'user-data-email' ] )
                || !isset( $_POST[ 'user-data-phone' ] )
                || !isset( $_POST[ 'user-data-id' ] ) )
            {
                throw new Exception( 'Неверные поля у формы - Изменение личных данных' );
            }

            $id = $_POST[ 'user-data-id' ];
            $name = $_POST[ 'user-data-name' ];
            $email = $_POST[ 'user-data-email' ];
            $phone = $_POST[ 'user-data-phone' ];

            //todo: проверка введенных данных

            $set = [
                'name' => $name,
                'email' => $email,
                'phone' => $phone
            ];

            try {
                Db::getInstance()->update( 'user', $set, 'id_user=:id_user', [ 'id_user' => $id ] );
            } catch ( PDOException $e ) {
                throw new Exception( 'Не удалось обновить данные пользователя' );
            }

            UserModel::refreshData( $id );

            header( 'Location: /account/' );
            exit();
        }

        $this->setView( 'user' );
        $this->setDataTitle( 'Изменить личные данные' );
        $this->setDataHeader( 'Изменить личные данные' );

        $user = UserModel::getUser();

        $this->setDataView( 'id', $user[ 'id' ] );
        $this->setDataView( 'login', $user[ 'login' ] );
        $this->setDataView( 'name', $user[ 'name' ] );
        $this->setDataView( 'email', $user[ 'email' ] );
        $this->setDataView( 'phone', $user[ 'phone' ] );

        return $this->getDataView();
    }

    /**
     * Изменить пароль пользователя.
     * @return array
     * @throws Exception
     */
    public function actionPass(): array
    {
        $this->setView( 'pass_change' );
        $this->setDataTitle( 'Изменить пароль' );
        $this->setDataHeader( 'Изменить пароль' );

        $this->setDataView( 'id', UserModel::getUserId() );

        if ( $_SERVER[ 'REQUEST_METHOD' ] == 'POST' && isset( $_POST[ 'user-pass-submit' ] ) ) {
            var_dump( $_POST );

            if ( !isset( $_POST[ 'user-pass-new' ] )
                || !isset( $_POST[ 'user-pass-confirm' ] )
                || !isset( $_POST[ 'user-pass-id' ] ) )
            {
                throw new Exception( 'Неверные поля у формы - Изменить пароль' );
            }

            $id = $_POST[ 'user-pass-id' ];
            $passNew = $_POST[ 'user-pass-new' ];
            $passConfirm = $_POST[ 'user-pass-confirm' ];

            if ( empty( $passNew ) ) {
                $errors[] = 'Укажите пароль';
            }

            if ( $passNew !== $passConfirm ) {
                $errors[] = 'Пароли не совпадают';
            }

            if ( !empty( $errors ) ) {
                $this->setDataView( 'errors', $errors );
                return $this->getDataView();
            }

            if ( UserModel::changePassword( $id, $passNew ) ) {
                $message = 'Пароль изменен';
            } else {
                $message = 'Пароль изменить не удалось';
            }

            $this->setDataView( 'message', $message );
        }

        return $this->getDataView();
    }
}
