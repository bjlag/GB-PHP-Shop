<?php

namespace Controller;

use Model\UserModel;
use PDOException;

/**
 * Class UserController
 * Контроллер для управления авторизацией пользователей.
 * @package Controller
 */
class UserController extends Controller
{
    /**
     * UserController constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->setView( 'auth' );
        $this->setDataTitle( 'Авторизация пользователя' );
        $this->setDataHeader( 'Авторизация пользователя' );
    }

    /**
     * Шаблон по умолчанию.
     */
    public function actionIndex(): void
    {
        header( 'Location: /user/auth/' );
        exit();
    }

    /**
     * Авторизация пользователя на сайте.
     * @return array - данные для шаблона
     */
    public function actionAuth(): array
    {
        if ( UserModel::isUserAuth() ) {
            header( 'Location: /account/' );
        }

        $this->setView( 'auth' );
        $this->setDataTitle( 'Авторизация пользователя' );
        $this->setDataHeader( 'Авторизация пользователя' );

        $this->setDataView( 'form_action', '/user/auth/' );
        $this->setDataView( 'page_registration', '/user/reg/' );

        if ( $_SERVER[ 'REQUEST_METHOD' ] == 'POST'
            && isset( $_POST[ 'auth-submit' ] )
            && isset( $_POST[ 'auth-login' ] )
            && isset( $_POST[ 'auth-password' ] ) ) {

            $login = $_POST[ 'auth-login' ];
            $password = $_POST[ 'auth-password' ];
            $remember = ( isset( $_POST[ 'auth-remember' ] ) ? true : false );

            $errors = [];

            $this->setDataView( 'login', $login );

            if ( empty( $login ) ) {
                $errors[] = 'Введите имя пользователя';
            }

            if ( !empty( $errors ) ) {
                $this->setDataView( 'errors', $errors );
                return $this->getDataView();
            }

            if ( UserModel::userLogin( $login, $password, $remember ) ) {
                header( 'Location: /account/' );
            } else {
                $this->setDataView( 'message', 'Неверные имя пользователя или пароль' );
            }
        }

        return $this->getDataView();
    }

    /**
     * Регистрация пользователя на сайте.
     * @return array - данные для шаблона
     */
    public function actionReg(): array
    {
        $this->setView( 'reg' );
        $this->setDataTitle( 'Регистрация пользователя' );
        $this->setDataHeader( 'Регистрация пользователя' );

        $this->setDataView( 'form_action', '/user/reg/' );

        if ( $_SERVER[ 'REQUEST_METHOD' ] == 'POST'
            && isset( $_POST[ 'reg-submit' ] )
            && isset( $_POST[ 'reg-login' ] )
            && isset( $_POST[ 'reg-password' ] )
            && isset( $_POST[ 'reg-password-confirm' ] )
            && isset( $_POST[ 'reg-name' ] ) )
        {

            $login = $_POST[ 'reg-login' ];
            $password = $_POST[ 'reg-password' ];
            $passwordConfirm = $_POST[ 'reg-password-confirm' ];
            $name = $_POST[ 'reg-name' ];

            $this->setDataView( 'login', $login );
            $this->setDataView( 'name', $name );

            $errors = [];

            if ( empty( $login ) ) {
                $errors[] = 'Введите логин';
            }

            if ( empty( $password ) ) {
                $errors[] = 'Введите пароль';
            }

            if ( $password !== $passwordConfirm ) {
                $errors[] = 'Пароли не совпадают';
            }

            if ( empty( $name ) ) {
                $errors[] = 'Введите ваше имя';
            }

            if ( !empty( $errors ) ) {
                $this->setDataView( 'errors', $errors );
                return $this->getDataView();
            }

            try {
                UserModel::userReg( $login, $password, $name  );
            } catch ( PDOException $e ) {
                if ( $e->getCode() === '23000' ) {
                    $this->setDataView( 'message', 'Пользователь с таким именем уже зарегистрирован' );
                    return $this->getDataView();
                }

                throw $e;
            }

            $this->setDataView( 'login', '' );
            $this->setDataView( 'message', $name . ', Вы успешно зарегистрировались! <a href="/user/auth/">Авторизоваться</a>' );
        }

        return $this->getDataView();
    }

    /**
     * Выход пользователя из системы.
     */
    public function actionLogout(): void
    {
        UserModel::userLogout();
        header( 'Location: /' );
    }
}
