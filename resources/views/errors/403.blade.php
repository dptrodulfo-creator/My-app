<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Not cleared | RunSheet</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600&amp;family=Barlow+Condensed:wght@600;700&amp;display=swap">
    <link rel="stylesheet" href="{{ asset('css/runsheet.css') }}">
</head>
<body class="lane">
    <div class="barrier" aria-hidden="true"></div>

    {{-- Feature 3: the 403 page that anyone without the right role sees. --}}
    <main class="lane-box">
        <h1>403</h1>
        <p class="lane-msg">Hold up. Your account isn’t cleared for this page.</p>
        <p class="muted">This page belongs to a different role than yours. If you think that’s a mistake, ask the dispatch desk.</p>
        <a class="btn" href="{{ url('/home') }}">Back to my page</a>
    </main>

    <div class="barrier" aria-hidden="true"></div>
</body>
</html>
