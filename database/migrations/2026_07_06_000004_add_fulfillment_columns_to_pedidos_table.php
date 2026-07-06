<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->foreignId('cliente_id')->nullable()->after('bot_contact_id')
                ->constrained('clientes')->nullOnDelete();
            $table->foreignId('numero_telefonico_id')->nullable()->after('plan_id')
                ->constrained('numeros_telefonicos')->nullOnDelete();
            $table->unsignedInteger('monto')->nullable()->after('estado');
            $table->text('notas')->nullable()->after('monto');
            $table->timestamp('pagado_at')->nullable()->after('notas');
            $table->timestamp('numero_asignado_at')->nullable()->after('pagado_at');
            $table->timestamp('entregado_at')->nullable()->after('numero_asignado_at');
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cliente_id');
            $table->dropConstrainedForeignId('numero_telefonico_id');
            $table->dropColumn(['monto', 'notas', 'pagado_at', 'numero_asignado_at', 'entregado_at']);
        });
    }
};
