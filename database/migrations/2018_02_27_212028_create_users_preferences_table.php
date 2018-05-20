<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersPreferencesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('users_preferences', function (Blueprint $table) {
			$table->integer('user_id')->unsigned()->primary();
			$table->foreign('user_id')->references('id')->on('users');
			$table->string('key');
			$table->string('value');
			$table->enum('type', [
				'STRING', 'INTEGER', 'DOUBLE', 'BOOLEAN', 'ARRAY',
			])->default('STRING');

			$table->timestamps();
			$table->primary(['user_id', 'key']);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('users_preferences');
	}
}
