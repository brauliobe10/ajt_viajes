<?php

// Funcion del archivo: Renderiza el panel del cliente con reservas, visas, favoritos y cuenta.
  $nombreCompleto = htmlspecialchars($user['nombres'] . ' ' . $user['apellidos']);
  $iniciales = strtoupper(substr($user['nombres'], 0, 1) . substr($user['apellidos'], 0, 1));
?>

<div class="profile">
  <!-- SIDEBAR DE CLIENTE -->
  <aside class="psidebar">
    <div class="psidebar__hd">
      <div class="psidebar__avatar" id="pAvatar"><?= $iniciales ?></div>
      <p class="psidebar__name" id="pName"><?= $nombreCompleto ?></p>
      <p class="psidebar__mail" id="pMail"><?= htmlspecialchars($user['email']) ?></p>
    </div>
    <nav class="psidebar__nav">
      <a href="#viajes" class="is-active"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg> Mis viajes</a>
      <a href="#tramites"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg> Trámites de visa</a>
      <a href="#favoritos"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg> Favoritos</a>
      <a href="#cuenta"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg> Cuenta</a>
      <form action="<?= BASE_URL ?>/auth/logout" method="POST" style="margin:0">
        <?= \App\Helper\Csrf::insertInput() ?>
        <button type="submit" style="color:var(--c-danger-500);background:none;border:0;padding:0;display:flex;align-items:center;gap:10px;cursor:pointer;font:inherit">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg> Cerrar sesión
        </button>
      </form>
    </nav>
  </aside>

  <!-- CONTENIDO DEL PERFIL -->
  <section style="flex:1">
    <?php if (isset($_SESSION['success'])): ?>
      <div class="alert alert--success">
        <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
      </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
      <div class="alert alert--error">
        <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
      </div>
    <?php endif; ?>

    <!-- KPIs -->
    <div class="kpi-grid" style="margin-bottom:24px">
      <div class="kpi">
        <div class="kpi__head"><span class="kpi__label">Viajes contratados</span><div class="kpi__icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg></div></div>
        <div class="kpi__value"><?= count($reservas) ?></div>
        <span class="kpi__delta kpi__delta--up">en el sistema</span>
      </div>
      <div class="kpi">
        <div class="kpi__head"><span class="kpi__label">Métodos de pago</span><div class="kpi__icon">🌍</div></div>
        <div class="kpi__value">3</div>
        <span class="kpi__delta">activos</span>
      </div>
      <div class="kpi">
        <div class="kpi__head"><span class="kpi__label">Soporte</span><div class="kpi__icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div></div>
        <div class="kpi__value">24/7</div>
        <span class="kpi__delta kpi__delta--up">WhatsApp activo</span>
      </div>
      <div class="kpi">
        <div class="kpi__head"><span class="kpi__label">Trámites Visa</span><div class="kpi__icon">📄</div></div>
        <div class="kpi__value"><?= count($solicitudesVisa) ?></div>
        <span class="kpi__delta">registrados</span>
      </div>
    </div>

    <!-- MIS VIAJES -->
    <div class="section-title"><h2 id="viajes" style="font-size:var(--fs-22)">Próximos viajes</h2></div>
    
    <div style="display:grid;gap:14px;margin-bottom:32px">
      <?php if (!empty($reservas)): ?>
        <?php foreach ($reservas as $res): ?>
          <?php 
            $moneda = 'S/';
            $montoTotal = ($res['precio_unitario'] * $res['cantidad_pasajeros']) - $res['descuento'];
            // Calcular IGV (18%) sobre la base imponible para mostrar el total real
            $igvMonto = round($montoTotal * 0.18, 2);
            $montoTotalConIGV = round($montoTotal + $igvMonto, 2);
          ?>
          <article class="trip-card">
            <div class="trip-card__img">
              <img src="<?= htmlspecialchars($res['imagen_url'] ?? BASE_URL . '/assets/img/package-placeholder.svg') ?>" alt="<?= htmlspecialchars($res['paquete_nombre']) ?>">
            </div>
            <div class="trip-card__body">
              <h3><?= htmlspecialchars($res['paquete_nombre']) ?></h3>
              <div class="trip-card__meta">
                <span>📅 Viaje: <?= date('d M Y', strtotime($res['fecha_viaje'])) ?></span>
                <span><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle;flex-shrink:0"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg> <?= (int)$res['cantidad_pasajeros'] ?> pasajeros</span>
                <span>🎫 <?= htmlspecialchars($res['codigo_reserva']) ?></span>
              </div>
              
              <?php if ($res['estado'] === 'pagado'): ?>
                <span class="status-pill s-active">Confirmado</span>
              <?php elseif ($res['estado'] === 'pendiente'): ?>
                <span class="status-pill s-pending">Pendiente de validación</span>
              <?php else: ?>
                <span class="status-pill s-error">Cancelado</span>
              <?php endif; ?>
            </div>
            <div class="trip-card__actions">
              <a class="btn btn--primary btn--sm" href="<?= BASE_URL ?>/profile/comprobante/<?= urlencode($res['codigo_reserva']) ?>" target="_blank" rel="noopener">Ver comprobante</a>
              <button class="btn btn--ghost btn--sm js-trip-details" 
                      data-title="<?= htmlspecialchars($res['paquete_nombre']) ?>"
                      data-img="<?= htmlspecialchars($res['imagen_url'] ?? '') ?>"
                      data-code="<?= htmlspecialchars($res['codigo_reserva']) ?>"
                      data-status="<?= $res['estado'] === 'pagado' ? 'Confirmado' : 'Pago pendiente' ?>"
                      data-dates="<?= date('d M Y', strtotime($res['fecha_viaje'])) ?>"
                      data-pax="<?= (int)$res['cantidad_pasajeros'] ?> viajeros"
                      data-total="<?= $moneda ?> <?= number_format($montoTotalConIGV, 2, '.', ',') ?>"
                      data-voucher-url="<?= BASE_URL ?>/profile/comprobante/<?= urlencode($res['codigo_reserva']) ?>">
                Detalles
              </button>
            </div>
          </article>
        <?php endforeach; ?>
      <?php else: ?>
        <p style="color: var(--c-ink-500)">No tienes reservas activas todavía. ¡Explora nuestro catálogo y agenda tu primer destino!</p>
      <?php endif; ?>
    </div>

    <!-- TRÁMITES DE VISA -->
    <div class="section-title"><h2 id="tramites" style="font-size:var(--fs-22)">Trámites de visa</h2></div>
    <div class="table-wrap" style="margin-bottom:32px">
      <table class="table">
        <thead><tr><th>Código</th><th>Trámite</th><th>País</th><th>Estado</th><th>Última actualización</th><th></th></tr></thead>
        <tbody>
          <?php if (!empty($solicitudesVisa)): ?>
            <?php foreach ($solicitudesVisa as $sv): ?>
              <?php 
                $statusClass = 's-pending';
                $statusLabel = 'Recibida';
                if ($sv['estado'] === 'aprobada') {
                    $statusClass = 's-active';
                    $statusLabel = 'Aprobada';
                } elseif ($sv['estado'] === 'rechazada') {
                    $statusClass = 's-cancel';
                    $statusLabel = 'Rechazada';
                }
              ?>
              <tr>
                <td><strong><?= htmlspecialchars($sv['codigo_solicitud']) ?></strong></td>
                <td><strong><?= htmlspecialchars($sv['tipo_visa']) ?></strong></td>
                <td><?= htmlspecialchars($sv['pais_nombre']) ?></td>
                <td><span class="status-pill <?= $statusClass ?>"><?= $statusLabel ?></span></td>
                <td><?= date('d M Y', strtotime($sv['created_at'])) ?></td>
                <td class="actions"><a class="btn btn--ghost btn--sm" href="<?= BASE_URL ?>/visas">Ver info</a></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="6" style="text-align: center; color: var(--c-ink-500)">No tienes trámites de visa registrados. <a href="<?= BASE_URL ?>/visas" style="color:var(--c-primary-700); font-weight:600;">Solicita una asesoría aquí</a>.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- MIS FAVORITOS -->
    <div class="section-title" style="margin-top:32px"><h2 id="favoritos" style="font-size:var(--fs-22)">Mis favoritos</h2></div>
    <div id="favList" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:16px;margin-bottom:32px">
      <p style="color: var(--c-ink-500); grid-column: 1 / -1;" id="noFavsText">No tienes paquetes favoritos guardados.</p>
    </div>

    <!-- CONFIGURACIÓN DE CUENTA -->
    <div class="section-title" style="margin-top:32px"><h2 id="cuenta" style="font-size:var(--fs-22)">Mi cuenta</h2></div>
    <div class="panel" style="margin-bottom:32px">
      <form action="<?= BASE_URL ?>/profile/guardar-cuenta" method="POST" id="accForm" style="display:grid;gap:14px">
        <?= App\Helper\Csrf::insertInput() ?>
        <div class="co__grid-2">
          <div class="field">
            <label>Nombres *</label>
            <input class="input" name="nombres" required value="<?= htmlspecialchars($user['nombres'] ?? '') ?>"
                   pattern="[\p{L}\s]+" title="Solo letras y espacios" minlength="2" maxlength="100">
          </div>
          <div class="field">
            <label>Apellidos *</label>
            <input class="input" name="apellidos" required value="<?= htmlspecialchars($user['apellidos'] ?? '') ?>"
                   pattern="[\p{L}\s]+" title="Solo letras y espacios" minlength="2" maxlength="100">
          </div>
        </div>
        <div class="co__grid-2">
          <div class="field">
            <label>Correo electrónico (No editable)</label>
            <input class="input" type="email" readonly value="<?= htmlspecialchars($user['email'] ?? '') ?>">
          </div>
          <div class="field">
            <label>DNI / Pasaporte</label>
            <input class="input" name="dni_pasaporte" value="<?= htmlspecialchars($user['dni_pasaporte'] ?? '') ?>"
                   minlength="6" maxlength="20" title="Entre 6 y 20 caracteres">
          </div>
        </div>
        <div class="co__grid-2">
          <div class="field">
            <label>Teléfono / WhatsApp</label>
            <input class="input" name="telefono" value="<?= htmlspecialchars($user['telefono'] ?? '') ?>"
                   pattern="[0-9]{7,15}" maxlength="15" inputmode="numeric"
                   title="Solo números, entre 7 y 15 dígitos">
          </div>
        </div>
        <div>
          <button type="submit" class="btn btn--primary">Guardar cambios</button>
        </div>
      </form>

      <!-- CAMBIO DE CONTRASEÑA -->
      <hr style="margin:24px 0;border:0;border-top:1px solid var(--c-ink-200)">
      <h3 style="font-size:var(--fs-18);margin:0 0 14px">Cambiar contraseña</h3>
      <form action="<?= BASE_URL ?>/profile/cambiar-password" method="POST" style="display:grid;gap:14px;max-width:400px">
        <?= App\Helper\Csrf::insertInput() ?>
        <div class="field">
          <label>Contraseña actual *</label>
          <input class="input" type="password" name="password_actual" required minlength="6">
        </div>
        <div class="field">
          <label>Nueva contraseña *</label>
          <input class="input" type="password" name="password_nueva" required minlength="6">
        </div>
        <div class="field">
          <label>Confirmar nueva contraseña *</label>
          <input class="input" type="password" name="password_confirm" required minlength="6">
        </div>
        <div>
          <button type="submit" class="btn btn--primary">Actualizar contraseña</button>
        </div>
      </form>
    </div>
  </section>
