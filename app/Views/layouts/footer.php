<!-- Funcion del archivo: Renderiza pie de pagina publico y acceso rapido a WhatsApp. -->
<footer class="footer" role="contentinfo">
  <div class="footer__grid">
    <div>
      <div class="footer__brand"><span class="nav__brand-mark">AJT</span>Viajes AJT</div>
      <p class="footer__about">Conectamos confianza, creamos experiencias. Paquetes nacionales e internacionales, asesoría de visas y reservas seguras.</p>
      <div class="footer__socials">
        <?php if ($fbUrl = \App\Helper\Config::getString('agencia_facebook')): ?>
        <a href="<?= htmlspecialchars($fbUrl) ?>" target="_blank" rel="noopener" aria-label="Facebook"><svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M13 22v-8h3l1-4h-4V7.5c0-1.1.3-1.8 1.9-1.8H17V2.1A26 26 0 0 0 14.4 2C11.9 2 10 3.5 10 6.7V10H7v4h3v8h3Z"/></svg></a>
        <?php endif; ?>
        <?php if ($igUrl = \App\Helper\Config::getString('agencia_instagram')): ?>
        <a href="<?= htmlspecialchars($igUrl) ?>" target="_blank" rel="noopener" aria-label="Instagram"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/></svg></a>
        <?php endif; ?>
        <?php if ($tkUrl = \App\Helper\Config::getString('agencia_tiktok')): ?>
        <a href="<?= htmlspecialchars($tkUrl) ?>" target="_blank" rel="noopener" aria-label="TikTok"><svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-2.88 2.64 2.89 2.89 0 0 1-2.88-2.89 2.89 2.89 0 0 1 2.88-2.89c.35 0 .69.06 1 .18V8.6a6.25 6.25 0 0 0-1-.08A6.34 6.34 0 0 0 4.1 17.5a6.34 6.34 0 0 0 6.34 6.34 6.32 6.32 0 0 0 6.04-4.28 6.28 6.28 0 0 0 .28-1.85V9.19a7.85 7.85 0 0 0 4.11 1.2v-3.7c-.3 0-.6-.03-.88-.1Z"/></svg></a>
        <?php endif; ?>
        <?php if ($ytUrl = \App\Helper\Config::getString('agencia_youtube')): ?>
        <a href="<?= htmlspecialchars($ytUrl) ?>" target="_blank" rel="noopener" aria-label="YouTube"><svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M23.5 6.2a2.89 2.89 0 0 0-2-2c-1.8-.5-9-.5-9-.5s-7.2 0-9 .5a2.89 2.89 0 0 0-2 2A30.2 30.2 0 0 0 1 12a30.2 30.2 0 0 0 .5 5.8 2.89 2.89 0 0 0 2 2c1.8.5 9 .5 9 .5s7.2 0 9-.5a2.89 2.89 0 0 0 2-2A30.2 30.2 0 0 0 24 12a30.2 30.2 0 0 0-.5-5.8ZM9.5 15.5V8.5l6 3.5-6 3.5Z"/></svg></a>
        <?php endif; ?>
      </div>
    </div>
    <div>
      <h4 class="footer__title">Explora</h4>
      <ul class="footer__list">
        <li><a href="<?= BASE_URL ?>/catalog">Paquetes</a></li>
        <li><a href="<?= BASE_URL ?>/visas">Visas</a></li>
        <li><a href="<?= BASE_URL ?>/contacto">Contacto</a></li>
        <li><a href="<?= BASE_URL ?>/auth/registro">Crear cuenta</a></li>
      </ul>
    </div>
    <div>
      <h4 class="footer__title">Información legal</h4>
      <ul class="footer__list">
        <li><a href="<?= BASE_URL ?>/terminos">Términos y condiciones</a></li>
        <li><a href="<?= BASE_URL ?>/privacidad">Política de privacidad</a></li>
        <li><a href="<?= BASE_URL ?>/cancelaciones">Política de cancelación</a></li>
        <li><a href="<?= BASE_URL ?>/reclamaciones">Libro de reclamaciones</a></li>
      </ul>
    </div>
    <div>
      <h4 class="footer__title">Contacto</h4>
      <ul class="footer__list">
        <li class="footer__contact"><svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 1 1 18 0Z"/><circle cx="12" cy="10" r="3"/></svg><?= htmlspecialchars(\App\Helper\Config::getString('agencia_direccion', 'Pendiente de configurar')) ?></li>
        <?php $telefono = \App\Helper\Config::getString('agencia_telefono', ''); ?>
        <li class="footer__contact"><svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92V21a1 1 0 0 1-1.1 1A19 19 0 0 1 2 4.1 1 1 0 0 1 3 3h4.09a1 1 0 0 1 1 .75l1 4a1 1 0 0 1-.29 1L7.21 10.4a16 16 0 0 0 6.39 6.39l1.63-1.6a1 1 0 0 1 1-.27l4 1a1 1 0 0 1 .77 1Z"/></svg><?php if ($telefono): ?><a href="tel:<?= htmlspecialchars(preg_replace('/[^+\d]/', '', $telefono)) ?>"><?= htmlspecialchars($telefono) ?></a><?php else: ?>Pendiente de configurar<?php endif; ?></li>
        <?php $email = \App\Helper\Config::getString('agencia_email', ''); ?>
        <li class="footer__contact"><svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 6-10 7L2 6"/></svg><?php if ($email): ?><a href="mailto:<?= htmlspecialchars($email) ?>"><?= htmlspecialchars($email) ?></a><?php else: ?>Pendiente de configurar<?php endif; ?></li>
      </ul>
    </div>
  </div>

  <!-- Medios de pago y certificaciones -->
  <div class="footer__trust">
    <div class="footer__payments">
      <h4 class="footer__title">Medios de pago</h4>
      <div class="footer__pay-logos">
        <span class="footer__pay-badge footer__pay-badge--yape" title="Yape">Yape</span>
        <span class="footer__pay-badge footer__pay-badge--plin" title="Plin">Plin</span>
        <span class="footer__pay-badge footer__pay-badge--visa" title="Visa">VISA</span>
        <span class="footer__pay-badge footer__pay-badge--mc" title="Mastercard">MC</span>
        <span class="footer__pay-badge" title="Transferencia bancaria">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18M3 10h18M5 6l7-3 7 3M4 10v11M20 10v11M8 14v3M12 14v3M16 14v3"/></svg>
          Transferencia
        </span>
      </div>
      <p class="footer__pay-note">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        Pago seguro con cifrado SSL
      </p>
    </div>
    <div class="footer__certs">
      <h4 class="footer__title">Certificaciones</h4>
      <div class="footer__cert-items">
        <?php if ($mincetur = \App\Helper\Config::getString('agencia_mincetur')): ?>
        <span class="footer__cert-badge" title="MINCETUR">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
          MINCETUR <?= htmlspecialchars($mincetur) ?>
        </span>
        <?php endif; ?>
        <span class="footer__cert-badge" title="Operador registrado">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
          Operador turístico registrado
        </span>
        <span class="footer__cert-badge" title="Atención personalizada">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"/></svg>
          Asesoría personalizada
        </span>
      </div>
    </div>
  </div>

  <div class="footer__bottom">
    <span>© 2026 Viajes AJT. Todos los derechos reservados.</span>
    <span>Hecho con orgullo en Perú 🇵🇪</span>
  </div>
</footer>

<?php if ($waNumber = \App\Helper\Config::getString('agencia_whatsapp')): ?>
<a href="https://wa.me/<?= htmlspecialchars($waNumber) ?>?text=Hola%20AJT%2C%20quiero%20info%20de%20un%20paquete" class="wa-fab" target="_blank" rel="noopener" aria-label="WhatsApp Viajes AJT">
  <svg width="28" height="28" viewBox="0 0 24 24" fill="#fff" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
  <span class="wa-fab__tooltip">¿Necesitas ayuda?</span>
</a>
<?php endif; ?>

</body>
</html>
