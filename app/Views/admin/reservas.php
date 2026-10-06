<!-- Funcion del archivo: Renderiza reservas con estado, comprobante y acci?n de validaci?n. -->
<main id="main-content" class="amain">
  <div class="atopbar">
    <div style="display:flex;align-items:center;gap:10px">
      <button class="amenu-toggle" id="aMenuToggle" aria-label="Menú"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M4 12h16M4 17h16"/></svg></button>
      <div>
        <h1>Reservas</h1>
        <p>Gestión de reservas, pagos, comprobantes y exportación</p>
      </div>
    </div>
    <div class="atopbar__actions">
      <input class="input" type="search" id="reservasSearch" placeholder="Buscar reserva, cliente o paquete..." style="width:280px">
      <a href="<?= BASE_URL ?>/admin/reservas/exportar" class="btn btn--primary btn--sm"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle;flex-shrink:0"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg> Exportar CSV</a>
    </div>
  </div>

  <!-- Alertas del sistema -->
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

  <?php
    $estadoActual = isset($_GET['estado']) ? trim($_GET['estado']) : 'todas';
    $pendientes = 0; $confirmadas = 0; $canceladas = 0;
    foreach ($reservas as $r) {
        if ($r['estado'] === 'pendiente') $pendientes++;
        elseif ($r['estado'] === 'pagado') $confirmadas++;
        elseif ($r['estado'] === 'cancelado') $canceladas++;
    }
  ?>

  <div class="kpi-grid">
    <div class="kpi">
      <div class="kpi__head"><span class="kpi__label">Pendientes</span><div class="kpi__icon">⏳</div></div>
      <div class="kpi__value"><?= $pendientes ?></div>
      <span class="kpi__delta">por confirmar</span>
    </div>
    <div class="kpi">
      <div class="kpi__head"><span class="kpi__label">Confirmadas</span><div class="kpi__icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></div></div>
      <div class="kpi__value"><?= $confirmadas ?></div>
      <span class="kpi__delta kpi__delta--up">pagadas</span>
    </div>
    <div class="kpi">
      <div class="kpi__head"><span class="kpi__label">Canceladas</span><div class="kpi__icon">✗</div></div>
      <div class="kpi__value"><?= $canceladas ?></div>
      <span class="kpi__delta">anuladas</span>
    </div>
    <div class="kpi">
      <div class="kpi__head"><span class="kpi__label">Total</span><div class="kpi__icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg></div></div>
      <div class="kpi__value"><?= count($reservas) ?></div>
      <span class="kpi__delta">reservas en sistema</span>
    </div>
  </div>

  <div class="panel">
    <div class="panel__hd">
      <h3>Listado de reservas</h3>
      <form method="GET" action="<?= BASE_URL ?>/admin/reservas" style="display:flex;align-items:center;gap:8px">
        <select class="input" id="reservasEstadoSelect" name="estado" style="width:180px">
          <option value="todas" <?= $estadoActual === 'todas' ? 'selected' : '' ?>>Todas las reservas</option>
          <option value="pendiente" <?= $estadoActual === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
          <option value="pagado" <?= $estadoActual === 'pagado' ? 'selected' : '' ?>>Confirmada</option>
          <option value="cancelado" <?= $estadoActual === 'cancelado' ? 'selected' : '' ?>>Cancelada</option>
        </select>
      </form>
    </div>
    <div class="table-wrap" style="border:0;box-shadow:none">
      <table class="table">
        <thead>
          <tr>
            <th>Código</th>
            <th>Cliente</th>
            <th>Paquete</th>
            <th>Fecha</th>
            <th>Total</th>
            <th>Comprobante</th>
            <th>Estado</th>
            <th>Acción</th>
          </tr>
        </thead>
        <tbody id="reservasTableBody">
          <?php if (!empty($reservas)): ?>
            <?php foreach ($reservas as $r): ?>
              <?php
                $statusClass = 's-pending';
                $statusLabel = 'Pendiente';
                if ($r['estado'] === 'pagado') {
                    $statusClass = 's-active';
                    $statusLabel = 'Confirmada';
                } elseif ($r['estado'] === 'cancelado') {
                    $statusClass = 's-cancel';
                    $statusLabel = 'Cancelada';
                }
                $fechaViaje = !empty($r['fecha_viaje']) ? date('d M Y', strtotime($r['fecha_viaje'])) : '—';
              ?>
              <tr>
                <td><strong><?= htmlspecialchars($r['codigo_reserva']) ?></strong></td>
                <td>
                  <strong><?= htmlspecialchars($r['cliente_nombre']) ?></strong>
                  <div class="small" style="color:var(--c-ink-500)"><?= htmlspecialchars($r['cliente_email']) ?></div>
                </td>
                <td><?= htmlspecialchars($r['paquete_nombre']) ?></td>
                <td><?= $fechaViaje ?></td>
                <td>S/ <?= number_format(round(((float)$r['precio_unitario'] * (int)$r['viajeros'] - (float)$r['descuento']) * 1.18, 2), 2) ?></td>
                <td>
                  <?php if (!empty($r['comprobante_url'])): ?>
                    <a href="<?= BASE_URL ?>/<?= htmlspecialchars($r['comprobante_url']) ?>" target="_blank" rel="noopener" class="btn btn--ghost btn--sm">Ver comprobante</a>
                  <?php else: ?>
                    <span style="color:var(--c-ink-500);font-size:var(--fs-12)">Sin archivo</span>
                  <?php endif; ?>
                </td>
                <td><span class="status-pill <?= $statusClass ?>"><?= $statusLabel ?></span></td>
                <td class="actions">
                  <form method="POST" action="<?= BASE_URL ?>/admin/reservas/cambiar-estado" style="display:flex;gap:6px;align-items:center">
                    <?= App\Helper\Csrf::insertInput() ?>
                    <input type="hidden" name="id_reserva" value="<?= (int)$r['id_reserva'] ?>">
                    <select class="input" name="nuevo_estado" style="width:130px;font-size:12px;padding:4px 6px">
                      <option value="pendiente" <?= $r['estado'] === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                      <option value="pagado" <?= $r['estado'] === 'pagado' ? 'selected' : '' ?>>Confirmada</option>
                      <option value="cancelado" <?= $r['estado'] === 'cancelado' ? 'selected' : '' ?>>Cancelada</option>
                    </select>
                    <button type="submit" class="btn btn--ghost btn--sm" title="Actualizar estado">Guardar</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="8" style="text-align: center; color: var(--c-ink-500)">No se encontraron reservas registradas.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>

<script nonce="<?= htmlspecialchars(\App\Helper\SecurityHeaders::getNonce(), ENT_QUOTES, 'UTF-8') ?>">
document.addEventListener('DOMContentLoaded', function(){
  var selectEstado = document.getElementById('reservasEstadoSelect');
  if (selectEstado) {
    selectEstado.addEventListener('change', function() {
      this.form.submit();
    });
  }
  var searchInput = document.getElementById('reservasSearch');
  var tableBody = document.getElementById('reservasTableBody');
  if (searchInput && tableBody) {
    searchInput.addEventListener('input', function(){
      var q = searchInput.value.trim().toLowerCase();
      tableBody.querySelectorAll('tr').forEach(function(tr){
        tr.style.display = tr.textContent.toLowerCase().indexOf(q) !== -1 ? '' : 'none';
      });
    });
  }
});
</script>
