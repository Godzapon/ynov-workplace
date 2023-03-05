<?php

namespace App\Tests;

use Hautelook\AliceBundle\PhpUnit\RecreateDatabaseTrait;

class GroupsTest extends \ApiPlatform\Symfony\Bundle\Test\ApiTestCase
{
    use RecreateDatabaseTrait;

    public function testGetCollection()
    {
        $response = static::createClient()->request('GET', '/api/groups');
        $this->assertResponseStatusCodeSame(200);
        $this->assertCount(3, $response->toArray()['hydra:member']);
    }

    public function testGetMembersFromGroup()
    {
        $response = static::createClient()->request('GET', '/api/groups/1/members');
        $this->assertResponseStatusCodeSame(200);
        $this->assertCount(5, $response->toArray()['hydra:member']);
    }
}