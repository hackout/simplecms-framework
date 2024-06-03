<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('dict_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dict_id')->references('id')->on('dicts')->onDelete('cascade')->comment("字典ID");
            $table->string("name")->comment("键名");
            $table->integer("content")->default(0)->comment("键值");
            $table->integer("sort_order")->default(0)->comment("排序");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dict_items');
    }
};
