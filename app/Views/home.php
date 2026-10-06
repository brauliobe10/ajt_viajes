<!-- Funcion del archivo: Renderiza la pagina principal con busqueda, destinos y testimonios. -->
<main id="main-content">
<section class="hero">
  <div class="hero__bg" aria-hidden="true"></div>
  <div class="container hero__inner">
    <?php if ($mincetur = \App\Helper\Config::getString('agencia_mincetur')): ?>
    <span class="eyebrow hero__eyebrow"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" stroke="none" class="svg-inline"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg> Tour operador certificado · <?= htmlspecialchars($mincetur) ?></span>
    <?php endif; ?>
    <h1>Conectamos confianza,<br>creamos experiencias.</h1>
    <p class="lead">Diseñamos viajes a la medida con asesoría experta en visas, pagos seguros y soporte 24/7 antes, durante y después de tu viaje.</p>
    <div class="hero__ctas">
      <a href="<?= BASE_URL ?>/catalog" class="btn btn--accent btn--lg">Explorar paquetes →</a>
      <a href="<?= BASE_URL ?>/visas" class="btn btn--white btn--lg">Asesoría de visas</a>
    </div>
  </div>
</section>

<!-- 6.6 Buscador con autocomplete + filtros -->
<div class="search">
  <div class="search__card">
    <form class="search__form" action="<?= BASE_URL ?>/catalog" method="GET">
      <div class="search__field" style="position:relative">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
        <label>Buscar paquete</label>
        <input type="text" name="destino" id="homeSearch" placeholder="Cusco, Cancún, Europa…" autocomplete="off">
        <div id="homeSearchResults" class="search__autocomplete" style="display:none"></div>
      </div>
      <?php if (!empty($categorias)): ?>
      <div class="search__filter">
        <label for="homeCategoria">Tipo</label>
        <select name="categoria" id="homeCategoria" class="search__select">
          <option value="">Todos</option>
          <?php foreach ($categorias as $cat): ?>
            <option value="<?= htmlspecialchars(strtolower($cat['nombre'])) ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <?php endif; ?>
      <div class="search__filter">
        <label for="homeDuracion">Duración</label>
        <select name="duracion" id="homeDuracion" class="search__select">
          <option value="">Cualquiera</option>
          <option value="1-3">1–3 días</option>
          <option value="4-7">4–7 días</option>
          <option value="8-14">8–14 días</option>
          <option value="15+">15+ días</option>
        </select>
      </div>
      <button class="btn btn--accent btn--lg search__submit" type="submit">Buscar</button>
    </form>
  </div>
</div>

<!-- 6.1 Destacados desde BD -->
<section class="section">
  <div class="container">
    <div class="section-head reveal">
      <span class="eyebrow">Destinos destacados</span>
      <h2>Inspírate con los más buscados</h2>
      <p>Selección curada por nuestros asesores con experiencia real en el destino.</p>
    </div>
    <div class="dest-grid reveal">
      <?php if (!empty($paquetesDestacados)): ?>
        <?php foreach ($paquetesDestacados as $p): ?>
          <?php
            $moneda = 'S/';
            $fallbacks = \App\Models\Paquete::fallbackImagesBySlug($p['slug']);
          ?>
          <a class="dest" href="<?= BASE_URL ?>/paquete/<?= htmlspecialchars($p['slug']) ?>">
            <img loading="lazy" src="<?= htmlspecialchars($p['imagen_url'] ?? ($fallbacks[0] ?? '')) ?>" data-fallback-src="<?= htmlspecialchars($fallbacks[1] ?? $fallbacks[0] ?? '') ?>" alt="<?= htmlspecialchars($p['nombre']) ?>">
            <div class="dest__body">
              <div class="dest__country"><?= htmlspecialchars($p['ciudad_nombre'] ?? '') ?></div>
              <div class="dest__city"><?= htmlspecialchars($p['nombre']) ?></div>
              <div class="dest__meta">Desde <?= $moneda ?> <?= number_format($p['precio_base'], 0, '.', ',') ?> · <?= (int)$p['duracion_dias'] ?> días</div>
            </div>
          </a>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="dest-empty">
          <p>Próximamente mostraremos nuestros destinos destacados.</p>
          <a href="<?= BASE_URL ?>/catalog" class="btn btn--primary">Explorar catálogo</a>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- 6.2 Cómo funciona -->
