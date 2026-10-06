<?php

// Funcion del archivo: Renderiza la navegacion lateral del panel administrativo.
$userName = isset($_SESSION['usuario']) ? $_SESSION['usuario']['nombres'] . ' ' . $_SESSION['usuario']['apellidos'] : 'Administrador';
$userRole = 'Administrador';
$initials = '';
if (isset($_SESSION['usuario'])) {
    $initials = strtoupper(substr($_SESSION['usuario']['nombres'], 0, 1) . substr($_SESSION['usuario']['apellidos'], 0, 1));
} else {
    $initials = 'AD';
}
?>
<aside class="asidebar" id="aSidebar">
  <div class="asidebar__brand">
    <div class="asidebar__mark">AJT</div>
    <div>
      <div class="asidebar__brand-name">Viajes AJT</div>
      <div class="asidebar__brand-sub">Admin Panel</div>
    </div>
    <button type="button" class="theme-toggle theme-toggle--icon" data-theme-toggle aria-label="Cambiar a modo oscuro" aria-pressed="false">
      <span class="theme-toggle__icon" aria-hidden="true">
        <svg class="theme-toggle__icon-sun" width="24" height="24" fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill-rule="evenodd" clip-rule="evenodd" d="M12.006 9.002a3 3 0 1 0 0 6.001 3 3 0 0 0 0-6.001Zm-4.5 3a4.5 4.5 0 1 1 9.001 0 4.5 4.5 0 0 1-9.001 0Zm4.5-9.976a.75.75 0 0 1 .75.75v1.5a.75.75 0 0 1-1.5 0v-1.5a.75.75 0 0 1 .75-.75Zm0 16.963a.75.75 0 0 1 .75.75v1.5a.75.75 0 0 1-1.5 0v-1.5a.75.75 0 0 1 .75-.75ZM4.86 4.82a.75.75 0 0 1 1.059.055l1.35 1.5A.75.75 0 0 1 6.154 7.38l-1.35-1.5a.75.75 0 0 1 .055-1.06Zm11.877 11.81a.75.75 0 0 1 1.06.056l1.35 1.5a.75.75 0 1 1-1.116 1.004l-1.35-1.5a.75.75 0 0 1 .056-1.06ZM2.014 12.003a.75.75 0 0 1 .75-.75h1.801a.75.75 0 0 1 0 1.5h-1.8a.75.75 0 0 1-.75-.75Zm16.638 0a.75.75 0 0 1 .75-.75h1.8a.75.75 0 0 1 0 1.5h-1.8a.75.75 0 0 1-.75-.75ZM7.23 16.566a.75.75 0 0 1 .055 1.059l-1.35 1.5a.75.75 0 1 1-1.115-1.003l1.35-1.5a.75.75 0 0 1 1.06-.056ZM19.135 4.82a.75.75 0 0 1 .056 1.059l-1.35 1.5a.75.75 0 1 1-1.115-1.003l1.35-1.5a.75.75 0 0 1 1.06-.056Z" fill="currentColor"></path></svg>
        <svg class="theme-toggle__icon-moon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M11.82 2.382a.75.75 0 0 1-.05.814 6.46 6.46 0 0 0 9.034 9.034.75.75 0 0 1 1.193.672 10.02 10.02 0 1 1-10.9-10.899.75.75 0 0 1 .723.379zm-2.12 1.4A8.52 8.52 0 1 0 20.217 14.3 7.96 7.96 0 0 1 9.7 3.783z" clip-rule="evenodd"></path></svg>
      </span>
    </button>
  </div>

  <nav class="asidebar__nav">
    <div class="asidebar__label">Principal</div>
    <a class="asidebar__item" data-akey="dashboard" href="<?= BASE_URL ?>/admin/dashboard">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <rect x="3" y="3" width="7" height="9" rx="1"/>
        <rect x="14" y="3" width="7" height="5" rx="1"/>
        <rect x="14" y="12" width="7" height="9" rx="1"/>
        <rect x="3" y="16" width="7" height="5" rx="1"/>
      </svg> 
      Dashboard
    </a>

    <div class="asidebar__label">Gestión</div>
    <a class="asidebar__item" data-akey="paquetes" href="<?= BASE_URL ?>/admin/paquetes">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M20 7h-3a2 2 0 0 1-2-2V4a2 2 0 0 0-2-2h-2a2 2 0 0 0-2 2v1a2 2 0 0 1-2 2H4a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2Z"/>
      </svg> 
      Paquetes
    </a>
    <a class="asidebar__item" data-akey="ventas" href="<?= BASE_URL ?>/admin/ventas">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M12 2v20"/>
        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
      </svg> 
      Ventas & Reportes
    </a>
    <a class="asidebar__item" data-akey="reservas" href="<?= BASE_URL ?>/admin/reservas">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <rect x="3" y="4" width="18" height="18" rx="2"/>
        <path d="M16 2v4M8 2v4M3 10h18"/>
      </svg> 
      Reservas
    </a>
    <a class="asidebar__item" data-akey="visas" href="<?= BASE_URL ?>/admin/visas">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/>
      </svg> 
      Trámites visas
    </a>
    <a class="asidebar__item" data-akey="reclamaciones" href="<?= BASE_URL ?>/admin/reclamaciones">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
        <path d="M16 13H8"/>
        <path d="M16 17H8"/>
        <path d="M10 9H8"/>
      </svg> 
      Reclamaciones
    </a>
    <a class="asidebar__item" data-akey="usuarios" href="<?= BASE_URL ?>/admin/usuarios">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
        <circle cx="9" cy="7" r="4"/>
      </svg> 
      Usuarios
    </a>

    <div class="asidebar__label">Sistema</div>
    <a class="asidebar__item" data-akey="ajustes" href="<?= BASE_URL ?>/admin/ajustes">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="3"/>
        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33"/>
      </svg> 
      Ajustes
    </a>
  </nav>

  <div class="asidebar__foot">
    <div class="avatar"><?= htmlspecialchars($initials) ?></div>
    <div>
      <div class="name"><?= htmlspecialchars($userName) ?></div>
      <div class="role"><?= htmlspecialchars($userRole) ?></div>
    </div>
      <form action="<?= BASE_URL ?>/auth/logout" method="POST" style="margin:0">
      <?= \App\Helper\Csrf::insertInput() ?>
      <button type="submit" class="asidebar__logout" title="Cerrar sesión" style="border:0;cursor:pointer">Cerrar sesión</button>
    </form>
  </div>
