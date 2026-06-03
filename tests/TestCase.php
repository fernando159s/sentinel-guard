<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Salvaguarda: los tests SIEMPRE corren contra sqlite en memoria.
     *
     * docker-compose inyecta DB_CONNECTION=mysql como variable real del
     * proceso, que queda en $_SERVER/$_ENV. El helper env() de Laravel lee
     * $_SERVER antes que getenv(), por lo que el `force="true"` de phpunit.xml
     * (que solo toca putenv) no basta. Sin esto, los tests con RefreshDatabase
     * ejecutarian migrate:fresh sobre la BD de desarrollo y la borrarian.
     */
    protected function setUp(): void
    {
        foreach ([
            'DB_CONNECTION' => 'sqlite',
            'DB_DATABASE' => ':memory:',
            'DB_URL' => '',
        ] as $key => $value) {
            $_SERVER[$key] = $_ENV[$key] = $value;
            putenv("{$key}={$value}");
        }

        parent::setUp();
    }
}
