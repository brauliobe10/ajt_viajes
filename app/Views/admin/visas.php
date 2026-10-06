<!-- Funcion del archivo: Renderiza la gesti?n administrativa de solicitudes de visa. -->
<style>
  /* Modal admin adaptado a claro/oscuro sin alterar la gestión de solicitudes. */
  .trip-modal {
    position: fixed;
    inset: 0;
    z-index: 100;
    display: grid;
    place-items: center;
    padding: 20px;
  }
  .trip-modal[hidden] {
    display: none;
  }
  .trip-modal__backdrop {
    position: absolute;
    inset: 0;
    background: var(--c-overlay);
    backdrop-filter: blur(4px);
  }
  .trip-modal__panel {
    position: relative;
    background: var(--c-surface);
    border-radius: var(--r-lg);
    max-width: 680px;
    width: 100%;
    max-height: 90vh;
    overflow: auto;
    box-shadow: var(--sh-3);
    border: 1px solid var(--c-border);
    animation: fadeIn .25s var(--ease-out);
  }
  .trip-modal__x {
    position: absolute;
    top: 14px;
    right: 14px;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: 0;
    background: var(--c-surface-muted);
    color: var(--c-ink-900);
    font-size: 22px;
    cursor: pointer;
    z-index: 2;
  }
  .trip-modal__cnt {
    padding: 24px 28px 28px;
  }
  .trip-modal__cnt h2 {
    margin: 0 0 4px;
    font-size: var(--fs-22);
  }
</style>

<main id="main-content" class="amain">
  <div class="atopbar">
    <div style="display:flex;align-items:center;gap:10px">
      <button class="amenu-toggle" id="aMenuToggle" aria-label="Menú"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M4 12h16M4 17h16"/></svg></button>
      <div>
        <h1>Trámites de visas</h1>
        <p>Seguimiento y gestión de solicitudes de visa</p>
      </div>
    </div>
    <div class="atopbar__actions">
      <input class="input" type="search" id="visaAdminSearch" placeholder="Buscar trámite o cliente..." style="width:280px">
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
    $enProcesoCount = 0;
    $aprobadasCount = 0;
    $rechazadasCount = 0;
    foreach ($solicitudes as $s) {
        $est = $s['estado'];
        if (in_array($est, ['nueva','contactado','documentos_pendientes','en_revision','en_tramite'])) $enProcesoCount++;
        elseif ($est === 'aprobada') $aprobadasCount++;
        elseif (in_array($est, ['rechazada','cancelada'])) $rechazadasCount++;
    }
    $totalCount = count($solicitudes);
    $exitoRate = $totalCount > 0 ? round(($aprobadasCount / $totalCount) * 100) : 100;
  ?>

  <div class="kpi-grid">
    <div class="kpi">
      <div class="kpi__head"><span class="kpi__label">En proceso</span><div class="kpi__icon">⏳</div></div>
      <div class="kpi__value"><?= $enProcesoCount ?></div>
      <span class="kpi__delta">activos</span>
    </div>
    <div class="kpi">
      <div class="kpi__head"><span class="kpi__label">Aprobadas</span><div class="kpi__icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></div></div>
      <div class="kpi__value"><?= $aprobadasCount ?></div>
      <span class="kpi__delta kpi__delta--up">sistema</span>
    </div>
    <div class="kpi">
      <div class="kpi__head"><span class="kpi__label">Rechazadas</span><div class="kpi__icon">✗</div></div>
      <div class="kpi__value"><?= $rechazadasCount ?></div>
      <span class="kpi__delta">sistema</span>
    </div>
    <div class="kpi">
      <div class="kpi__head"><span class="kpi__label">Tasa éxito</span><div class="kpi__icon">📈</div></div>
      <div class="kpi__value"><?= $exitoRate ?>%</div>
      <span class="kpi__delta kpi__delta--up">global</span>
    </div>
  </div>

  <div class="panel">
    <div class="panel__hd">
      <h3>Solicitudes de Asesoría de Visas</h3>
    </div>
    <div class="table-wrap" style="border:0;box-shadow:none">
      <table class="table">
        <thead>
          <tr>
            <th>Cliente</th>
            <th>Tipo de visa</th>
            <th>País</th>
            <th>Código Solicitud</th>
            <th>Fecha estimada viaje</th>
            <th>Estado</th>
            <th></th>
          </tr>
        </thead>
        <tbody id="visaTableBody">
          <?php if (!empty($solicitudes)): ?>
            <?php foreach ($solicitudes as $s): ?>
              <?php 
                $statusMap = [
                    'nueva' => ['class' => 's-pending', 'label' => 'Nueva'],
                    'contactado' => ['class' => 's-pending', 'label' => 'Contactado'],
                    'documentos_pendientes' => ['class' => 's-pending', 'label' => 'Docs pendientes'],
                    'en_revision' => ['class' => 's-pending', 'label' => 'En revisión'],
                    'en_tramite' => ['class' => 's-active', 'label' => 'En trámite'],
                    'aprobada' => ['class' => 's-active', 'label' => 'Aprobada'],
                    'rechazada' => ['class' => 's-cancel', 'label' => 'Rechazada'],
                    'cancelada' => ['class' => 's-cancel', 'label' => 'Cancelada'],
                ];
                $statusInfo = $statusMap[$s['estado']] ?? ['class' => 's-pending', 'label' => ucfirst($s['estado'])];
                $statusClass = $statusInfo['class'];
                $statusLabel = $statusInfo['label'];
                $fechaCita = !empty($s['fecha_viaje_aprox']) ? date('d M Y', strtotime($s['fecha_viaje_aprox'])) : '—';
              ?>
              <tr>
                <td>
                  <strong><?= htmlspecialchars($s['usuario_nombres'] . ' ' . $s['usuario_apellidos']) ?></strong>
                  <div class="small" style="color:var(--c-ink-500)"><?= htmlspecialchars($s['usuario_email']) ?></div>
                </td>
                <td><?= htmlspecialchars($s['tipo_visa']) ?></td>
                <td><?= htmlspecialchars($s['pais_nombre']) ?></td>
                <td><strong><?= htmlspecialchars($s['codigo_solicitud']) ?></strong></td>
                <td><?= $fechaCita ?></td>
                <td><span class="status-pill <?= $statusClass ?>"><?= $statusLabel ?></span></td>
                <td class="actions">
                  <button type="button" 
                          class="btn btn--ghost btn--sm js-detail-visa"
                          data-cliente="<?= htmlspecialchars($s['usuario_nombres'] . ' ' . $s['usuario_apellidos']) ?>"
                          data-email="<?= htmlspecialchars($s['usuario_email']) ?>"
                          data-visa="<?= htmlspecialchars($s['tipo_visa']) ?>"
                          data-pais="<?= htmlspecialchars($s['pais_nombre']) ?>"
                          data-codigo="<?= htmlspecialchars($s['codigo_solicitud']) ?>"
                          data-precio="S/ <?= number_format((float)$s['precio_asesoria'], 0) ?>"
                          data-fecha="<?= !empty($s['fecha_viaje_aprox']) ? date('d M Y', strtotime($s['fecha_viaje_aprox'])) : 'No especificada' ?>"
                          data-solicitud="<?= date('d M Y, H:i', strtotime($s['created_at'])) ?>"
                          data-estado-label="<?= $statusLabel ?>">
                    Ver detalle
                  </button>
                  <button type="button" 
                          class="btn btn--ghost btn--sm js-manage-visa"
                          data-id="<?= $s['id_solicitud'] ?>"
                          data-cliente="<?= htmlspecialchars($s['usuario_nombres'] . ' ' . $s['usuario_apellidos']) ?>"
                          data-visa="<?= htmlspecialchars($s['tipo_visa']) ?>"
                          data-pais="<?= htmlspecialchars($s['pais_nombre']) ?>"
                          data-estado="<?= $s['estado'] ?>">
                    Gestionar
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="7" style="text-align: center; color: var(--c-ink-500)">No se encontraron solicitudes de visa registradas.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>

