<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('area', function (Blueprint $table) {
            $table->id('id_area');
            $table->string('nombre_area', 50)->unique(); // ❌ Ya no es nullable
        });
    }

    public function down()
    {
        Schema::dropIfExists('area');
    }
};
