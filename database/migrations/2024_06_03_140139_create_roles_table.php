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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string("name")->comment("角色名");
            $table->string("description")->nullable()->comment("角色说明");
            $table->integer("type")->default(0)->comment("角色类型");
            $table->json("routes")->nullable()->comment("路由权限");
            $table->json("extra")->nullable()->comment("附加角色");
            $table->integer("sort_order")->default(0)->comment("排序");
            $table->timestamps();
            $table->comment = "角色信息表";
        });
        Schema::create('roles_more', function (Blueprint $table) {
            $table->integer('role_id')->comment("角色ID");
            $table->string("model_id")->comment("关联ID");
            $table->string("model_type")->nullable()->comment("关联类");
            $table->primary(['role_id','model_id'],'role_more');
            $table->comment = "角色关联表";
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
        Schema::dropIfExists('roles_more');
    }
};
