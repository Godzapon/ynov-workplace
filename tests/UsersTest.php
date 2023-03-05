<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\User;
use Hautelook\AliceBundle\PhpUnit\RecreateDatabaseTrait;

class UsersTest extends ApiTestCase
{
    use RecreateDatabaseTrait;

    public function testPostUser()
    {
        static::createClient()->request('POST', '/api/users', ['json' => [
            'nickname' => 'bogossDu06',
            'email' => 'machin@domain.com',
            'plainPassword' => 'toto1234',
        ]]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertJsonContains([
            '@context' => '/api/contexts/User',
            '@id' => '/api/users/11',
            '@type' => 'User',
            'id' => 11,
            'email' => 'machin@domain.com',
            'nickname' => 'bogossDu06'
        ]);
        $this->assertMatchesResourceItemJsonSchema(User::class);
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