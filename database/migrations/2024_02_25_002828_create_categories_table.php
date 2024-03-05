<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id('cate_no');
            $table->string('cate_ko', 30)->unique()->comment('한글 카테고리 고유 테그');
            $table->string('cate_en', 30)->unique()->comment('영문 카테고리 고유 테그');
            $table->enum('use', ['y', 'n'])->default('y')->comment('사용 유무')->index();
            $table->integer('hit')->unsigned()->default(0)->comment('클릭 수 확인용');
            $table->string('update_admin', '50')->comment('등록한사람 아이디');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('categories');
    }
};