<section class="section section--tight">
  <div class="container">
    <div class="section-head reveal">
      <span class="eyebrow">¿Cómo funciona?</span>
      <h2>Tu viaje en 3 pasos</h2>
    </div>
    <div class="steps-grid reveal">
      <div class="step-card">
        <div class="step-card__num">1</div>
        <h3 class="step-card__title">Buscar</h3>
        <p class="step-card__text">Explora nuestros paquetes por destino, categoría o presupuesto. Usa el buscador para encontrar exactamente lo que necesitas.</p>
      </div>
      <div class="step-card">
        <div class="step-card__num">2</div>
        <h3 class="step-card__title">Reservar</h3>
        <p class="step-card__text">Selecciona fechas, completa tus datos y elige tu método de pago. Todo en un proceso seguro y rápido.</p>
      </div>
      <div class="step-card">
        <div class="step-card__num">3</div>
        <h3 class="step-card__title">Viajar</h3>
        <p class="step-card__text">Recibe tu itinerario y documentos de viaje. Disfruta tu destino con el respaldo de nuestro equipo las 24 horas.</p>
      </div>
    </div>
  </div>
</section>

<!-- 6.3 Por qué elegirnos (sin estadísticas inventadas) -->
<section class="section">
  <div class="container">
    <div class="section-head reveal">
      <span class="eyebrow">¿Por qué elegirnos?</span>
      <h2>Lo que nos hace diferentes</h2>
    </div>
    <div class="benefits-grid reveal">
      <div class="benefit-card">
        <div class="benefit-card__icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg></div>
        <h3 class="benefit-card__title">Experiencia</h3>
        <p class="benefit-card__text">Más de <?= $statsExperiencia ?> años organizando viajes. Conocemos cada destino porque hemos estado ahí.</p>
      </div>
      <div class="benefit-card">
        <div class="benefit-card__icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-inline"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></div>
        <h3 class="benefit-card__title">Seguridad</h3>
        <p class="benefit-card__text">Pagos protegidos, reservas con confirmación inmediata y documentación verificada antes de cada viaje.</p>
      </div>
      <div class="benefit-card">
        <div class="benefit-card__icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"/><path d="M8 9h8M8 13h5"/></svg></div>
        <h3 class="benefit-card__title">Soporte 24/7</h3>
        <p class="benefit-card__text">¿Necesitas ayuda? Nuestro equipo está disponible antes, durante y después de tu viaje por WhatsApp y teléfono.</p>
      </div>
      <div class="benefit-card">
        <div class="benefit-card__icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-inline"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg></div>
        <h3 class="benefit-card__title">Asesoría experta</h3>
        <p class="benefit-card__text">Te ayudamos con visas, itinerarios personalizados y recomendaciones basadas en experiencia real.</p>
      </div>
    </div>
  </div>
</section>

<!-- 6.4 Temporadas / Categorías -->
<?php if (!empty($categorias)): ?>
<section class="section section--tight">
  <div class="container">
    <div class="section-head reveal">
      <span class="eyebrow">Explora por categoría</span>
      <h2>¿Qué tipo de viaje buscas?</h2>
    </div>
    <div class="category-grid reveal">
      <?php foreach ($categorias as $cat):
        $pkgs = $paquetesPorCategoria[$cat['id_categoria']] ?? [];
        $count = count($pkgs);
        if ($count === 0) continue;
        // Tomar imagen del primer paquete de la categoría
        $first = $pkgs[0];
        $catFallbacks = \App\Models\Paquete::fallbackImagesBySlug($first['slug']);
      ?>
        <a href="<?= BASE_URL ?>/catalog?categoria=<?= urlencode(strtolower($cat['nombre'])) ?>" class="category-card">
          <img loading="lazy" src="<?= htmlspecialchars($first['imagen_url'] ?? ($catFallbacks[0] ?? '')) ?>" alt="<?= htmlspecialchars($cat['nombre']) ?>">
          <div class="category-card__body">
            <h3 class="category-card__title"><?= htmlspecialchars($cat['nombre']) ?></h3>
            <span class="category-card__count"><?= $count ?> paquete<?= $count > 1 ? 's' : '' ?></span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- 6.5 Ofertas reales -->
