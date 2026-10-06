<!-- Funcion del archivo: Renderiza el resumen administrativo con KPIs y graficos. -->
<main id="main-content" class="amain">
  <div class="atopbar">
    <div style="display:flex;align-items:center;gap:10px">
      <button class="amenu-toggle" id="aMenuToggle" aria-label="Menú"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M4 12h16M4 17h16"/></svg></button>
      <div>
        <h1>Dashboard general</h1>
        <p>Resumen del rendimiento de la agencia · histórico consolidado</p>
      </div>
    </div>
    <div class="atopbar__actions">
      <button class="btn btn--primary btn--sm" id="btnExportDashboard"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle;flex-shrink:0"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg> Exportar</button>
    </div>
  </div>

  <div class="kpi-grid">
    <div class="kpi">
      <div class="kpi__head"><span class="kpi__label">Ingresos</span><div class="kpi__icon">$</div></div>
      <div class="kpi__value">S/ <?= number_format($ingresosTotal, 0, '.', ',') ?></div>
      <span class="kpi__delta kpi__delta--up">validados en BD</span>
    </div>
    <div class="kpi">
      <div class="kpi__head"><span class="kpi__label">Reservas</span><div class="kpi__icon">📦</div></div>
      <div class="kpi__value"><?= $reservasCount ?></div>
      <span class="kpi__delta kpi__delta--up">en el sistema</span>
    </div>
    <div class="kpi">
      <div class="kpi__head"><span class="kpi__label">Visas tramitadas</span><div class="kpi__icon">📄</div></div>
      <div class="kpi__value"><?= $visasCount ?></div>
      <span class="kpi__delta kpi__delta--up">asesorías</span>
    </div>
    <div class="kpi">
      <div class="kpi__head"><span class="kpi__label">Clientes</span><div class="kpi__icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg></div></div>
      <div class="kpi__value"><?= $clientesCount ?></div>
      <span class="kpi__delta">cuentas registradas</span>
    </div>
  </div>

  <!-- Revenue Chart — Full Width -->
  <div class="panel" style="margin-bottom:24px">
    <div class="panel__hd">
      <div>
        <h3>Ingresos mensuales</h3>
        <div class="sub">Total del año <?= date('Y') ?>: S/ <?= number_format($ingresosTotal, 0, '.', ',') ?></div>
      </div>
      <div><span class="badge badge--info"><?= date('Y') ?></span></div>
    </div>
    <div class="chart-box" style="height:380px"><canvas id="chRev"></canvas></div>
  </div>

  <!-- Donut + Reservas Row -->
  <div class="dash-grid">
    <div class="panel">
      <div class="panel__hd">
        <h3>Distribución por destino</h3>
      </div>
      <div class="chart-box" style="height:300px"><canvas id="chDonut"></canvas></div>
      <ul class="legend legend--compact">
        <?php if (!empty($destinosDistribucion)): ?>
          <?php foreach ($destinosDistribucion as $d): ?>
            <li>
              <span class="dot" style="background:<?= $d['color'] ?>"></span>
              <?= htmlspecialchars($d['nombre']) ?> · <?= $d['pct'] ?>% (<?= $d['qty'] ?>)
            </li>
          <?php endforeach; ?>
        <?php else: ?>
          <li style="color:var(--c-ink-500)">No hay datos de destinos suficientes.</li>
        <?php endif; ?>
      </ul>
    </div>
    <div class="panel">
      <div class="panel__hd">
        <h3>Últimas reservas</h3>
        <a href="<?= BASE_URL ?>/admin/ventas" class="btn btn--ghost btn--sm">Ver todas →</a>
      </div>
      <div class="table-wrap" style="border:0;box-shadow:none">
        <table class="table">
          <thead>
            <tr>
              <th>Cliente</th>
              <th>Paquete</th>
              <th>Fecha</th>
              <th>Monto</th>
              <th>Estado</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($ultimasReservas)): ?>
              <?php foreach ($ultimasReservas as $res): ?>
                <?php 
                  $statusClass = 's-pending';
                  $statusLabel = 'Pendiente';
                  if ($res['estado'] === 'pagado') {
                      $statusClass = 's-active';
                      $statusLabel = 'Confirmado';
                  } elseif ($res['estado'] === 'cancelado') {
                      $statusClass = 's-cancel';
                      $statusLabel = 'Cancelado';
                  }
                ?>
                <tr>
                  <td>
                    <strong><?= htmlspecialchars($res['nombres'] . ' ' . $res['apellidos']) ?></strong>
                    <br><span style="color:var(--c-ink-500);font-size:12px"><?= htmlspecialchars($res['email']) ?></span>
                  </td>
                  <td><?= htmlspecialchars($res['paquete_nombre'] ?? 'Paquete Turístico') ?></td>
                  <td><?= date('d M Y', strtotime($res['created_at'])) ?></td>
                  <td>S/ <?= number_format($res['monto_total'], 0) ?></td>
                  <td><span class="status-pill <?= $statusClass ?>"><?= $statusLabel ?></span></td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="5" style="text-align: center; color: var(--c-ink-500)">No se registraron reservas en el sistema.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</main>

<!-- Inyección de gráficos interactivos usando la librería charts.js -->
<script src="<?= BASE_URL ?>/assets/js/ui/charts.js" defer></script>
<script nonce="<?= htmlspecialchars(\App\Helper\SecurityHeaders::getNonce(), ENT_QUOTES, 'UTF-8') ?>">
document.addEventListener('DOMContentLoaded', function(){
  setTimeout(function(){
    if (window.AJTCharts){
      // 1. Gráfico de líneas (Ingresos Mensuales)
      const lineCanvas = document.getElementById('chRev');
      if (lineCanvas) {
        const lineData = <?= json_encode($ingresosMes) ?>;
        AJTCharts.line(lineCanvas, lineData, {
          labels: ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic']
        });
      }

      // 2. Gráfico circular (Distribución de Destinos)
      const donutCanvas = document.getElementById('chDonut');
      if (donutCanvas) {
        const rawDest = <?= json_encode($destinosDistribucion) ?>;
        const donutData = rawDest.map(d => ({
          value: d.pct || 1, 
          color: d.color
        }));

        // Si está vacío, cargar por defecto
        if (donutData.length === 0) {
          donutData.push({value: 100, color: '#94a3b8'});
        }

        AJTCharts.donut(donutCanvas, donutData, {
          center: '<?= $reservasCount ?>', 
          centerSub: 'reservas'
        });
      }
    }
  }, 150);

  // Botón Exportar
  const btnExport = document.getElementById('btnExportDashboard');
  if (btnExport) {
    btnExport.addEventListener('click', function(){
      window.toast({type: 'success', text: 'Resumen del dashboard preparado para impresión.'});
      window.print();
    });
  }
});
</script>
