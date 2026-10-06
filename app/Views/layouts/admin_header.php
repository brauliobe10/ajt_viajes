<!-- Funcion del archivo: Abre el layout administrativo, carga estilos y sidebar. -->
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($title ?? 'Administración — Viajes AJT') ?></title>
  <link rel="icon" type="image/png" href="<?= BASE_URL ?>/assets/img/logo/AJT.png">
  <script nonce="<?= htmlspecialchars(\App\Helper\SecurityHeaders::getNonce(), ENT_QUOTES, 'UTF-8') ?>">
    // Aplica el tema guardado antes de cargar CSS para evitar parpadeos al recargar.
    (function(){try{var t=localStorage.getItem('ajt_theme');if(!t&&window.matchMedia&&matchMedia('(prefers-color-scheme: dark)').matches)t='dark';document.documentElement.setAttribute('data-theme',t||'light');document.documentElement.style.colorScheme=t||'light';}catch(e){}})();
  </script>
  <link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/tokens.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/base.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/components.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/app.css?v=20260618-sidebar-sticky">
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
</head>
<body data-akey="<?= htmlspecialchars($pageKey ?? 'dashboard') ?>" data-paths-home="<?= BASE_URL ?>/" data-paths-assets="<?= BASE_URL ?>/assets/">
<a href="#main-content" class="skip-link">Saltar al contenido principal</a>
<div class="admin-shell">
  <?php require_once dirname(__DIR__) . '/layouts/admin_sidebar.php'; ?>
