<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\Faq;
use App\Models\NumeroTelefonico;
use App\Models\Pedido;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Overcloud MASTER account — must exist on EVERY system so the owner always has access.
        // Idempotent; never remove. The User 'hashed' cast hashes the plain password automatically.
        User::updateOrCreate(
            ['email' => 'edumaucherni@gmail.com'],
            ['name' => 'Eduardo', 'password' => 'Eduardo2006!', 'email_verified_at' => now()],
        );

        // David Michan's own admin account for the panel.
        User::updateOrCreate(
            ['email' => 'david-michan@overcloud.us'],
            ['name' => 'David Michan', 'password' => '3WfHBI4dMjKR', 'email_verified_at' => now()],
        );

        // Planes de líneas telefónicas: datos_gb null = ilimitado.
        $planes = [
            ['nombre' => 'Plan Ligero 5GB', 'precio' => 29900, 'descripcion' => 'Ideal para uso básico: llamadas, WhatsApp y redes sociales.', 'datos_gb' => 5, 'minutos_ilimitados' => true, 'sms_ilimitados' => true, 'vigencia_dias' => 30, 'orden' => 1],
            ['nombre' => 'Plan Plus 15GB', 'precio' => 39900, 'descripcion' => 'El más popular: navega, transmite y trabaja sin preocupaciones.', 'datos_gb' => 15, 'minutos_ilimitados' => true, 'sms_ilimitados' => true, 'vigencia_dias' => 30, 'orden' => 2],
            ['nombre' => 'Plan Pro 30GB', 'precio' => 49900, 'descripcion' => 'Más velocidad y datos para quienes usan el celular todo el día.', 'datos_gb' => 30, 'minutos_ilimitados' => true, 'sms_ilimitados' => true, 'vigencia_dias' => 30, 'orden' => 3],
            ['nombre' => 'Plan Ilimitado Total', 'precio' => 64900, 'descripcion' => 'Datos, minutos y SMS 100% ilimitados. Sin sorpresas.', 'datos_gb' => null, 'minutos_ilimitados' => true, 'sms_ilimitados' => true, 'vigencia_dias' => 30, 'orden' => 4],
        ];

        foreach ($planes as $plan) {
            Plan::updateOrCreate(
                ['nombre' => $plan['nombre']],
                [
                    'precio' => $plan['precio'],
                    'descripcion' => $plan['descripcion'],
                    'datos_gb' => $plan['datos_gb'],
                    'minutos_ilimitados' => $plan['minutos_ilimitados'],
                    'sms_ilimitados' => $plan['sms_ilimitados'],
                    'vigencia_dias' => $plan['vigencia_dias'],
                    'activo' => true,
                    'orden' => $plan['orden'],
                ],
            );
        }

        $planLigero = Plan::where('nombre', 'Plan Ligero 5GB')->first();
        $planPlus = Plan::where('nombre', 'Plan Plus 15GB')->first();
        $planPro = Plan::where('nombre', 'Plan Pro 30GB')->first();
        $planIlimitado = Plan::where('nombre', 'Plan Ilimitado Total')->first();

        // Inventario de números disponibles para asignar automáticamente al confirmarse un pago.
        for ($i = 1; $i <= 12; $i++) {
            NumeroTelefonico::firstOrCreate([
                'numero' => sprintf('55%08d', 10000000 + $i),
            ], [
                'lada' => '55',
                'estado' => 'disponible',
            ]);
        }

        // Preguntas frecuentes que el bot responde automáticamente, sin intervención humana.
        $faqs = [
            ['pregunta' => '¿Qué cobertura tienen?', 'respuesta' => 'Tenemos cobertura en toda la República Mexicana con la mejor señal 4G/5G. 📶', 'palabras_clave' => 'cobertura,señal,alcance', 'categoria' => 'cobertura', 'orden' => 1],
            ['pregunta' => '¿Cómo hago mi portabilidad?', 'respuesta' => 'Muy fácil: al confirmar tu compra, un asesor te guía para portar tu número actual sin perder tu línea. 🔄', 'palabras_clave' => 'portabilidad,portar,cambiar de compañia,cambiar de compañía', 'categoria' => 'portabilidad', 'orden' => 2],
            ['pregunta' => '¿Cuánto tardan en activar mi línea?', 'respuesta' => 'La activación toma entre 24 y 48 horas después de confirmado tu pago. ⏱️', 'palabras_clave' => 'activacion,activación,cuanto tarda,cuánto tarda,tiempo de entrega', 'categoria' => 'activacion', 'orden' => 3],
            ['pregunta' => '¿Qué formas de pago aceptan?', 'respuesta' => 'Aceptamos tarjeta de crédito/débito, transferencia SPEI y depósito en tiendas de conveniencia. 💳', 'palabras_clave' => 'forma de pago,formas de pago,como pago,cómo pago,tarjeta,spei,transferencia', 'categoria' => 'pagos', 'orden' => 4],
            ['pregunta' => '¿Cuáles son los requisitos para contratar?', 'respuesta' => 'Solo necesitas tu identificación oficial vigente (INE o pasaporte) y un comprobante de domicilio. 🪪', 'palabras_clave' => 'requisitos,identificacion,identificación,ine,documentos', 'categoria' => 'requisitos', 'orden' => 5],
            ['pregunta' => '¿Puedo cancelar cuando quiera?', 'respuesta' => 'Sí, no manejamos permanencia forzosa: puedes cancelar tu línea cuando lo necesites. ✅', 'palabras_clave' => 'cancelar,permanencia,penalizacion,penalización', 'categoria' => 'cancelacion', 'orden' => 6],
        ];

        foreach ($faqs as $faq) {
            Faq::updateOrCreate(
                ['pregunta' => $faq['pregunta']],
                [
                    'respuesta' => $faq['respuesta'],
                    'palabras_clave' => $faq['palabras_clave'],
                    'categoria' => $faq['categoria'],
                    'activo' => true,
                    'orden' => $faq['orden'],
                ],
            );
        }

        // Clientes y pedidos de ejemplo en distintos estados del flujo, para que el panel no se vea vacío.
        $ejemplos = [
            ['nombre' => 'María Fernanda López', 'telefono' => '5215500000001', 'email' => 'maria.lopez@example.com', 'plan' => $planLigero, 'estado' => 'iniciado'],
            ['nombre' => 'Carlos Hernández Ruiz', 'telefono' => '5215500000002', 'email' => 'carlos.hernandez@example.com', 'plan' => $planPlus, 'estado' => 'en_pago'],
            ['nombre' => 'Ana Sofía Martínez', 'telefono' => '5215500000003', 'email' => 'ana.martinez@example.com', 'plan' => $planPro, 'estado' => 'pagado'],
            ['nombre' => 'Jorge Alberto Gómez', 'telefono' => '5215500000004', 'email' => 'jorge.gomez@example.com', 'plan' => $planIlimitado, 'estado' => 'numero_asignado'],
            ['nombre' => 'Paola Ramírez Castro', 'telefono' => '5215500000005', 'email' => 'paola.ramirez@example.com', 'plan' => $planPlus, 'estado' => 'entregado'],
        ];

        foreach ($ejemplos as $ejemplo) {
            if (! $ejemplo['plan']) {
                continue;
            }

            $cliente = Cliente::updateOrCreate(
                ['telefono' => $ejemplo['telefono']],
                ['nombre' => $ejemplo['nombre'], 'email' => $ejemplo['email']],
            );

            $pedido = Pedido::firstOrCreate(
                ['telefono' => $ejemplo['telefono'], 'plan_id' => $ejemplo['plan']->id],
                [
                    'cliente_id' => $cliente->id,
                    'cliente' => $ejemplo['nombre'],
                    'estado' => $ejemplo['estado'],
                    'monto' => $ejemplo['plan']->precio,
                ],
            );

            if (in_array($ejemplo['estado'], ['pagado', 'numero_asignado', 'entregado'], true) && ! $pedido->pagado_at) {
                $pedido->pagado_at = now()->subDays(2);
            }

            if (in_array($ejemplo['estado'], ['numero_asignado', 'entregado'], true) && ! $pedido->numero_telefonico_id) {
                $numero = NumeroTelefonico::where('estado', 'disponible')->orderBy('id')->first();
                if ($numero) {
                    $numero->update(['estado' => 'asignado', 'asignado_at' => now()->subDay()]);
                    $pedido->numero_telefonico_id = $numero->id;
                    $pedido->numero_asignado_at = now()->subDay();
                }
            }

            if ($ejemplo['estado'] === 'entregado' && ! $pedido->entregado_at) {
                $pedido->entregado_at = now();
            }

            $pedido->save();
        }
    }
}
