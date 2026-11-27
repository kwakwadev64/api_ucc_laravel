<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialisation du mot de passe - FSI Hub</title>
</head>

<body style="margin:0; padding:0; background-color:#f3f5f9; font-family:Arial, Helvetica, sans-serif; color:#263247;">

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#f3f5f9; padding:35px 12px;">
        <tr>
            <td align="center">

                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:600px; background-color:#ffffff; border-radius:12px; overflow:hidden;">

                    <!-- En-tête -->
                    <tr>
                        <td align="center" style="padding:30px 25px 20px;">

                            <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td align="center" valign="middle" style="padding:0 12px;">
                                       <img
                                            src="{{ asset('images/fsi-hub-logo.jpeg') }}"
                                            alt="FSI Hub"
                                            width="75"
                                            style="display:block; width:75px; height:auto; border:0; object-fit:contain;"
                                        >
                                    </td>

                                    <td align="center" valign="middle" style="padding:0 12px;">
                                        <img
                                                src="{{ asset('images/delegation_logo.jpeg') }}"
                                                alt="Délégation facultaire"
                                                width="75"
                                                style="display:block; width:75px; height:auto; border:0; object-fit:contain;"
                                            >
                                    </td>
                                </tr>
                            </table>

                            <h1 style="margin:20px 0 5px; font-size:25px; color:#173b70;">
                                FSI Hub
                            </h1>

                            <p style="margin:0; font-size:13px; color:#7b8798;">
                                Faculté des Sciences Informatiques
                            </p>

                        </td>
                    </tr>

                    <!-- Bandeau -->
                    <tr>
                        <td style="background-color:#173b70; padding:25px 30px; text-align:center;">
                            <h2 style="margin:0; color:#ffffff; font-size:21px; font-weight:600;">
                                Réinitialisation de votre mot de passe
                            </h2>
                        </td>
                    </tr>

                    <!-- Contenu -->
                    <tr>
                        <td style="padding:35px 30px 25px;">

                            <p style="margin:0 0 18px; font-size:15px; line-height:1.7;">
                                Bonjour <strong>{{ $user->first_name ?? 'Utilisateur' }}</strong>,
                            </p>

                            <p style="margin:0 0 18px; font-size:15px; line-height:1.7; color:#536174;">
                                Nous avons reçu une demande de réinitialisation du mot de passe associé à votre compte FSI Hub.
                            </p>

                            <p style="margin:0 0 25px; font-size:15px; line-height:1.7; color:#536174;">
                                Pour choisir un nouveau mot de passe, cliquez sur le bouton ci-dessous :
                            </p>

                            <!-- Bouton -->
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center" style="margin:0 auto 28px;">
                                <tr>
                                    <td align="center" bgcolor="#173b70" style="border-radius:7px;">
                                        <a
                                            href="{{ $url }}"
                                            style="display:inline-block; padding:15px 30px; color:#ffffff; text-decoration:none; font-size:15px; font-weight:bold; border-radius:7px;"
                                        >
                                            Réinitialiser mon mot de passe
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0 0 12px; font-size:14px; line-height:1.7; color:#536174;">
                                Ce lien est valable pendant <strong>60 minutes</strong>.
                            </p>

                            <p style="margin:0 0 12px; font-size:14px; line-height:1.7; color:#536174;">
                                Si le bouton ne fonctionne pas, copiez et collez ce lien dans votre navigateur ou ouvrez-le sur votre appareil :
                            </p>

                            <p style="margin:0 0 25px; font-size:12px; line-height:1.7; word-break:break-all;">
                                <a href="{{ $url }}" style="color:#2563a6; text-decoration:underline;">
                                    {{ $url }}
                                </a>
                            </p>

                            <p style="margin:0; font-size:14px; line-height:1.7; color:#536174;">
                                Si vous n'êtes pas à l'origine de cette demande, vous pouvez simplement ignorer cet email. Votre mot de passe actuel restera inchangé.
                            </p>

                        </td>
                    </tr>

                    <!-- Pied de page -->
                    <tr>
                        <td style="background-color:#f8fafc; padding:22px 25px; text-align:center; border-top:1px solid #e8edf3;">

                            <p style="margin:0 0 8px; font-size:13px; font-weight:bold; color:#173b70;">
                                FSI Hub
                            </p>

                            <p style="margin:0; font-size:12px; line-height:1.6; color:#8792a2;">
                                Faculté des Sciences Informatiques<br>
                                Université Catholique du Congo
                            </p>

                            <p style="margin:15px 0 0; font-size:11px; color:#a0a9b5;">
                                © {{ date('Y') }} FSI Hub. Tous droits réservés.
                            </p>

                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>
</html>
