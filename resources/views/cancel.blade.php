<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pago Cancelado</title>
     <style>
        body { font-family: sans-serif; text-align: center; padding-top: 50px; background-color: #fcf8e3;}
        h1 { color: #8a6d3b; }
        p { color: #555;}
        a { color: #31708f; text-decoration: none; }
         .error { color: red; margin-top: 15px; }
    </style>
</head>
<body>
    <h1>Pago Cancelado</h1>
    <p>Parece que has cancelado el proceso de pago.</p>

     @if (session('error'))
        <div class="error">{{ session('error') }}</div>
    @endif

    <p>Puedes <a href="{{ route('product.show') }}">volver a la tienda</a> e intentarlo de nuevo si lo deseas.</p>
</body>
</html>