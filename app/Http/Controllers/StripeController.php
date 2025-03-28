<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Exception; // Importa la clase Exception

class StripeController extends Controller
{
    /**
     * Muestra la página del producto.
     */
    public function showProduct()
    {
        // Datos del producto (pueden venir de la base de datos en una app real)
        $product = [
            'name' => 'Reserva Podideportiva Badajoz',
            'description' => 'Reservar pista de tenis Nº1 de 11:30 a 12:30',
            'price' => 10.00, // Precio en tu moneda (ej. EUR, USD)
            'image' => 'https://picsum.photos/600/400', // URL de una imagen
            'currency' => 'eur', // Código de moneda ISO (eur, usd, mxn, etc.)
        ];

        return view('product', compact('product'));
    }

    /**
     * Inicia la sesión de Stripe Checkout.
     */
    public function checkout(Request $request)
    {
        // Valida la solicitud si es necesario (ej. cantidad, etc.)

        // Configura la clave secreta de Stripe
        Stripe::setApiKey(config('services.stripe.secret'));

        // Datos del producto (podrías pasarlos desde el form o recuperarlos)
        $productName = 'Reservar pista de tenis Nº1 de 11:30 a 12:30';
        $productPrice = 1000; // ¡IMPORTANTE! Precio en la unidad mínima (céntimos)
        $currency = 'eur';

        try {
            // Crea la sesión de Checkout
            $session = Session::create([
                'payment_method_types' => ['card'], // Métodos de pago aceptados
                'line_items' => [[
                    'price_data' => [
                        'currency' => $currency,
                        'product_data' => [
                            'name' => $productName,
                            // 'description' => 'Descripción opcional aquí',
                        ],
                        'unit_amount' => $productPrice, // Precio en céntimos
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment', // Modo 'payment' para pagos únicos
                'success_url' => route('success') . '?session_id={CHECKOUT_SESSION_ID}', // URL de éxito
                'cancel_url' => route('cancel'), // URL de cancelación
                // 'customer_email' => 'correo@ejemplo.com', // Opcional: Pre-rellenar email
            ]);

            // Redirige al usuario a la página de pago de Stripe
            return redirect($session->url);

        } catch (Exception $e) {
            // Maneja el error (log, mostrar mensaje al usuario, etc.)
            // dd($e->getMessage()); // Para depuración
             return back()->withError('Hubo un problema al iniciar el pago: ' . $e->getMessage());
        }
    }

    /**
     * Muestra la página de éxito.
     */
    public function success(Request $request)
    {
        // Configura la clave secreta
        Stripe::setApiKey(config('services.stripe.secret'));

        $sessionId = $request->query('session_id');

        try {
            // Recupera la sesión de Checkout para verificar el estado (opcional pero recomendado)
            $session = Session::retrieve($sessionId);

            // Aquí podrías:
            // 1. Verificar si $session->payment_status == 'paid'
            // 2. Obtener detalles del cliente: $session->customer_details->email
            // 3. Marcar el pedido como pagado en tu base de datos (si tuvieras una)

            // Simplemente mostramos una vista de éxito
            return view('success', ['session' => $session]);

        } catch (Exception $e) {
            // Manejar error si la sesión no se encuentra o hay otro problema
            return redirect()->route('cancel')->withError('No se pudo verificar el estado del pago.');
        }
    }

    /**
     * Muestra la página de cancelación.
     */
    public function cancel()
    {
        return view('cancel');
    }

    /**
     * Manejador de Webhooks (Opcional pero crucial para producción).
     * Esta función recibe notificaciones asíncronas de Stripe sobre eventos.
     */
    // public function handleWebhook(Request $request)
    // {
    //     Stripe::setApiKey(config('services.stripe.secret'));
    //     $webhookSecret = config('services.stripe.webhook.secret');
    //     $payload = $request->getContent();
    //     $sigHeader = $request->server('HTTP_STRIPE_SIGNATURE');
    //     $event = null;

    //     try {
    //         $event = \Stripe\Webhook::constructEvent(
    //             $payload, $sigHeader, $webhookSecret
    //         );
    //     } catch(\UnexpectedValueException $e) {
    //         // Payload inválido
    //         return response('Payload inválido', 400);
    //     } catch(\Stripe\Exception\SignatureVerificationException $e) {
    //         // Firma inválida
    //         return response('Firma inválida', 400);
    //     }

    //     // Manejar el evento
    //     switch ($event->type) {
    //         case 'checkout.session.completed':
    //             $session = $event->data->object;
    //             // Lógica cuando el pago se completa con éxito
    //             // - Marcar pedido como pagado en DB
    //             // - Enviar email de confirmación
    //             // ¡Importante! No confíes solo en la redirección a 'success'.
    //             // El webhook es la forma segura de confirmar el pago.
    //             Log::info('Pago completado para sesión: ' . $session->id);
    //             break;
    //         // ... manejar otros tipos de eventos (payment_failed, etc.)
    //         default:
    //             // Evento no manejado
    //     }

    //     return response('Webhook recibido', 200); // ¡Siempre responde 200 OK a Stripe!
    // }
}