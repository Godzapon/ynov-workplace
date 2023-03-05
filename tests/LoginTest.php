<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\User;
use Hautelook\AliceBundle\PhpUnit\RecreateDatabaseTrait;

class LoginTest extends ApiTestCase
{
    public function testPostUser()
    {
        static::createClient()->request('POST', '/api/users', ['json' => [
            'nickname' => 'bogossDu06',
            'email' => 'machin@domain.com',
            'plainPassword' => 'toto1234',
        ]]);
        static::createClient()->request('POST', '/auth', ['json' => [
            'email' => 'machin@domain.com',
            'password' => 'toto1234',
        ]]);
        $this->assertResponseIsSuccessful();
    }

    public function testLoginFail()
    {
        static::createClient()->request('POST', '/auth', ['json' => [
            'email' => 'toto@mail.com',
            'password' => 'wrong_password',
        ]]);

        $this->assertResponseStatusCodeSame(401);
    }
}