<!-- Funcion del archivo: Renderiza el inicio de sesion unificado por rol. -->
<section class="auth">
  <aside class="auth__visual">
    <div class="auth__brand-mark">VIAJES · AJT</div>
    <div>
      <h2>Tu próxima aventura comienza con un clic.</h2>
      <p>Accede a tu cuenta y continua automaticamente segun tu rol.</p>
      <ul class="auth__bullets">
        <li><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> Reservas y vouchers en un solo lugar</li>
        <li><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> Seguimiento en tiempo real de tu visa</li>
        <li><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> Soporte 24/7 antes, durante y después</li>
      </ul>
    </div>
    <div class="auth__quote">
      "Lo mejor de Viajes AJT es la asesoría humana. Resolvieron mi visa Schengen en tiempo récord."
      <small>— Lucía R., clienta verificada</small>
    </div>
  </aside>

  <form class="auth__form" id="loginForm" method="POST" action="<?= BASE_URL ?>/auth/login">
    <?= App\Helper\Csrf::insertInput() ?>
    <h1 class="auth__title">Bienvenido de vuelta 👋</h1>
    <p class="auth__sub">Ingresa con tu correo para continuar.</p>

    <!-- Mensajes Flash de Error/Éxito/Aviso -->
    <?php if (isset($_SESSION['error'])): ?>
      <div class="alert alert--error">
        <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
      </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['warning'])): ?>
      <script nonce="<?= htmlspecialchars(\App\Helper\SecurityHeaders::getNonce(), ENT_QUOTES, 'UTF-8') ?>">
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof window.toast === 'function') {
                window.toast({
                    type: 'warning',
                    title: 'Protección de cuenta',
                    text: '<?= addslashes($_SESSION['warning']); ?>'
                });
            } else {
                console.error('Toast notification script not loaded.');
            }
        });
      </script>
      <?php unset($_SESSION['warning']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['success'])): ?>
      <div class="alert alert--success">
        <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
      </div>
    <?php endif; ?>

    <!-- Pop Up flotante lateral (Toast pequeño de intentos por correo) -->
    <?php if (isset($_SESSION['rate_limit_warning'])): 
        $rlWarning = $_SESSION['rate_limit_warning'];
        unset($_SESSION['rate_limit_warning']);
    ?>
      <div id="sideRateLimitPopup" class="side-popup" role="alert" aria-live="assertive">
        <div class="side-popup__header">
          <div class="side-popup__icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
              <line x1="12" y1="9" x2="12" y2="13"/>
              <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
          </div>
          <span class="side-popup__title">Aviso de seguridad</span>
          <button type="button" class="side-popup__close" onclick="closeSidePopup()" aria-label="Cerrar">&times;</button>
        </div>
        <div class="side-popup__body">
          <?= htmlspecialchars($rlWarning['message']) ?>
        </div>
        <div class="side-popup__bar"></div>
      </div>

      <style>
        .side-popup {
          position: fixed;
          top: 24px;
          right: 24px;
          z-index: 99999;
          width: 320px;
          background: var(--c-surface, #ffffff);
          color: var(--c-ink-900, #0f172a);
          border: 1px solid rgba(245, 158, 11, 0.4);
          border-left: 5px solid #f59e0b;
          border-radius: 12px;
          padding: 14px 16px;
          box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
          animation: sidePopupSlideIn 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
          overflow: hidden;
        }

        .side-popup.is-leaving {
          animation: sidePopupSlideOut 0.4s cubic-bezier(0.7, 0, 0.84, 0) forwards;
        }

        .side-popup__header {
          display: flex;
          align-items: center;
          gap: 10px;
          margin-bottom: 6px;
        }

        .side-popup__icon {
          color: #d97706;
          display: flex;
          align-items: center;
          justify-content: center;
          flex-shrink: 0;
        }

        .side-popup__title {
          font-weight: 700;
          font-size: 0.88rem;
          color: var(--c-ink-900, #0f172a);
          flex: 1;
        }

        .side-popup__close {
          background: transparent;
          border: none;
          font-size: 20px;
          line-height: 1;
          color: var(--c-ink-500, #64748b);
          cursor: pointer;
          padding: 0 4px;
          transition: color 0.15s ease;
        }

        .side-popup__close:hover {
          color: var(--c-ink-900, #0f172a);
        }

        .side-popup__body {
          font-size: 0.85rem;
          color: var(--c-ink-700, #334155);
          line-height: 1.4;
          padding-left: 28px;
        }

        .side-popup__bar {
          position: absolute;
          bottom: 0;
          left: 0;
          height: 3px;
          background: #f59e0b;
          width: 100%;
          animation: sidePopupTimer 4.5s linear forwards;
        }

        @keyframes sidePopupSlideIn {
          from {
            opacity: 0;
            transform: translateX(100%) scale(0.95);
          }
          to {
            opacity: 1;
            transform: translateX(0) scale(1);
          }
        }

        @keyframes sidePopupSlideOut {
          from {
            opacity: 1;
            transform: translateX(0);
          }
          to {
            opacity: 0;
            transform: translateX(120%);
          }
        }

        @keyframes sidePopupTimer {
          from { width: 100%; }
          to { width: 0%; }
        }
      </style>

      <script nonce="<?= htmlspecialchars(\App\Helper\SecurityHeaders::getNonce(), ENT_QUOTES, 'UTF-8') ?>">
        function closeSidePopup() {
          var pop = document.getElementById('sideRateLimitPopup');
          if (pop && !pop.classList.contains('is-leaving')) {
            pop.classList.add('is-leaving');
            setTimeout(function() { if (pop) pop.remove(); }, 400);
          }
        }

        document.addEventListener('DOMContentLoaded', function() {
          setTimeout(function() {
            closeSidePopup();
          }, 4500);
        });
      </script>
    <?php endif; ?>

    <div class="auth__divider">Ingresa con tu correo</div>

    <div class="field">
      <label for="lEmail">Correo electrónico</label>
      <input class="input" type="email" id="lEmail" name="email" required placeholder="tu@correo.com" autocomplete="email">
    </div>
    
    <div class="field" style="margin-top:14px">
      <label for="lPass" style="display:flex;justify-content:space-between">
        Contraseña 
        <a href="<?= BASE_URL ?>/contacto" style="color:var(--c-primary-700);font-weight:600">¿La olvidaste?</a>
      </label>
      <input class="input" type="password" id="lPass" name="password" required placeholder="••••••••" autocomplete="current-password" minlength="6">
    </div>
    
    <label class="check" style="margin:16px 0">
      <input type="checkbox" name="remember"> Mantener la sesión iniciada
    </label>

    <button type="submit" class="btn btn--primary btn--lg btn--block">Ingresar</button>

    <p class="auth__foot">¿No tienes cuenta aún? <a href="<?= BASE_URL ?>/auth/registro">Crea una en 30 segundos →</a></p>
  </form>
</section>