</div>

<!-- Modal detalles de viaje e interactividad -->
<div id="tripModal" class="trip-modal" hidden>
  <div class="trip-modal__backdrop" data-close></div>
  <div class="trip-modal__panel" role="dialog" aria-modal="true">
    <button class="trip-modal__x" data-close aria-label="Cerrar">×</button>
    <div id="tripModalBody"></div>
  </div>
</div>

<style>
  /* Modal de perfil con superficies temáticas para reservas y pagos. */
  .trip-modal{position:fixed;inset:0;z-index:100;display:grid;place-items:center;padding:20px}
  .trip-modal[hidden]{display:none}
  .trip-modal__backdrop{position:absolute;inset:0;background:var(--c-overlay);backdrop-filter:blur(4px)}
  .trip-modal__panel{position:relative;background:var(--c-surface);border-radius:var(--r-lg);max-width:680px;width:100%;max-height:90vh;overflow:auto;box-shadow:var(--sh-3);animation:fadeIn .25s var(--ease-out);border:1px solid var(--c-border)}
  .trip-modal__x{position:absolute;top:14px;right:14px;width:36px;height:36px;border-radius:50%;border:0;background:var(--c-surface-muted);color:var(--c-ink-900);font-size:22px;cursor:pointer;z-index:2}
  .trip-modal__hero{aspect-ratio:16/8;overflow:hidden;border-radius:var(--r-lg) var(--r-lg) 0 0}
  .trip-modal__hero img{width:100%;height:100%;object-fit:cover}
  .trip-modal__cnt{padding:24px 28px 28px}
  .trip-modal__cnt h2{margin:0 0 4px;font-size:var(--fs-22)}
  .trip-modal__grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin:18px 0}
  .trip-modal__grid div{background:var(--c-surface-muted);border-radius:var(--r-md);padding:12px 14px}
  .trip-modal__grid small{display:block;color:var(--c-ink-500);font-size:var(--fs-12);text-transform:uppercase;letter-spacing:.06em}
  .trip-modal__grid strong{font-size:var(--fs-15);color:var(--c-ink-900)}
  .trip-modal h4{margin:18px 0 8px;font-size:var(--fs-15)}
  .trip-modal ul{margin:0;padding-left:20px;color:var(--c-ink-700);font-size:var(--fs-14)}
