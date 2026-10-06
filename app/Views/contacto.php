<!-- Funcion del archivo: Renderiza datos de contacto, mapa y formulario de mensaje. -->
<section class="ct-hero" style="background:linear-gradient(135deg,var(--c-primary-900),var(--c-primary-700));color:#fff;padding:56px 0 32px">
  <div class="container">
    <nav class="breadcrumbs" style="margin-bottom:10px">
      <a href="<?= BASE_URL ?>/" style="color:rgba(255,255,255,.7)">Inicio</a>
      <span class="sep">/</span>
      <span class="current" style="color:#fff">Contacto</span>
    </nav>
    <h1 style="color:#fff">Hablemos de tu próximo viaje</h1>
    <p style="color:rgba(255,255,255,.82);max-width:60ch">Nuestros asesores responden en menos de 1 hora en horario de oficina. WhatsApp disponible 24/7.</p>
  </div>
</section>

<div class="container">
  <div class="ct-cards" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px;margin-top:-32px;position:relative;z-index:2">
    <div class="ct-card" style="background:var(--c-surface);border:1px solid var(--c-border);border-radius:var(--r-lg);padding:22px;box-shadow:var(--sh-2);display:flex;flex-direction:column;gap:8px">
      <div style="width:44px;height:44px;border-radius:var(--r-md);background:var(--c-primary-50);color:var(--c-primary-700);display:grid;place-items:center;margin-bottom:6px">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 1 1 18 0Z"/><circle cx="12" cy="10" r="3"/></svg>
      </div>
      <h3 style="margin:0;font-size:var(--fs-16)">Oficina principal</h3>
      <p style="margin:0;font-size:var(--fs-14);color:var(--c-ink-600)"><?= nl2br(htmlspecialchars(\App\Helper\Config::getString('agencia_direccion', 'Pendiente de configurar'))) ?></p>
      <?php 
        $lat = \App\Helper\Config::getString('agencia_latitud');
        $lng = \App\Helper\Config::getString('agencia_longitud');
        $mapsUrl = ($lat && $lng) 
          ? "https://www.google.com/maps?q={$lat},{$lng}" 
          : null;
      ?>
      <?php if ($mapsUrl): ?>
      <a href="<?= htmlspecialchars($mapsUrl) ?>" target="_blank" rel="noopener" style="font-weight:var(--fw-semibold);color:var(--c-primary-700)">Ver en Google Maps →</a>
      <?php endif; ?>
    </div>
    <div class="ct-card" style="background:var(--c-surface);border:1px solid var(--c-border);border-radius:var(--r-lg);padding:22px;box-shadow:var(--sh-2);display:flex;flex-direction:column;gap:8px">
      <div style="width:44px;height:44px;border-radius:var(--r-md);background:var(--c-primary-50);color:var(--c-primary-700);display:grid;place-items:center;margin-bottom:6px">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.37 1.9.72 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.35 1.85.59 2.81.72A2 2 0 0 1 22 16.92Z"/></svg>
      </div>
      <h3 style="margin:0;font-size:var(--fs-16)">Teléfono / WhatsApp</h3>
      <p style="margin:0;font-size:var(--fs-14);color:var(--c-ink-600)"><?= nl2br(htmlspecialchars(\App\Helper\Config::getString('agencia_telefono', 'Pendiente de configurar'))) ?></p>
      <?php if ($waNumber = \App\Helper\Config::getString('agencia_whatsapp')): ?>
      <a href="https://wa.me/<?= htmlspecialchars($waNumber) ?>" target="_blank" rel="noopener" style="font-weight:var(--fw-semibold);color:var(--c-primary-700)">Escribir por WhatsApp →</a>
      <?php endif; ?>
    </div>
    <div class="ct-card" style="background:var(--c-surface);border:1px solid var(--c-border);border-radius:var(--r-lg);padding:22px;box-shadow:var(--sh-2);display:flex;flex-direction:column;gap:8px">
      <div style="width:44px;height:44px;border-radius:var(--r-md);background:var(--c-primary-50);color:var(--c-primary-700);display:grid;place-items:center;margin-bottom:6px">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
      </div>
      <h3 style="margin:0;font-size:var(--fs-16)">Correo</h3>
      <p style="margin:0;font-size:var(--fs-14);color:var(--c-ink-600)"><?= nl2br(htmlspecialchars(\App\Helper\Config::getString('agencia_email', 'Pendiente de configurar'))) ?></p>
      <?php if ($email = \App\Helper\Config::getString('agencia_email')): ?>
      <a href="mailto:<?= htmlspecialchars($email) ?>" style="font-weight:var(--fw-semibold);color:var(--c-primary-700)">Enviar correo →</a>
      <?php endif; ?>
    </div>
    <div class="ct-card" style="background:var(--c-surface);border:1px solid var(--c-border);border-radius:var(--r-lg);padding:22px;box-shadow:var(--sh-2);display:flex;flex-direction:column;gap:8px">
      <div style="width:44px;height:44px;border-radius:var(--r-md);background:var(--c-success-100);color:var(--c-success-600);display:grid;place-items:center;margin-bottom:6px">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
      </div>
      <h3 style="margin:0;font-size:var(--fs-16)">Horarios</h3>
      <p style="margin:0;font-size:var(--fs-14);color:var(--c-ink-600)"><?= nl2br(htmlspecialchars(\App\Helper\Config::getString('agencia_horario', 'Pendiente de configurar'))) ?></p>
    </div>
  </div>
</div>

