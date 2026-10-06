<?php

// Funcion del archivo: Renderiza el detalle del paquete, galer?a, reserva y relacionados.
  // Aseguramos que la galería tenga imágenes coherentes con el paquete mostrado.
  $galeria = [];
  foreach (($imagenes ?? []) as $img) {
      if (!empty($img['url_imagen'])) {
          $galeria[] = $img['url_imagen'];
      }
  }
  $fallbacks = \App\Models\Paquete::fallbackImagesBySlug($paquete['slug'] ?? '');
  $fbIndex = 0;
  $prevCount = count($galeria);
  while (count($galeria) < 5) {
      $candidate = $fallbacks[$fbIndex % count($fallbacks)];
      if (!in_array($candidate, $galeria, true) || count($fallbacks) === 1) {
          $galeria[] = $candidate;
      }
      $fbIndex++;
      // Break if we cycled through all fallbacks without adding any new image
      if ($fbIndex > count($fallbacks) && count($galeria) === $prevCount) {
          break;
      }
      $prevCount = count($galeria);
  }

  // Precios y monedas
  $moneda = 'S/';
  $precio = (float)($paquete['precio_base'] ?? 0);
  $precioAnterior = (!empty($paquete['precio_anterior']) && (float)$paquete['precio_anterior'] > $precio) ? (float)$paquete['precio_anterior'] : null;
  $incluye = array_values(array_filter($paquete['incluye'] ?? []));
  $noIncluye = array_values(array_filter($paquete['no_incluye'] ?? []));
  $politicas = array_values(array_filter($paquete['politicas'] ?? [], static fn($item) => !empty($item['titulo']) || !empty($item['descripcion'])));
  $documentos = array_values(array_filter($paquete['documentos'] ?? []));
  $tags = array_values(array_filter($paquete['tags'] ?? []));
  $mainDesc = trim((string)(($paquete['descripcion_corta'] ?? '') ?: ($mainDesc ?? $paquete['descripcion'] ?? '')));
  $benefits = array_filter([
      'Hotel' => $paquete['hotel'] ?? '',
      'Habitación' => $paquete['habitacion'] ?? '',
      'Comidas' => $paquete['comidas'] ?? '',
      'Vuelos' => $paquete['vuelos'] ?? '',
      'Movilidad' => $paquete['movilidad'] ?? '',
      'Guía' => $paquete['guia'] ?? '',
  ], static fn($value) => trim((string)$value) !== '');
  $hasItinerary = !empty($itinerario);
  $hasIncludes = !empty($incluye) || !empty($noIncluye);
  $hasPolicies = !empty($politicas) || !empty($documentos);
?>


<div class="det-head">
  <div class="container">
    <nav class="breadcrumbs">
      <a href="<?= BASE_URL ?>/">Inicio</a>
      <span class="sep">/</span>
      <a href="<?= BASE_URL ?>/catalog">Catálogo</a>
      <span class="sep">/</span>
      <span class="current"><?= htmlspecialchars($paquete['nombre'] ?? '') ?></span>
    </nav>
    <div class="row row--between" style="align-items:flex-start;margin-top:8px">
      <div>
         <div class="row" style="gap:8px;margin-bottom:6px">
          <span class="badge"><?= htmlspecialchars($paquete['categoria_nombre'] ?? '') ?></span>
          <span class="badge badge--success">Disponible</span>
        </div>
        <h1><?= htmlspecialchars($paquete['nombre'] ?? '') ?></h1>
        <div class="det-meta">
          <span><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-inline"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 1 1 18 0Z"/><circle cx="12" cy="10" r="3"/></svg> <?= htmlspecialchars($paquete['ciudad_nombre'] ?? '') ?></span>
          <span><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-inline"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg> <?= (int)($paquete['duracion_dias'] ?? 0) ?> días · <?= (int)($paquete['duracion_noches'] ?? 0) ?> noches</span>
          <?php if (!empty($paquete['cupo_maximo'])): ?><span><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-inline"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg> Cupo máximo: <?= (int)$paquete['cupo_maximo'] ?> pax</span><?php endif; ?>
          <?php if (!empty($paquete['dificultad'])): ?><span><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-inline"><circle cx="12" cy="12" r="10"/><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/></svg> <?= htmlspecialchars($paquete['dificultad']) ?></span><?php endif; ?>
        </div>
      </div>
      <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
        <?php if (!empty($lat) && !empty($lng)): ?>
        <button type="button" class="det-share" id="openMapModal" aria-label="Ver ubicación en mapa">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 1 1 18 0Z"/><circle cx="12" cy="10" r="3"/></svg>
          Ver mapa
        </button>
        <?php endif; ?>
        <button class="det-share" id="shareBtn" type="button" aria-label="Compartir este viaje">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="m8.59 13.51 6.83 3.98M15.41 6.51l-6.82 3.98"/></svg>
          Compartir este viaje
        </button>
        <button class="fav-btn" data-fav="<?= htmlspecialchars($paquete['slug'] ?? '') ?>" aria-label="Favorito">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 1 0-7.78 7.78L12 21.23l8.84-8.84a5.5 5.5 0 0 0 0-7.78z"/></svg>
        </button>
      </div>
    </div>
  </div>
