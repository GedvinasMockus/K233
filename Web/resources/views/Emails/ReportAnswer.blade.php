<!DOCTYPE html>
<html>
    <head>
        <title>Atsakymas į pažeidimą</title>
    </head>
    <body>
        <h1>Sveiki jūsų pranešimas apie pažeidimą buvo atsakytas! Informacija, kuri buvo pateikta jūsų:</h1>
        <p><b>Aikštelė: </b>{{ $name }}</p>
        <p><b>Adresas: </b>{{ $address }}</p>
        <p><b>Laikas: </b>{{ $time }}</p>
        <p><b>Aprašymas: </b>{{ $description }}</p>
        <h2>Administratoriaus atsakymas:</h2>
        <p>{{ $answer }}</p>
        <p><b>Administratorius: </b>{{ $admin }} - {{ $adminEmail }}</p>
        <img src="{{ $message->embed(public_path('storage/' . $image)) }}" alt="img" style="display: block; border: 1px; max-width: 100%; height: auto" />
    </body>
</html>