<?php if (!empty($paquetesOfertas)): ?>
<section class="section">
  <div class="container">
    <div class="section-head reveal">
      <span class="eyebrow">Ofertas especiales</span>
      <h2>Paquetes con descuento real</h2>
      <p>Precios verificados. El descuento se muestra porque el precio anterior era mayor al actual.</p>
    </div>
    <div class="dest-grid reveal">
      <?php foreach (array_slice($paquetesOfertas, 0, 4) as $p):
        $moneda = 'S/';
        $fallbacks = \App\Models\Paquete::fallbackImagesBySlug($p['slug']);
        $descuento = round((1 - $p['precio_base'] / $p['precio_anterior']) * 100);
      ?>
        <a class="dest dest--offer" href="<?= BASE_URL ?>/paquete/<?= htmlspecialchars($p['slug']) ?>">
          <div class="dest__badge">-<?= $descuento ?>%</div>
          <img loading="lazy" src="<?= htmlspecialchars($p['imagen_url'] ?? ($fallbacks[0] ?? '')) ?>" data-fallback-src="<?= htmlspecialchars($fallbacks[1] ?? $fallbacks[0] ?? '') ?>" alt="<?= htmlspecialchars($p['nombre']) ?>">
          <div class="dest__body">
            <div class="dest__country"><?= htmlspecialchars($p['ciudad_nombre'] ?? '') ?></div>
            <div class="dest__city"><?= htmlspecialchars($p['nombre']) ?></div>
            <div class="dest__meta">
              <span class="dest__price-old"><?= $moneda ?> <?= number_format($p['precio_anterior'], 0, '.', ',') ?></span>
              Desde <?= $moneda ?> <?= number_format($p['precio_base'], 0, '.', ',') ?> · <?= (int)$p['duracion_dias'] ?> días
            </div>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
    <div style="text-align:center;margin-top:20px">
      <a href="<?= BASE_URL ?>/catalog" class="btn btn--ghost">Ver todos los paquetes</a>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- Viajes por temporada -->
<?php if (!empty($temporadas)): ?>
<section class="section">
  <div class="container">
    <div class="section-head reveal">
      <span class="eyebrow">Viajes por temporada</span>
      <h2>¿Cuándo quieres viajar?</h2>
      <p>Encuentra el paquete ideal según la época del año.</p>
    </div>
    <div class="season-tabs reveal" role="tablist">
      <?php foreach ($temporadas as $idx => $temp): ?>
        <button class="season-tab <?= $idx === 0 ? 'is-active' : '' ?>" type="button" role="tab" aria-selected="<?= $idx === 0 ? 'true' : 'false' ?>" data-season="<?= $idx ?>">
          <?php if ($temp['icon'] === 'sun'): ?>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-inline"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
          <?php elseif ($temp['icon'] === 'cross'): ?>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-inline"><line x1="12" y1="2" x2="12" y2="22"/><line x1="2" y1="12" x2="22" y2="12"/></svg>
          <?php elseif ($temp['icon'] === 'flag'): ?>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-inline"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/></svg>
          <?php else: ?>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-inline"><polyline points="20 12 20 22 4 22 4 12"/><rect x="2" y="7" width="20" height="5"/><line x1="12" y1="22" x2="12" y2="7"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/></svg>
          <?php endif; ?>
          <?= htmlspecialchars($temp['name']) ?>
          <span class="season-tab__count"><?= $temp['count'] ?></span>
        </button>
      <?php endforeach; ?>
    </div>

    <?php foreach ($temporadas as $idx => $temp): ?>
    <div class="season-panel <?= $idx === 0 ? 'is-active' : '' ?>" data-season-panel="<?= $idx ?>" role="tabpanel">
      <?php if (empty($temp['packages'])): ?>
        <div class="season-empty">
          <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="var(--c-ink-300)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M16 16s-1.5-2-4-2-4 2-4 2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg>
          <p>No hay paquetes disponibles para <?= htmlspecialchars($temp['name']) ?> en este momento.</p>
          <a href="<?= BASE_URL ?>/catalog" class="btn btn--ghost btn--sm">Explorar todos los paquetes</a>
        </div>
      <?php else: ?>
        <div class="dest-grid">
          <?php foreach ($temp['packages'] as $p):
            $moneda = 'S/';
            $fallbacks = \App\Models\Paquete::fallbackImagesBySlug($p['slug']);
          ?>
            <a class="dest" href="<?= BASE_URL ?>/paquete/<?= htmlspecialchars($p['slug']) ?>">
              <img loading="lazy" src="<?= htmlspecialchars($p['imagen_url'] ?? ($fallbacks[0] ?? '')) ?>" data-fallback-src="<?= htmlspecialchars($fallbacks[1] ?? $fallbacks[0] ?? '') ?>" alt="<?= htmlspecialchars($p['nombre']) ?>">
              <div class="dest__body">
                <div class="dest__country"><?= htmlspecialchars($p['ciudad_nombre'] ?? '') ?></div>
                <div class="dest__city"><?= htmlspecialchars($p['nombre']) ?></div>
                <div class="dest__meta">
                  Desde <?= $moneda ?> <?= number_format($p['precio_base'], 0, '.', ',') ?> · <?= (int)$p['duracion_dias'] ?> días
                </div>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<!-- Testimonios (condicional) -->