</style>

<script nonce="<?= htmlspecialchars(\App\Helper\SecurityHeaders::getNonce(), ENT_QUOTES, 'UTF-8') ?>">
document.addEventListener('DOMContentLoaded', () => {
  const profileNavLinks = document.querySelectorAll('.psidebar__nav a[href^="#"]');

  // Funcion: Activa la seccion seleccionada del perfil.
  function setActiveProfileNav(hash) {
    const activeHash = hash || '#viajes';
    profileNavLinks.forEach(link => {
      link.classList.toggle('is-active', link.getAttribute('href') === activeHash);
    });
  }

  setActiveProfileNav(window.location.hash);

  profileNavLinks.forEach(link => {
    link.addEventListener('click', () => {
      setActiveProfileNav(link.getAttribute('href'));
    });
  });

  window.addEventListener('hashchange', () => {
    setActiveProfileNav(window.location.hash);
  });

  // Modal de Detalles
  const tripModal = document.getElementById('tripModal');
  const tripModalBody = document.getElementById('tripModalBody');

  // Funcion: Abre el modal con detalles de una reserva.
  function openDetails(btn) {
    const data = btn.dataset;
    const isConfirm = data.status === 'Confirmado';
  const fallbackImg = '<?= BASE_URL ?>/assets/img/package-placeholder.svg';

    // Build DOM safely — never use innerHTML with user-controlled data
    tripModalBody.textContent = ''; // clear

    const hero = document.createElement('div');
    hero.className = 'trip-modal__hero';
    const img = document.createElement('img');
    img.src = data.img || fallbackImg;
    img.alt = '';
    hero.appendChild(img);
    tripModalBody.appendChild(hero);

    const cnt = document.createElement('div');
    cnt.className = 'trip-modal__cnt';

    const statusPill = document.createElement('span');
    statusPill.className = 'status-pill ' + (isConfirm ? 's-active' : 's-pending');
    statusPill.textContent = data.status;
    cnt.appendChild(statusPill);

    const h2 = document.createElement('h2');
    h2.style.marginTop = '10px';
    h2.textContent = data.title;
    cnt.appendChild(h2);

    const codeP = document.createElement('p');
    codeP.style.cssText = 'color:var(--c-ink-500);margin:0';
    codeP.appendChild(document.createTextNode('Código de reserva: '));
    const strong = document.createElement('strong');
    strong.textContent = data.code;
    codeP.appendChild(strong);
    cnt.appendChild(codeP);

    const grid = document.createElement('div');
    grid.className = 'trip-modal__grid';

    const datesEl = document.createElement('div');
    const datesSm = document.createElement('small');
    datesSm.textContent = 'Fechas';
    const datesStr = document.createElement('strong');
    datesStr.textContent = data.dates;
    datesEl.appendChild(datesSm);
    datesEl.appendChild(datesStr);
    grid.appendChild(datesEl);

    const paxEl = document.createElement('div');
    const paxSm = document.createElement('small');
    paxSm.textContent = 'Pasajeros';
    const paxStr = document.createElement('strong');
    paxStr.textContent = data.pax;
    paxEl.appendChild(paxSm);
    paxEl.appendChild(paxStr);
    grid.appendChild(paxEl);

    const hotelEl = document.createElement('div');
    const hotelSm = document.createElement('small');
    hotelSm.textContent = 'Hotel';
    const hotelStr = document.createElement('strong');
    hotelStr.textContent = 'Hotel 4\u2605 Incluido';
    hotelEl.appendChild(hotelSm);
    hotelEl.appendChild(hotelStr);
    grid.appendChild(hotelEl);

    const vueloEl = document.createElement('div');
    const vueloSm = document.createElement('small');
    vueloSm.textContent = 'Vuelo';
    const vueloStr = document.createElement('strong');
    vueloStr.textContent = 'Vuelo de Línea Comercial';
    vueloEl.appendChild(vueloSm);
    vueloEl.appendChild(vueloStr);
    grid.appendChild(vueloEl);

    cnt.appendChild(grid);

    const h4 = document.createElement('h4');
    h4.textContent = 'Qué incluye';
    cnt.appendChild(h4);

    const ul = document.createElement('ul');
    ['Alojamiento seleccionado', 'Desayunos e itinerarios guiados', 'Traslados aeropuerto-hotel-aeropuerto', 'Entradas y tren de excursión'].forEach(function(item) {
      const li = document.createElement('li');
      li.textContent = item;
      ul.appendChild(li);
    });
    cnt.appendChild(ul);

    const footer = document.createElement('div');
    footer.style.cssText = 'display:flex;justify-content:space-between;align-items:center;margin-top:22px;padding-top:18px;border-top:1px dashed var(--c-ink-200)';

    const totalWrap = document.createElement('div');
    const totalSm = document.createElement('small');
    totalSm.style.color = 'var(--c-ink-500)';
    totalSm.textContent = 'Total';
    const totalVal = document.createElement('div');
    totalVal.style.cssText = 'font-size:var(--fs-22);font-weight:800;color:var(--c-primary-700)';
    totalVal.textContent = data.total;
    totalWrap.appendChild(totalSm);
    totalWrap.appendChild(totalVal);
    footer.appendChild(totalWrap);

    const voucherBtn = document.createElement('a');
    voucherBtn.className = 'btn btn--primary';
    voucherBtn.href = data.voucherUrl;
    voucherBtn.target = '_blank';
    voucherBtn.rel = 'noopener';
    voucherBtn.textContent = 'Ver comprobante';
    footer.appendChild(voucherBtn);

    cnt.appendChild(footer);
    tripModalBody.appendChild(cnt);

    tripModal.hidden = false;
    document.body.style.overflow = 'hidden';
  }

  document.querySelectorAll('.js-trip-details').forEach(b => {
    b.addEventListener('click', () => openDetails(b));
  });

  // Cerrar modales
  document.querySelectorAll('.trip-modal').forEach(m => {
    m.addEventListener('click', e => {
      if (e.target.hasAttribute('data-close') || e.target.classList.contains('trip-modal__x')) {
        m.hidden = true;
        document.body.style.overflow = '';
      }
    });
  });

  // Renderizar Favoritos desde LocalStorage
  let favs = [];
  try {
    favs = JSON.parse(localStorage.getItem('ajt_favs') || '[]');
  } catch(e){}

  // Sanitización de teléfono en formulario de cuenta: solo números, max 15 dígitos
  const accPhoneInput = document.querySelector('#accForm input[name="telefono"]');
  if (accPhoneInput) {
    accPhoneInput.addEventListener('input', () => {
      accPhoneInput.value = accPhoneInput.value.replace(/[^0-9]/g, '').slice(0, 15);
    });
  }

  const favList = document.getElementById('favList');
  const noFavsText = document.getElementById('noFavsText');

  if (favs.length > 0) {
    if (noFavsText) noFavsText.style.display = 'none';
  }
});
</script>

