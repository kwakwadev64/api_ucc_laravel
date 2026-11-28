
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialiser mon mot de passe - FSI Hub</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: #f4f6f9;
            font-family: Arial, sans-serif;
        }

        .container {
            width: 100%;
            max-width: 430px;
            padding: 35px;
            background: white;
            border-radius: 14px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.07);
        }

        .logo {
            display: block;
            width: 85px;
            margin: 0 auto 20px;
        }

        h1 {
            margin-bottom: 10px;
            text-align: center;
            font-size: 23px;
            color: #172554;
        }

        .description {
            margin-bottom: 25px;
            text-align: center;
            color: #64748b;
            font-size: 14px;
            line-height: 1.6;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: bold;
            color: #334155;
        }

        input {
            width: 100%;
            padding: 13px;
            margin-bottom: 18px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 15px;
        }

        input:focus {
            outline: none;
            border-color: #2563eb;
        }

        button {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 8px;
            background: #1d4ed8;
            color: white;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
        }

        button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        #message {
            display: none;
            margin-top: 18px;
            padding: 12px;
            border-radius: 8px;
            font-size: 14px;
            line-height: 1.5;
        }

        .success {
            display: block !important;
            color: #166534;
            background: #dcfce7;
        }

        .error {
            display: block !important;
            color: #991b1b;
            background: #fee2e2;
        }
    </style>
</head>

<body>
    <div class="container">

        <img
            src="{{ asset('images/fsi-hub-logo.png') }}"
            alt="FSI Hub"
            class="logo"
        >

        <h1>Nouveau mot de passe</h1>

        <p class="description">
            Choisissez un nouveau mot de passe pour retrouver
            l'accès à votre compte FSI Hub.
        </p>

        <form id="resetForm">
            <input type="hidden" id="token" value="{{ $token }}">
            <input type="hidden" id="email" value="{{ $email }}">

            <label for="password">Nouveau mot de passe</label>
            <input
                type="password"
                id="password"
                placeholder="Votre nouveau mot de passe"
                required
                minlength="8"
                autocomplete="new-password"
            >

            <label for="password_confirmation">Confirmer le mot de passe</label>
            <input
                type="password"
                id="password_confirmation"
                placeholder="Confirmez votre mot de passe"
                required
                minlength="8"
                autocomplete="new-password"
            >

            <button type="submit" id="submitButton">
                Réinitialiser mon mot de passe
            </button>
        </form>

        <div id="message"></div>
    </div>

    <script>
        const form = document.getElementById('resetForm');
        const button = document.getElementById('submitButton');
        const message = document.getElementById('message');

        form.addEventListener('submit', async function (event) {
            event.preventDefault();

            const password = document.getElementById('password').value;
            const confirmation = document.getElementById('password_confirmation').value;

            message.className = '';
            message.textContent = '';

            if (password !== confirmation) {
                message.textContent = 'Les deux mots de passe ne correspondent pas.';
                message.className = 'error';
                return;
            }

            button.disabled = true;
            button.textContent = 'Réinitialisation en cours...';

            try {
                const response = await fetch('/api/reset-password', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        token: document.getElementById('token').value,
                        email: document.getElementById('email').value,
                        password: password,
                        password_confirmation: confirmation
                    })
                });

                const data = await response.json();

                if (!response.ok) {
                    let errorMessage = data.message || 'Une erreur est survenue.';

                    if (data.errors) {
                        errorMessage = Object.values(data.errors)
                            .flat()
                            .join(' ');
                    } else if (data.error) {
                        errorMessage = data.error;
                    }

                    throw new Error(errorMessage);
                }

                form.style.display = 'none';

                message.textContent = data.message ||
                    'Votre mot de passe a été réinitialisé avec succès.';

                message.className = 'success';

            } catch (error) {
                message.textContent = error.message ||
                    'Impossible de contacter le serveur. Réessayez.';

                message.className = 'error';

            } finally {
                button.disabled = false;
                button.textContent = 'Réinitialiser mon mot de passe';
            }
        });
    </script>
    <script src="/js/favicon.js"></script>
</body>
</html>
