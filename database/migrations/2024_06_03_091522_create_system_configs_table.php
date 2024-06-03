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
        Schema::create('system_configs', function (Blueprint $table) {
            $table->string('code')->primary()->comment('设置主键');
            $table->string('name')->comment("说明");
            $table->string('description')->nullable()->comment('介绍文');
            $table->string('sort_order')->default(0)->comment('排序');
            $table->longText('content')->nullable()->comment('内容');
            $table->enum('type',['input','textarea','editor','file','image','radio','switch','checkbox','list','select'])->default('input')->comment('类型');
            $table->json('options')->nullable()->comment('选项');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_configs');
    }
};
