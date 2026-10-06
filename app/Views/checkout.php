<?php

// Funcion del archivo: Renderiza el flujo de reserva, datos de viajeros y pago.
  // Precios y monedas
  $moneda = $configPago['moneda_simbolo'] ?? 'S/';
  
  $viajerosCount = isset($viajeros) ? (int)$viajeros : (isset($viajeros_count) ? (int)$viajeros_count : 1);
  $precioUnitario = (float)$paquete['precio_base'];
  
  // Use controller-provided financial breakdown if available, otherwise calculate inline
  if (isset($desglose) && is_array($desglose)) {
      $subtotal = $desglose['subtotal'];
      $descuento = $desglose['descuento'];
      $igv = $desglose['igv'];
      $total = $desglose['total'];
  } else {
      $subtotal = $precioUnitario * $viajerosCount;
      $descuento = 0.00;
      if ($viajerosCount >= 2) {
          $descuento = round($subtotal * 0.10, 2);
      }
      $igv = round(($subtotal - $descuento) * 0.18, 2);
      $total = round($subtotal - $descuento + $igv, 2);
  }
  
  $currentStep = isset($step) ? (int)$step : 1;

  // Config values with fallbacks
  $terminos = $configPago['terminos_condiciones'] ?? 'Términos y condiciones pendientes de configurar.';
  $politica = $configPago['politica_cancelacion'] ?? 'Política de cancelación pendiente de configurar.';
  $yapeQr = $configPago['yape_qr_url'] ?? '';
  $yapeQrSrc = $yapeQr;
  if ($yapeQr !== '' && !preg_match('#^(?:https?:)?//#i', $yapeQr) && !str_starts_with($yapeQr, '/')) {
    $yapeQrSrc = BASE_URL . '/' . ltrim($yapeQr, '/');
  }
  $yapeNum = $configPago['yape_numero'] ?? '';
  $yapeTitular = $configPago['yape_titular'] ?? '';
  $plinQr = $configPago['plin_qr_url'] ?? '';
  $plinNum = $configPago['plin_numero'] ?? '';
  $plinTitular = $configPago['plin_titular'] ?? '';
  $bancoNombre = $configPago['banco_nombre'] ?? '';
  $bancoCuenta = $configPago['banco_cuenta'] ?? '';
  $bancoCci = $configPago['banco_cci'] ?? '';
  $contactoTel = $configPago['contacto_telefono'] ?? '';
  $contactoEmail = $configPago['contacto_email'] ?? '';
  $contactoWsp = $configPago['contacto_whatsapp'] ?? '';
?>

