<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprar {{ $product['name'] }}</title>
    <style>
        body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; background-color: #f4f4f4; }
        .product-card { background-color: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-align: center; }
        .product-card img { max-width: 150px; margin-bottom: 15px; }
        .product-card h1 { margin-top: 0; font-size: 1.8em; }
        .product-card p { color: #555; }
        .price { font-size: 1.5em; font-weight: bold; color: #333; margin: 15px 0; }
        .buy-button { background-color: #6772e5; color: white; border: none; padding: 12px 25px; font-size: 1em; border-radius: 5px; cursor: pointer; transition: background-color 0.3s; }
        .buy-button:hover { background-color: #545ddb; }
        .error { color: red; margin-top: 15px; }
    </style>
</head>
<body>
    <div class="product-card">
        <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}">
        <h1>{{ $product['name'] }}</h1>
        <p>{{ $product['description'] }}</p>
        <div class="price">{{ number_format($product['price'], 2) }} {{ strtoupper($product['currency']) }}</div>

        @if (session('error'))
            <div class="error">{{ session('error') }}</div>
        @endif

        <form action="{{ route('checkout') }}" method="POST">
            @csrf
            {{-- Puedes añadir campos ocultos aquí si necesitas pasar más datos --}}
            {{-- <input type="hidden" name="product_id" value="123"> --}}
            <button type="submit" class="buy-button">Comprar Ahora</button>
        </form>
    </div>
</body>
</html>