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
        Schema::create('dicts', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->comment("字典标识");
            $table->string('name')->comment("字典名称");
            $table->integer("sort_order")->default(0)->comment("排序");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dicts');
    }
};
