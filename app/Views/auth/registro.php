<!-- Funcion del archivo: Renderiza el registro publico de nuevos clientes. -->
<section class="auth">
  <aside class="auth__visual" style="background:linear-gradient(135deg,rgba(7,13,58,.85),rgba(58,81,209,.5)),url('https://images.unsplash.com/photo-1530789253388-582c481c54b0?auto=format&fit=crop&w=1400&q=80') center/cover">
    <div class="auth__brand-mark">VIAJES · AJT</div>
    <div>
      <h2>Únete a más de 12,000 viajeros felices.</h2>
      <p>Crea tu cuenta gratis y desbloquea promociones exclusivas, alertas de ofertas y un asesor de viajes dedicado.</p>
      <ul class="auth__bullets">
        <li><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> -10% de descuento en tu primera reserva</li>
        <li><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> Asesor personal vía WhatsApp</li>
        <li><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> Acceso prioritario a paquetes nuevos</li>
      </ul>
    </div>
    <div class="auth__quote"><svg width="14" height="14" viewBox="0 0 24 24" fill="var(--c-accent-500)" stroke="none" style="display:inline-block;vertical-align:middle;flex-shrink:0"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg><svg width="14" height="14" viewBox="0 0 24 24" fill="var(--c-accent-500)" stroke="none" style="display:inline-block;vertical-align:middle;flex-shrink:0"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg><svg width="14" height="14" viewBox="0 0 24 24" fill="var(--c-accent-500)" stroke="none" style="display:inline-block;vertical-align:middle;flex-shrink:0"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg><svg width="14" height="14" viewBox="0 0 24 24" fill="var(--c-accent-500)" stroke="none" style="display:inline-block;vertical-align:middle;flex-shrink:0"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg><svg width="14" height="14" viewBox="0 0 24 24" fill="var(--c-accent-500)" stroke="none" style="display:inline-block;vertical-align:middle;flex-shrink:0"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg> 4.9/5 en reseñas verificadas — Google & TripAdvisor.</div>
  </aside>

  <form class="auth__form" action="<?= BASE_URL ?>/auth/registro" method="POST" id="regForm">
    <?= App\Helper\Csrf::insertInput() ?>
    <h1 class="auth__title">Crea tu cuenta</h1>
    <p class="auth__sub">Toma menos de 30 segundos. Sin tarjeta de crédito.</p>

    <?php if (isset($_SESSION['error'])): ?>
      <div class="alert alert--error">
        <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
      </div>
    <?php endif; ?>

    <div class="co__grid-2">
      <div class="field">
        <label for="rName">Nombres *</label>
        <input class="input" id="rName" name="nombres" required placeholder="María" 
               value="<?= htmlspecialchars($_POST['nombres'] ?? '') ?>"
               pattern="[\p{L}\s]+" title="Solo letras y espacios" minlength="2" maxlength="100">
      </div>
      <div class="field">
        <label for="rLast">Apellidos *</label>
        <input class="input" id="rLast" name="apellidos" required placeholder="González" 
               value="<?= htmlspecialchars($_POST['apellidos'] ?? '') ?>"
               pattern="[\p{L}\s]+" title="Solo letras y espacios" minlength="2" maxlength="100">
      </div>
    </div>
    <div class="field" style="margin-top:14px">
      <label for="rEmail">Correo electrónico *</label>
      <input class="input" type="email" id="rEmail" name="email" required placeholder="tu@correo.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      <span id="emailFeedback" style="display:none;font-size:var(--fs-12);margin-top:4px"></span>
    </div>
    <div class="field co__grid-2" style="margin-top:14px">
      <div class="field">
        <label for="rPhone">Teléfono / WhatsApp</label>
        <input class="input" type="tel" id="rPhone" name="telefono" placeholder="999888777" 
               value="<?= htmlspecialchars($_POST['telefono'] ?? '') ?>"
               pattern="[0-9]{7,15}" maxlength="15" inputmode="numeric"
               title="Solo números, entre 7 y 15 dígitos">
      </div>
      <div class="field">
        <label for="rDni">DNI / Pasaporte *</label>
        <input class="input" id="rDni" name="dni_pasaporte" required placeholder="Nro de Documento" 
               value="<?= htmlspecialchars($_POST['dni_pasaporte'] ?? '') ?>"
               minlength="6" maxlength="20" title="Entre 6 y 20 caracteres">
      </div>
    </div>
    <div class="field" style="margin-top:14px">
      <label for="rPass">Contraseña *</label>
      <input class="input" type="password" id="rPass" name="password" required placeholder="Mínimo 6 caracteres" minlength="6">
    </div>

    <label class="check" style="margin:18px 0">
      <input type="checkbox" name="acepta_terminos" required> Acepto los <a href="<?= BASE_URL ?>/terminos" target="_blank" style="color:var(--c-primary-700);font-weight:600">Términos</a> y la <a href="<?= BASE_URL ?>/privacidad" target="_blank" style="color:var(--c-primary-700);font-weight:600">Política de privacidad</a>.
    </label>

    <button type="submit" class="btn btn--accent btn--lg btn--block">Crear cuenta gratis</button>

    <p class="auth__foot">¿Ya tienes cuenta? <a href="<?= BASE_URL ?>/login">Inicia sesión →</a></p>
  </form>
</section>

<script nonce="<?= htmlspecialchars(\App\Helper\SecurityHeaders::getNonce(), ENT_QUOTES, 'UTF-8') ?>">
$(function() {
  var $emailInput = $('#rEmail');
  var $feedback = $('#emailFeedback');
  var timeout;

  // Validación de email en vivo via $.ajax() (endpoint /api/validar-email)
  $emailInput.on('blur', function() {
    var email = $.trim($emailInput.val());
    if (!email || email.indexOf('@') === -1) {
      $feedback.hide();
      return;
    }

    clearTimeout(timeout);
    timeout = setTimeout(function() {
      $.ajax({
        url: '<?= BASE_URL ?>/api/validar-email',
        method: 'GET',
        dataType: 'json',
        data: { email: email },
        success: function(data) {
          $feedback.show();
          if (data.valid) {
            $feedback.text('✓ Correo disponible').css('color', 'var(--c-success-600)');
          } else {
            $feedback.text('✗ ' + (data.message || 'Correo ya registrado')).css('color', 'var(--c-danger-600)');
          }
        },
        error: function() {
          $feedback.hide();
        }
      });
    }, 400);
  });

  $emailInput.on('input', function() {
    $feedback.hide();
  });

  // Sanitización de teléfono: solo números, max 15 dígitos via jQuery
  var $phoneInput = $('#rPhone');
  if ($phoneInput.length) {
    $phoneInput.on('input', function() {
      $phoneInput.val($phoneInput.val().replace(/[^0-9]/g, '').slice(0, 15));
    });
  }
});
</script>
