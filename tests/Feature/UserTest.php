<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Tests\TestCase;

use function PHPUnit\Framework\assertNotEquals;
use function PHPUnit\Framework\assertNotFalse;

class UserTest extends TestCase
{
    public function testRegisterSuccess()
    {
        $this->post('/api/users', [
            'username' => 'zami',
            'password' => 'rahasia',
            'name' => 'Abdul Aziz Zam Zami'
        ])->assertStatus(201)
            ->assertJson([
                "data" => [
                    'username' => 'zami',
                    'name' => 'Abdul Aziz Zam Zami'
                ]
            ]);
    }

    public function testRegisterFailed()
    {
        $this->post('/api/users', [
            'username' => '',
            'password' => '',
            'name' => ''
        ])->assertStatus(400)
            ->assertJson([
                "errors" => [
                    'username' => [
                        "The username field is required."
                    ],
                    'password' => [
                        "The password field is required."
                    ],
                    'name' => [
                        "The name field is required."
                    ]
                ]
            ]);

    }

    public function testRegisterUsernameAlreadyExists()
    {
        $this->testRegisterSuccess();
        $this->post('/api/users', [
            'username' => 'zami',
            'password' => 'rahasia',
            'name' => 'Abdul Aziz Zam Zami'
        ])->assertStatus(400)
            ->assertJson([
                "errors" => [
                    "username" => [
                        "username already registered"
                    ]
                ]
            ]);
    }

    public function testLoginSuccess()
    {
        $this->seed(UserSeeder::class);
        $this->post('/api/users/login', [
            'username' => 'test',
            'password' => 'test'
        ])->assertStatus(200)
            ->assertJson([
                'data' => [
                    'username' => 'test',
                    'name' => 'test',
                ]
            ]);
        $user = User::where('username','test')->first();
        self::assertNotNull($user->token);
    }

    public function testLoginFaliedUsernameNotFound()
    {
        $this->post('/api/users/login', [
            'username' => 'test',
            'password' => 'test'
        ])->assertStatus(401)
            ->assertJson([
                'errors' => [
                    "message" => [
                        "username or password wrong"
                    ]
                ]
            ]);
    }

    public function testLoginFaliedPasswordWrong()
    {
        $this->seed(UserSeeder::class);
        $this->post('/api/users/login', [
            'username' => 'test',
            'password' => 'salah'
        ])->assertStatus(401)
            ->assertJson([
                'errors' => [
                    "message" => [
                        "username or password wrong"
                    ]
                ]
            ]);
    }

    public function testGetSuccess()
    {
        $this->seed([UserSeeder::class]);

        $this->get('/api/users/current',[
            'Authorization' => 'test'
        ])->assertStatus(200)
            ->assertJson([
                'data' => [
                    "username" => "test",
                    "name" => "test"
                ]
            ]);
    }

    public function testGetUnauthorized()
    {
        // $this->seed([UserSeeder::class]);

        $this->get('/api/users/current')->assertStatus(401)
            ->assertJson([
                "errors" => [
                    "message" => [
                        "unauthorized"
                    ]
                ]
            ]);
    }

    public function testGetInvalidToken()
    {
        $this->seed([UserSeeder::class]);

        $this->get('/api/users/current',[
            'Authorization' => 'salah'
        ])->assertStatus(401)
            ->assertJson([
                "errors" => [
                    "message" => [
                        "unauthorized"
                    ]
                ]
            ]);
    }

    public function testUpdatePasswordSuccess()
    {
        $this->seed([UserSeeder::class]);

        $oldUser = User::where('username','test')->first();

        $this->patch('/api/users/current',
        [
            'password' => 'baru'
        ],
        [
            'Authorization' => 'test'
        ]
        )->assertStatus(200)
            ->assertJson([
                'data' => [
                    "username" => "test",
                    "name" => "test"
                ]
            ]);

        $newUser = User::where('username','test')->first();
        assertNotEquals($oldUser->password, $newUser->password);
    }

    public function testUpdateNameSuccess()
    {
        $this->seed([UserSeeder::class]);

        $oldUser = User::where('username','test')->first();

        $this->patch('/api/users/current',
        [
            'name' => 'zami'
        ],
        [
            'Authorization' => 'test'
        ]
        )->assertStatus(200)
            ->assertJson([
                'data' => [
                    "username" => "test",
                    "name" => "zami"
                ]
            ]);

        $newUser = User::where('username','test')->first();
        assertNotEquals($oldUser->name, $newUser->name);
    }

    public function testUpdateFailed()
    {
        $this->seed([UserSeeder::class]);

        $oldUser = User::where('username','test')->first();

        $this->patch('/api/users/current',
        [
            'name' => 'zamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizamizami'
        ],
        [
            'Authorization' => 'test'
        ]
        )->assertStatus(400)
            ->assertJson([
                'errors' => [
                    "name" => [
                        "The name field must not be greater than 100 characters."
                    ]
                ]
            ]);
    }

    public function testLogoutSuccess()
    {
        $this->seed([UserSeeder::class]);

        $this->delete(uri: 'api/users/logout', headers:[
            'Authorization' => 'test'
        ])->assertStatus(200)
            ->assertJson([
                'data' => true
            ]);
        $user = User::where('username','test')->first();
        self::assertNull($user->token);
    }

    public function testLogoutFailed()
    {
        $this->seed([UserSeeder::class]);

        $this->delete(uri: 'api/users/logout', headers:[
            'Authorization' => 'salah'
        ])->assertStatus(401)
            ->assertJson([
                'errors' => [
                    'message' => [
                        "unauthorized"
                    ]
                ]
            ]);
    }
}
