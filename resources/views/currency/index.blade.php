<!-- resources/views/currency/show.blade.php -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Exchange Rate</title>
</head>
<body>
    <h1>Exchange Rate</h1>
    
    @if (isset($rate))
        <p>USD to INR: {{ $rate }}</p>
    @else
        <p>{{ $error }}</p>
    @endif
</body>
</html>
