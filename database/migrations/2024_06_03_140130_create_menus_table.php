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
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string("name")->comment("名称");
            $table->json("url")->comment("路由地址");
            $table->integer("type")->default(0)->comment("菜单类型");
            $table->boolean("is_valid")->default(true)->comment("是否生效");
            $table->boolean("is_show")->default(true)->comment("是否显示");
            $table->integer("sort_order")->default(0)->comment("排序");
            $table->integer("parent_id")->nullable()->comment("父级ID");
            $table->comment = "菜单信息表";
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
