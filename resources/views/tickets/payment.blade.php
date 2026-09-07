<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement | TicketRapide Point G</title>
    <link rel="stylesheet" href="{{ asset('style.css') }}">
</head>
<body>
    <div class="app-container">
        <header class="navbar">
            <a href="{{ route('dashboard') }}" class="navbar-brand">TicketRapide Point G</a>
            <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-sm">Annuler</a>
        </header>

        <main class="main-content payment-page">
            <div class="payment-card glass-panel">
                <div class="payment-badge">Démonstration</div>
                <h1>Paiement du ticket</h1>
                <p class="payment-intro">Validez le paiement avant de recevoir votre ticket pour le service <strong>{{ $service->nom }}</strong>.</p>

                <div class="payment-amount">
                    <span>Montant à payer</span>
                    <strong>{{ number_format($amount, 0, ',', ' ') }} F CFA</strong>
                </div>

                <div class="orange-money-choice">
                    <span class="orange-money-logo">OM</span>
                    <div>
                        <strong>Orange Money</strong>
                        <span>Paiement mobile au Mali</span>
                    </div>
                    <span class="payment-selected">Sélectionné</span>
                </div>

                <div class="payment-notice">
                    Ceci est un paiement simulé. Aucun débit réel ne sera effectué.
                </div>

                <form action="{{ route('ticket.payment.confirm') }}" method="POST">
                    @csrf
                    <input type="hidden" name="payment_method" value="orange_money">
                    <label class="payment-check">
                        <input type="checkbox" name="demo_confirmation" value="1" required>
                        <span>Je confirme le paiement de 1 000 F CFA.</span>
                    </label>
                    <button type="submit" class="btn btn-primary payment-submit">Confirmer le paiement et recevoir mon ticket</button>
                </form>

                @if($errors->any())
                    <div class="alert-box alert-danger" style="margin-top: 1rem;">{{ $errors->first() }}</div>
                @endif
            </div>
        </main>
    </div>
</body>
</html>