</aside>

<script nonce="<?= htmlspecialchars(\App\Helper\SecurityHeaders::getNonce(), ENT_QUOTES, 'UTF-8') ?>">
(function(){
  var page = document.body.getAttribute('data-akey');
  if (page){
    var el = document.querySelector('.asidebar__item[data-akey="'+page+'"]');
    if (el) el.classList.add('is-active');
  }
  function bindSidebar(){
    var toggle = document.getElementById('aMenuToggle');
    var sidebar = document.getElementById('aSidebar');
    if (!toggle || !sidebar) return false;
    var backdrop = null;
    function openSidebar(){
      sidebar.classList.add('is-open');
      if (!backdrop){
        backdrop = document.createElement('div');
        backdrop.className = 'admin-backdrop';
        document.body.appendChild(backdrop);
        backdrop.addEventListener('click', closeSidebar);
      }
    }
    function closeSidebar(){
      sidebar.classList.remove('is-open');
      if (backdrop){
        backdrop.remove();
        backdrop = null;
      }
    }
    toggle.addEventListener('click', function(e){
      e.stopPropagation();
      if (sidebar.classList.contains('is-open')){
        closeSidebar();
      } else {
        openSidebar();
      }
    });
    return true;
  }
  if (!bindSidebar()){
    document.addEventListener('DOMContentLoaded', bindSidebar);
  }
})();
</script>