<!-- Modal Detalle de Solicitud -->
<div id="detailVisaModal" class="trip-modal" hidden>
  <div class="trip-modal__backdrop" data-close></div>
  <div class="trip-modal__panel" role="dialog" aria-modal="true" style="max-width: 560px;">
    <button class="trip-modal__x" data-close aria-label="Cerrar">×</button>
    <div class="trip-modal__cnt">
      <h2 id="detailModalTitle">Detalle de Solicitud</h2>
      <p style="color:var(--c-ink-500);margin:0 0 16px" id="detailModalSubtitle"></p>

      <div style="display:grid;gap:12px;font-size:14px">
        <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #e2e8f0">
          <strong style="color:var(--c-ink-500)">Cliente</strong>
          <span id="detailCliente">—</span>
        </div>
        <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #e2e8f0">
          <strong style="color:var(--c-ink-500)">Email</strong>
          <span id="detailEmail">—</span>
        </div>
        <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #e2e8f0">
          <strong style="color:var(--c-ink-500)">Tipo de Visa</strong>
          <span id="detailVisa">—</span>
        </div>
        <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #e2e8f0">
          <strong style="color:var(--c-ink-500)">País</strong>
          <span id="detailPais">—</span>
        </div>
        <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #e2e8f0">
          <strong style="color:var(--c-ink-500)">Código Solicitud</strong>
          <span id="detailCodigo">—</span>
        </div>
        <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #e2e8f0">
          <strong style="color:var(--c-ink-500)">Precio Asesoría</strong>
          <span id="detailPrecio">—</span>
        </div>
        <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #e2e8f0">
          <strong style="color:var(--c-ink-500)">Fecha Estimada Viaje</strong>
          <span id="detailFecha">—</span>
        </div>
        <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #e2e8f0">
          <strong style="color:var(--c-ink-500)">Fecha Solicitud</strong>
          <span id="detailSolicitud">—</span>
        </div>
        <div style="display:flex;justify-content:space-between;padding:8px 0">
          <strong style="color:var(--c-ink-500)">Estado Actual</strong>
          <span id="detailEstado">—</span>
        </div>
      </div>

      <div style="display:flex;justify-content:flex-end;margin-top:22px">
        <button type="button" class="btn btn--primary" data-close>Cerrar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Gestionar Visa -->
