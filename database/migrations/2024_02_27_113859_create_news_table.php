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
     *
     * 기사 생성시 use y cron n service n
     * 관리자가 노출될 날자를 입력하고 저장하면 매일 지정된 시간에 크론이 돌면서 use === y & cron === n & service === n 인 기사만 불러와서 service & cron  === y 로 변경시킴
     * 뉴스 등록 후 크론 돌기전 use 상태를 n 으로 바꾸면 기사를 등록시키지 않는다.
     */
    public function up()
    {
        Schema::create('news', function (Blueprint $table) {
            $table->id('news_no');
            $table->string('multiple_insert_check_id', 30)->unique()->nullable()->comment('다중 저장시 아이디 가져오기 위해 교유값을 설정');
            $table->string('subject_ko')->fulltext();
            $table->string('subject_en')->fulltext();
            $table->text('contents_ko');
            $table->text('contents_en');
            $table->string('update_admin');
            $table->enum('use', ['y', 'n'])->index()->default('y')->comment('사용 유무')->index();
            $table->enum('service', ['y', 'n'])->index()->default('n')->comment('작성 후 서비스 할건지 여부 서비스 y 면 매일 정해진 시간에 배치 듈려 y로 수정')->index();
            $table->timestamp('service_date')->nullable()->default(null)->comment('서비스에 노출 시킬 날자를 정함 해당일이 되면 배치로 서비스 노출 시킴');
            $table->integer('hit')->unsigned()->default(0)->comment('클릭 수 확인용');
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
        Schema::dropIfExists('news');
    }
};
