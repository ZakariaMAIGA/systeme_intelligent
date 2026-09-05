<x-guest-layout>
    <x-auth-session-status class="guest-status-message" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="guest-field">
            <x-input-label for="email" value="Adresse e-mail" />
            <x-text-input id="email" class="guest-input" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="vous@hopital-pointg.ml" />
            <x-input-error :messages="$errors->get('email')" class="guest-error" />
        </div>

        <div class="guest-field">
            <x-input-label for="password" value="Mot de passe" />

            <x-text-input id="password" class="guest-input"
                            type="password"
                            name="password"
                            required autocomplete="current-password" placeholder="Saisissez votre mot de passe" />

            <x-input-error :messages="$errors->get('password')" class="guest-error" />
        </div>

        <div class="guest-options">
            <label for="remember_me" class="guest-checkbox">
                <input id="remember_me" type="checkbox" name="remember">
                <span>Se souvenir de moi</span>
            </label>

            @if (Route::has('password.request'))
                <a class="guest-forgot-link" href="{{ route('password.request') }}">
                    Mot de passe oublié ?
                </a>
            @endif
        </div>

        <button type="submit" class="guest-submit">
            Se connecter
            <span aria-hidden="true">→</span>
        </button>
    </form>
</x-guest-layout>