<div id="manageVisaModal" class="trip-modal" hidden>
  <div class="trip-modal__backdrop" data-close></div>
  <div class="trip-modal__panel" role="dialog" aria-modal="true" style="max-width: 480px;">
    <button class="trip-modal__x" data-close aria-label="Cerrar">×</button>
    <div class="trip-modal__cnt">
      <h2>Gestionar Trámite de Visa</h2>
      <p style="color:var(--c-ink-500);margin:0 0 16px" id="manageModalTitle"></p>
      
      <form action="<?= BASE_URL ?>/admin/visas/update-estado" method="POST" id="manageModalForm">
        <?= App\Helper\Csrf::insertInput() ?>
        <input type="hidden" name="id_solicitud" id="manageModalSolicitudId">
        
        <div class="field">
          <label>Estado del Trámite</label>
          <select class="input" name="estado" id="manageModalEstado" required style="margin-top: 6px;">
            <option value="nueva">Nueva</option>
            <option value="contactado">Contactado</option>
            <option value="documentos_pendientes">Documentos pendientes</option>
            <option value="en_revision">En revisión</option>
            <option value="en_tramite">En trámite</option>
            <option value="aprobada">Aprobada</option>
            <option value="rechazada">Rechazada</option>
            <option value="cancelada">Cancelada</option>
          </select>
        </div>
        
        <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:22px">
          <button type="button" class="btn btn--ghost" data-close>Cancelar</button>
          <button type="submit" class="btn btn--primary">Guardar Cambios</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script nonce="<?= htmlspecialchars(\App\Helper\SecurityHeaders::getNonce(), ENT_QUOTES, 'UTF-8') ?>">
document.addEventListener('DOMContentLoaded', () => {
  // Buscador
  const searchInput = document.getElementById('visaAdminSearch');
  const tableBody = document.getElementById('visaTableBody');
  
  if (searchInput && tableBody) {
    searchInput.addEventListener('input', () => {
      const query = searchInput.value.trim().toLowerCase();
      tableBody.querySelectorAll('tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(query) ? '' : 'none';
      });
    });
  }

  // Modales
  const manageModal = document.getElementById('manageVisaModal');
  const manageModalTitle = document.getElementById('manageModalTitle');
  const manageModalSolicitudId = document.getElementById('manageModalSolicitudId');
  const manageModalEstado = document.getElementById('manageModalEstado');

  const detailModal = document.getElementById('detailVisaModal');

  // Gestionar (cambiar estado)
  document.querySelectorAll('.js-manage-visa').forEach(btn => {
    btn.addEventListener('click', () => {
      const data = btn.dataset;
      manageModalTitle.textContent = data.cliente + ' · ' + data.visa + ' (' + data.pais + ')';
      manageModalSolicitudId.value = data.id;
      manageModalEstado.value = data.estado;
      manageModal.hidden = false;
      document.body.style.overflow = 'hidden';
    });
  });

  // Ver detalle
  document.querySelectorAll('.js-detail-visa').forEach(btn => {
    btn.addEventListener('click', () => {
      const data = btn.dataset;
      document.getElementById('detailModalTitle').textContent = 'Solicitud ' + data.codigo;
      document.getElementById('detailModalSubtitle').textContent = data.cliente + ' · ' + data.pais;
      document.getElementById('detailCliente').textContent = data.cliente;
      document.getElementById('detailEmail').textContent = data.email;
      document.getElementById('detailVisa').textContent = data.visa;
      document.getElementById('detailPais').textContent = data.pais;
      document.getElementById('detailCodigo').textContent = data.codigo;
      document.getElementById('detailPrecio').textContent = data.precio;
      document.getElementById('detailFecha').textContent = data.fecha;
      document.getElementById('detailSolicitud').textContent = data.solicitud;
      document.getElementById('detailEstado').textContent = data.estadoLabel;
      detailModal.hidden = false;
      document.body.style.overflow = 'hidden';
    });
  });

  // Cerrar modales
  document.querySelectorAll('.trip-modal').forEach(m => {
    m.addEventListener('click', e => {
      if (e.target.hasAttribute('data-close') || e.target.classList.contains('trip-modal__x')) {
        m.hidden = true;
        document.body.style.overflow = '';
      }
    });
  });
});
</script>
