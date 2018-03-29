<?php

namespace Model;

use Engine\Config;
use Engine\Db;
use PDOException;

/**
 * Class UserModel
 * Работа с пользователями.
 * @package Model
 */
class UserModel
{
    /**
     * Проверяем авторизован пользователь или нет
     * @return bool - TRUE пользователь авторизован, иначе FALSE
     */
    static public function isUserAuth(): bool
    {
        return isset( $_SESSION[ 'auth' ] ) ? $_SESSION[ 'auth' ] : false;
    }

    /**
     * Получить информацию о пользователе
     * @return array - ассоциативный массив с информацией о пользователе
     */
    static public function getUser(): array
    {
        return isset( $_SESSION[ 'user' ] ) ? $_SESSION[ 'user' ] : [];
    }

    /**
     * Получить ID авторизованного пользователя
     * @return string - ID авторизованного пользователя
     */
    static public function getUserId(): string
    {
        return isset( $_SESSION[ 'user' ][ 'id' ] ) ? $_SESSION[ 'user' ][ 'id' ] : '';
    }

    /**
     * Получить данные пользователя из БД по его ID.
     * @param string $userId
     * @return array
     */
    static public function getUserFromDb( string $userId ): array
    {
        $query = "SELECT id_user AS id, login, cookie, password, name, email, phone 
                  FROM user 
                  WHERE id_user=:id_user";
        $user = Db::getInstance()->select( $query, [ 'id_user' => $userId ], true );

        return $user;
    }

    /**
     * Обновить в таблице USER для указанного пользователя о COOKIE.
     *
     * @param string $userId
     * @param string $cookie - значение COOKIE
     * @return bool - TRUE данные обновлены, FALSE если иначе
     */
    static public function setCookieInDb( string $userId, string $cookie ): bool
    {
        $dataSet = [ 'cookie' => $cookie ];
        $dataWhere = [ 'id_user' => $userId ];
        $where = 'id_user=:id_user';
        $result = Db::getInstance()->update( 'user', $dataSet, $where, $dataWhere );

        return ( $result > 0 ? true : false );
    }

