<?php

namespace App\Http\Controllers\Auth\Concerns;

trait RedirectsToMobileApp
{
    /** URL du deep link, définie par chaque contrôleur. */
    abstract protected function mobileRedirectUrl(): string;

    /**
     * Ramène l'utilisateur vers l'app mobile via le deep link.
     */
    protected function backToApp(array $params)
    {
        $url = $this->mobileRedirectUrl() . '?' . http_build_query($params);

        $jsUrl = json_encode($url, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG
            | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
        $htmlUrl = e($url);

        $html = <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="referrer" content="no-referrer">
<title>FSI Hub</title>
<style>
body{font-family:sans-serif;text-align:center;padding:48px 24px}
a{display:inline-block;margin-top:24px;padding:14px 24px;background:#1877f2;color:#fff;border-radius:8px;text-decoration:none}
</style>
</head>
<body>
<p>Retour vers FSI Hub…</p>
<a href="{$htmlUrl}">Ouvrir l’application</a>
<script>window.location.replace({$jsUrl});</script>
</body>
</html>
HTML;

        return response($html, 200)
            ->header('Content-Type', 'text/html; charset=UTF-8')
            ->header('Cache-Control', 'no-store')
            ->header('Referrer-Policy', 'no-referrer');
    }
}
