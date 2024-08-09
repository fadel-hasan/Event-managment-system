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
    <body class="font-[Cairo] h-screen w-screen flex justify-center items-center flex-col">
        <h1 class="text-xl">الرجاء الضغط على الزر التالي:</h1>
        <a href="{{ route("home") }}/auth/reset-password/{{$token}}" class="button-blue my-4">تغير كلمة السر</a>
    </body>
</html>