<main id="main-content" class="container section" style="padding-top:48px;padding-bottom:48px">
  <div style="display:grid;grid-template-columns:1.1fr 1fr;gap:32px">
    <div style="position:relative;border-radius:var(--r-lg);overflow:hidden;border:1px solid var(--c-border);box-shadow:var(--sh-1);min-height:420px">
      <?php 
        $lat = \App\Helper\Config::getString('agencia_latitud');
        $lng = \App\Helper\Config::getString('agencia_longitud');
      ?>
      <?php if ($lat && $lng): ?>
      <div id="contactMap" style="position:absolute;inset:0;width:100%;height:100%;min-height:420px;z-index:1"></div>
      <noscript>
        <iframe src="https://www.openstreetmap.org/export/embed.html?bbox=<?= urlencode(($lng - 0.01) . ',' . ($lat - 0.007) . ',' . ($lng + 0.01) . ',' . ($lat + 0.007)) ?>&amp;layer=mapnik&amp;marker=<?= urlencode("{$lat},{$lng}") ?>" width="100%" height="100%" style="position:absolute;inset:0;display:block;width:100%;height:100%;min-height:420px;border:0" loading="lazy" allowfullscreen title="Ubicación de la oficina principal de Viajes AJT"></iframe>
      </noscript>
      <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.min.css" />
      <script nonce="<?= htmlspecialchars(\App\Helper\SecurityHeaders::getNonce(), ENT_QUOTES, 'UTF-8') ?>" src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.min.js"></script>
      <script nonce="<?= htmlspecialchars(\App\Helper\SecurityHeaders::getNonce(), ENT_QUOTES, 'UTF-8') ?>">
        document.addEventListener('DOMContentLoaded', function() {
          var map = L.map('contactMap', { scrollWheelZoom: false }).setView([<?= $lat ?>, <?= $lng ?>], 16);
          L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            maxZoom: 19
          }).addTo(map);
          L.marker([<?= $lat ?>, <?= $lng ?>]).addTo(map)
            .bindPopup('<?= addslashes(\App\Helper\Config::getString('agencia_nombre', 'Nuestra oficina')) ?>')
            .openPopup();
        });
      </script>
      <?php else: ?>
      <div style="min-height:420px;display:grid;place-items:center;background:var(--c-surface-muted);color:var(--c-ink-500);font-size:var(--fs-14)">Pendiente de configurar</div>
      <?php endif; ?>
    </div>

    <?php
      $formData = $_SESSION['form_data'] ?? [];
      unset($_SESSION['form_data']);
    ?>

    <div style="background:var(--c-surface);border:1px solid var(--c-border);border-radius:var(--r-lg);padding:28px;box-shadow:var(--sh-2)">
      <h2 style="margin-bottom:6px">Envíanos un mensaje</h2>
      <p style="font-size:var(--fs-14);color:var(--c-ink-500);margin-bottom:18px">Responderemos en menos de 24 h hábiles.</p>

      <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert--error">
          <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
        </div>
      <?php endif; ?>

      <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert--success">
          <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
        </div>
      <?php endif; ?>

      <form action="<?= BASE_URL ?>/contacto" method="POST">
        <?= \App\Helper\Csrf::insertInput() ?>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
          <div class="field">
            <label for="ctNombre">Nombre completo *</label>
            <input class="input" id="ctNombre" name="nombre" type="text" required placeholder="Tu nombre"
                   value="<?= htmlspecialchars($formData['nombre'] ?? '') ?>">
          </div>
          <div class="field">
            <label for="ctEmail">Correo electrónico *</label>
            <input class="input" id="ctEmail" name="email" type="email" required placeholder="tu@correo.com"
                   value="<?= htmlspecialchars($formData['email'] ?? '') ?>">
          </div>
          <div class="field">
            <label for="ctTel">Teléfono</label>
            <input class="input" id="ctTel" name="telefono" type="tel" placeholder="+51 999 999 999"
                   value="<?= htmlspecialchars($formData['telefono'] ?? '') ?>">
          </div>
          <div class="field">
            <label for="ctAsunto">Motivo *</label>
            <select class="select" id="ctAsunto" name="asunto" required>
              <option value="">Seleccionar motivo…</option>
              <option value="Paquete turístico" <?= (isset($formData['asunto']) && $formData['asunto'] === 'Paquete turístico') ? 'selected' : '' ?>>Paquete turístico</option>
              <option value="Asesoría de visa" <?= (isset($formData['asunto']) && $formData['asunto'] === 'Asesoría de visa') ? 'selected' : '' ?>>Asesoría de visa</option>
              <option value="Cotización corporativa" <?= (isset($formData['asunto']) && $formData['asunto'] === 'Cotización corporativa') ? 'selected' : '' ?>>Cotización corporativa</option>
              <option value="Otro" <?= (isset($formData['asunto']) && $formData['asunto'] === 'Otro') ? 'selected' : '' ?>>Otro</option>
            </select>
          </div>
        </div>

        <div class="field" style="margin-top:14px">
          <label for="ctMensaje">Mensaje *</label>
          <textarea class="textarea" id="ctMensaje" name="mensaje" rows="5" required placeholder="Cuéntanos a dónde quieres ir…"><?= htmlspecialchars($formData['mensaje'] ?? '') ?></textarea>
        </div>

        <label class="check" style="margin-top:14px;display:flex;align-items:center;gap:8px">
          <input type="checkbox" name="acepta_privacidad" required> Acepto la política de privacidad
        </label>

        <div style="margin-top:18px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px">
          <span style="font-size:var(--fs-13);color:var(--c-ink-500)"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle;flex-shrink:0"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg> Tus datos están protegidos</span>
          <button type="submit" class="btn btn--primary btn--lg">Enviar mensaje</button>
        </div>
      </form>
    </div>
  </div>
</main>
