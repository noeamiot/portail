<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email', 128)->unique();
            $table->string('firstname', 128)->nullable();
            $table->string('lastname', 128)->nullable();

            /*
            $table->string('domaine');       // etu, ...
            $table->string('branche');       // GI, ...
            $table->string('filiere');       // FDD, ...
            $table->integer('telephone', 10);       // 06...
            $table->string('semestre', 5);       // 06...
            */

            $table->rememberToken();
            $table->timestamps();
            $table->timestamp('last_login_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
