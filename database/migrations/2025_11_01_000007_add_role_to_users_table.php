<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Only add role column if configured to do so
        if (config('tallpress.roles.add_role_field', true)) {
            if (! Schema::hasColumn('users', config('tallpress.roles.role_field', 'role'))) {
                Schema::table('users', function (Blueprint $table) {
                    $roleField = config('tallpress.roles.role_field', 'role');
                    $defaultRole = config('tallpress.roles.default_role', 'editor');

                    $table->string($roleField)->default($defaultRole)->after('email');
                    $table->index($roleField);
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (config('tallpress.roles.add_role_field', true)) {
            if (Schema::hasColumn('users', config('tallpress.roles.role_field', 'role'))) {
                Schema::table('users', function (Blueprint $table) {
                    $roleField = config('tallpress.roles.role_field', 'role');
                    $table->dropColumn($roleField);
                });
            }
        }
    }
};
