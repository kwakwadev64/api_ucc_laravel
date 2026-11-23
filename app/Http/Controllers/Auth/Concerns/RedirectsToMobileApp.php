<?php

namespace App\Http\Controllers\Auth\Concerns;

trait RedirectsToMobileApp
{
    /** URL du deep link, définie par chaque contrôleur. */
    abstract protected function mobileRedirectUrl(): string;

    /**
     * Ramène l'utilisateur vers l'app mobile via le deep link.
     * Page HTML avec redirection automatique + bouton de secours,
     * plus fiable qu'un simple 302 vers un schéma personnalisé.
     */
    protected function backToApp(array $params)
    {
        $isError = isset($params['error']);

        $url = $this->mobileRedirectUrl() . '?' . http_build_query($params);

        $jsUrl = json_encode($url, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG
            | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
        $htmlUrl = e($url);
        $logoUrl = e(asset('images/fsi-hub-logo.png'));
        $delegationLogoUrl = e(asset('images/delegation_logo.jpeg'));

        $title   = $isError ? 'Connexion interrompue' : 'Connexion réussie';
        $message = $isError
            ? 'Retournez dans l’application pour réessayer.'
            : 'Retour vers FSI Hub…';
        $button  = $isError ? 'Retourner à l’application' : 'Ouvrir l’application';
        $spinner = $isError ? '' : '<div class="spinner" aria-hidden="true"></div>';

        $html = <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="referrer" content="no-referrer">
<meta name="robots" content="noindex">
<title>FSI Hub</title>
<style>
:root{
  --accent:#1d4ed8;
  --accent-text:#ffffff;
  --bg:#f3f5f9;
  --card:#ffffff;
  --text:#111827;
  --muted:#6b7280;
  --border:#e5e7eb;
}
@media (prefers-color-scheme: dark){
  :root{
    --bg:#0b0f17;
    --card:#151b27;
    --text:#f3f4f6;
    --muted:#9ca3af;
    --border:#263043;
  }
}
*{box-sizing:border-box}
html,body{height:100%;margin:0}
body{
  display:flex;align-items:center;justify-content:center;
  padding:24px;
  background:var(--bg);color:var(--text);
  font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;
  -webkit-font-smoothing:antialiased;
}
.card{
  width:100%;max-width:380px;
  padding:36px 28px 28px;
  text-align:center;
  background:var(--card);
  border:1px solid var(--border);
  border-radius:20px;
  box-shadow:0 10px 30px rgba(0,0,0,.06);
}
.logos{
  display:flex;
  align-items:center;
  justify-content:center;
  gap:18px;
  margin-bottom:20px;
}
.logo{
  display:block;
  width:88px;height:88px;
  margin:0;
  object-fit:contain;
}
.logo-divider{
  width:1px;
  height:60px;
  background:var(--border);
}
h1{margin:0 0 8px;font-size:20px;font-weight:700}
p{margin:0;font-size:15px;line-height:1.5;color:var(--muted)}
.spinner{
  width:26px;height:26px;
  margin:22px auto 0;
  border:3px solid var(--border);
  border-top-color:var(--accent);
  border-radius:50%;
  animation:spin .8s linear infinite;
}
@keyframes spin{to{transform:rotate(360deg)}}
.btn{
  display:block;
  margin-top:24px;
  padding:14px 20px;
  background:var(--accent);color:var(--accent-text);
  border-radius:12px;
  font-size:16px;font-weight:600;text-decoration:none;
}
.btn:active{opacity:.85}
.hint{margin-top:14px;font-size:13px}
</style>
</head>
<body>
<main class="card">
  <div class="logos">
    <img class="logo" src="{$logoUrl}" alt="FSI Hub" onerror="this.style.display='none'">
    <span class="logo-divider" aria-hidden="true"></span>
    <img class="logo" src="{$delegationLogoUrl}" alt="Délégation facultaire" onerror="this.style.display='none'">
  </div>
  <h1>{$title}</h1>
  <p>{$message}</p>
  {$spinner}
  <a class="btn" href="{$htmlUrl}">{$button}</a>
  <p class="hint">Si rien ne se passe, touchez le bouton ci-dessus.</p>
</main>
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