<!-- Inyectar todos los paquetes para la sección de favoritos -->
<?php
  try {
      $todosP = \App\Models\Paquete::all();
      $avail = array_filter($todosP, function($x){ return (int)$x['disponible'] === 1; });
      $jsPackages = [];
      foreach ($avail as $p) {
          $moneda = 'S/';
          $jsPackages[] = [
              'slug' => $p['slug'],
              'nombre' => $p['nombre'],
              'precio' => $moneda . ' ' . number_format($p['precio_base'], 0),
              'imagen' => $p['imagen_url'] ?? (BASE_URL . '/assets/img/package-placeholder.svg')
          ];
      }
  } catch(Exception $e) {
      $jsPackages = [];
  }
?>

<script nonce="<?= htmlspecialchars(\App\Helper\SecurityHeaders::getNonce(), ENT_QUOTES, 'UTF-8') ?>">
(function(){
  const allPackages = <?= json_encode($jsPackages, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
  let favs = [];
  try { favs = JSON.parse(localStorage.getItem('ajt_favs') || '[]'); } catch(e){}

  const favList = document.getElementById('favList');
  if (favList && favs.length > 0) {
    const matched = allPackages.filter(p => favs.includes(p.slug));
    if (matched.length > 0) {
      document.getElementById('noFavsText').style.display = 'none';
      matched.forEach(p => {
        const art = document.createElement('article');
        art.className = 'trip-card';
        art.style.gridTemplateColumns = '1fr';
        art.style.display = 'block';

        // Build DOM safely — never use innerHTML with user-controlled data
        const imgWrap = document.createElement('div');
        imgWrap.className = 'trip-card__img';
        imgWrap.style.cssText = 'aspect-ratio:16/10;border-radius:var(--r-md);overflow:hidden;margin-bottom:12px';
        const imgEl = document.createElement('img');
        imgEl.src = p.imagen;
        imgEl.alt = p.nombre;
        imgEl.style.cssText = 'width:100%;height:100%;object-fit:cover';
        imgEl.onerror = function() { this.onerror = null; if (window.AJTFallbackImage) window.AJTFallbackImage(this); };
        imgWrap.appendChild(imgEl);
        art.appendChild(imgWrap);

        const bodyWrap = document.createElement('div');
        bodyWrap.style.cssText = 'padding:0 14px 14px';

        const h3f = document.createElement('h3');
        h3f.style.cssText = 'font-size:var(--fs-16);margin:0 0 4px';
        h3f.textContent = p.nombre;
        bodyWrap.appendChild(h3f);

        const pf = document.createElement('p');
        pf.style.cssText = 'color:var(--c-ink-500);font-size:var(--fs-13);margin:0 0 10px';
        pf.textContent = 'Desde ' + p.precio;
        bodyWrap.appendChild(pf);

        const link = document.createElement('a');
        link.className = 'btn btn--primary btn--sm btn--block';
        link.href = '<?= BASE_URL ?>/paquete/' + encodeURIComponent(p.slug);
        link.textContent = 'Ver paquete';
        bodyWrap.appendChild(link);

        art.appendChild(bodyWrap);
        favList.appendChild(art);
      });
    }
  }
})();
</script>