<main id="main-content" class="container section">
  <nav class="breadcrumbs" style="margin-bottom:16px">
    <a href="<?= BASE_URL ?>/">Inicio</a> <span class="sep">/</span>
    <a href="<?= BASE_URL ?>/catalog">Catálogo</a> <span class="sep">/</span>
    <span class="current">Reservar</span>
  </nav>

  <h1 style="margin:0 0 6px">Completa tu reserva</h1>
  <p style="color:var(--c-ink-500);margin:0 0 24px">Solo 3 pasos. Pagos 100% seguros con cifrado SSL.</p>

  <div class="stepper" id="coStepper" style="margin-bottom:28px;max-width:780px">
    <div class="step <?= ($currentStep === 1) ? 'is-active' : '' ?> <?= ($currentStep > 1) ? 'is-done' : '' ?>" data-step="1">
      <div class="step__num">1</div>
      <div class="step__lbl">Datos del viajero</div>
    </div>
    <div class="step__bar"></div>
    <div class="step <?= ($currentStep === 2) ? 'is-active' : '' ?> <?= ($currentStep > 2) ? 'is-done' : '' ?>" data-step="2">
      <div class="step__num">2</div>
      <div class="step__lbl">Pago</div>
    </div>
    <div class="step__bar"></div>
    <div class="step <?= ($currentStep === 3) ? 'is-active' : '' ?>" data-step="3">
      <div class="step__num">3</div>
      <div class="step__lbl">Confirmación</div>
    </div>
  </div>

  <div class="co">
    <div>
      <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert--error">
          <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
        </div>
      <?php endif; ?>

      <?php if ($currentStep === 3): ?>
        <!-- STEP 3 (CONFIRMACIÓN) -->
        <section class="co__panel co__section is-active" data-section="3">
          <div style="text-align:center; padding: 20px 20px 32px;">
            <div class="state__icon" style="background:var(--c-success-100);color:var(--c-success-600);margin:0 auto 16px; display: flex; align-items: center; justify-content: center; width: 64px; height: 64px; border-radius: 50%;">
              <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <h2 style="margin:0 0 6px"><?= $metodo_pago === 'card' ? 'Reserva confirmada' : 'Reserva registrada' ?></h2>
            <div style="background:var(--c-primary-50);border:1px dashed var(--c-primary-300);border-radius:var(--r-md);padding:14px;display:inline-block;margin-bottom:18px">
              Código de reserva: <strong style="color:var(--c-primary-700);font-size:var(--fs-18);letter-spacing:.1em"><?= htmlspecialchars($codigoReserva) ?></strong>
            </div>
          </div>

          <!-- Resumen completo -->
          <div style="border-top:1px solid var(--c-border);padding-top:24px;">
            <h3 style="margin:0 0 16px;font-size:var(--fs-16)">Detalle de la reserva</h3>
            <div class="co__detail-grid">
              <div class="co__detail-item">
                <span class="co__detail-label">Paquete</span>
                <span class="co__detail-value"><?= htmlspecialchars($paquete['nombre']) ?></span>
              </div>
              <div class="co__detail-item">
                <span class="co__detail-label">Destino</span>
                <span class="co__detail-value"><?= htmlspecialchars($paquete['ciudad_nombre'] ?? 'Consultar') ?></span>
              </div>
              <div class="co__detail-item">
                <span class="co__detail-label">Fecha de viaje</span>
                <span class="co__detail-value"><?= htmlspecialchars($fecha_viaje) ?></span>
              </div>
              <div class="co__detail-item">
                <span class="co__detail-label">Pasajeros</span>
                <span class="co__detail-value"><?= $viajerosCount ?> persona<?= $viajerosCount > 1 ? 's' : '' ?></span>
              </div>
              <div class="co__detail-item">
                <span class="co__detail-label">Viajero principal</span>
                <span class="co__detail-value"><?= htmlspecialchars(($nombres_v[0] ?? '') . ' ' . ($apellidos_v[0] ?? '')) ?></span>
              </div>
              <div class="co__detail-item">
                <span class="co__detail-label">Teléfono</span>
                <span class="co__detail-value"><?= htmlspecialchars($telefono_pasajero ?? '') ?></span>
              </div>
              <div class="co__detail-item">
                <span class="co__detail-label">Método de pago</span>
                <span class="co__detail-value"><?= $metodo_pago === 'card' ? 'Tarjeta de crédito/débito' : ($metodo_pago === 'yape' ? 'Yape / Plin' : 'Transferencia bancaria') ?></span>
              </div>
              <div class="co__detail-item">
                <span class="co__detail-label">Estado</span>
                <span class="co__detail-value"><?= $metodo_pago === 'card' ? 'Confirmado' : 'Pendiente de validación' ?></span>
              </div>
            </div>
          </div>

          <!-- Qué sigue -->
          <div class="co__timeline" style="margin-top:28px;">
            <h3 style="margin:0 0 14px;font-size:var(--fs-16)">¿Qué sigue?</h3>
            <div class="co__timeline-step">
              <div class="co__timeline-num">1</div>
              <div>
                <strong>Confirmación de disponibilidad</strong>
                <p class="co__timeline-desc">Un asesor verifica la disponibilidad del paquete para tu fecha.</p>
              </div>
            </div>
            <div class="co__timeline-step">
              <div class="co__timeline-num">2</div>
              <div>
                <strong>Validación de pago</strong>
                <p class="co__timeline-desc">Verificamos tu comprobante o confirmamos el pago con tarjeta.</p>
              </div>
            </div>
            <div class="co__timeline-step">
              <div class="co__timeline-num">3</div>
              <div>
                <strong>Documentos de viaje</strong>
                <p class="co__timeline-desc">Recibes tus vouchers y documentos finales por correo y WhatsApp.</p>
              </div>
            </div>
          </div>

          <!-- Comprobante link -->
          <div style="margin-top:24px;text-align:center;">
            <a href="<?= BASE_URL ?>/profile/comprobante/<?= htmlspecialchars($codigoReserva) ?>" class="btn btn--ghost" target="_blank">
              Ver comprobante de reserva
            </a>
          </div>

          <!-- Info de contacto -->
          <?php if ($contactoTel || $contactoEmail || $contactoWsp): ?>
          <div class="co__policy-box co__section-gap">
            <h4 class="co__panel-heading">Contacto Viajes AJT</h4>
            <?php if ($contactoTel): ?><p class="co__contact-line">Tel: <?= htmlspecialchars($contactoTel) ?></p><?php endif; ?>
            <?php if ($contactoEmail): ?><p class="co__contact-line">Email: <?= htmlspecialchars($contactoEmail) ?></p><?php endif; ?>
            <?php if ($contactoWsp): ?><p class="co__contact-line">WhatsApp: <?= htmlspecialchars($contactoWsp) ?></p><?php endif; ?>
          </div>
          <?php endif; ?>

          <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap;margin-top:24px;">
            <a href="<?= BASE_URL ?>/profile/mi-perfil" class="btn btn--primary">Ir a mi panel</a>
            <a href="<?= BASE_URL ?>/catalog" class="btn btn--ghost">Seguir explorando</a>
          </div>
        </section>
      <?php else: ?>
        <form action="<?= BASE_URL ?>/booking/procesar-checkout" method="POST" id="checkoutForm" enctype="multipart/form-data">
          <?= App\Helper\Csrf::insertInput() ?>
          <input type="hidden" name="paquete_slug" value="<?= htmlspecialchars($paquete['slug']) ?>">
          <input type="hidden" name="fecha_viaje" value="<?= htmlspecialchars($fecha_viaje) ?>">
          <input type="hidden" name="viajeros_count" value="<?= $viajerosCount ?>">

          <!-- STEP 1 (DATOS DEL PASAJERO) -->
          <section class="co__panel co__section is-active" data-section="1">
            <h2 class="co__section-title">Datos de los pasajeros</h2>
            <p class="co__section-subtitle">Completa la información de los viajeros para el registro de boletos.</p>
            
            <?php for ($i = 0; $i < $viajerosCount; $i++): ?>
              <div style="margin-bottom: 24px; padding-bottom: 20px; border-bottom: 1px solid var(--c-border)">
                <h3 style="font-size: var(--fs-15); margin-bottom: 10px; color: var(--c-primary-800)">
                  Pasajero <?= $i + 1 ?> <?= ($i === 0) ? '(Viajero principal)' : '' ?>
                </h3>
                <div class="co__grid-2">
                  <div class="field">
                    <label>Nombres *</label>
                    <input class="input" name="nombres_viajero[]" required placeholder="Nombres" 
                           pattern="[\p{L}\s]+" title="Solo letras y espacios" minlength="2" maxlength="100"
                           value="<?= ($i === 0 && $usuarioLogueado) ? htmlspecialchars($usuarioLogueado['nombres']) : '' ?>">
                  </div>
                  <div class="field">
                    <label>Apellidos *</label>
                    <input class="input" name="apellidos_viajero[]" required placeholder="Apellidos" 
                           pattern="[\p{L}\s]+" title="Solo letras y espacios" minlength="2" maxlength="100"
                           value="<?= ($i === 0 && $usuarioLogueado) ? htmlspecialchars($usuarioLogueado['apellidos']) : '' ?>">
                  </div>
                  <div class="field">
                    <label>DNI / Pasaporte *</label>
                    <input class="input" name="documento_viajero[]" required placeholder="N.° de documento" 
                           minlength="6" maxlength="20" title="Entre 6 y 20 caracteres"
                           value="<?= ($i === 0 && $usuarioLogueado) ? htmlspecialchars($usuarioLogueado['dni_pasaporte'] ?? '') : '' ?>">
                  </div>
                  <?php if ($i === 0): ?>
                    <div class="field">
                      <label>Correo electrónico (usuario principal)</label>
                      <input class="input" type="email" readonly value="<?= $usuarioLogueado ? htmlspecialchars($usuarioLogueado['email']) : '' ?>">
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            <?php endfor; ?>

            <!-- Teléfono del pasajero principal -->
            <div style="margin-bottom: 24px;">
              <div class="co__grid-2">
                <div class="field">
                  <label>Teléfono del pasajero principal *</label>
                  <input class="input" name="telefono_pasajero" required 
                         placeholder="Ej: 999888777" 
                         pattern="[0-9]{7,15}" 
                         maxlength="15"
                         inputmode="numeric"
                         title="Ingrese entre 7 y 15 dígitos numéricos"
                         value="<?= htmlspecialchars($telefono_pasajero ?? '') ?>">
                  <small style="color:var(--c-ink-500);font-size:var(--fs-12)">Solo números, entre 7 y 15 dígitos.</small>
                </div>
              </div>
            </div>

            <div class="co__actions">
              <a href="<?= BASE_URL ?>/paquete/<?= htmlspecialchars($paquete['slug']) ?>" class="btn btn--ghost">Volver al paquete</a>
              <button type="button" class="btn btn--primary" id="btnNextStep">Continuar al pago</button>
            </div>
          </section>

          <!-- STEP 2 (PAGO) -->
          <section class="co__panel co__section" data-section="2" style="display:none">
            <h2 class="co__section-title">Método de pago</h2>
            <p class="co__section-subtitle">Elige cómo quieres pagar. Todas las opciones son seguras.</p>

            <div class="co__pay" role="radiogroup">
              <label class="co__pay-opt is-active">
                <input type="radio" name="pay" value="card" checked>
                <div>
                  <strong>Tarjeta de crédito / débito</strong>
                  <div class="co__pay-desc">Visa / Mastercard / Amex (Confirmación inmediata)</div>
                </div>
                <span class="co__pay-logo">CARD</span>
              </label>
              <label class="co__pay-opt">
                <input type="radio" name="pay" value="yape">
                <div>
                  <strong>Yape / Plin</strong>
                  <div class="co__pay-desc">Pago inmediato vía código QR / número</div>
                </div>
                <span class="co__pay-logo">QR</span>
              </label>
              <label class="co__pay-opt">
                <input type="radio" name="pay" value="trans">
                <div>
                  <strong>Transferencia bancaria</strong>
                  <div class="co__pay-desc">Liquidación manual (BCP, BBVA, Interbank)</div>
                </div>
                <span class="co__pay-logo">BANK</span>
              </label>
            </div>

            <!-- Tarjeta de crédito -->
            <div id="payCard" class="co__section-gap">
              <div style="background:var(--c-warning-50,#fef9c3);border:1px solid var(--c-warning-300,#fde047);border-radius:var(--r-md);padding:10px 14px;margin-bottom:12px;font-size:var(--fs-13);color:var(--c-warning-800,#854d0e);">
                Ingresa los datos de tu tarjeta para validar y confirmar tu reserva de forma segura.
              </div>
              <div class="co__card-form">
                <div class="co__card-brands">
                  <span class="co__card-brand">VISA</span>
                  <span class="co__card-brand">MC</span>
                  <span class="co__card-brand">AMEX</span>
                </div>
                <div class="co__grid-2">
                  <div class="field" style="grid-column:1 / -1"><label>Número de tarjeta</label><input class="input" name="card_number" placeholder="4242 4242 4242 4242" inputmode="numeric" maxlength="19" pattern="[0-9 ]{13,19}"></div>
                  <div class="field"><label>Vencimiento</label><input class="input" name="card_expiry" placeholder="MM/AA" inputmode="numeric" maxlength="5" pattern="(0[1-9]|1[0-2])\/[0-9]{2}"></div>
                  <div class="field"><label>CVV</label><input class="input" name="card_cvv" placeholder="123" inputmode="numeric" maxlength="4" pattern="[0-9]{3,4}"></div>
                  <div class="field" style="grid-column:1 / -1"><label>Titular</label><input class="input" name="card_holder" placeholder="Cómo aparece en la tarjeta" pattern="[A-Za-zÀ-ÿ\s]{3,}" minlength="3"></div>
                </div>
                <input type="hidden" name="card_last4" id="cardLast4">
                <div class="co__payment-badge">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
                  Validación segura de tarjeta
                </div>
              </div>
            </div>

            <!-- Yape / Plin -->
            <div id="payQrInfo" style="margin-top:20px; display:none;">
              <div class="co__qr-section">
                <h4 class="co__panel-subtitle">Pagar con Yape o Plin</h4>
                <?php if ($yapeQr): ?>
                  <div class="qr-container">
                    <img src="<?= htmlspecialchars($yapeQrSrc) ?>" alt="QR de Yape para realizar el pago">
                  </div>
                <?php else: ?>
                  <!-- QR mock profesional placeholder -->
                  <div class="qr-mock">
                    <svg class="qr-mock__svg" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                      <rect width="200" height="200" rx="12" fill="#f8fafc" stroke="#e2e8f0" stroke-width="1.5"/>
                      <!-- Finder patterns -->
                      <rect x="20" y="20" width="44" height="44" rx="4" fill="#1e293b"/>
                      <rect x="28" y="28" width="28" height="28" rx="2" fill="#f8fafc"/>
                      <rect x="34" y="34" width="16" height="16" rx="1" fill="#1e293b"/>
                      <rect x="136" y="20" width="44" height="44" rx="4" fill="#1e293b"/>
                      <rect x="144" y="28" width="28" height="28" rx="2" fill="#f8fafc"/>
                      <rect x="150" y="34" width="16" height="16" rx="1" fill="#1e293b"/>
                      <rect x="20" y="136" width="44" height="44" rx="4" fill="#1e293b"/>
                      <rect x="28" y="144" width="28" height="28" rx="2" fill="#f8fafc"/>
                      <rect x="34" y="150" width="16" height="16" rx="1" fill="#1e293b"/>
                      <!-- Data modules (decorative) -->
                      <rect x="76" y="20" width="8" height="8" fill="#1e293b" opacity=".6"/><rect x="92" y="20" width="8" height="8" fill="#1e293b" opacity=".4"/><rect x="108" y="20" width="8" height="8" fill="#1e293b" opacity=".7"/>
                      <rect x="76" y="36" width="8" height="8" fill="#1e293b" opacity=".5"/><rect x="108" y="36" width="8" height="8" fill="#1e293b" opacity=".3"/>
                      <rect x="76" y="76" width="8" height="8" fill="#1e293b" opacity=".6"/><rect x="92" y="76" width="8" height="8" fill="#1e293b" opacity=".4"/><rect x="108" y="76" width="8" height="8" fill="#1e293b" opacity=".5"/><rect x="124" y="76" width="8" height="8" fill="#1e293b" opacity=".3"/>
                      <rect x="76" y="92" width="8" height="8" fill="#1e293b" opacity=".7"/><rect x="108" y="92" width="8" height="8" fill="#1e293b" opacity=".4"/>
                      <rect x="76" y="108" width="8" height="8" fill="#1e293b" opacity=".5"/><rect x="92" y="108" width="8" height="8" fill="#1e293b" opacity=".6"/><rect x="108" y="108" width="8" height="8" fill="#1e293b" opacity=".3"/><rect x="124" y="108" width="8" height="8" fill="#1e293b" opacity=".7"/>
                      <rect x="136" y="76" width="8" height="8" fill="#1e293b" opacity=".4"/><rect x="152" y="76" width="8" height="8" fill="#1e293b" opacity=".6"/><rect x="168" y="76" width="8" height="8" fill="#1e293b" opacity=".3"/>
                      <rect x="136" y="92" width="8" height="8" fill="#1e293b" opacity=".5"/><rect x="168" y="92" width="8" height="8" fill="#1e293b" opacity=".7"/>
                      <rect x="136" y="108" width="8" height="8" fill="#1e293b" opacity=".3"/><rect x="152" y="108" width="8" height="8" fill="#1e293b" opacity=".6"/><rect x="168" y="108" width="8" height="8" fill="#1e293b" opacity=".4"/>
                      <rect x="20" y="76" width="8" height="8" fill="#1e293b" opacity=".5"/><rect x="36" y="76" width="8" height="8" fill="#1e293b" opacity=".3"/><rect x="52" y="76" width="8" height="8" fill="#1e293b" opacity=".6"/>
                      <rect x="20" y="92" width="8" height="8" fill="#1e293b" opacity=".4"/><rect x="52" y="92" width="8" height="8" fill="#1e293b" opacity=".7"/>
                      <rect x="20" y="108" width="8" height="8" fill="#1e293b" opacity=".6"/><rect x="36" y="108" width="8" height="8" fill="#1e293b" opacity=".5"/><rect x="52" y="108" width="8" height="8" fill="#1e293b" opacity=".3"/>
                      <rect x="76" y="136" width="8" height="8" fill="#1e293b" opacity=".5"/><rect x="92" y="136" width="8" height="8" fill="#1e293b" opacity=".7"/><rect x="108" y="136" width="8" height="8" fill="#1e293b" opacity=".4"/><rect x="124" y="136" width="8" height="8" fill="#1e293b" opacity=".6"/>
                      <rect x="76" y="152" width="8" height="8" fill="#1e293b" opacity=".3"/><rect x="108" y="152" width="8" height="8" fill="#1e293b" opacity=".5"/><rect x="124" y="152" width="8" height="8" fill="#1e293b" opacity=".7"/>
                      <rect x="76" y="168" width="8" height="8" fill="#1e293b" opacity=".6"/><rect x="92" y="168" width="8" height="8" fill="#1e293b" opacity=".4"/><rect x="108" y="168" width="8" height="8" fill="#1e293b" opacity=".3"/><rect x="124" y="168" width="8" height="8" fill="#1e293b" opacity=".5"/>
                      <rect x="136" y="136" width="8" height="8" fill="#1e293b" opacity=".7"/><rect x="152" y="136" width="8" height="8" fill="#1e293b" opacity=".4"/><rect x="168" y="136" width="8" height="8" fill="#1e293b" opacity=".6"/>
                      <rect x="136" y="152" width="8" height="8" fill="#1e293b" opacity=".5"/><rect x="168" y="152" width="8" height="8" fill="#1e293b" opacity=".3"/>
                      <rect x="136" y="168" width="8" height="8" fill="#1e293b" opacity=".4"/><rect x="152" y="168" width="8" height="8" fill="#1e293b" opacity=".6"/><rect x="168" y="168" width="8" height="8" fill="#1e293b" opacity=".5"/>
                      <!-- Center icon -->
                      <circle cx="100" cy="100" r="18" fill="#7c3aed" opacity=".9"/>
                      <text x="100" y="105" text-anchor="middle" font-size="11" font-weight="bold" fill="#fff" font-family="sans-serif">QR</text>
                    </svg>
                    <span class="qr-mock__label">Código QR de pago</span>
                  </div>
                <?php endif; ?>

                <div class="co__pay-details">
                  <?php if ($yapeNum || $yapeTitular): ?>
                    <div class="co__pay-account">
                      <span class="co__pay-account-badge co__pay-account-badge--yape">YAPE</span>
                      <div>
                        <strong><?= htmlspecialchars($yapeNum) ?></strong>
                        <?php if ($yapeTitular): ?><span class="co__account-detail"><?= htmlspecialchars($yapeTitular) ?></span><?php endif; ?>
                      </div>
                    </div>
                  <?php endif; ?>
                  <?php if ($plinNum || $plinTitular): ?>
                    <div class="co__pay-account">
                      <span class="co__pay-account-badge co__pay-account-badge--plin">PLIN</span>
                      <div>
                        <strong><?= htmlspecialchars($plinNum) ?></strong>
                        <?php if ($plinTitular): ?><span class="co__account-detail"><?= htmlspecialchars($plinTitular) ?></span><?php endif; ?>
                      </div>
                    </div>
                  <?php endif; ?>
                  <?php if (!$yapeNum && !$yapeTitular && !$plinNum && !$plinTitular): ?>
                    <p class="co__pendiente">Datos de pago pendientes de configurar</p>
                  <?php endif; ?>
                </div>
                <p class="co__payment-note"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-inline"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg> Pago móvil mediante código QR o número autorizado</p>
              </div>
            </div>

            <!-- Transferencia bancaria -->
            <div id="payTransferInfo" style="margin-top:20px; display:none;">
              <div class="co__policy-box">
                <h4 class="co__panel-subtitle">Cuentas bancarias de Viajes AJT</h4>
                <?php if ($bancoNombre): ?>
                  <ul style="font-size: var(--fs-13); line-height: 1.8; color: var(--c-ink-800); margin:0; padding-left:0; list-style:none;">
                    <li><strong>Banco:</strong> <?= htmlspecialchars($bancoNombre) ?></li>
                    <?php if ($bancoCuenta): ?><li><strong>Cuenta:</strong> <?= htmlspecialchars($bancoCuenta) ?></li><?php endif; ?>
                    <?php if ($bancoCci): ?><li><strong>CCI:</strong> <?= htmlspecialchars($bancoCci) ?></li><?php endif; ?>
                  </ul>
                <?php else: ?>
                  <p class="co__pendiente">Datos bancarios pendientes de configurar</p>
                <?php endif; ?>
              </div>
            </div>

            <!-- Upload de comprobante para Yape/Plin y transferencia -->
            <div id="payComprobanteInfo" style="margin-top:16px; display:none;">
              <div class="co__upload-box" style="margin-top:16px;">
                <label for="comprobanteFile" style="font-weight:var(--fw-bold);font-size:var(--fs-14);display:block;margin-bottom:6px;">Adjuntar comprobante de pago *</label>
                <div class="co__upload-zone" id="uploadZone">
                  <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="var(--c-ink-400)" stroke-width="1.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12"/></svg>
                  <span style="color:var(--c-ink-600);font-size:var(--fs-14)">Arrastra tu comprobante aquí o</span>
                  <label for="comprobanteFile" class="btn btn--ghost btn--sm" style="cursor:pointer;margin:0">Seleccionar archivo</label>
                  <input type="file" name="comprobante" id="comprobanteFile" accept=".jpg,.jpeg,.png,.pdf" disabled style="display:none">
                  <span style="color:var(--c-ink-400);font-size:var(--fs-12)">JPG, PNG o PDF — Máximo 5 MB</span>
                </div>
                <div id="filePreview" class="co__file-preview" style="display:none">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--c-primary-700)" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                  <span id="fileName" style="font-size:var(--fs-13);color:var(--c-ink-700)"></span>
                  <button type="button" id="fileRemove" style="margin-left:auto;background:none;border:none;color:var(--c-danger-600);cursor:pointer;font-size:var(--fs-13)">✕ Quitar</button>
                </div>
                <div id="fileError" role="alert" style="color:var(--c-danger-600);font-size:var(--fs-13);margin-top:4px;display:none;"></div>
              </div>

              <!-- Estado de revisión -->
              <div class="co__review-status">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--c-warning-600)" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                <div>
                  <strong>Estado: Pendiente de validación</strong>
                  <p style="margin:2px 0 0;font-size:var(--fs-12);color:var(--c-ink-500)">Un asesor revisará tu comprobante en un máximo de 2 horas hábiles. Recibirás confirmación por correo y WhatsApp.</p>
                </div>
              </div>
            </div>

            <!-- Política de cancelación -->
            <div class="co__policy-box co__section-gap">
              <h4 class="co__panel-heading">Política de cancelación</h4>
              <p style="margin:0;font-size:var(--fs-13);color:var(--c-ink-700);line-height:1.6"><?= nl2br(htmlspecialchars($politica)) ?></p>
            </div>

            <!-- Términos y condiciones -->
            <div class="co__terms-box co__section-gap">
              <h4 class="co__panel-heading">Términos y condiciones</h4>
              <div class="co__terms-text">
                <?= nl2br(htmlspecialchars($terminos)) ?>
              </div>
              <label class="co__terms-check">
                <input type="checkbox" name="acepta_terminos" id="aceptaTerminos" required aria-describedby="termsError">
                <span>He leído y acepto los términos y condiciones y la política de cancelación.</span>
              </label>
              <div id="termsError" role="alert" style="color:var(--c-danger-600);font-size:var(--fs-13);margin-top:4px;display:none;">
                Debe aceptar los términos y condiciones para continuar.
              </div>
            </div>

            <div class="co__actions" style="margin-top: 24px;">
              <button type="button" class="btn btn--ghost" id="btnPrevStep">Atrás</button>
              <button type="submit" class="btn btn--accent" id="payBtn">Pagar y confirmar</button>
            </div>
          </section>
        </form>
      <?php endif; ?>
    </div>

    <!-- SIDEBAR DE DETALLE DE LA RESERVA -->
    <aside class="co__panel co__summary" id="coSummary">
      <button type="button" class="co__summary-toggle" id="btnSummaryToggle">
        <span>Resumen de reserva</span>
      </button>
      <div class="co__summary-body">
      <div class="co__summary-img">
        <?php $checkoutFallbacks = \App\Models\Paquete::fallbackImagesBySlug($paquete['slug'] ?? ''); ?>
        <img src="<?= htmlspecialchars($paquete['imagen_url'] ?? ($checkoutFallbacks[0] ?? '')) ?>" data-fallback-src="<?= htmlspecialchars($checkoutFallbacks[1] ?? $checkoutFallbacks[0] ?? '') ?>" alt="<?= htmlspecialchars($paquete['nombre']) ?>">
      </div>
      <h3 style="margin:0 0 4px"><?= htmlspecialchars($paquete['nombre']) ?></h3>
      <div class="card__meta" style="margin-bottom:14px"><?= (int)$paquete['duracion_dias'] ?> días / <?= (int)$paquete['duracion_noches'] ?> noches</div>

      <!-- Detalle de la reserva -->
      <div class="co__summary-details">
        <div class="co__summary-detail">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 1 1 18 0Z"/><circle cx="12" cy="10" r="3"/></svg>
          <span><?= htmlspecialchars($paquete['ciudad_nombre'] ?? 'Destino') ?></span>
        </div>
        <div class="co__summary-detail">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
          <span><?= htmlspecialchars($fecha_viaje) ?></span>
        </div>
        <div class="co__summary-detail">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          <span><?= $viajerosCount ?> persona<?= $viajerosCount > 1 ? 's' : '' ?></span>
        </div>
      </div>

      <div style="border-top:1px solid var(--c-border);margin:14px 0"></div>

      <div class="co__row">
        <span>Subtotal (<?= $viajerosCount ?> pax)</span>
        <span><?= $moneda ?> <?= number_format($subtotal, 2) ?></span>
      </div>
      
      <?php if ($descuento > 0): ?>
        <div class="co__row" style="color:var(--c-success-600)">
          <span>Descuento grupo (10%)</span>
          <span>- <?= $moneda ?> <?= number_format($descuento, 2) ?></span>
        </div>
      <?php endif; ?>

      <div class="co__row">
        <span>IGV (18%)</span>
        <span><?= $moneda ?> <?= number_format($igv, 2) ?></span>
      </div>

      <div class="co__row co__row--total">
        <span>Total</span>
        <span class="price"><?= $moneda ?> <?= number_format($total, 2) ?></span>
      </div>

      <!-- Política de cancelación -->
      <div class="co__summary-cancel">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--c-ink-500)" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
        <span>Cancelación gratis hasta 7 días antes del viaje</span>
      </div>

      <p style="font-size:var(--fs-12);color:var(--c-ink-500);margin:12px 0 0;text-align:center"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-inline"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg> Pago seguro con cifrado SSL</p>
      </div><!-- /co__summary-body -->
    </aside>
  </div>
