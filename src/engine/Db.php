<?php

namespace Engine;

use PDO;
use PDOException;

/**
 * Class Db
 * Работа с БД посредством PDO.
 * @package Engine
 */
class Db
{
    /** @var null|Db $instance - экземпляр класса */
    private static $instance = null;

    /** @var null|PDO - соединение с БД */
    private $link = null;

    /**
     * Db constructor.
     */
    private function __construct()
    {
        setlocale( LC_ALL, 'ru_RU.UTF8' );
        $this->link = $this->connect();
    }

    /**
     * Получить экземпляр класса.
     * @return Db|null
     */
    public static function getInstance()
    {
        if ( is_null( self::$instance ) ) {
            self::$instance = new Db();
        }

        return self::$instance;
    }

    /**
     * Получить имя источника данных или DNS, содержащее информацию, необходимую для подключения к базе данных.
     * @return string
     */
    private static function getDataSourceName()
    {
        return Config::get( 'DB_DRIVER' )
            . ':host=' . Config::get( 'DB_HOST' )
            . ';dbname=' . Config::get( 'DB_DATABASE' )
            . ';charset=' . Config::get( 'DB_CHARSET' );
    }

    /**
     * Подлючение к базе данных.
     * @return PDO
     */
    private function connect()
    {
        $dsn = self::getDataSourceName();

        try {
            $db = new PDO( $dsn, Config::get( 'DB_USER' ), Config::get( 'DB_PASSWORD' ) );
        } catch ( PDOException $e ) {
            throw $e;
        }

        // указываем какой набор символов будет использоваться клиентом при отправке запросов
        $db->exec( 'SET NAMES ' . Config::get( 'DB_CHARSET' ) );

        // устанавливаем режим обработки исключений в случае ошибки
        $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
        // устанавливаем возврат результата в ассоциативном массиве
        $db->setAttribute( PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC );

        return $db;
    }

    /**
     * Выборка данных.
     * SELECT * FROM user WHERE id_user=:id_user
     *
     * @param string $query - текст запроса с именованными параметрами, например, :id_user
     * @param array $params - ассоциативный массив содержащий значения именовынных параметров
     * @param bool $one - если TRUE, то метод вернет только одну зупись (первую)
     * @return array
     */
    public function select( string $query, array $params = [], bool $one = false )
    {
        $sth = $this->link->prepare( $query );
        $sth->execute( $params );
        if ( $one ) {
            $result = $sth->fetch();
        } else {
            $result = $sth->fetchAll();
        }

        return $result;
    }

    /**
     * Добавляет новую запись в указанную таблицу
     * INSERT INTO table ( col_1, col_2 ) VALUES( :col_1, :col_2 )
     *
     * @param string $table - имя таблицы, куда добавляется запись
     * @param array $data - ассоциативный массив с данными, вида [ 'col_1' => 'val_1', 'col_2' => val_2 ]
     * @return string|bool - ID добавленной записи, в случае ошибки FALSE
     */
    public function insert( string $table, array $data )
    {
        $columns = [];
        $placeholders = [];

        foreach ( $data as $key => $value ) {
            $columns[] = "`$key`";
            $placeholders[] = ":$key";

            if ( is_null( $value ) ) {
                $data[ $key ] = 'NULL';
            }
        }

        $columns = implode( ',', $columns );
        $placeholders = implode( ',', $placeholders );

        $query = "INSERT INTO `$table` ($columns) VALUES($placeholders)";

        $sth = $this->link->prepare( $query );
        if ( $sth->execute( $data ) ) {
            return $this->link->lastInsertId();
        } else {
            return false;
        }
    }

    /**
     * Обновляет данные в указанной таблице
     * UPDATE user SET password=:password WHERE id_user=:id_user
     *
     * @param string $table - имя таблицы, где обновляются данные
     * @param array $dataSets - ассоциативный массив с данными для блока SET запроса UPDATE, например, [ 'username' => 'admin' ]
     * @param string $where - строка с условием выборки данных для обновления с именованными параметрами, например, 'id_user=:id_user'
     * @param array $dataWhere - ассоциативный массив с данными для блока WHERE запроса UPDATE, например, [ 'id_user' => '1' ]
     * @return int - количество записей, которые участвовали в операции
     */
    public function update( string $table, array $dataSets, string $where, array $dataWhere )
    {
        $sets = [];
        $data = [];

        foreach ( $dataSets as $key => $value ) {
            $sets[] = "`$key`=:$key";
            $data[ $key ] = ( !is_null( $value ) ? $value : 'NULL' );
        }

        foreach ( $dataWhere as $key => $value ) {
            $data[ $key ] = ( !is_null( $value ) ? $value : 'NULL' );
        }

        $sets = implode( ',', $sets );

        $query = "UPDATE `$table` SET $sets WHERE $where";

        $sth = $this->link->prepare( $query );
        $sth->execute( $data );

        return $sth->rowCount();
    }

    /**
     * Удалить данные из указанной таблицы
     * DELETE FROM user WHERE id_user=:id_user
     *
     * @param string $table - имя таблицы, из которой удаляются данные
     * @param array $data - ассоциативный массив с данными для блока WHERE запроса DELETE, например, [ 'id_user' => '1' ]
     * @return int - количество записей, которые участвовали в операции
     */
    public function delete( string $table, array $data )
    {
        $where = [];

        foreach ( $data as $key => $value ) {
            $where[] = "`$key`=:$key";
        }

        $where = implode( ' AND ', $where );

        $query = "DELETE FROM `$table` WHERE $where";

        $sth = $this->link->prepare( $query );
        $sth->execute( $data );

        return $sth->rowCount();
    }

    /**
     * Начинаем транзацию
     */
    public function transactionStart()
    {
        $this->link->beginTransaction();
    }

    /**
     * Фиксируем изменения в транзакции
     */
    public function transactionCommit()
    {
        if ( $this->link->inTransaction() ) {
            $this->link->commit();
        }
    }

    /**
     * Отменяем изменения в транзакции
     */
    public function transactionRollBack()
    {
        if ( $this->link->inTransaction() ) {
            $this->link->rollBack();
        }
    }

    /**
     * Проверяем начата ли транзакция.
     * @return bool - TRUE если начата, иначе FALSE.
     */
    public function isTransactionAlreadyStarted(): bool
    {
        return $this->link->inTransaction();
    }
}
