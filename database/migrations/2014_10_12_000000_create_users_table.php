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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('fname');
            $table->string('lname');
            $table->string('email')->unique();
            $table->string('username')->nullable()->unique();
            $table->string('phone');
            $table->string('user_type');
            $table->string('status');
            $table->boolean('isActive')->nullable();
            $table->string('photo');
            $table->longText('description')->nullable();
            $table->string('my_ref_id')->nullable();

            // vendor
            $table->string('store_name')->nullable();
            $table->string('store_id')->nullable();
            $table->string('store_url')->nullable();

            // vendor account detail
            $table->string('acct_name')->nullable();
            $table->string('acct_number')->nullable();
            $table->string('acct_type')->nullable();
            $table->string('bank')->nullable();

            // Address
            $table->string('state')->nullable();
            $table->string('lga')->nullable();
            $table->longText('address')->nullable();

            // referral ID
            $table->string('ref_id')->nullable();

            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();

            // $table->unsignedBigInteger('store_id')->nullable();
            $table->unsignedBigInteger('role_id')->nullable();
            $table->unsignedBigInteger('package_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