    /**
     * Авторизация пользователя на сайте.
     *
     * @param string $login - логин пользователя введенный в форме авторизации
     * @param string $password - пароль введенный в форме авторизации
     * @param bool $remember - TRUE запомнить авторизацию, иначе FALSE
     * @return bool - в случае успеха TRUE, иначе FALSE
     */
    static public function userLogin( string $login, string $password, bool $remember = false ): bool
    {
        $_SESSION[ 'auth' ] = false;

        $user = self::userVerify( $login, $password );
        if ( $user !== false ) {
            self::setDataSession( $user );

            if ( $remember === true ) {
                $cookieKey = self::generateSalt();

                if ( self::setCookieInDb( $user[ 'id' ], $cookieKey ) ) {
                    setcookie( 'user', $user[ 'id' ], time() + 3600 * 24 * 30, '/' );
                    setcookie( 'key', $cookieKey, time() + 3600 * 24 * 30, '/' );
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Поиск пользователя в базе данных. Если пользователь с указанными логином и паролем найден,
     * возвращаем TRUE, иначе FALSE.
     *
     * @param string $login - логин пользователя
     * @param string $password - пароль пользователя, нехешированный
     * @return array|bool
     */
    static public function userVerify( string $login, string $password )
    {
        $params = [
            'login' => $login
        ];
        $query = "SELECT id_user AS id, login, cookie, password, name, email, phone 
                  FROM user 
                  WHERE login=:login";
        $result = Db::getInstance()->select( $query, $params, true );

        if ( isset( $result[ 'password' ] )
            && self::confirmPassword( $result[ 'password' ], $password ) )
        {
            return $result;
        }

        return false;
    }

    /**
     * Добавление информации о пользователе в базу данных.
     *
     * @param string $login - логин пользователя
     * @param string $password - пароль (нехешированный)
     * @param string $name - имя пользователя
     * @return string
     */
    static public function userReg( string $login, string $password, string $name ): string
    {
        $queryData = [
            'login' => $login,
            'password' => self::hashPassword( $password ),
            'name' => $name
        ];

        try {
            $userId = Db::getInstance()->insert( 'user', $queryData );
        } catch ( PDOException $e ) {
            throw $e;
        }

        return $userId;
    }

    /**
     * Восстанавливаем авторизацию пользователя по данным сессии и кук.
     * @return array|bool - FALSE, если авторизацию восстановить не удалось, Array, если иначе.
     */
    static public function restoreAuth(): bool
    {
        if ( isset( $_SESSION[ 'auth' ] )
            && $_SESSION[ 'auth' ] === true
            && isset( $_SESSION[ 'user' ] ) )
        {
            return true;

        } elseif ( isset( $_COOKIE[ 'user' ] ) && isset( $_COOKIE[ 'key' ] ) ) {
            $user = self::getUserFromDb( $_COOKIE[ 'user' ] );
            if ( $user === false ) {
                return false;
            }

            // Если кука браузера совпадает с кукой в БД, то восстанавливаем авторизацию.
            // Куку не продливаем, чтобы в случае кражи куки, у злоумышленника был временный доступ,
            // т.к при следующей авторизации пользователем, кука изменится.
            if ( $_COOKIE[ 'key' ] === $user[ 'cookie' ] ) {
                self::setDataSession( $user );
                return true;
            }
        }

        return false;
    }

    /**
     * Выход пользователя.
     * Уничтожение кук и сессии.
     */
    static public function userLogout(): void
    {
        if ( $_SESSION[ 'auth' ] === true ) {
            if ( isset( $_COOKIE[ 'user' ] ) ) {
                setcookie( 'user', '', time() - 60, '/' );
                setcookie( 'key', '', time() - 60, '/' );
            }

            session_destroy();
        }
    }

    /**
     * Изменить пароль пользователя.
     * @param string $userId
     * @param string $password
     * @return bool
     */
    static function changePassword( string $userId, string $password ): bool
    {
        $rowCount = Db::getInstance()->update(
            'user',
            [ 'password' => self::hashPassword( $password ) ],
            'id_user=:id_user',
            [ 'id_user' => $userId ]
        );

        return $rowCount > 0 ? true : false;
    }

    static public function refreshData( string $userId )
    {
        $user = self::getUserFromDb( $userId );
        self::setDataSession( $user );
    }

    /**
     * Записать данные пользователя в сессию.
     * @param array $user - массив с данными пользователя.
     */
    static private function setDataSession( array $user ): void
    {
        $_SESSION[ 'auth' ] = true;
        $_SESSION[ 'user' ][ 'id' ] = $user[ 'id' ];
        $_SESSION[ 'user' ][ 'login' ] = $user[ 'login' ];
        $_SESSION[ 'user' ][ 'name' ] = $user[ 'name' ];
        $_SESSION[ 'user' ][ 'email' ] = $user[ 'email' ];
        $_SESSION[ 'user' ][ 'phone' ] = $user[ 'phone' ];
        $_SESSION[ 'user' ][ 'cookie' ] = $user[ 'cookie' ];
    }

    /**
     * Сравнение пароля с его хешем.
     *
     * @param string $hash - эталонный хеш
     * @param string $password - пароль в нехешированном виде
     * @return bool - если сгенерированный хеш совпадает с эталонным, то TRUE, иначе FALSE
     */
    static public function confirmPassword( string $hash, string $password ): bool
    {
        return crypt( $password, $hash ) === $hash;
    }

    /**
     * Генерация хеша, Blowfish.
     *
     * @param string $password - пароль, для которого нужно получить хеш
     * @return string - готовый хеш
     */
    static public function hashPassword( string $password ): string
    {
        /** @var string $salt */
        $salt = md5( uniqid( Config::get( 'SECRET_KEY' ), true ) );
        $salt = substr( strtr( base64_encode( $salt ), '+', '.' ), 0, 22 );

        return crypt( $password, '$2y$08$' . $salt );
    }

    /**
     * Генерируем случайную строку длинной указанной в параметре $length.
     *
     * @param int $length - необходимая длина генерируемой строки, по-умолчанию 10.
     * @return string
     */
    static public function generateSalt( int $length = 10 ): string
    {
        /** @var string $salt */
        $salt = '';
        for ( $i = 0; $i < $length; $i++ ) {
            $salt .= chr( mt_rand( 33, 126 ) );
        }

        return $salt;
    }
}
