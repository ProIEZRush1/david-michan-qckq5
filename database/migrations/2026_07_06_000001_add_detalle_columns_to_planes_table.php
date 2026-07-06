<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('planes', function (Blueprint $table) {
            $table->unsignedInteger('datos_gb')->nullable()->after('descripcion');
            $table->boolean('minutos_ilimitados')->default(true)->after('datos_gb');
            $table->boolean('sms_ilimitados')->default(true)->after('minutos_ilimitados');
            $table->unsignedInteger('vigencia_dias')->default(30)->after('sms_ilimitados');
        });
    }

    public function down(): void
    {
        Schema::table('planes', function (Blueprint $table) {
            $table->dropColumn(['datos_gb', 'minutos_ilimitados', 'sms_ilimitados', 'vigencia_dias']);
        });
    }
};