<?php if (\App\Helper\Config::getBool('testimonios_habilitado') && !empty($testimonios)): ?>
<section class="section">
  <div class="container">
    <div class="section-head reveal"><span class="eyebrow">Testimonios</span><h2>Quienes ya viajaron con nosotros</h2></div>
    <?php if ($soloIniciales): ?>
    <p style="text-align:center;color:var(--c-ink-500);font-size:var(--fs-13);margin-bottom:16px">Estos son testimonios de ejemplo. Pronto verás aquí opiniones reales de nuestros clientes.</p>
    <?php endif; ?>
    <div class="testi reveal">
      <?php foreach ($testimonios as $t): ?>
      <article class="testi__card">
        <div class="rating"><span class="star">★★★★★</span> <?= number_format((float)$t['rating'], 1) ?></div>
        <p class="testi__quote">"<?= htmlspecialchars($t['comentario']) ?>"</p>
        <div class="testi__user">
          <div class="testi__avatar" aria-hidden="true" style="width:48px;height:48px;border-radius:50%;background:var(--c-accent,#0ea5e9);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:18px;flex-shrink:0">
            <?= strtoupper(mb_substr($t['nombre'], 0, 1)) ?>
          </div>
          <div>
            <strong><?= htmlspecialchars($t['nombre']) ?></strong>
            <span><?= htmlspecialchars($t['ciudad']) ?><?= !empty($t['servicio']) ? ' · ' . htmlspecialchars($t['servicio']) : '' ?></span>
          </div>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- Trust bar -->
<section class="section--tight" style="padding-block:24px"><div class="container trust"><span><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-inline"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg> Pagos 100% seguros</span><?php if (\App\Helper\Config::getString('agencia_mincetur')): ?><span><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-inline"><polyline points="20 6 9 17 4 12"/></svg> Tour operador certificado</span><?php endif; ?><span><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" stroke="none" class="svg-inline"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg> <?= $statsDestinos ?> destinos disponibles</span><span><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-inline"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg> Soporte 24/7</span></div></section>

<!-- FAQ -->
<section class="section">
  <div class="container container--narrow">
    <div class="section-head reveal"><span class="eyebrow">Preguntas frecuentes</span><h2>Resolvemos tus dudas</h2></div>
    <div class="faq-cats reveal">
      <div class="faq-cat">
        <div class="faq-cat__icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 1 1 18 0Z"/><circle cx="12" cy="10" r="3"/></svg></div>
        <h3 class="faq-cat__title">Paquetes</h3>
        <div class="faq">
          <details class="faq__item" open><summary>¿Qué incluyen los paquetes turísticos?</summary><div class="faq__body">Cada paquete detalla vuelos, alojamiento, traslados, tours y comidas incluidas. Puedes ver el desglose completo en la página de detalle.</div></details>
          <details class="faq__item"><summary>¿Puedo personalizar un paquete?</summary><div class="faq__body">Sí, contáctanos y un asesor te ayudará a armar un viaje a medida según tu presupuesto y preferencias.</div></details>
        </div>
      </div>
      <div class="faq-cat">
        <div class="faq-cat__icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></div>
        <h3 class="faq-cat__title">Pagos</h3>
        <div class="faq">
          <details class="faq__item"><summary>¿Puedo pagar en cuotas?</summary><div class="faq__body">Sí, aceptamos tarjetas hasta en 12 cuotas con bancos peruanos. También aceptamos Yape, Plin y transferencia bancaria.</div></details>
          <details class="faq__item"><summary>¿Es seguro pagar en línea?</summary><div class="faq__body">Sí, todos los pagos se procesan con cifrado SSL. Nunca almacenamos datos de tu tarjeta.</div></details>
        </div>
      </div>
      <div class="faq-cat">
        <div class="faq-cat__icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></div>
        <h3 class="faq-cat__title">Visas</h3>
        <div class="faq">
          <details class="faq__item"><summary>¿Cómo funciona la asesoría de visa?</summary><div class="faq__body">Revisamos tu perfil, preparamos DS-160, te entrenamos para la entrevista y te acompañamos hasta el resultado.</div></details>
          <details class="faq__item"><summary>¿Qué países requieren visa?</summary><div class="faq__body">Estados Unidos, Canadá, Reino Unido, entre otros. Consulta nuestra sección de visas para más detalles.</div></details>
        </div>
      </div>
      <div class="faq-cat">
        <div class="faq-cat__icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"/></svg></div>
        <h3 class="faq-cat__title">Soporte</h3>
        <div class="faq">
          <details class="faq__item"><summary>¿Qué pasa si necesito cancelar?</summary><div class="faq__body">Cada paquete tiene su política específica visible antes de pagar. Ofrecemos seguro opcional de cancelación.</div></details>
          <details class="faq__item"><summary>¿Tienen atención 24/7?</summary><div class="faq__body">Sí, nuestro equipo está disponible antes, durante y después de tu viaje por WhatsApp y teléfono.</div></details>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- CTA final -->
