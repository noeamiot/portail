<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArticlesActionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('articles_actions', function (Blueprint $table) {
			$table->charset = 'utf8';
            $table->collation = 'utf8_bin';
            $table->uuid('id')->primary();
            $table->uuid('article_id');
            $table->uuid('user_id');
            $table->string('key');
            $table->string('value')->nullable();
            $table->enum('type', [
                'STRING', 'INTEGER', 'DOUBLE', 'BOOLEAN', 'ARRAY', 'DATETIME', 'NULL',
            ])->default('STRING');

            $table->timestamps();

            $table->foreign('article_id')->references('id')->on('articles');
            $table->foreign('user_id')->references('id')->on('users');

            $table->unique(['article_id', 'user_id', 'key']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('articles_actions');
    }
}
