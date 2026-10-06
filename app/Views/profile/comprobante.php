<!-- Funcion del archivo: Renderiza el comprobante imprimible de una reserva. -->
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $title ?? 'Comprobante — Viajes AJT' ?></title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Segoe UI', Roboto, Arial, sans-serif; color: #1a1a1a; background: #f5f5f5; padding: 24px; line-height: 1.6; }
    .comprobante { max-width: 800px; margin: 0 auto; background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 40px; }
    .comprobante__header { text-align: center; border-bottom: 2px solid #1a3c7a; padding-bottom: 20px; margin-bottom: 28px; }
    .comprobante__header h1 { font-size: 22px; color: #1a3c7a; margin-bottom: 4px; }
    .comprobante__header p { color: #666; font-size: 14px; }
    .comprobante__code { display: inline-block; background: #eef2ff; border: 1px dashed #7b93d4; padding: 10px 24px; border-radius: 6px; font-size: 20px; font-weight: 700; letter-spacing: 0.12em; color: #1a3c7a; margin: 16px 0; }
    .comprobante__section { margin-bottom: 24px; }
    .comprobante__section h3 { font-size: 15px; color: #1a3c7a; border-bottom: 1px solid #eee; padding-bottom: 6px; margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.06em; }
    .comprobante__grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px 24px; }
    .comprobante__field { display: flex; flex-direction: column; }
    .comprobante__field .label { font-size: 12px; color: #888; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 2px; }
    .comprobante__field .value { font-size: 15px; font-weight: 600; color: #1a1a1a; }
    .comprobante__table { width: 100%; border-collapse: collapse; font-size: 14px; }
    .comprobante__table td { padding: 8px 0; }
    .comprobante__table td:last-child { text-align: right; }
    .comprobante__table .total td { border-top: 2px solid #1a3c7a; font-weight: 700; font-size: 16px; padding-top: 12px; color: #1a3c7a; }
    .comprobante__status { display: inline-block; padding: 4px 14px; border-radius: 20px; font-size: 13px; font-weight: 700; }
    .comprobante__status--pagado { background: #dcfce7; color: #166534; }
    .comprobante__status--pendiente { background: #fef3c7; color: #92400e; }
    .comprobante__footer { margin-top: 32px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 13px; color: #666; text-align: center; }
    .comprobante__footer p { margin: 4px 0; }
    .btn-print { display: inline-block; background: #1a3c7a; color: #fff; border: none; padding: 12px 32px; border-radius: 6px; font-size: 15px; font-weight: 600; cursor: pointer; margin-top: 20px; }
    .btn-print:hover { background: #2a5298; }
    .no-print { text-align: center; margin-top: 16px; }
    .no-print a { color: #1a3c7a; font-size: 14px; }

    @media print {
      body { background: #fff; padding: 0; }
      .comprobante { border: none; border-radius: 0; box-shadow: none; padding: 20px; max-width: 100%; }
      .no-print { display: none !important; }
      .navbar, .sidebar, .footer, .psidebar, .asidebar { display: none !important; }
    }
  </style>
</head>
<body>
  <div class="comprobante">
    <div class="comprobante__header">
      <h1>Viajes AJT</h1>
      <p>Comprobante de Reserva</p>
      <div class="comprobante__code"><?= htmlspecialchars($reserva['codigo_reserva']) ?></div>
    </div>

    <!-- Datos del paquete -->
    <div class="comprobante__section">
      <h3>Paquete reservado</h3>
      <div class="comprobante__grid">
        <div class="comprobante__field">
          <span class="label">Paquete</span>
          <span class="value"><?= htmlspecialchars($reserva['paquete_nombre']) ?></span>
        </div>
        <div class="comprobante__field">
          <span class="label">Destino</span>
          <span class="value"><?= htmlspecialchars($reserva['ciudad_nombre'] ?? 'Consultar') ?></span>
        </div>
        <div class="comprobante__field">
          <span class="label">Fecha de viaje</span>
          <span class="value"><?= htmlspecialchars($reserva['fecha_viaje']) ?></span>
        </div>
        <div class="comprobante__field">
          <span class="label">Duracion</span>
          <span class="value"><?= (int)$reserva['duracion_dias'] ?> dias / <?= (int)$reserva['duracion_noches'] ?> noches</span>
        </div>
        <div class="comprobante__field">
          <span class="label">Pasajeros</span>
          <span class="value"><?= (int)$reserva['cantidad_pasajeros'] ?> persona<?= (int)$reserva['cantidad_pasajeros'] > 1 ? 's' : '' ?></span>
        </div>
      </div>
    </div>

    <!-- Viajeros -->
    <div class="comprobante__section">
      <h3>Viajeros</h3>
      <?php if (!empty($reserva['viajeros'])): ?>
        <table class="comprobante__table">
          <tr>
            <td style="font-weight:700;">Viajero</td>
            <td style="font-weight:700;">Documento</td>
          </tr>
          <?php foreach ($reserva['viajeros'] as $idx => $v): ?>
          <tr>
            <td><?= htmlspecialchars($v['nombres'] . ' ' . $v['apellidos']) ?> <?= $idx === 0 ? '(Principal)' : '' ?></td>
            <td><?= htmlspecialchars($v['num_documento']) ?></td>
          </tr>
          <?php endforeach; ?>
        </table>
      <?php endif; ?>
      <?php if (!empty($reserva['telefono_pasajero'])): ?>
        <div class="comprobante__field" style="margin-top:10px;">
          <span class="label">Telefono de contacto</span>
          <span class="value"><?= htmlspecialchars($reserva['telefono_pasajero']) ?></span>
        </div>
      <?php endif; ?>
    </div>

    <!-- Pago -->
    <div class="comprobante__section">
      <h3>Informacion de pago</h3>
      <div class="comprobante__grid">
        <div class="comprobante__field">
          <span class="label">Metodo de pago</span>
          <span class="value"><?= htmlspecialchars($reserva['metodo_pago_nombre'] ?? 'No especificado') ?></span>
        </div>
        <div class="comprobante__field">
          <span class="label">Nro. operacion</span>
          <span class="value"><?= htmlspecialchars($reserva['pago_operacion'] ?? '-') ?></span>
        </div>
        <div class="comprobante__field">
          <span class="label">Estado</span>
          <span class="value">
            <?php $estadoReserva = $reserva['estado'] ?? 'pendiente'; ?>
            <span class="comprobante__status <?= ($estadoReserva === 'pagado') ? 'comprobante__status--pagado' : 'comprobante__status--pendiente' ?>">
              <?= ($estadoReserva === 'pagado') ? 'Pagado' : 'Pendiente' ?>
            </span>
          </span>
        </div>
        <?php if (!empty($reserva['comprobante_url'])): ?>
        <div class="comprobante__field">
          <span class="label">Comprobante adjunto</span>
          <span class="value"><a href="<?= BASE_URL ?>/<?= htmlspecialchars($reserva['comprobante_url']) ?>" target="_blank">Ver archivo</a></span>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Desglose financiero -->
    <div class="comprobante__section">
      <h3>Desglose financiero</h3>
      <table class="comprobante__table">
        <tr>
          <td>Subtotal (<?= (int)$reserva['cantidad_pasajeros'] ?> pax x <?= $moneda ?><?= number_format($precioUnitario, 2) ?>)</td>
          <td><?= $moneda ?> <?= number_format($subtotal, 2) ?></td>
        </tr>
        <?php if ($descuento > 0): ?>
        <tr>
          <td>Descuento grupo (10%)</td>
          <td>- <?= $moneda ?> <?= number_format($descuento, 2) ?></td>
        </tr>
        <?php endif; ?>
        <tr>
          <td>IGV (18%)</td>
          <td><?= $moneda ?> <?= number_format($igv, 2) ?></td>
        </tr>
        <tr class="total">
          <td>Total</td>
          <td><?= $moneda ?> <?= number_format($total, 2) ?></td>
        </tr>
      </table>
    </div>

    <!-- Footer -->
    <div class="comprobante__footer">
      <p><strong>Viajes AJT</strong></p>
      <p>Este documento es un comprobante de reserva. No constituye factura.</p>
      <p>Generado el <?= date('d/m/Y H:i') ?></p>
    </div>
  </div>

  <div class="no-print">
    <button class="btn-print" id="btnPrintComprobante">Imprimir comprobante</button>
    <script nonce="<?= htmlspecialchars(\App\Helper\SecurityHeaders::getNonce(), ENT_QUOTES, 'UTF-8') ?>">
      document.getElementById('btnPrintComprobante').addEventListener('click', function() {
        window.print();
      });
    </script>
    <br>
    <a href="<?= BASE_URL ?>/profile/mi-perfil">Volver a mi perfil</a>
  </div>
</body>
</html>