</div>

<main id="main-content" class="container">
  <div class="gallery" id="detailGallery">
    <?php foreach ($galeria as $idx => $imgUrl): ?>
      <?php $fallbackImg = $galeria[($idx + 1) % count($galeria)] ?? $imgUrl; ?>
      <img src="<?= htmlspecialchars($imgUrl) ?>" data-fallback-src="<?= htmlspecialchars($fallbackImg) ?>" alt="<?= htmlspecialchars($paquete['nombre'] ?? '') ?>" data-idx="<?= $idx ?>" <?= $idx > 0 ? 'loading="lazy" decoding="async"' : '' ?>>
    <?php endforeach; ?>
    <div class="gallery-counter" id="galleryCounter">1 / <?= count($galeria) ?></div>
  </div>

  <!-- Visor ampliado de imágenes del paquete. -->
  <div class="gallery-lightbox" id="galleryLightbox" hidden aria-hidden="true">
    <div class="gallery-lightbox__panel" role="dialog" aria-modal="true" aria-label="Imagen ampliada del paquete">
      <button class="gallery-lightbox__close" type="button" data-gallery-close aria-label="Cerrar imagen ampliada">×</button>
      <button class="gallery-lightbox__nav-btn gallery-lightbox__nav-btn--prev" type="button" data-gallery-prev aria-label="Imagen anterior">&#8249;</button>
      <button class="gallery-lightbox__nav-btn gallery-lightbox__nav-btn--next" type="button" data-gallery-next aria-label="Imagen siguiente">&#8250;</button>
      <img class="gallery-lightbox__img" id="galleryLightboxImg" src="" alt="">
      <div class="gallery-lightbox__caption" id="galleryLightboxCaption"></div>
    </div>
  </div>

  <!-- Mapa de ubicación (en modal) -->
  <?php if (!empty($lat) && !empty($lng)): ?>
  <div class="gallery-lightbox" id="mapModal" hidden aria-hidden="true">
    <div class="gallery-lightbox__panel" role="dialog" aria-modal="true" aria-label="Mapa de <?= htmlspecialchars($paquete['ciudad_nombre'] ?? '') ?>" style="width:min(900px,95%);max-height:85vh">
      <button class="gallery-lightbox__close" type="button" data-map-close aria-label="Cerrar mapa">×</button>
      <div id="detailMap" style="width:100%;min-height:500px;border:0;border-radius:var(--r-lg);display:block"></div>
      <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
      <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
      <script>
        (function() {
          var mapEl = document.getElementById('detailMap');
          var mapModal = document.getElementById('mapModal');
          var mapInstance = null;
          var observer = new MutationObserver(function() {
            if (!mapModal.hidden && !mapInstance) {
              mapInstance = L.map('detailMap', { scrollWheelZoom: false }).setView([<?= $lat ?>, <?= $lng ?>], 13);
              L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                maxZoom: 19
              }).addTo(mapInstance);
              L.marker([<?= $lat ?>, <?= $lng ?>]).addTo(mapInstance)
                .bindPopup('<?= addslashes(htmlspecialchars($paquete['ciudad_nombre'] ?? '')) ?>')
                .openPopup();
              setTimeout(function() { mapInstance.invalidateSize(); }, 200);
            }
          });
          observer.observe(mapModal, { attributes: true, attributeFilter: ['hidden'] });
        })();
      </script>
    </div>
  </div>
  <?php endif; ?>

  <div class="det-layout">
    <section>
      <div class="tabs" data-tabs role="tablist">
        <button class="tab is-active" data-tab="desc" aria-selected="true">Descripción</button>
        <?php if ($hasItinerary): ?><button class="tab" data-tab="itin">Itinerario</button><?php endif; ?>
        <?php if ($hasIncludes): ?><button class="tab" data-tab="inc">Incluye</button><?php endif; ?>
        <?php if ($hasPolicies): ?><button class="tab" data-tab="pol">Políticas</button><?php endif; ?>
      </div>

      <div class="tab-panel is-active" data-tab-panel="desc">
        <p class="lead"><?= nl2br(htmlspecialchars($mainDesc)) ?></p>
        <?php if (!empty($tags)): ?>
          <div class="row" style="gap:8px;flex-wrap:wrap;margin-top:14px">
            <?php foreach ($tags as $tag): ?><span class="badge"><?= htmlspecialchars($tag) ?></span><?php endforeach; ?>
          </div>
        <?php endif; ?>
        <?php if (!empty($benefits)): ?>
          <div class="benefits">
            <?php foreach ($benefits as $label => $value): ?>
              <div class="benefit"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M8 12h8M12 8v8"/></svg><div><strong><?= htmlspecialchars($label) ?></strong><span><?= htmlspecialchars($value) ?></span></div></div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <?php if ($hasItinerary): ?>
      <div class="tab-panel" data-tab-panel="itin">
        <ol class="timeline">
          <?php foreach ($itinerario as $step): ?>
            <?php
              $dia = is_array($step) ? ($step['dia'] ?? 1) : 1;
              $titulo = is_array($step) ? ($step['titulo'] ?? '') : '';
              $descripcion = is_array($step) ? ($step['descripcion'] ?? $step) : $step;
            ?>
            <li class="timeline__item">
              <span class="timeline__dot"><?= (int)$dia ?></span>
              <span class="timeline__time">Día <?= (int)$dia ?></span>
              <?php if (!empty($titulo)): ?>
                <h4 class="timeline__title"><?= htmlspecialchars($titulo) ?></h4>
              <?php endif; ?>
              <p class="timeline__text"><?= htmlspecialchars($descripcion) ?></p>
            </li>
          <?php endforeach; ?>
        </ol>
      </div>
      <?php endif; ?>

      <?php if ($hasIncludes): ?>
      <div class="tab-panel" data-tab-panel="inc">
        <div class="grid grid-2">
          <?php if (!empty($incluye)): ?>
          <div class="card card--padded">
            <h4><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-inline"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg> Incluye</h4>
            <ul class="detail-list detail-list--ink-700">
              <?php foreach ($incluye as $item): ?><li><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-inline"><polyline points="20 6 9 17 4 12"/></svg> <?= htmlspecialchars($item) ?></li><?php endforeach; ?>
            </ul>
          </div>
          <?php endif; ?>
          <?php if (!empty($noIncluye)): ?>
          <div class="card card--padded">
            <h4>✗ No incluye</h4>
            <ul class="detail-list detail-list--ink-600">
              <?php foreach ($noIncluye as $item): ?><li>✗ <?= htmlspecialchars($item) ?></li><?php endforeach; ?>
            </ul>
          </div>
          <?php endif; ?>
        </div>
      </div>
      <?php endif; ?>

      <?php if ($hasPolicies): ?>
      <div class="tab-panel" data-tab-panel="pol">
        <div class="faq">
          <?php foreach ($politicas as $idx => $politica): ?>
            <details class="faq__item" <?= $idx === 0 ? 'open' : '' ?>>
              <summary><?= htmlspecialchars($politica['titulo'] ?? 'Política') ?></summary>
              <div class="faq__body"><?= nl2br(htmlspecialchars($politica['descripcion'] ?? '')) ?></div>
            </details>
          <?php endforeach; ?>

          <?php if (!empty($documentos)): ?>
            <details class="faq__item" <?= empty($politicas) ? 'open' : '' ?>>
              <summary>Documentación requerida</summary>
              <div class="faq__body">
                <ul style="display:flex;flex-direction:column;gap:6px;margin:0;padding-left:18px">
                  <?php foreach ($documentos as $documento): ?><li><?= htmlspecialchars($documento) ?></li><?php endforeach; ?>
                </ul>
              </div>
            </details>
          <?php endif; ?>
        </div>
      </div>
      <?php endif; ?>
    </section>

    <aside>
      <div class="book">
        <div class="book__price">
          <?php if ($precioAnterior): ?><span class="o"><?= $moneda ?> <?= number_format($precioAnterior, 0, '.', ',') ?></span><?php endif; ?>
          <span class="v" id="pricePerson"><?= $moneda ?> <?= number_format($precio, 0, '.', ',') ?></span>
          <span style="color:var(--c-ink-500);font-size:var(--fs-13)">/ persona</span>
        </div>
        <form action="<?= BASE_URL ?>/checkout" method="GET">
          <input type="hidden" name="paquete" value="<?= htmlspecialchars($paquete['slug'] ?? '') ?>">
          <div class="book__row">
            <div class="field"><label for="fecha_viaje">Inicio</label><input class="input" type="date" id="fecha_viaje" name="fecha_viaje" required value="<?= date('Y-m-d', strtotime('+30 days')) ?>"></div>
          </div>
          <div class="field traveler-picker" data-traveler-picker data-max-travelers="10" style="margin-bottom:10px">
            <label id="travelerPickerLabel">Viajeros</label>
            <input type="hidden" id="cant_viajeros" name="viajeros" value="2">
            <button class="traveler-picker__trigger" type="button" aria-expanded="false" aria-controls="travelerPickerPanel" aria-labelledby="travelerPickerLabel travelerPickerSummary">
              <span>
                <strong id="travelerPickerSummary">2 adultos</strong>
                <small>Adultos, niños y bebés</small>
              </span>
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
            </button>
            <div class="traveler-picker__panel" id="travelerPickerPanel" hidden>
              <div class="traveler-row" data-traveler-row="adults">
                <div><strong>Adultos</strong><small>13 años o más</small></div>
                <div class="traveler-stepper"><button type="button" data-traveler-dec="adults" aria-label="Quitar adulto">−</button><span data-traveler-count="adults">2</span><button type="button" data-traveler-inc="adults" aria-label="Agregar adulto">+</button></div>
              </div>
              <div class="traveler-row" data-traveler-row="children">
                <div><strong>Niños</strong><small>De 2 a 12 años</small></div>
                <div class="traveler-stepper"><button type="button" data-traveler-dec="children" aria-label="Quitar niño">−</button><span data-traveler-count="children">0</span><button type="button" data-traveler-inc="children" aria-label="Agregar niño">+</button></div>
              </div>
              <div class="traveler-row" data-traveler-row="babies">
                <div><strong>Bebés</strong><small>Menores de 2 años</small></div>
                <div class="traveler-stepper"><button type="button" data-traveler-dec="babies" aria-label="Quitar bebé">−</button><span data-traveler-count="babies">0</span><button type="button" data-traveler-inc="babies" aria-label="Agregar bebé">+</button></div>
              </div>
              <p class="traveler-picker__hint">El checkout recibirá el total de viajeros. Máximo 10 por reserva.</p>
            </div>
          </div>
          <div class="book__total"><span>Total estimado</span><span id="totalEstVal"><?= $moneda ?> <?= number_format($precio * 2, 0, '.', ',') ?></span></div>
          <button type="submit" class="btn btn--accent btn--lg btn--block">Reservar ahora</button>
        </form>
        <a href="<?= BASE_URL ?>/contacto" class="btn btn--ghost btn--block" style="margin-top:10px">Consultar asesor</a>
        <p class="small text-center" style="margin-top:12px"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-inline"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg> Pago seguro · sin cargos ocultos</p>
      </div>
    </aside>
  </div>
