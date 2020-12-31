<html>
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @livewireStyles
</head>
<body>
    {{ $slot }}

    @livewireScripts
    @stack('scripts')
</body>
</html>
