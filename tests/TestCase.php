<?php

namespace Tests;

use Mockery;
use App\Models\User;
use Mockery\MockInterface;
use Laravel\Lumen\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * @var $userAlias an alias mock for the User model
     */
    protected User|MockInterface $userAlias;

    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        return require __DIR__.'/../bootstrap/app.php';
    }

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->userAlias = Mockery::mock('alias:' . User::class);
    }
}
