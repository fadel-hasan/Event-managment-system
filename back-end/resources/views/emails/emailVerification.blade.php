<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>إستعادة كلمة السر</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Styles -->
    @vite(['resources/scss/style.scss'])
</head>
<body class="mt-6 text-center min-h-screen bg-pink">
    <h1 class="text-3xl">Email Verification Mail</h1>
    <p class="text-lg mt-16">
        لرجاء تأكيد بريدك الإلكتروني بالضغط هنا:
    </p>
    <a href="{{ route('user.verify', $token) }}" class="button-orange mt-8 inline-block">Verify Email</a>
</body>
</html>
