<!-- Funcion del archivo: Abre el layout publico, carga SEO, estilos y navegacion. -->
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($title ?? 'Viajes AJT — Paquetes turísticos y asesoría de visas en Perú') ?></title>
  <meta name="description" content="<?= $description ?? 'Reserva tu próximo viaje con Viajes AJT. Paquetes nacionales e internacionales, asesoría especializada en visas y atención personalizada.' ?>">
    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= htmlspecialchars($ogTitle ?? $title ?? 'Viajes AJT — Paquetes turísticos y asesoría de visas en Perú') ?>">
    <meta property="og:description" content="<?= htmlspecialchars($ogDescription ?? $description ?? 'Reserva tu próximo viaje con Viajes AJT.') ?>">
    <meta property="og:url" content="<?= htmlspecialchars((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ($_SERVER['REQUEST_URI'] ?? '/')) ?>">
    <meta property="og:image" content="<?= htmlspecialchars($ogImage ?? BASE_URL . '/assets/img/logo/AJT.png') ?>">
    <meta property="og:site_name" content="Viajes AJT">
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($ogTitle ?? $title ?? 'Viajes AJT') ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($ogDescription ?? $description ?? '') ?>">
    <meta name="twitter:image" content="<?= htmlspecialchars($ogImage ?? BASE_URL . '/assets/img/logo/AJT.png') ?>">
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
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/app.css?v=20260724-hide-native-eye">
  <?php if (($pageKey ?? 'home') === 'home'): ?>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/home.css?v=20260626-refine">
  <?php endif; ?>
  <?php if (isset($extraStyles) && is_array($extraStyles)): ?>
    <?php foreach ($extraStyles as $style): ?>
      <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/<?= htmlspecialchars($style) ?>?v=20260618-password-toggle">
    <?php endforeach; ?>
  <?php endif; ?>
  <style>
    .skip-link {
      position: absolute;
      top: -1000px;
      left: -1000px;
      width: 1px;
      height: 1px;
      overflow: hidden;
      clip: rect(1px, 1px, 1px, 1px);
      white-space: nowrap;
      z-index: 9999;
      background: #1b2c8a;
      color: #fff;
      padding: 0.75rem 1.5rem;
      border-radius: 0 0 8px 8px;
      font-weight: 600;
      text-decoration: none;
    }
    .skip-link:focus {
      top: 0;
      left: 0;
      width: auto;
      height: auto;
      overflow: visible;
      clip: auto;
      white-space: normal;
    }
    .search-results { display: none; }
    .search-results.is-visible { display: block; }
  </style>
  <script nonce="<?= htmlspecialchars(\App\Helper\SecurityHeaders::getNonce(), ENT_QUOTES, 'UTF-8') ?>">var BASE_URL = '<?= BASE_URL ?>';</script>
  <script src="<?= BASE_URL ?>/assets/js/jquery-3.7.1.min.js"></script>
  <script src="<?= BASE_URL ?>/assets/js/main.js?v=20260618-password-toggle" defer></script>
  <script nonce="<?= htmlspecialchars(\App\Helper\SecurityHeaders::getNonce(), ENT_QUOTES, 'UTF-8') ?>">
    document.addEventListener('DOMContentLoaded', function () {
      <?php if (session_status() === PHP_SESSION_NONE) { session_start(); } ?>
      <?php if (isset($_SESSION['toast']) && is_array($_SESSION['toast'])): ?>
        var toastData = <?= json_encode($_SESSION['toast'], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
        if (toastData && typeof window.toast === 'function') {
          window.toast({
            type: toastData.type || 'warning',
            title: toastData.title || 'Aviso',
            text: toastData.text || toastData.message || ''
          });
        }
        <?php unset($_SESSION['toast']); ?>
      <?php endif; ?>
    });
  </script>
    <script type="application/ld+json" nonce="<?= htmlspecialchars(\App\Helper\SecurityHeaders::getNonce(), ENT_QUOTES, 'UTF-8') ?>">
    {
      "@context": "https://schema.org",
      "@type": "TravelAgency",
      "name": "Viajes AJT",
      "url": "<?= BASE_URL ?>",
      "logo": "<?= BASE_URL ?>/assets/img/logo/AJT.png",
      "description": "Paquetes turísticos y asesoría de visas en Perú",
      "areaServed": { "@type": "Country", "name": "Perú" },
      "serviceType": ["Paquetes turísticos", "Asesoría de visas"]
    }
    </script>
</head>
<body data-page="<?= $pageKey ?? 'home' ?>" data-paths-home="<?= BASE_URL ?>/" data-paths-assets="<?= BASE_URL ?>/assets/">
<!-- Saltar al contenido: enlace visible solo al recibir foco (accesibilidad teclado) -->
<a href="#main-content" class="skip-link">Saltar al contenido principal</a>

<header class="nav" id="siteNav">
  <div class="nav__inner">
    <a href="<?= BASE_URL ?>/" class="nav__brand" aria-label="Viajes AJT — Inicio">
      <span class="nav__brand-mark" aria-hidden="true">AJT</span>
      Viajes AJT
    </a>

    <nav aria-label="Navegación principal">
      <ul class="nav__menu" id="navMenu">
        <li><a class="nav__link" data-nav="home"     href="<?= BASE_URL ?>/">Inicio</a></li>
        <li><a class="nav__link" data-nav="catalog"  href="<?= BASE_URL ?>/catalog">Catálogo</a></li>
        <li><a class="nav__link" data-nav="visas"    href="<?= BASE_URL ?>/visas">Visas</a></li>
        <li><a class="nav__link" data-nav="contacto" href="<?= BASE_URL ?>/contacto">Contacto</a></li>
      </ul>
    </nav>

    <div class="nav__actions">
      <button type="button" class="theme-toggle" data-theme-toggle aria-label="Cambiar a modo oscuro" aria-pressed="false">
        <span class="theme-toggle__icon" aria-hidden="true">
          <svg class="theme-toggle__icon-sun" width="24" height="24" fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill-rule="evenodd" clip-rule="evenodd" d="M12.006 9.002a3 3 0 1 0 0 6.001 3 3 0 0 0 0-6.001Zm-4.5 3a4.5 4.5 0 1 1 9.001 0 4.5 4.5 0 0 1-9.001 0Zm4.5-9.976a.75.75 0 0 1 .75.75v1.5a.75.75 0 0 1-1.5 0v-1.5a.75.75 0 0 1 .75-.75Zm0 16.963a.75.75 0 0 1 .75.75v1.5a.75.75 0 0 1-1.5 0v-1.5a.75.75 0 0 1 .75-.75ZM4.86 4.82a.75.75 0 0 1 1.059.055l1.35 1.5A.75.75 0 0 1 6.154 7.38l-1.35-1.5a.75.75 0 0 1 .055-1.06Zm11.877 11.81a.75.75 0 0 1 1.06.056l1.35 1.5a.75.75 0 1 1-1.116 1.004l-1.35-1.5a.75.75 0 0 1 .056-1.06ZM2.014 12.003a.75.75 0 0 1 .75-.75h1.801a.75.75 0 0 1 0 1.5h-1.8a.75.75 0 0 1-.75-.75Zm16.638 0a.75.75 0 0 1 .75-.75h1.8a.75.75 0 0 1 0 1.5h-1.8a.75.75 0 0 1-.75-.75ZM7.23 16.566a.75.75 0 0 1 .055 1.059l-1.35 1.5a.75.75 0 1 1-1.115-1.003l1.35-1.5a.75.75 0 0 1 1.06-.056ZM19.135 4.82a.75.75 0 0 1 .056 1.059l-1.35 1.5a.75.75 0 1 1-1.115-1.003l1.35-1.5a.75.75 0 0 1 1.06-.056Z" fill="currentColor"></path></svg>
          <svg class="theme-toggle__icon-moon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M11.82 2.382a.75.75 0 0 1-.05.814 6.46 6.46 0 0 0 9.034 9.034.75.75 0 0 1 1.193.672 10.02 10.02 0 1 1-10.9-10.899.75.75 0 0 1 .723.379zm-2.12 1.4A8.52 8.52 0 1 0 20.217 14.3 7.96 7.96 0 0 1 9.7 3.783z" clip-rule="evenodd"></path></svg>
        </span>
        <span class="theme-toggle__text" data-theme-label>Tema</span>
      </button>
      <?php if (isset($_SESSION['usuario'])): ?>
        <?php 
          $nombreCorto = explode(' ', $_SESSION['usuario']['nombres'])[0];
          $panelUrl = (int)$_SESSION['usuario']['id_rol'] === 2
            ? BASE_URL . '/admin/dashboard'
            : BASE_URL . '/profile/mi-perfil';
        ?>
        <a href="<?= htmlspecialchars($panelUrl) ?>" class="btn btn--ghost btn--sm" id="navLoginBtn"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle;flex-shrink:0"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg> <?= htmlspecialchars($nombreCorto) ?></a>
        <form action="<?= BASE_URL ?>/auth/logout" method="POST" style="display:inline">
          <?= \App\Helper\Csrf::insertInput() ?>
          <button type="submit" class="btn btn--accent btn--sm">Cerrar sesión</button>
        </form>
      <?php else: ?>
        <a href="<?= BASE_URL ?>/auth/login" class="btn btn--ghost btn--sm" id="navLoginBtn">Ingresar</a>
        <a href="<?= BASE_URL ?>/auth/registro" class="btn btn--accent btn--sm">Registrarse</a>
      <?php endif; ?>
      <button type="button" class="nav__toggle" id="navToggle" aria-label="Abrir menú" aria-expanded="false" aria-controls="navMenu">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
      </button>
    </div>
  </div>
</header>

<script nonce="<?= htmlspecialchars(\App\Helper\SecurityHeaders::getNonce(), ENT_QUOTES, 'UTF-8') ?>">
(function(){
  var nav = document.getElementById('siteNav');
  var btn = document.getElementById('navToggle');
  if (btn) {
    btn.addEventListener('click', function(){
      var open = nav.classList.toggle('is-open');
      btn.setAttribute('aria-expanded', open ? 'true':'false');
      document.body.classList.toggle('nav-open', open);
    });
  }
  var page = document.body.getAttribute('data-page');
  if (page) {
    var active = nav.querySelector('.nav__link[data-nav="'+page+'"]');
    if (active) active.classList.add('is-active');
  }
})();
</script>
