<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ settings('site_name', 'Himashva') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

        <!-- Scripts -->
        <script src="https://cdn.tailwindcss.com"></script>
        <style>
            .font-display { font-family: 'Cormorant Garamond', serif; }
        </style>
    </head>
    <body class="font-sans text-[#2C2018] antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-[#FDFBF7]">
            <div>
                <a href="/" class="font-display text-3xl font-semibold text-[#3D2B1F]">{{ settings('site_name', 'Himashva') }}</a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg border border-[#EAE2D6]">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
