<!-- Funcion del archivo: Renderiza reportes de ventas, metricas y transacciones. -->
<main id="main-content" class="amain">
  <style>
    .ventas-section{margin-bottom:28px}
    .ventas-chart-box{height:360px}
    .tp-card{display:flex;align-items:center;gap:14px;padding:14px 16px;background:var(--c-surface-muted);border:1px solid var(--c-border);border-radius:var(--r-md);transition:border-color var(--t-fast)}
    .tp-card:hover{border-color:var(--c-primary-300)}
    .tp-card__rank{width:32px;height:32px;border-radius:50%;background:var(--c-primary-100);color:var(--c-primary-700);display:grid;place-items:center;font-weight:800;font-size:var(--fs-13);flex-shrink:0}
    .tp-card__info{flex:1;min-width:0}
    .tp-card__name{font-weight:var(--fw-bold);font-size:var(--fs-14);color:var(--c-ink-900);margin:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .tp-card__bar{height:6px;border-radius:3px;background:var(--c-ink-100);margin-top:6px;overflow:hidden}
    .tp-card__bar-fill{height:100%;border-radius:3px;background:linear-gradient(90deg,var(--c-primary-500),var(--c-primary-700));transition:width .4s var(--ease-out)}
    .tp-card__amount{font-weight:var(--fw-bold);font-size:var(--fs-15);color:var(--c-ink-900);white-space:nowrap}
    .ventas-table tbody tr:nth-child(even){background:var(--c-surface-muted)}
    .ventas-table tbody tr:hover{background:var(--c-primary-50)}
    @media(max-width:980px){.ventas-chart-box{height:280px}}
  </style>
  <div class="atopbar">
    <div style="display:flex;align-items:center;gap:10px">
      <button class="amenu-toggle" id="aMenuToggle" aria-label="Menú">☰</button>
      <div>
        <h1>Ventas & Reportes</h1>
        <p>Análisis de rendimiento y exportación de datos transaccionales</p>
      </div>
    </div>
    <div class="atopbar__actions">
      <input class="input" type="search" id="salesSearch" placeholder="Buscar venta, cliente o paquete..." style="width:280px">
      <a href="<?= BASE_URL ?>/admin/ventas/exportar" class="btn btn--primary btn--sm"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle;flex-shrink:0"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg> Exportar CSV</a>
      <a href="<?= BASE_URL ?>/admin/reservas/exportar" class="btn btn--primary btn--sm"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle;flex-shrink:0"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg> Exportar Reservas</a>
    </div>
  </div>

  <!-- Alertas de sesión -->
  <?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert--success">
      <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
    </div>
  <?php endif; ?>

  <div class="kpi-grid ventas-section">
    <div class="kpi">
      <div class="kpi__head"><span class="kpi__label">Total vendido</span><div class="kpi__icon">$</div></div>
      <div class="kpi__value">S/ <?= number_format($totalVendido, 0, '.', ',') ?></div>
      <span class="kpi__delta kpi__delta--up">pagos validados</span>
    </div>
    <div class="kpi">
      <div class="kpi__head"><span class="kpi__label">Pagos confirmados</span><div class="kpi__icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></div></div>
      <div class="kpi__value"><?= $pagosConfirmados ?></div>
      <span class="kpi__delta kpi__delta--up">transacciones</span>
    </div>
    <div class="kpi">
      <div class="kpi__head"><span class="kpi__label">Pagos pendientes</span><div class="kpi__icon">⏳</div></div>
      <div class="kpi__value"><?= $pagosPendientes ?></div>
      <span class="kpi__delta">por validar</span>
    </div>
    <div class="kpi">
      <div class="kpi__head"><span class="kpi__label">Ticket promedio</span><div class="kpi__icon">🎫</div></div>
      <div class="kpi__value">S/ <?= number_format($ticketPromedio, 0, '.', ',') ?></div>
      <span class="kpi__delta">por pago confirmado</span>
    </div>
  </div>

  <div class="dual ventas-section">
    <div class="panel">
      <div class="panel__hd"><h3>Ventas por mes (S/)</h3></div>
      <div class="chart-box ventas-chart-box"><canvas id="chBars"></canvas></div>
    </div>
    <div class="panel">
      <div class="panel__hd"><h3>Top paquetes por ingresos</h3></div>
      <div style="display:grid;gap:10px">
        <?php if (!empty($topPaquetes)): ?>
          <?php foreach ($topPaquetes as $idx => $tp): ?>
            <?php 
              $pct = $maxTotal > 0 ? round(((float)$tp['total'] / $maxTotal) * 100) : 0;
            ?>
            <div class="tp-card">
              <div class="tp-card__rank"><?= $idx + 1 ?></div>
              <div class="tp-card__info">
                <p class="tp-card__name"><?= htmlspecialchars($tp['nombre']) ?></p>
                <div class="tp-card__bar"><div class="tp-card__bar-fill" style="width:<?= $pct ?>%"></div></div>
              </div>
              <div class="tp-card__amount">S/ <?= number_format($tp['total'], 0, '.', ',') ?></div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p style="color:var(--c-ink-500)">No hay datos de paquetes vendidos.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="panel ventas-section">
    <div class="panel__hd">
      <h3>Detalle de transacciones</h3>
      <div style="display:flex;align-items:center;gap:8px">
        <form method="GET" action="<?= BASE_URL ?>/admin/ventas" style="display:flex;align-items:center;gap:8px">
          <select class="input" id="ventasMetodoSelect" name="metodo" style="width:180px">
            <option value="" <?= empty($filtroMetodo) ? 'selected' : '' ?>>Todos los métodos</option>
            <?php if (!empty($metodosPago)): ?>
              <?php foreach ($metodosPago as $mp): ?>
                <option value="<?= htmlspecialchars($mp['nombre']) ?>" <?= ($filtroMetodo ?? '') === $mp['nombre'] ? 'selected' : '' ?>><?= htmlspecialchars($mp['nombre']) ?></option>
              <?php endforeach; ?>
            <?php endif; ?>
          </select>
        </form>
        <span class="badge"><?= count($transacciones) ?> ventas</span>
      </div>
    </div>
    <div class="table-wrap" style="border:0;box-shadow:none">
      <table class="table ventas-table" id="salesTable">
        <thead>
          <tr>
            <th>Código reserva</th>
            <th>Cliente</th>
            <th>Paquete</th>
            <th>Método de pago</th>
            <th>Monto</th>
            <th>Estado del pago</th>
            <th>Fecha</th>
            <th>Comprobante</th>
          </tr>
        </thead>
        <tbody id="salesTableBody">
          <?php if (!empty($transacciones)): ?>
            <?php foreach ($transacciones as $t): ?>
              <?php 
                $statusClass = 's-pending';
                $statusLabel = 'Pendiente';
                if ($t['pago_estado'] === 'validado') {
                    $statusClass = 's-active';
                    $statusLabel = 'Confirmado';
                } elseif ($t['pago_estado'] === 'rechazado') {
                    $statusClass = 's-cancel';
                    $statusLabel = 'Rechazado';
                }
              ?>
              <tr>
                <td><strong><?= htmlspecialchars($t['codigo_reserva']) ?></strong></td>
                <td><?= htmlspecialchars($t['cliente_nombre']) ?></td>
                <td><?= htmlspecialchars($t['paquete_nombre']) ?></td>
                <td><?= htmlspecialchars($t['metodo_pago_nombre'] ?? 'Sin registrar') ?></td>
                <td>S/ <?= number_format((float)($t['monto_pago'] ?? $t['monto_total']), 0) ?></td>
                <td><span class="status-pill <?= $statusClass ?>"><?= $statusLabel ?></span></td>
                <td><?= date('d M Y', strtotime($t['created_at'])) ?></td>
                <td>
                  <?php if (!empty($t['comprobante_url'])): ?>
                    <a href="<?= BASE_URL ?>/<?= htmlspecialchars($t['comprobante_url']) ?>" target="_blank" class="btn btn--ghost btn--sm">Ver</a>
                  <?php else: ?>
                    <span style="color:var(--c-ink-500)">—</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="8" style="text-align: center; color: var(--c-ink-500)">No se encontraron transacciones registradas.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>

<script src="<?= BASE_URL ?>/assets/js/ui/charts.js" defer></script>
<script nonce="<?= htmlspecialchars(\App\Helper\SecurityHeaders::getNonce(), ENT_QUOTES, 'UTF-8') ?>">
document.addEventListener('DOMContentLoaded', function(){
  const selectMetodo = document.getElementById('ventasMetodoSelect');
  if (selectMetodo) {
    selectMetodo.addEventListener('change', function() {
      this.form.submit();
    });
  }
  // Buscador interactivo
  const searchInput = document.getElementById('salesSearch');
  const tableBody = document.getElementById('salesTableBody');
  if (searchInput && tableBody) {
    searchInput.addEventListener('input', () => {
      const q = searchInput.value.trim().toLowerCase();
      tableBody.querySelectorAll('tr').forEach(tr => {
        tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
      });
    });
  }

  // Gráfico de barras — datos reales desde Reporte::getMonthlyIncome()
  setTimeout(function(){
    if (window.AJTCharts && document.getElementById('chBars')) {
      AJTCharts.bars(document.getElementById('chBars'),
        <?= json_encode($ingresosMensuales, JSON_UNESCAPED_UNICODE) ?>,
        {labels:['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic']});
    }
  }, 150);

});
</script>
