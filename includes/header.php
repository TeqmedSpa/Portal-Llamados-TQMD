<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= htmlspecialchars($pageTitle ?? 'Portal de Llamados — TEQMED') ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'tq-blue':       '#00618e',
                        'tq-blue-dark':  '#004f73',
                        'tq-blue-light': '#e6f2f8',
                        'tq-green':      '#00755d',
                        'tq-green-dark': '#005c49',
                        'tq-green-light':'#e6f4f1',
                    },
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <link rel="stylesheet" href="/assets/css/custom.css">
</head>
<body class="font-sans antialiased bg-gray-50 min-h-screen text-gray-900">

<header class="bg-white border-b border-gray-200 px-6 py-4">
    <div class="max-w-2xl mx-auto flex items-center gap-3">
        <img src="/assets/images/logo.svg" alt="TEQMED" class="h-8 w-auto" onerror="this.style.display='none'">
        <div class="flex items-baseline gap-2">
            <span class="text-tq-blue font-bold text-xl tracking-wide">TEQMED</span>
            <span class="text-gray-400 text-sm">Portal de Llamados</span>
        </div>
    </div>
</header>

<main class="max-w-2xl mx-auto px-4 py-10">