<section class="section section--tight">
  <div class="container">
    <div class="cta-band reveal">
      <div><h2>¿Listo para tu próximo destino?</h2><p>Recibe una propuesta personalizada en menos de 24 horas, sin compromiso.</p></div>
      <div class="row"><a href="<?= BASE_URL ?>/contacto" class="btn btn--accent btn--lg">Hablar con un asesor</a><a href="<?= BASE_URL ?>/catalog" class="btn btn--white btn--lg">Explorar paquetes</a></div>
    </div>
  </div>
</section>

</main>

<!-- 6.6 Autocomplete JS -->
<script nonce="<?= htmlspecialchars(\App\Helper\SecurityHeaders::getNonce(), ENT_QUOTES, 'UTF-8') ?>">
document.addEventListener('DOMContentLoaded', () => {
  const input = document.getElementById('homeSearch');
  const results = document.getElementById('homeSearchResults');
  if (!input || !results) return;

  let debounce = null;

  input.addEventListener('input', () => {
    clearTimeout(debounce);
    const q = input.value.trim();
    if (q.length < 2) { results.style.display = 'none'; return; }

    debounce = setTimeout(() => {
      fetch('<?= BASE_URL ?>/api/paquetes/buscar?q=' + encodeURIComponent(q))
        .then(r => r.json())
        .then(data => {
          if (!data.length) { results.style.display = 'none'; return; }
          results.innerHTML = data.slice(0, 6).map(p => {
            const price = p.precio_anterior && parseFloat(p.precio_anterior) > parseFloat(p.precio_base)
              ? '<s style="color:#999;font-size:12px">S/ ' + Number(p.precio_anterior).toLocaleString() + '</s> '
              : '';
            return '<a href="<?= BASE_URL ?>/paquete/' + p.slug + '" class="search__result">'
              + '<strong>' + p.nombre + '</strong>'
              + '<span>' + (p.ciudad_nombre || '') + ' · ' + p.duracion_dias + ' días · ' + price + 'S/ ' + Number(p.precio_base).toLocaleString() + '</span>'
              + '</a>';
          }).join('');
          results.style.display = 'block';
        })
        .catch(() => { results.style.display = 'none'; });
    }, 300);
  });

  document.addEventListener('click', (e) => {
    if (!input.contains(e.target) && !results.contains(e.target)) {
      results.style.display = 'none';
    }
  });

  // Season tabs switching
  document.querySelectorAll('.season-tab').forEach(tab => {
    tab.addEventListener('click', () => {
      const idx = tab.getAttribute('data-season');
      document.querySelectorAll('.season-tab').forEach(t => {
        t.classList.remove('is-active');
        t.setAttribute('aria-selected', 'false');
      });
      document.querySelectorAll('.season-panel').forEach(p => p.classList.remove('is-active'));
      tab.classList.add('is-active');
      tab.setAttribute('aria-selected', 'true');
      const panel = document.querySelector('[data-season-panel="' + idx + '"]');
      if (panel) panel.classList.add('is-active');
    });
  });
});
</script>