</main>

<!-- Paquetes similares -->
<?php if (!empty($relacionados)): ?>
<section class="container det-related">
  <h2>Paquetes similares</h2>
  <div class="det-related__grid">
    <?php foreach ($relacionados as $rel): ?>
      <?php
        $relMoneda = 'S/';
        $relFallbacks = \App\Models\Paquete::fallbackImagesBySlug($rel['slug'] ?? '');
      ?>
      <a href="<?= BASE_URL ?>/paquete/<?= htmlspecialchars($rel['slug']) ?>" class="det-related__card">
        <img class="det-related__img" src="<?= htmlspecialchars($rel['imagen_url'] ?? ($relFallbacks[0] ?? '')) ?>" alt="<?= htmlspecialchars($rel['nombre']) ?>" loading="lazy">
        <div class="det-related__body">
          <h4><?= htmlspecialchars($rel['nombre']) ?></h4>
          <div class="det-related__meta"><?= htmlspecialchars($rel['ciudad_nombre'] ?? '') ?> · <?= (int)($rel['duracion_dias'] ?? 0) ?> días</div>
          <div class="det-related__price"><?= $relMoneda ?> <?= number_format((float)($rel['precio_base'] ?? 0), 0, '.', ',') ?></div>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<div class="sticky-mobile-bar">
  <div><div style="font-size:var(--fs-12);color:var(--c-ink-500)">Desde</div><strong style="color:var(--c-primary-800);font-size:var(--fs-18)"><?= $moneda ?> <?= number_format($precio, 0, '.', ',') ?></strong></div>
  <a href="<?= BASE_URL ?>/checkout?paquete=<?= htmlspecialchars($paquete['slug'] ?? '') ?>" class="btn btn--accent">Reservar</a>
