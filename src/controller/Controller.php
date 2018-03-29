<?php
namespace Controller;

use Model\BasketModel;
use Model\UserModel;

/**
 * Class Controller
 * Базовый класс контроллеров
 * @package Controller
 */
class Controller
{
    /** @var array $dataView - ассоциативный массив с информацией для шаблона  */
    private $dataView = [];

    /** @var null|string $view - название шаблона  */
    private $view = null;

    /** @var array $user - информация о пользователе. Вознможно удалить. */
    protected $user = [];

    /** @var array $basket - корзина */
    protected $basket = [];

    /**
     * Controller constructor.
     */
    protected function __construct()
    {
        // Пробуем восстановить авторизацию пользователя
        if ( UserModel::restoreAuth() ) {
            $this->user = UserModel::getUser();
        }

        $this->basket = BasketModel::getBasket();

        $this->setDataUser(  $this->getUser() );
        $this->setDataView( 'basket', $this->getBasket() );
    }

    /**
     * Установить шаблон для вывода информации.
     * @param string $view - название шаблона
     */
    protected function setView( string $view ): void
    {
        $this->view = $view;
    }

    /**
     * Установить заголовок браузера для страницы.
     * @param $title - заголовок браузера
     */
    protected function setDataTitle( string $title ): void
    {
        $this->dataView[ 'title' ] = $title;
    }

    /**
     * Установить заголовок страницы.
     * @param string $header - заголовок старницы
     */
    protected function setDataHeader( string $header ): void
    {
        $this->dataView[ 'header' ] = $header;
    }

    /**
     * Добавить в ассоциативный массив данных для шаблона информацию о авторизованном пользователе.
     * @param array $userInfo - ассоциативный массив с информацией о пользователе
     */
    protected function setDataUser( array $userInfo ): void
    {
        $this->dataView[ 'user' ] = $userInfo;
    }

    /**
     * Добавить в ассоциативный массив данных для шаблона информацию с указанным ключем.
     * @param string $key - ключ ассоциативного массива
     * @param mixed $data - любые данные, которые будут храниться с указанным ключем
     */
    protected function setDataView( string $key, $data ): void
    {
        $this->dataView[ $key ] = $data;
    }

    /**
     * Получить заголовок браузера.
     * @return string
     */
    protected function getDataTitle(): string
    {
        return $this->dataView[ 'title' ];
    }

    /**
     * Получить сохраненный заголовок страницы.
     * @return string
     */
    protected function getDataHeader(): string
    {
        return $this->dataView[ 'header' ];
    }

    /**
     * Получить корзину
     * @return array
     */
    protected function getBasket(): array
    {
        return $this->basket;
    }

    /**
     * Получить данные пользователя.
     * @return array
     */
    protected function getUser(): array
    {
        return $this->user;
    }

    /**
     * Получить название шаблона.
     * Для шаблона index.tmpl, будет получено index.
     * @return string
     */
    public function getView(): string
    {
        return $this->view;
    }

    /**
     * Получить данные для шаблона.
     * @return array
     */
    public function getDataView(): array
    {
        return $this->dataView;
    }
}
