<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>¡Pago Exitoso!</title>
     <style>
        body { font-family: sans-serif; text-align: center; padding-top: 50px; background-color: #e8fadf;}
        h1 { color: #3c763d; }
        p { color: #555;}
        a { color: #31708f; text-decoration: none; }
        .details { margin-top: 20px; font-size: 0.9em; color: #777; }
    </style>
</head>
<body>
    <h1>¡Gracias por tu compra!</h1>
    <p>Tu pago ha sido procesado exitosamente.</p>

    @isset($session)
        <div class="details">
            ID de Sesión: {{ $session->id }} <br>
            @if($session->customer_details && $session->customer_details->email)
                Email: {{ $session->customer_details->email }}
            @endif
             {{-- Muestra más detalles si es necesario, pero sé cuidadoso con la información sensible --}}
        </div>
    @endisset

    <p><a href="{{ route('product.show') }}">Volver a la tienda</a></p>
</body>
</html>