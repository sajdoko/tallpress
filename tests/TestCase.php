<?php

namespace Sajdoko\TallPress\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;
use Sajdoko\TallPress\TallPressServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Sajdoko\\TallPress\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            TallPressServiceProvider::class,
            \Livewire\LivewireServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
        config()->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Set application key for encryption
        config()->set('app.key', 'base64:'.base64_encode(random_bytes(32)));

        // Set TallPress config to use test User model class string
        config()->set('tallpress.author_model', 'App\\Models\\User');

        // Create User model class if it doesn't exist
        if (! class_exists('App\\Models\\User')) {
            eval('
                namespace App\Models;
                use Illuminate\Foundation\Auth\User as Authenticatable;
                class User extends Authenticatable {
                    protected $fillable = ["name", "email", "password", "role", "email_verified_at"];
                }
            ');
        }
    }

    protected function defineDatabaseMigrations()
    {
        // Create users table first
        $this->app['db']->connection()->getSchemaBuilder()->create('users', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('role')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        // Load package migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    /**
     * Set up the test user for tests that need authentication.
     */
    protected function setUpTestUser(): object
    {
        // Create users table if it doesn't exist
        if (! \Illuminate\Support\Facades\Schema::hasTable('users')) {
            \Illuminate\Support\Facades\Schema::create('users', function ($table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->string('role')->nullable();
                $table->rememberToken();
                $table->timestamps();
            });
        }

        // Create User model dynamically if it doesn't exist
        $userClass = config('tallpress.author_model', 'App\\Models\\User');
        if (! class_exists($userClass)) {
            eval('
                namespace App\Models;
                use Illuminate\Foundation\Auth\User as Authenticatable;
                class User extends Authenticatable {
                    protected $fillable = ["name", "email", "password", "role"];
                }
            ');
        }

        return (new $userClass)->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);
    }
}
