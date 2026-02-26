<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // mobile may already exist, check first
            if (!Schema::hasColumn('users', 'mobile')) {
                $table->string('mobile', 15)->nullable()->unique()->after('email');
            } else {
                $table->string('mobile', 15)->nullable()->unique()->change();
            }

            if (!Schema::hasColumn('users', 'otp')) {
                $table->string('otp', 10)->nullable()->after('mobile');
            }
            if (!Schema::hasColumn('users', 'otp_expires_at')) {
                $table->timestamp('otp_expires_at')->nullable()->after('otp');
            }
            if (!Schema::hasColumn('users', 'city')) {
                $table->string('city')->nullable()->after('otp_expires_at');
            }
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->tinyInteger('is_active')->default(1)->after('city');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['otp', 'otp_expires_at', 'city', 'is_active']);
        });
    }
};
