<?php
use PHPUnit\Framework\TestCase;
use Model\UserModel;
//use Engine\Config;

class UserModelTest extends TestCase
{
    protected $obj;

    protected function setUp()
    {
        $this->obj = new UserModel();
    }

    protected function tearDown()
    {
        $this->obj = null;
    }

    public function testGetUserInfo()
    {
        $result = null;
        $result = $this->obj::getUserInfo( 9 );

        $this->assertArrayHasKey( 'id_user', $result );
    }


}
