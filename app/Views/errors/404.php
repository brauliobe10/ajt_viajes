<!-- Funcion del archivo: Renderiza la pantalla de recurso no encontrado. -->
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>404 — Página no encontrada · Viajes AJT</title>
  <link rel="icon" type="image/png" href="<?= BASE_URL ?>/assets/img/logo/AJT.png">
  <script nonce="<?= htmlspecialchars(\App\Helper\SecurityHeaders::getNonce(), ENT_QUOTES, 'UTF-8') ?>">
    // Aplica el tema guardado antes de cargar CSS para evitar parpadeos al recargar.
    (function(){try{var t=localStorage.getItem('ajt_theme');if(!t&&window.matchMedia&&matchMedia('(prefers-color-scheme: dark)').matches)t='dark';document.documentElement.setAttribute('data-theme',t||'light');document.documentElement.style.colorScheme=t||'light';}catch(e){}})();
  </script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/tokens.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/base.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/components.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/layout.css">
  <script src="<?= BASE_URL ?>/assets/js/main.js" defer></script>
  <style>
    .err-page {
      min-height: 80vh;
      display: flex;
      align-items: center;
      justify-content: center;
      text-align: center;
      padding: 40px 20px;
    }
    .err-card {
      max-width: 520px;
      margin: 0 auto;
    }
    .err-code {
      font-size: clamp(4rem, 12vw, 7rem);
      font-weight: var(--fw-black);
      color: var(--c-primary-200);
      line-height: 1;
      margin: 0;
    }
    .err-title {
      font-size: var(--fs-24);
      font-weight: var(--fw-bold);
      color: var(--c-ink-800);
      margin: 8px 0 12px;
    }
    .err-msg {
      color: var(--c-ink-500);
      font-size: var(--fs-15);
      line-height: var(--lh-base);
      margin-bottom: 28px;
    }
  </style>
</head>
<body data-page="404">
  <button type="button" class="theme-toggle theme-toggle--floating" data-theme-toggle aria-label="Cambiar a modo oscuro" aria-pressed="false">
    <span class="theme-toggle__icon" aria-hidden="true">
      <svg class="theme-toggle__icon-sun" width="24" height="24" fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill-rule="evenodd" clip-rule="evenodd" d="M12.006 9.002a3 3 0 1 0 0 6.001 3 3 0 0 0 0-6.001Zm-4.5 3a4.5 4.5 0 1 1 9.001 0 4.5 4.5 0 0 1-9.001 0Zm4.5-9.976a.75.75 0 0 1 .75.75v1.5a.75.75 0 0 1-1.5 0v-1.5a.75.75 0 0 1 .75-.75Zm0 16.963a.75.75 0 0 1 .75.75v1.5a.75.75 0 0 1-1.5 0v-1.5a.75.75 0 0 1 .75-.75ZM4.86 4.82a.75.75 0 0 1 1.059.055l1.35 1.5A.75.75 0 0 1 6.154 7.38l-1.35-1.5a.75.75 0 0 1 .055-1.06Zm11.877 11.81a.75.75 0 0 1 1.06.056l1.35 1.5a.75.75 0 1 1-1.116 1.004l-1.35-1.5a.75.75 0 0 1 .056-1.06ZM2.014 12.003a.75.75 0 0 1 .75-.75h1.801a.75.75 0 0 1 0 1.5h-1.8a.75.75 0 0 1-.75-.75Zm16.638 0a.75.75 0 0 1 .75-.75h1.8a.75.75 0 0 1 0 1.5h-1.8a.75.75 0 0 1-.75-.75ZM7.23 16.566a.75.75 0 0 1 .055 1.059l-1.35 1.5a.75.75 0 1 1-1.115-1.003l1.35-1.5a.75.75 0 0 1 1.06-.056ZM19.135 4.82a.75.75 0 0 1 .056 1.059l-1.35 1.5a.75.75 0 1 1-1.115-1.003l1.35-1.5a.75.75 0 0 1 1.06-.056Z" fill="currentColor"></path></svg>
      <svg class="theme-toggle__icon-moon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M11.82 2.382a.75.75 0 0 1-.05.814 6.46 6.46 0 0 0 9.034 9.034.75.75 0 0 1 1.193.672 10.02 10.02 0 1 1-10.9-10.899.75.75 0 0 1 .723.379zm-2.12 1.4A8.52 8.52 0 1 0 20.217 14.3 7.96 7.96 0 0 1 9.7 3.783z" clip-rule="evenodd"></path></svg>
    </span>
    <span class="theme-toggle__text" data-theme-label>Tema</span>
  </button>
  <main id="main-content" class="err-page">
    <div class="err-card">
      <div class="err-code">404</div>
      <h1 class="err-title">Página no encontrada</h1>
      <p class="err-msg">La sección que buscas no existe, fue movida o la URL no es correcta. Verificá el enlace o volvé al inicio para seguir explorando nuestros paquetes.</p>
      <a href="<?= BASE_URL ?>/" class="btn btn--primary btn--lg">Volver al inicio</a>
    </div>
  </main>
</body>
</html>
