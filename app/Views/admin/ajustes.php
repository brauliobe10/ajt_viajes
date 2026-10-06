<!-- Funcion del archivo: Renderiza ajustes administrativos de agencia, pagos y seguridad. -->
<main id="main-content" class="amain">
  <div class="atopbar">
    <div style="display:flex;align-items:center;gap:10px">
      <button class="amenu-toggle" id="aMenuToggle" aria-label="Menú"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M4 12h16M4 17h16"/></svg></button>
      <div>
        <h1>Ajustes</h1>
        <p>Configuración general de la agencia y plataforma</p>
      </div>
    </div>
  </div>

  <!-- Alertas de sesión -->
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

  <div class="dual">
    <!-- Empresa -->
    <div class="panel">
      <div class="panel__hd"><h3>Empresa</h3></div>
      <form action="<?= BASE_URL ?>/admin/ajustes" method="POST" style="display:grid;gap:14px">
        <?= \App\Helper\Csrf::insertInput() ?>
        <div class="field">
          <label for="agencia_nombre">Nombre comercial</label>
          <input class="input" type="text" id="agencia_nombre" name="agencia_nombre"
                 value="<?= htmlspecialchars(\App\Helper\Config::getString('agencia_nombre', 'Viajes AJT')) ?>">
        </div>
        <div class="field">
          <label for="agencia_razon_social">Razón social</label>
          <input class="input" type="text" id="agencia_razon_social" name="agencia_razon_social"
                 value="<?= htmlspecialchars(\App\Helper\Config::getString('agencia_razon_social')) ?>">
        </div>
        <div class="field">
          <label for="agencia_ruc">RUC</label>
          <input class="input" type="text" id="agencia_ruc" name="agencia_ruc"
                 value="<?= htmlspecialchars(\App\Helper\Config::getString('agencia_ruc')) ?>">
        </div>
        <div class="field">
          <label for="agencia_direccion">Dirección</label>
          <input class="input" type="text" id="agencia_direccion" name="agencia_direccion"
                 value="<?= htmlspecialchars(\App\Helper\Config::getString('agencia_direccion')) ?>">
        </div>
        <div class="field">
          <label for="agencia_horario">Horario de atención</label>
          <input class="input" type="text" id="agencia_horario" name="agencia_horario"
                 value="<?= htmlspecialchars(\App\Helper\Config::getString('agencia_horario')) ?>">
        </div>
        <div><button type="submit" class="btn btn--primary">Guardar empresa</button></div>
      </form>
    </div>

    <!-- Contacto -->
    <div class="panel">
      <div class="panel__hd"><h3>Contacto</h3></div>
      <form action="<?= BASE_URL ?>/admin/ajustes" method="POST" style="display:grid;gap:14px">
        <?= \App\Helper\Csrf::insertInput() ?>
        <div class="field">
          <label for="agencia_telefono">Teléfono</label>
          <input class="input" type="text" id="agencia_telefono" name="agencia_telefono"
                 value="<?= htmlspecialchars(\App\Helper\Config::getString('agencia_telefono')) ?>">
        </div>
        <div class="field">
          <label for="agencia_whatsapp">WhatsApp (sin + ni espacios)</label>
          <input class="input" type="text" id="agencia_whatsapp" name="agencia_whatsapp"
                 value="<?= htmlspecialchars(\App\Helper\Config::getString('agencia_whatsapp')) ?>">
        </div>
        <div class="field">
          <label for="agencia_email">Correo electrónico</label>
          <input class="input" type="email" id="agencia_email" name="agencia_email"
                 value="<?= htmlspecialchars(\App\Helper\Config::getString('agencia_email')) ?>">
        </div>
        <div><button type="submit" class="btn btn--primary">Guardar contacto</button></div>
      </form>
    </div>

    <!-- Redes sociales -->
    <div class="panel">
      <div class="panel__hd"><h3>Redes sociales</h3></div>
      <form action="<?= BASE_URL ?>/admin/ajustes" method="POST" style="display:grid;gap:14px">
        <?= \App\Helper\Csrf::insertInput() ?>
        <div class="field">
          <label for="agencia_facebook">Facebook URL</label>
          <input class="input" type="url" id="agencia_facebook" name="agencia_facebook"
                 value="<?= htmlspecialchars(\App\Helper\Config::getString('agencia_facebook')) ?>">
        </div>
        <div class="field">
          <label for="agencia_instagram">Instagram URL</label>
          <input class="input" type="url" id="agencia_instagram" name="agencia_instagram"
                 value="<?= htmlspecialchars(\App\Helper\Config::getString('agencia_instagram')) ?>">
        </div>
        <div class="field">
          <label for="agencia_tiktok">TikTok URL</label>
          <input class="input" type="url" id="agencia_tiktok" name="agencia_tiktok"
                 value="<?= htmlspecialchars(\App\Helper\Config::getString('agencia_tiktok')) ?>">
        </div>
        <div class="field">
          <label for="agencia_youtube">YouTube URL</label>
          <input class="input" type="url" id="agencia_youtube" name="agencia_youtube"
                 value="<?= htmlspecialchars(\App\Helper\Config::getString('agencia_youtube')) ?>">
        </div>
        <div><button type="submit" class="btn btn--primary">Guardar redes</button></div>
      </form>
    </div>

    <!-- Pagos / Métodos de pago -->
    <div class="panel">
      <div class="panel__hd"><h3>Métodos de pago</h3></div>
      <form action="<?= BASE_URL ?>/admin/ajustes" method="POST" style="display:grid;gap:14px">
        <?= \App\Helper\Csrf::insertInput() ?>

        <details open>
          <summary style="cursor:pointer;font-weight:var(--fw-bold);font-size:var(--fs-14);margin-bottom:10px">Yape</summary>
          <div style="display:grid;gap:14px;padding-left:12px">
            <div class="field">
              <label for="yape_numero">Número de Yape</label>
              <input class="input" type="text" id="yape_numero" name="yape_numero"
                     value="<?= htmlspecialchars(\App\Helper\Config::getString('yape_numero')) ?>">
            </div>
            <div class="field">
              <label for="yape_titular">Titular de Yape</label>
              <input class="input" type="text" id="yape_titular" name="yape_titular"
                     value="<?= htmlspecialchars(\App\Helper\Config::getString('yape_titular')) ?>">
            </div>
          </div>
        </details>

        <details open>
          <summary style="cursor:pointer;font-weight:var(--fw-bold);font-size:var(--fs-14);margin-bottom:10px">Plin</summary>
          <div style="display:grid;gap:14px;padding-left:12px">
            <div class="field">
              <label for="plin_numero">Número de Plin</label>
              <input class="input" type="text" id="plin_numero" name="plin_numero"
                     value="<?= htmlspecialchars(\App\Helper\Config::getString('plin_numero')) ?>">
            </div>
            <div class="field">
              <label for="plin_titular">Titular de Plin</label>
              <input class="input" type="text" id="plin_titular" name="plin_titular"
                     value="<?= htmlspecialchars(\App\Helper\Config::getString('plin_titular')) ?>">
            </div>
          </div>
        </details>

        <details open>
          <summary style="cursor:pointer;font-weight:var(--fw-bold);font-size:var(--fs-14);margin-bottom:10px">Transferencia bancaria</summary>
          <div style="display:grid;gap:14px;padding-left:12px">
            <div class="field">
              <label for="banco_nombre">Nombre del banco</label>
              <input class="input" type="text" id="banco_nombre" name="banco_nombre"
                     value="<?= htmlspecialchars(\App\Helper\Config::getString('banco_nombre')) ?>">
            </div>
            <div class="field">
              <label for="banco_cuenta">Número de cuenta</label>
              <input class="input" type="text" id="banco_cuenta" name="banco_cuenta"
                     value="<?= htmlspecialchars(\App\Helper\Config::getString('banco_cuenta')) ?>">
            </div>
            <div class="field">
              <label for="banco_cci">Código CCI</label>
              <input class="input" type="text" id="banco_cci" name="banco_cci"
                     value="<?= htmlspecialchars(\App\Helper\Config::getString('banco_cci')) ?>">
            </div>
          </div>
        </details>

        <div><button type="submit" class="btn btn--primary">Guardar métodos de pago</button></div>
      </form>
    </div>
  </div>

  <!-- Preferencias del sistema -->
  <div class="panel" style="max-width:600px">
    <div class="panel__hd"><h3>Preferencias del sistema</h3></div>
    <form action="<?= BASE_URL ?>/admin/ajustes" method="POST" style="display:grid;gap:18px">
      <?= \App\Helper\Csrf::insertInput() ?>
      <div class="field">
        <label for="dias_anticipacion">Días de anticipación para reservas</label>
        <input class="input" type="number" id="dias_anticipacion" name="dias_anticipacion_reserva"
               value="<?= htmlspecialchars(\App\Helper\Config::getString('dias_anticipacion_reserva', '30')) ?>"
               min="1" max="365" required>
        <span style="color:var(--c-ink-500);font-size:var(--fs-12)">Los clientes solo podrán reservar con esta cantidad de días de anticipación.</span>
      </div>
      <div>
        <button type="submit" class="btn btn--primary">Guardar configuración</button>
      </div>
    </form>
  </div>

  <!-- Cambio de contraseña -->
  <div class="panel" style="max-width:600px">
    <div class="panel__hd"><h3>Cambiar contraseña</h3></div>
    <form action="<?= BASE_URL ?>/admin/ajustes/cambiar-password" method="POST" style="display:grid;gap:14px">
      <?= \App\Helper\Csrf::insertInput() ?>
      <div class="field">
        <label for="pwd_actual">Contraseña actual *</label>
        <input class="input" type="password" id="pwd_actual" name="password_actual" required minlength="6">
      </div>
      <div class="field">
        <label for="pwd_nueva">Nueva contraseña *</label>
        <input class="input" type="password" id="pwd_nueva" name="password_nueva" required minlength="6">
      </div>
      <div class="field">
        <label for="pwd_confirm">Confirmar nueva contraseña *</label>
        <input class="input" type="password" id="pwd_confirm" name="password_confirm" required minlength="6">
      </div>
      <div>
        <button type="submit" class="btn btn--primary">Actualizar contraseña</button>
      </div>
    </form>
  </div>
</main>
