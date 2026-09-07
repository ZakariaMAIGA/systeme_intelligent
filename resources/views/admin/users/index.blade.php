<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des utilisateurs | TicketRapide Point G</title>
    <link rel="stylesheet" href="{{ asset('style.css') }}">
</head>
<body>
    <div class="app-container">
        <header class="navbar">
            <a href="{{ route('dashboard') }}" class="navbar-brand">TicketRapide Point G <span>• Gestion des utilisateurs</span></a>
            <div class="navbar-actions">
                <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-sm">Retour au dashboard</a>
                <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                    @csrf
                    <button type="submit" class="btn btn-secondary btn-sm">Se déconnecter</button>
                </form>
            </div>
        </header>

        <main class="main-content">
            @if(session('notification'))
                <div class="alert-box alert-{{ session('notification.type') === 'success' ? 'success' : 'info' }}" style="margin-bottom: 1.5rem;">
                    {{ session('notification.text') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert-box alert-danger" style="margin-bottom: 1.5rem; display: block;">
                    <strong>Veuillez corriger les erreurs suivantes :</strong>
                    <ul style="margin: 0.5rem 0 0 1.25rem;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="dashboard-header">
                <h1>Gestion des utilisateurs</h1>
                <p>Créez les comptes de l'hôpital et attribuez à chacun le niveau d'accès adapté.</p>
            </div>

            <div class="glass-panel" style="margin-bottom: 1.5rem;">
                <h2 style="margin-bottom: 1.25rem;">Ajouter un utilisateur</h2>
                <form action="{{ route('admin.users.store') }}" method="POST">
                    @csrf
                    <div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label" for="name">Nom complet</label>
                            <input class="form-input" id="name" name="name" value="{{ old('name') }}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="email">Adresse e-mail</label>
                            <input class="form-input" id="email" type="email" name="email" value="{{ old('email') }}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="password">Mot de passe</label>
                            <input class="form-input" id="password" type="password" name="password" minlength="8" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="password_confirmation">Confirmer le mot de passe</label>
                            <input class="form-input" id="password_confirmation" type="password" name="password_confirmation" minlength="8" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="role">Rôle</label>
                            <select class="form-select" id="role" name="role" required>
                                @include('admin.users.role-options', ['selectedRole' => old('role', 'patient')])
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary" style="margin-top: 0.5rem;">Créer le compte</button>
                </form>
            </div>

            <div class="glass-panel">
                <h2 style="margin-bottom: 1.25rem;">Comptes existants ({{ $users->count() }})</h2>
                <div class="table-container">
                    <table class="custom-table">
                        <thead>
                            <tr><th>Nom</th><th>E-mail</th><th>Rôle</th><th>Modifier</th><th>Supprimer</th></tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                                <tr>
                                    <td><strong>{{ $user->name }}</strong></td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $user->role)) }}</td>
                                    <td>
                                        <form action="{{ route('admin.users.update', $user) }}" method="POST" style="display: grid; grid-template-columns: minmax(100px, 1fr) minmax(100px, 1fr) auto; gap: 0.4rem; min-width: 420px;">
                                            @csrf
                                            @method('PUT')
                                            <input class="form-input" name="name" value="{{ $user->name }}" required aria-label="Nom de {{ $user->name }}">
                                            <select class="form-select" name="role" aria-label="Rôle de {{ $user->name }}">
                                                @include('admin.users.role-options', ['selectedRole' => $user->role])
                                            </select>
                                            <input type="hidden" name="email" value="{{ $user->email }}">
                                            <input type="hidden" name="password" value="">
                                            <input type="hidden" name="password_confirmation" value="">
                                            <button type="submit" class="btn btn-primary btn-sm">Enregistrer</button>
                                        </form>
                                    </td>
                                    <td>
                                        @if($user->is(auth()->user()))
                                            <span style="color: var(--text-muted);">Compte actif</span>
                                        @else
                                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Supprimer ce compte ?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-secondary btn-sm" style="color: var(--danger-text);">Supprimer</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
