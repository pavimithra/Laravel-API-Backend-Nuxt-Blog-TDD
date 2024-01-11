<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use App\Models\User;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    
    public User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = $this->createUser();
    }

    private function createUser(): User
    {
        return User::factory()->create();
    }
}
