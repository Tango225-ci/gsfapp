<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialisation de mot de passe – GSF</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #f3f4f6; font-family: 'Helvetica Neue', Arial, sans-serif; color: #374151; }
        .wrapper { max-width: 580px; margin: 40px auto; }
        .card { background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
        .bar { height: 5px; background: #b91c1c; }
        .header { padding: 32px 40px 24px; text-align: center; border-bottom: 1px solid #f3f4f6; }
        .header img { height: 56px; margin-bottom: 16px; }
        .header h1 { font-size: 20px; font-weight: 700; color: #111827; }
        .body { padding: 32px 40px; }
        .greeting { font-size: 15px; color: #374151; margin-bottom: 20px; }
        .message { font-size: 14px; color: #6b7280; line-height: 1.7; margin-bottom: 28px; }
        .btn-wrap { text-align: center; margin: 28px 0; }
        .btn { display: inline-block; background: #b91c1c; color: #ffffff !important; text-decoration: none; padding: 13px 32px; border-radius: 8px; font-size: 15px; font-weight: 600; letter-spacing: 0.2px; }
        .btn:hover { background: #991b1b; }
        .divider { border: none; border-top: 1px solid #f3f4f6; margin: 24px 0; }
        .fallback { font-size: 12px; color: #9ca3af; line-height: 1.6; }
        .fallback a { color: #b91c1c; word-break: break-all; }
        .warning { background: #fef9ec; border: 1px solid #fde68a; border-radius: 8px; padding: 12px 16px; font-size: 13px; color: #92400e; margin-top: 20px; }
        .footer { padding: 20px 40px; text-align: center; background: #f9fafb; font-size: 12px; color: #9ca3af; border-top: 1px solid #f3f4f6; }
        .footer strong { color: #b91c1c; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="card">
        <div class="bar"></div>

        <div class="header">
            <img src="{{ asset('images/logo/gsf-logo.png') }}" alt="GSF Logo">
            <h1>Réinitialisation de mot de passe</h1>
        </div>

        <div class="body">
            <p class="greeting">Bonjour <strong>{{ $userName }}</strong>,</p>

            <p class="message">
                Vous avez demandé la réinitialisation de votre mot de passe pour votre compte GSF.<br>
                Cliquez sur le bouton ci-dessous pour choisir un nouveau mot de passe.
            </p>

            <div class="btn-wrap">
                <a href="{{ $resetUrl }}" class="btn">Réinitialiser mon mot de passe</a>
            </div>

            <hr class="divider">

            <p class="fallback">
                Si le bouton ne fonctionne pas, copiez et collez ce lien dans votre navigateur :<br>
                <a href="{{ $resetUrl }}">{{ $resetUrl }}</a>
            </p>

            <div class="warning">
                ⏱ Ce lien expirera dans <strong>{{ $expiresIn }} minutes</strong>.<br>
                Si vous n'avez pas demandé cette réinitialisation, ignorez cet email — votre mot de passe restera inchangé.
            </div>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} <strong>GSF</strong> — Cet email a été envoyé automatiquement, merci de ne pas y répondre.
        </div>

        <div class="bar"></div>
    </div>
</div>
</body>
</html>
