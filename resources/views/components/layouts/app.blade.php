<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Remi Digital</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
</head>
<body class="bg-gray-900 text-white font-sans antialiased">
    
    {{ $slot }}

    @livewireScripts
</body>
</html>