</main>

<script nonce="<?= htmlspecialchars(\App\Helper\SecurityHeaders::getNonce(), ENT_QUOTES, 'UTF-8') ?>">
document.addEventListener('DOMContentLoaded', () => {
  const btnToggle = document.getElementById('btnSummaryToggle');
  if (btnToggle) {
    btnToggle.addEventListener('click', () => {
      document.getElementById('coSummary').classList.toggle('is-collapsed');
    });
  }
  const qrImg = document.querySelector('.qr-container img');
  if (qrImg) {
    qrImg.addEventListener('error', function() {
      this.parentElement.innerHTML = '<p style="color:var(--c-ink-500)">QR no disponible</p>';
    });
  }

  const steps = document.querySelectorAll('#coStepper .step');
  const section1 = document.querySelector('.co__section[data-section="1"]');
  const section2 = document.querySelector('.co__section[data-section="2"]');
  
  const btnNext = document.getElementById('btnNextStep');
  const btnPrev = document.getElementById('btnPrevStep');
  const form = document.getElementById('checkoutForm');

  // Funcion: Obtiene la etiqueta legible de un campo.
  function getFieldLabel(input) {
    const field = input.closest('.field');
    const label = field ? field.querySelector('label') : null;
    return (label ? label.textContent : input.name || 'Este campo').replace('*', '').trim();
  }

  // Funcion: Crea el contenedor de error de un campo.
  function ensureFieldError(input) {
    const field = input.closest('.field');
    if (!field) return null;
    let error = field.querySelector('.field__error');
    if (!error) {
      error = document.createElement('div');
      error.className = 'field__error';
      error.setAttribute('role', 'alert');
      const safeName = (input.name || 'field').replace(/[^a-z0-9_-]/gi, '-');
      error.id = safeName + '-error-' + Math.random().toString(36).slice(2, 7);
      field.appendChild(error);
    }
    input.setAttribute('aria-describedby', error.id);
    return error;
  }

  // Funcion: Muestra un mensaje de error en un campo.
  function setFieldError(input, message) {
    const field = input.closest('.field');
    const error = ensureFieldError(input);
    if (field) field.classList.add('is-invalid');
    input.setAttribute('aria-invalid', 'true');
    if (error) error.textContent = message;
  }

  // Funcion: Limpia el error visual de un campo.
  function clearFieldError(input) {
    const field = input.closest('.field');
    const error = field ? field.querySelector('.field__error') : null;
    if (field) field.classList.remove('is-invalid');
    input.removeAttribute('aria-invalid');
    if (error) error.textContent = '';
  }

  // Funcion: Enfoca el primer campo invalido de una seccion.
  function focusFirstInvalid(scope) {
    const first = scope.querySelector('[aria-invalid="true"]');
    if (first) {
      first.focus();
      first.scrollIntoView({behavior: 'smooth', block: 'center'});
    }
  }

  // Step navigation
  if (btnNext && section1 && section2) {
    btnNext.addEventListener('click', () => {
      const requiredInputs = section1.querySelectorAll('input[required]');
      let allValid = true;
      requiredInputs.forEach(input => {
        clearFieldError(input);
        if (!input.value.trim()) {
          allValid = false;
          setFieldError(input, getFieldLabel(input) + ' es obligatorio.');
        }
      });

      // Validate phone pattern
      const phoneEl = document.querySelector('input[name="telefono_pasajero"]');
      if (phoneEl && phoneEl.value.trim()) {
        const phoneVal = phoneEl.value.replace(/[^0-9]/g, '');
        if (!/^[0-9]{7,15}$/.test(phoneVal)) {
          allValid = false;
          setFieldError(phoneEl, 'El teléfono debe contener entre 7 y 15 dígitos numéricos.');
        } else {
          clearFieldError(phoneEl);
        }
      }

      if (!allValid) {
        focusFirstInvalid(section1);
        if (window.toast) {
          window.toast({type: 'error', text: 'Revisa los campos marcados antes de continuar.'});
        }
        return;
      }

      steps[0].classList.remove('is-active');
      steps[0].classList.add('is-done');
      steps[1].classList.add('is-active');

      section1.style.display = 'none';
      section2.style.display = 'block';
      window.scrollTo({top: 0, behavior: 'smooth'});
    });
  }

  if (btnPrev && section1 && section2) {
    btnPrev.addEventListener('click', () => {
      steps[1].classList.remove('is-active');
      steps[0].classList.remove('is-done');
      steps[0].classList.add('is-active');

      section2.style.display = 'none';
      section1.style.display = 'block';
      window.scrollTo({top: 0, behavior: 'smooth'});
    });
  }

  // Payment method switching
  const payRadios = document.querySelectorAll('input[name="pay"]');
  const payCard = document.getElementById('payCard');
  const payQr = document.getElementById('payQrInfo');
  const payBank = document.getElementById('payTransferInfo');
  const payComprobante = document.getElementById('payComprobanteInfo');
  const comprobanteInput = document.getElementById('comprobanteFile');

  // Funcion: Muestra los campos del metodo de pago elegido.
  function showPaymentMethod(method) {
    if (payCard) payCard.style.display = method === 'card' ? 'block' : 'none';
    if (payQr) payQr.style.display = method === 'yape' ? 'block' : 'none';
    if (payBank) payBank.style.display = method === 'trans' ? 'block' : 'none';
    const requiresProof = method === 'trans' || method === 'yape';
    if (payComprobante) payComprobante.style.display = requiresProof ? 'block' : 'none';

    // El comprobante participa en la validación y envío para pagos manuales.
    if (comprobanteInput) {
      comprobanteInput.disabled = !requiresProof;
      comprobanteInput.required = requiresProof;
    }
  }

  payRadios.forEach(radio => {
    radio.addEventListener('change', () => {
      document.querySelectorAll('.co__pay-opt').forEach(opt => {
        opt.classList.remove('is-active');
      });
      radio.closest('.co__pay-opt').classList.add('is-active');
      showPaymentMethod(radio.value);
    });
  });

  const selectedPayment = document.querySelector('input[name="pay"]:checked');
  if (selectedPayment) showPaymentMethod(selectedPayment.value);

  // Terms validation on submit
  if (form) {
    form.addEventListener('submit', (e) => {
      const termsCheckbox = document.getElementById('aceptaTerminos');
      const termsError = document.getElementById('termsError');
      
      if (termsCheckbox && !termsCheckbox.checked) {
        e.preventDefault();
        if (termsError) termsError.style.display = 'block';
        termsCheckbox.focus();
        return false;
      }
      if (termsError) termsError.style.display = 'none';

      // File validation for manual payment methods
      const payMethod = document.querySelector('input[name="pay"]:checked');
      if (payMethod && (payMethod.value === 'trans' || payMethod.value === 'yape')) {
        const fileInput = document.getElementById('comprobanteFile');
        const fileError = document.getElementById('fileError');

        if (!fileInput || fileInput.files.length === 0) {
          e.preventDefault();
          if (fileError) {
            fileError.textContent = 'Debe adjuntar el comprobante de pago.';
            fileError.style.display = 'block';
            fileError.scrollIntoView({behavior: 'smooth', block: 'center'});
          }
          return false;
        }

        if (fileInput.files.length > 0) {
          const file = fileInput.files[0];
          const maxSize = 5 * 1024 * 1024;
          const allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
          
          if (file.size > maxSize) {
            e.preventDefault();
            if (fileError) {
              fileError.textContent = 'El archivo no puede superar 5MB.';
              fileError.style.display = 'block';
            }
            return false;
          }
          
          if (!allowedTypes.includes(file.type)) {
            e.preventDefault();
            if (fileError) {
              fileError.textContent = 'Solo se permiten archivos JPG, JPEG, PNG o PDF.';
              fileError.style.display = 'block';
            }
            return false;
          }
          
          if (fileError) fileError.style.display = 'none';
        }
      }

      // Card validation
      if (payMethod && payMethod.value === 'card') {
        const cn = document.querySelector('input[name="card_number"]');
        const ce = document.querySelector('input[name="card_expiry"]');
        const cc = document.querySelector('input[name="card_cvv"]');
        const ch = document.querySelector('input[name="card_holder"]');
        const cardLast4 = document.getElementById('cardLast4');
        let cardValid = true;

        const cnDigits = cn ? cn.value.replace(/[^0-9]/g, '') : '';
        if (!cnDigits || cnDigits.length < 13 || cnDigits.length > 16) {
          cardValid = false;
          if (cn) setFieldError(cn, 'Ingresa un número de tarjeta válido.');
        } else {
          if (cn) clearFieldError(cn);
        }

        const ceVal = ce ? ce.value : '';
        if (!/^(0[1-9]|1[0-2])\/\d{2}$/.test(ceVal)) {
          cardValid = false;
          if (ce) setFieldError(ce, 'Ingresa el vencimiento en formato MM/AA.');
        } else {
          if (ce) clearFieldError(ce);
        }

        const ccDigits = cc ? cc.value.replace(/[^0-9]/g, '') : '';
        if (!ccDigits || ccDigits.length < 3 || ccDigits.length > 4) {
          cardValid = false;
          if (cc) setFieldError(cc, 'Ingresa un CVV de 3 o 4 dígitos.');
        } else {
          if (cc) clearFieldError(cc);
        }

        const chVal = ch ? ch.value.trim() : '';
        if (chVal.length < 3 || !/^[A-Za-zÀ-ÿ\s]+$/.test(chVal)) {
          cardValid = false;
          if (ch) setFieldError(ch, 'Ingresa el titular como aparece en la tarjeta.');
        } else {
          if (ch) clearFieldError(ch);
        }

        if (!cardValid) {
          e.preventDefault();
          focusFirstInvalid(section2);
          if (window.toast) {
            window.toast({type: 'error', text: 'Revisa los datos de la tarjeta.'});
          }
          return false;
        }

        // Populate hidden card_last4 before submit
        if (cardLast4 && cnDigits.length >= 4) {
          cardLast4.value = cnDigits.slice(-4);
        }
      }
    });
  }

  // Phone: strip non-numeric, enforce 15 max
  const phoneInput = document.querySelector('input[name="telefono_pasajero"]');
  if (phoneInput) {
    phoneInput.addEventListener('input', () => {
      phoneInput.value = phoneInput.value.replace(/[^0-9]/g, '').slice(0, 15);
    });
  }

  // Card fields: real-time formatting and validation
  const cardNumberInput = document.querySelector('input[name="card_number"]');
  const cardExpiryInput = document.querySelector('input[name="card_expiry"]');
  const cardCvvInput = document.querySelector('input[name="card_cvv"]');
  const cardHolderInput = document.querySelector('input[name="card_holder"]');
  const cardLast4Input = document.getElementById('cardLast4');

  if (cardNumberInput) {
    cardNumberInput.addEventListener('input', () => {
      let v = cardNumberInput.value.replace(/[^0-9]/g, '').slice(0, 16);
      let formatted = v.replace(/(.{4})/g, '$1 ').trim();
      cardNumberInput.value = formatted;
    });
  }

  if (cardExpiryInput) {
    cardExpiryInput.addEventListener('input', () => {
      let v = cardExpiryInput.value.replace(/[^0-9]/g, '').slice(0, 4);
      if (v.length >= 2) {
        let month = parseInt(v.substring(0, 2), 10);
        if (month < 1) month = 1;
        if (month > 12) month = 12;
        v = String(month).padStart(2, '0') + '/' + v.substring(2);
      }
      cardExpiryInput.value = v;
    });
  }

  if (cardCvvInput) {
    cardCvvInput.addEventListener('input', () => {
      cardCvvInput.value = cardCvvInput.value.replace(/[^0-9]/g, '').slice(0, 4);
    });
  }

  if (cardHolderInput) {
    cardHolderInput.addEventListener('input', () => {
      cardHolderInput.value = cardHolderInput.value.replace(/[^A-Za-zÀ-ÿ\s]/g, '');
    });
  }

  // Real-time terms checkbox clearing error
  const termsCheckbox = document.getElementById('aceptaTerminos');
  if (termsCheckbox) {
    termsCheckbox.addEventListener('change', () => {
      const termsError = document.getElementById('termsError');
      if (termsCheckbox.checked && termsError) {
        termsError.style.display = 'none';
      }
    });
  }

  // File upload: drag & drop + preview
  const uploadZone = document.getElementById('uploadZone');
  const fileInput = comprobanteInput;
  const filePreview = document.getElementById('filePreview');
  const fileName = document.getElementById('fileName');
  const fileRemove = document.getElementById('fileRemove');

  if (uploadZone && fileInput) {
    uploadZone.addEventListener('click', () => fileInput.click());
    uploadZone.addEventListener('dragover', (e) => { e.preventDefault(); uploadZone.classList.add('dragover'); });
    uploadZone.addEventListener('dragleave', () => uploadZone.classList.remove('dragover'));
    uploadZone.addEventListener('drop', (e) => {
      e.preventDefault();
      uploadZone.classList.remove('dragover');
      if (e.dataTransfer.files.length) {
        fileInput.files = e.dataTransfer.files;
        showFilePreview(e.dataTransfer.files[0]);
      }
    });
    fileInput.addEventListener('change', () => {
      if (fileInput.files.length) showFilePreview(fileInput.files[0]);
    });
  }

  // Funcion: Muestra una vista previa del comprobante subido.
  function showFilePreview(file) {
    if (!filePreview || !fileName) return;
    fileName.textContent = file.name + ' (' + (file.size / 1024 / 1024).toFixed(2) + ' MB)';
    filePreview.style.display = 'flex';
    if (uploadZone) uploadZone.style.display = 'none';
  }

  if (fileRemove) {
    fileRemove.addEventListener('click', () => {
      if (fileInput) fileInput.value = '';
      if (filePreview) filePreview.style.display = 'none';
      if (uploadZone) uploadZone.style.display = 'flex';
    });
  }
});
</script>
