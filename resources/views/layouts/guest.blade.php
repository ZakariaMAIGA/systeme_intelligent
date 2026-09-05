<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Hôpital du Point G | Connexion</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="guest-page">
        <div class="guest-shell">
            <aside class="guest-brand-panel">
                <a href="/" class="guest-brand" aria-label="Accueil Hôpital du Point G">
                    <span class="guest-brand-mark" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 12h-4l-3 8L9 4l-3 8H2" />
                        </svg>
                    </span>
                    <span>Hôpital du Point G</span>
                </a>

                <div class="guest-brand-copy">
                    <p class="guest-eyebrow">Espace professionnel</p>
                    <h1>Une attente mieux organisée, des soins plus sereins.</h1>
                    <p>Accédez à votre espace pour piloter les files d'attente et accompagner chaque patient avec précision.</p>
                </div>

                <div class="guest-status"><span></span> Service de gestion opérationnel</div>
            </aside>

            <main class="guest-form-area">
                <div class="guest-form-card">
                    <div class="guest-form-heading">
                        <p class="guest-eyebrow">Bienvenue</p>
                        <h2>Connexion à votre espace</h2>
                        <p>Utilisez vos identifiants professionnels pour continuer.</p>
                    </div>
                    {{ $slot }}
                </div>
                <p class="guest-footer">Hôpital du Point G <span aria-hidden="true">•</span> Gestion de files d'attente</p>
            </main>
        </div>
    </body>
</html>