</div>

<?php if ($waNumber = \App\Helper\Config::getString('agencia_whatsapp')): ?>
<a href="https://wa.me/<?= htmlspecialchars($waNumber) ?>" class="wa-fab" aria-label="WhatsApp" target="_blank" rel="noopener">
  <svg viewBox="0 0 24 24" fill="currentColor"><path d="M20.5 3.5A11.9 11.9 0 0 0 12 0C5.4 0 0 5.4 0 12c0 2.1.5 4.1 1.6 5.9L0 24l6.3-1.6A12 12 0 0 0 12 24c6.6 0 12-5.4 12-12 0-3.2-1.2-6.2-3.5-8.5z"/></svg>
 </a>
<?php endif; ?>

<script nonce="<?= htmlspecialchars(\App\Helper\SecurityHeaders::getNonce(), ENT_QUOTES, 'UTF-8') ?>">
document.addEventListener('DOMContentLoaded', () => {

  // Calculador de precio dinámico y selector moderno de viajeros.
  const travelerInput = document.getElementById('cant_viajeros');
  const travelerPicker = document.querySelector('[data-traveler-picker]');
  const travelerTrigger = travelerPicker?.querySelector('.traveler-picker__trigger');
  const travelerPanel = document.getElementById('travelerPickerPanel');
  const travelerSummary = document.getElementById('travelerPickerSummary');
  const priceBase = <?= $precio ?>;
  const totalValEl = document.getElementById('totalEstVal');
  const mobileCta = document.getElementById('detailMobileCta');
  const dateInput = document.getElementById('fecha_viaje');
  const moneda = '<?= $moneda ?> ';
  const travelerState = { adults: 2, children: 0, babies: 0 };
  const maxTravelers = parseInt(travelerPicker?.dataset.maxTravelers || '10', 10);

  // Funcion: Devuelve una palabra en singular o plural segun el valor.
  function plural(value, singular, pluralText) {
    return value + ' ' + (value === 1 ? singular : pluralText);
  }

  // Funcion: Actualiza el resumen de viajeros y totales.
  function updateTravelerSummary() {
    const total = travelerState.adults + travelerState.children + travelerState.babies;
    const parts = [plural(travelerState.adults, 'adulto', 'adultos')];
    if (travelerState.children > 0) parts.push(plural(travelerState.children, 'niño', 'niños'));
    if (travelerState.babies > 0) parts.push(plural(travelerState.babies, 'bebé', 'bebés'));

    if (travelerInput) travelerInput.value = total;
    if (travelerSummary) travelerSummary.textContent = parts.join(', ');
    if (totalValEl) totalValEl.textContent = moneda + (priceBase * total).toLocaleString('es-PE');

    document.querySelectorAll('[data-traveler-count]').forEach(el => {
      const key = el.getAttribute('data-traveler-count');
      el.textContent = travelerState[key];
    });
    document.querySelectorAll('[data-traveler-dec]').forEach(btn => {
      const key = btn.getAttribute('data-traveler-dec');
      btn.disabled = key === 'adults' ? travelerState.adults <= 1 : travelerState[key] <= 0;
    });
    document.querySelectorAll('[data-traveler-inc]').forEach(btn => {
      btn.disabled = total >= maxTravelers;
    });

    if (mobileCta && dateInput) {
      const url = new URL(mobileCta.href, window.location.origin);
      url.searchParams.set('fecha_viaje', dateInput.value);
      url.searchParams.set('viajeros', String(total));
      mobileCta.href = url.toString();
    }
  }

  if (travelerTrigger && travelerPanel) {
    travelerTrigger.addEventListener('click', () => {
      const expanded = travelerTrigger.getAttribute('aria-expanded') === 'true';
      travelerTrigger.setAttribute('aria-expanded', expanded ? 'false' : 'true');
      travelerPanel.hidden = expanded;
    });

    document.addEventListener('click', (event) => {
      if (!travelerPicker.contains(event.target)) {
        travelerTrigger.setAttribute('aria-expanded', 'false');
        travelerPanel.hidden = true;
      }
    });
  }

  document.querySelectorAll('[data-traveler-inc],[data-traveler-dec]').forEach(btn => {
    btn.addEventListener('click', () => {
      const incKey = btn.getAttribute('data-traveler-inc');
      const decKey = btn.getAttribute('data-traveler-dec');
      const total = travelerState.adults + travelerState.children + travelerState.babies;
      if (incKey && total < maxTravelers) travelerState[incKey] += 1;
      if (decKey) {
        const min = decKey === 'adults' ? 1 : 0;
        travelerState[decKey] = Math.max(min, travelerState[decKey] - 1);
      }
      updateTravelerSummary();
    });
  });

  if (dateInput) dateInput.addEventListener('change', updateTravelerSummary);
  updateTravelerSummary();

  // Abre las fotos de la galería en un visor amplio con cierre por fondo, botón o Escape.
  const gallery = document.querySelector('.gallery');
  const lightbox = document.getElementById('galleryLightbox');
  const lightboxImg = document.getElementById('galleryLightboxImg');
  const lightboxCaption = document.getElementById('galleryLightboxCaption');

  // Gallery images array for navigation
  const galleryImages = <?= json_encode($galeria) ?>;
  let currentGalleryIdx = 0;

  // Funcion: Cierra el visor ampliado de imagenes.
  function closeLightbox() {
    if (!lightbox) return;
    lightbox.hidden = true;
    lightbox.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
    if (lightboxImg) lightboxImg.src = '';
  }

  // Funcion: Muestra una imagen especifica en el visor.
  function showLightboxImage(idx) {
    if (!galleryImages.length) return;
    currentGalleryIdx = ((idx % galleryImages.length) + galleryImages.length) % galleryImages.length;
    if (lightboxImg) {
      lightboxImg.src = galleryImages[currentGalleryIdx];
      lightboxImg.alt = '<?= htmlspecialchars($paquete['nombre'], ENT_QUOTES) ?> — Imagen ' + (currentGalleryIdx + 1);
    }
    if (lightboxCaption) lightboxCaption.textContent = (currentGalleryIdx + 1) + ' / ' + galleryImages.length;
  }

  if (gallery && lightbox && lightboxImg) {
    gallery.querySelectorAll('img').forEach((img) => {
      img.setAttribute('tabindex', '0');
      img.setAttribute('role', 'button');
      img.setAttribute('aria-label', 'Ver imagen ampliada de <?= htmlspecialchars($paquete['nombre'], ENT_QUOTES) ?>');

      const openLightbox = () => {
        const idx = parseInt(img.getAttribute('data-idx') || '0');
        showLightboxImage(idx);
        lightbox.hidden = false;
        lightbox.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
      };

      img.addEventListener('click', openLightbox);
      img.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' || event.key === ' ') {
          event.preventDefault();
          openLightbox();
        }
      });
    });

    lightbox.addEventListener('click', (event) => {
      if (event.target === lightbox || event.target.hasAttribute('data-gallery-close')) {
        closeLightbox();
      }
    });

    document.addEventListener('keydown', (event) => {
      if (lightbox.hidden) return;
      if (event.key === 'Escape') closeLightbox();
      if (event.key === 'ArrowLeft') showLightboxImage(currentGalleryIdx - 1);
      if (event.key === 'ArrowRight') showLightboxImage(currentGalleryIdx + 1);
    });

    // Arrow button click handlers
    const prevBtn = lightbox.querySelector('[data-gallery-prev]');
    const nextBtn = lightbox.querySelector('[data-gallery-next]');
    if (prevBtn) prevBtn.addEventListener('click', (e) => { e.stopPropagation(); showLightboxImage(currentGalleryIdx - 1); });
    if (nextBtn) nextBtn.addEventListener('click', (e) => { e.stopPropagation(); showLightboxImage(currentGalleryIdx + 1); });
  }

  // Map modal
  const mapModal = document.getElementById('mapModal');
  const openMapBtn = document.getElementById('openMapModal');
  if (mapModal && openMapBtn) {
    openMapBtn.addEventListener('click', () => {
      mapModal.hidden = false;
      mapModal.setAttribute('aria-hidden', 'false');
      document.body.style.overflow = 'hidden';
    });
    mapModal.addEventListener('click', (e) => {
      if (e.target === mapModal || e.target.hasAttribute('data-map-close')) {
        mapModal.hidden = true;
        mapModal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
      }
    });
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && mapModal && !mapModal.hidden) {
        mapModal.hidden = true;
        mapModal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
      }
    });
  }

  // Sincronizar favorito local
  const favBtn = document.querySelector('.fav-btn');
  if (favBtn) {
    const slug = favBtn.getAttribute('data-fav');
    
    // Funcion: Actualiza el estado visual del favorito actual.
    function checkFav(){
      let favs = [];
      try { favs = JSON.parse(localStorage.getItem('ajt_favs') || '[]'); } catch(e){}
      const isFav = favs.includes(slug);
      favBtn.classList.toggle('is-active', isFav);
      favBtn.setAttribute('aria-pressed', isFav ? 'true' : 'false');
    }
    
    checkFav();
    
    // El click de favoritos lo gestiona main.js para no alternar dos veces el mismo estado.
  }

  // Gallery counter: updates when hovering over images
  const galleryEl = document.getElementById('detailGallery');
  const counterEl = document.getElementById('galleryCounter');
  if (galleryEl && counterEl) {
    const imgs = galleryEl.querySelectorAll('img[data-idx]');
    imgs.forEach(img => {
      img.addEventListener('mouseenter', () => {
        counterEl.textContent = (parseInt(img.dataset.idx) + 1) + ' / ' + imgs.length;
      });
    });
  }

  // Share button: Web Share API with clipboard fallback
  const shareBtn = document.getElementById('shareBtn');
  if (shareBtn) {
    shareBtn.addEventListener('click', async () => {
      const shareData = {
        title: <?= json_encode($paquete['nombre'] ?? 'Viajes AJT') ?>,
        text: <?= json_encode('Mira este paquete turístico: ' . ($paquete['nombre'] ?? '')) ?>,
        url: window.location.href
      };
      if (navigator.share) {
        try {
          await navigator.share(shareData);
        } catch (e) {
          // User cancelled — do nothing
        }
      } else {
        try {
          await navigator.clipboard.writeText(window.location.href);
          shareBtn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg> ¡Link copiado!';
          setTimeout(() => {
            shareBtn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="m8.59 13.51 6.83 3.98M15.41 6.51l-6.82 3.98"/></svg> Compartir este viaje';
          }, 2500);
        } catch (e) {
          // Clipboard not available — show fallback
          prompt('Copia este enlace:', window.location.href);
        }
      }
    });
  }
});
</script>

<!-- CTA sticky para móvil -->
<div class="detail-mobile-cta">
  <a href="<?= BASE_URL ?>/checkout?paquete=<?= htmlspecialchars($paquete['slug'] ?? '') ?>&fecha_viaje=<?= date('Y-m-d', strtotime('+30 days')) ?>&viajeros=1" class="btn btn--accent btn--lg" id="detailMobileCta">
    Reservar desde <?= $moneda ?> <?= number_format($paquete['precio_base'] ?? 0, 0, '.', ',') ?>
  </a>
</div>

    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "TouristTrip",
      "name": <?= json_encode($paquete['nombre'] ?? '') ?>,
      "description": <?= json_encode($paquete['descripcion_corta'] ?? $paquete['descripcion'] ?? '') ?>,
      "touristType": <?= json_encode($paquete['tipo'] ?? '') ?>,
      "offers": {
        "@type": "Offer",
        "price": <?= json_encode((float)($paquete['precio_base'] ?? 0)) ?>,
        "priceCurrency": "PEN",
        "availability": "https://schema.org/InStock",
        "seller": { "@type": "TravelAgency", "name": "Viajes AJT" }
      }
    }
    </script>
