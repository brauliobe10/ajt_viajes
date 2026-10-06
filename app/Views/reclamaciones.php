<?php

// Funcion del archivo: Renderiza el formulario y seguimiento de reclamos del cliente.
/**
 * Vista pública del Libro de Reclamaciones (Ley 29571).
 * Variables esperadas: $usuario, $hasServices, $reclamaciones, $reservas
 */
$categorias = [
    'Atención al cliente',
    'Información del paquete',
    'Problema con reserva',
    'Problema con pago',
    'Problema con visa',
    'Otro',
];
$estadosMap = [
    'nuevo' => 'Nuevo',
    'en_revision' => 'En revisión',
    'respondido' => 'Respondido',
    'cerrado' => 'Cerrado',
];
?>

<main id="main-content" class="container section" style="padding-top:48px;padding-bottom:48px">
  <nav class="breadcrumbs" style="margin-bottom:18px">
    <a href="<?= BASE_URL ?>/">Inicio</a>
    <span class="sep">/</span>
    <span class="current">Libro de reclamaciones</span>
  </nav>

  <article style="max-width:860px">
    <h1>Libro de Reclamaciones</h1>
    <p style="color:var(--c-ink-600);margin-bottom:12px">
      De acuerdo con la <strong>Ley N.° 29571</strong>, Código de Protección y Defensa del Consumidor, ponemos a disposición nuestro libro de reclamaciones virtual.
    </p>
    <p style="color:var(--c-ink-500);margin-bottom:32px;font-size:var(--fs-14)">
      Aquí puedes registrar reclamos o sugerencias relacionados con nuestros servicios turísticos, reservas y asesoría de visas.
    </p>

    <!-- Alerts -->
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

    <?php if (!$usuario): ?>
      <!-- No logueado -->
      <div style="background:var(--c-surface-muted);border:1px solid var(--c-border);border-radius:var(--r-lg);padding:32px;text-align:center;color:var(--c-ink-600)">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="var(--c-ink-400)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin:0 auto 12px;display:block"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
        <p style="font-size:var(--fs-15);margin:0 0 8px;font-weight:var(--fw-semibold)">Inicia sesión para registrar tu reclamo</p>
        <p style="font-size:var(--fs-13);margin:0 0 16px;color:var(--c-ink-500)">Debes tener una cuenta y haber realizado al menos una compra, reserva o solicitud de servicio.</p>
        <a href="<?= BASE_URL ?>/auth/login" class="btn btn--primary">Iniciar sesión</a>
      </div>

    <?php elseif (!$hasServices): ?>
      <!-- Logueado sin servicios -->
      <div style="background:var(--c-surface-muted);border:1px solid var(--c-border);border-radius:var(--r-lg);padding:32px;text-align:center;color:var(--c-ink-600)">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="var(--c-ink-400)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin:0 auto 12px;display:block"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
        <p style="font-size:var(--fs-15);margin:0 0 8px;font-weight:var(--fw-semibold)">Sin servicios registrados</p>
        <p style="font-size:var(--fs-13);margin:0;color:var(--c-ink-500)">Solo los usuarios que hayan realizado una compra, reserva o solicitud de servicio pueden registrar reclamos o sugerencias.</p>
        <div style="margin-top:16px">
          <a href="<?= BASE_URL ?>/catalog" class="btn btn--primary btn--sm">Explorar paquetes</a>
          <a href="<?= BASE_URL ?>/visas" class="btn btn--ghost btn--sm" style="margin-left:8px">Asesoría de visas</a>
        </div>
      </div>

    <?php else: ?>
      <!-- Formulario + historial -->
      <form action="<?= BASE_URL ?>/reclamaciones" method="POST" style="background:var(--c-surface);border:1px solid var(--c-border);border-radius:var(--r-lg);padding:28px;margin-bottom:32px">
        <?= App\Helper\Csrf::insertInput() ?>

        <h3 style="margin:0 0 18px">Registrar reclamo o sugerencia</h3>

        <div class="field">
          <label style="font-weight:var(--fw-semibold)">Tipo *</label>
          <div style="display:flex;gap:18px;margin-top:6px">
            <label style="display:flex;align-items:center;gap:6px;cursor:pointer">
              <input type="radio" name="tipo" value="reclamo" required checked> Reclamo
            </label>
            <label style="display:flex;align-items:center;gap:6px;cursor:pointer">
              <input type="radio" name="tipo" value="sugerencia"> Sugerencia
            </label>
          </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px" class="field-grid">
          <div class="field">
            <label style="font-weight:var(--fw-semibold)">Reserva asociada <span style="color:var(--c-ink-400);font-weight:400">(opcional)</span></label>
            <select class="input" name="reserva_id" style="margin-top:6px">
              <option value="">Sin reserva asociada</option>
              <?php foreach ($reservas as $res): ?>
                <?php if ($res['id_reserva']): ?>
                  <option value="<?= $res['id_reserva'] ?>">
                    <?= htmlspecialchars($res['codigo_reserva']) ?> — <?= htmlspecialchars($res['paquete_nombre'] ?? 'Paquete') ?>
                  </option>
                <?php endif; ?>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="field">
            <label style="font-weight:var(--fw-semibold)">Categoría *</label>
            <select class="input" name="categoria" required style="margin-top:6px">
              <option value="">Seleccionar categoría</option>
              <?php foreach ($categorias as $cat): ?>
                <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="field" style="margin-top:16px">
          <label style="font-weight:var(--fw-semibold)">Descripción del reclamo o sugerencia *</label>
          <textarea class="input" name="descripcion" required rows="4" placeholder="Describa detalladamente su reclamo o sugerencia..." style="margin-top:6px;resize:vertical"></textarea>
        </div>

        <div class="field" style="margin-top:16px">
          <label style="font-weight:var(--fw-semibold)">Pedido del cliente / Solución esperada</label>
          <textarea class="input" name="pedido_cliente" rows="3" placeholder="¿Qué solución espera para su caso?" style="margin-top:6px;resize:vertical"></textarea>
        </div>

        <div class="field" style="margin-top:16px">
          <label style="font-weight:var(--fw-semibold)">Medio de respuesta preferido *</label>
          <div style="display:flex;gap:18px;margin-top:6px">
            <label style="display:flex;align-items:center;gap:6px;cursor:pointer">
              <input type="radio" name="medio_respuesta" value="correo" required checked> Correo
            </label>
            <label style="display:flex;align-items:center;gap:6px;cursor:pointer">
              <input type="radio" name="medio_respuesta" value="whatsapp"> WhatsApp
            </label>
            <label style="display:flex;align-items:center;gap:6px;cursor:pointer">
              <input type="radio" name="medio_respuesta" value="telefono"> Teléfono
            </label>
          </div>
        </div>

        <div style="display:flex;justify-content:flex-end;margin-top:22px">
          <button type="submit" class="btn btn--primary">Registrar reclamación</button>
        </div>
      </form>

      <!-- Historial del usuario -->
      <?php if (!empty($reclamaciones)): ?>
        <div style="margin-top:8px">
          <h3 style="margin:0 0 14px">Mis reclamaciones anteriores</h3>
          <div class="table-wrap" style="border:0;box-shadow:none">
            <table class="table">
              <thead>
                <tr>
                  <th>N.°</th>
                  <th>Tipo</th>
                  <th>Categoría</th>
                  <th>Estado</th>
                  <th>Fecha</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($reclamaciones as $rec): ?>
                  <tr>
                    <td><strong>#<?= str_pad($rec['id'], 5, '0', STR_PAD_LEFT) ?></strong></td>
                    <td><?= ucfirst(htmlspecialchars($rec['tipo'])) ?></td>
                    <td><?= htmlspecialchars($rec['categoria']) ?></td>
                    <td><span class="status-pill <?= $rec['estado'] === 'nuevo' ? 's-pending' : ($rec['estado'] === 'respondido' ? 's-active' : ($rec['estado'] === 'cerrado' ? 's-cancel' : 's-pending')) ?>"><?= $estadosMap[$rec['estado']] ?? ucfirst($rec['estado']) ?></span></td>
                    <td><?= date('d M Y', strtotime($rec['created_at'])) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      <?php endif; ?>
    <?php endif; ?>

    <p style="margin-top:32px">
      <a href="<?= BASE_URL ?>/" class="btn btn--ghost">← Volver al inicio</a>
    </p>
  </article>
</main>
