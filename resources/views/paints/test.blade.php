php<!DOCTYPE html>
<html>
<head>
    <title>Paint Test</title>
</head>
<body>
    <h1>Paint Page is Working!</h1>
    <p>Paints count: {{ $paints->count() }}</p>
    
    @foreach($paints as $paint)
        <div>
            <h3>{{ $paint->product_name }}</h3>
            <p>Price: £{{ $paint->price_gbp }}</p>
        </div>
    @endforeach
</body>
</html>