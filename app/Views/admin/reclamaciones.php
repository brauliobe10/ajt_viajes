<?php

// Funcion del archivo: Renderiza la gesti?n administrativa de reclamos y respuestas.
/**
 * Vista admin del Libro de Reclamaciones.
 * Variables esperadas: $reclamaciones
 */
$estadosMap = [
    'nuevo' => 'Nuevo',
    'en_revision' => 'En revisión',
    'respondido' => 'Respondido',
    'cerrado' => 'Cerrado',
];
$tipoMap = [
    'reclamo' => 'Reclamo',
    'sugerencia' => 'Sugerencia',
];
$medioMap = [
    'whatsapp' => 'WhatsApp',
    'correo' => 'Correo',
    'telefono' => 'Teléfono',
];
?>

<style>
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
        <h1>Libro de Reclamaciones</h1>
        <p>Gestión de reclamos y sugerencias de clientes (Ley 29571)</p>
      </div>
    </div>
    <div class="atopbar__actions">
      <input class="input" type="search" id="recAdminSearch" placeholder="Buscar por cliente, categoría..." style="width:280px">
    </div>
  </div>

  <!-- Alertas -->
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
    $nuevosCount = 0; $enRevisionCount = 0; $respondidosCount = 0; $cerradosCount = 0;
    $reclamosCount = 0; $sugerenciasCount = 0;
    foreach ($reclamaciones as $r) {
      if ($r['estado'] === 'nuevo') $nuevosCount++;
      elseif ($r['estado'] === 'en_revision') $enRevisionCount++;
      elseif ($r['estado'] === 'respondido') $respondidosCount++;
      elseif ($r['estado'] === 'cerrado') $cerradosCount++;
      if ($r['tipo'] === 'reclamo') $reclamosCount++;
      else $sugerenciasCount++;
    }
  ?>

  <div class="kpi-grid">
    <div class="kpi">
      <div class="kpi__head"><span class="kpi__label">Nuevos</span><div class="kpi__icon">📩</div></div>
      <div class="kpi__value"><?= $nuevosCount ?></div>
      <span class="kpi__delta">pendientes</span>
    </div>
    <div class="kpi">
      <div class="kpi__head"><span class="kpi__label">En revisión</span><div class="kpi__icon">⏳</div></div>
      <div class="kpi__value"><?= $enRevisionCount ?></div>
      <span class="kpi__delta">activos</span>
    </div>
    <div class="kpi">
      <div class="kpi__head"><span class="kpi__label">Respondidos</span><div class="kpi__icon">✅</div></div>
      <div class="kpi__value"><?= $respondidosCount ?></div>
      <span class="kpi__delta kpi__delta--up">atendidos</span>
    </div>
    <div class="kpi">
      <div class="kpi__head"><span class="kpi__label">Total</span><div class="kpi__icon">📊</div></div>
      <div class="kpi__value"><?= count($reclamaciones) ?></div>
      <span class="kpi__delta"><?= $reclamosCount ?> reclamos · <?= $sugerenciasCount ?> sugerencias</span>
    </div>
  </div>

  <!-- Filtros -->
  <div style="display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap">
    <select class="input" id="filterTipo" style="width:auto">
      <option value="">Todos los tipos</option>
      <option value="reclamo">Reclamo</option>
      <option value="sugerencia">Sugerencia</option>
    </select>
    <select class="input" id="filterEstado" style="width:auto">
      <option value="">Todos los estados</option>
      <option value="nuevo">Nuevo</option>
      <option value="en_revision">En revisión</option>
      <option value="respondido">Respondido</option>
      <option value="cerrado">Cerrado</option>
    </select>
  </div>

  <div class="panel">
    <div class="panel__hd">
      <h3>Reclamaciones registradas</h3>
    </div>
    <div class="table-wrap" style="border:0;box-shadow:none">
      <table class="table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Usuario</th>
            <th>Tipo</th>
            <th>Categoría</th>
            <th>Estado</th>
            <th>Fecha</th>
            <th></th>
          </tr>
        </thead>
        <tbody id="recTableBody">
          <?php if (!empty($reclamaciones)): ?>
            <?php foreach ($reclamaciones as $rec): ?>
              <?php
                $statusClass = 's-pending';
                if ($rec['estado'] === 'en_revision') $statusClass = 's-active';
                elseif ($rec['estado'] === 'respondido') $statusClass = 's-active';
                elseif ($rec['estado'] === 'cerrado') $statusClass = 's-cancel';

                $estadoLabel = $estadosMap[$rec['estado']] ?? ucfirst($rec['estado']);
                $tipoLabel = $tipoMap[$rec['tipo']] ?? ucfirst($rec['tipo']);
              ?>
              <tr data-tipo="<?= htmlspecialchars($rec['tipo']) ?>" data-estado="<?= htmlspecialchars($rec['estado']) ?>">
                <td><strong>#<?= str_pad($rec['id'], 5, '0', STR_PAD_LEFT) ?></strong></td>
                <td>
                  <strong><?= htmlspecialchars($rec['usuario_nombres'] . ' ' . $rec['usuario_apellidos']) ?></strong>
                  <div class="small" style="color:var(--c-ink-500)"><?= htmlspecialchars($rec['usuario_email']) ?></div>
                </td>
                <td><?= $tipoLabel ?></td>
                <td><?= htmlspecialchars($rec['categoria']) ?></td>
                <td><span class="status-pill <?= $statusClass ?>"><?= $estadoLabel ?></span></td>
                <td><?= date('d M Y', strtotime($rec['created_at'])) ?></td>
                <td class="actions">
                  <button type="button"
                          class="btn btn--ghost btn--sm js-detail-rec"
                          data-id="<?= $rec['id'] ?>"
                          data-codigo="#<?= str_pad($rec['id'], 5, '0', STR_PAD_LEFT) ?>"
                          data-cliente="<?= htmlspecialchars($rec['usuario_nombres'] . ' ' . $rec['usuario_apellidos']) ?>"
                          data-email="<?= htmlspecialchars($rec['usuario_email']) ?>"
                          data-tipo="<?= $tipoLabel ?>"
                          data-categoria="<?= htmlspecialchars($rec['categoria']) ?>"
                          data-descripcion="<?= htmlspecialchars($rec['descripcion']) ?>"
                          data-pedido="<?= htmlspecialchars($rec['pedido_cliente'] ?? '') ?>"
                          data-medio="<?= $medioMap[$rec['medio_respuesta']] ?? $rec['medio_respuesta'] ?>"
                          data-estado-label="<?= $estadoLabel ?>"
                          data-reserva="<?= htmlspecialchars($rec['codigo_reserva'] ?? '—') ?>"
                          data-paquete="<?= htmlspecialchars($rec['paquete_nombre'] ?? '—') ?>"
                          data-fecha="<?= date('d M Y, H:i', strtotime($rec['created_at'])) ?>">
                    Ver detalle
                  </button>
                  <button type="button"
                          class="btn btn--ghost btn--sm js-manage-rec"
                          data-id="<?= $rec['id'] ?>"
                          data-codigo="#<?= str_pad($rec['id'], 5, '0', STR_PAD_LEFT) ?>"
                          data-cliente="<?= htmlspecialchars($rec['usuario_nombres'] . ' ' . $rec['usuario_apellidos']) ?>"
                          data-estado="<?= $rec['estado'] ?>"
                          data-respuesta="<?= htmlspecialchars($rec['respuesta_admin'] ?? '') ?>">
                    Gestionar
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="7" style="text-align:center;color:var(--c-ink-500)">No hay reclamaciones registradas.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>

<!-- Modal Detalle -->
<div id="detailRecModal" class="trip-modal" hidden>
  <div class="trip-modal__backdrop" data-close></div>
  <div class="trip-modal__panel" role="dialog" aria-modal="true" style="max-width:560px">
    <button class="trip-modal__x" data-close aria-label="Cerrar">×</button>
    <div class="trip-modal__cnt">
      <h2 id="detailRecTitle">Detalle de Reclamación</h2>
      <p style="color:var(--c-ink-500);margin:0 0 16px" id="detailRecSubtitle"></p>

      <div style="display:grid;gap:12px;font-size:14px">
        <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--c-border)">
          <strong style="color:var(--c-ink-500)">Cliente</strong>
          <span id="detailRecCliente">—</span>
        </div>
        <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--c-border)">
          <strong style="color:var(--c-ink-500)">Email</strong>
          <span id="detailRecEmail">—</span>
        </div>
        <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--c-border)">
          <strong style="color:var(--c-ink-500)">Tipo</strong>
          <span id="detailRecTipo">—</span>
        </div>
        <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--c-border)">
          <strong style="color:var(--c-ink-500)">Categoría</strong>
          <span id="detailRecCategoria">—</span>
        </div>
        <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--c-border)">
          <strong style="color:var(--c-ink-500)">Reserva</strong>
          <span id="detailRecReserva">—</span>
        </div>
        <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--c-border)">
          <strong style="color:var(--c-ink-500)">Medio respuesta</strong>
          <span id="detailRecMedio">—</span>
        </div>
        <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--c-border)">
          <strong style="color:var(--c-ink-500)">Fecha</strong>
          <span id="detailRecFecha">—</span>
        </div>
        <div style="display:flex;justify-content:space-between;padding:8px 0">
          <strong style="color:var(--c-ink-500)">Estado</strong>
          <span id="detailRecEstado">—</span>
        </div>
      </div>

      <div style="margin-top:16px">
        <strong style="color:var(--c-ink-500);font-size:14px">Descripción</strong>
        <p style="margin:6px 0 0;font-size:14px;background:var(--c-surface-muted);padding:12px;border-radius:var(--r-md);white-space:pre-wrap" id="detailRecDesc">—</p>
      </div>

      <div style="margin-top:12px">
        <strong style="color:var(--c-ink-500);font-size:14px">Pedido del cliente</strong>
        <p style="margin:6px 0 0;font-size:14px;background:var(--c-surface-muted);padding:12px;border-radius:var(--r-md);white-space:pre-wrap;min-height:40px" id="detailRecPedido">—</p>
      </div>

      <div style="display:flex;justify-content:flex-end;margin-top:22px">
        <button type="button" class="btn btn--primary" data-close>Cerrar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Gestionar -->
<div id="manageRecModal" class="trip-modal" hidden>
  <div class="trip-modal__backdrop" data-close></div>
  <div class="trip-modal__panel" role="dialog" aria-modal="true" style="max-width:480px">
    <button class="trip-modal__x" data-close aria-label="Cerrar">×</button>
    <div class="trip-modal__cnt">
      <h2>Gestionar Reclamación</h2>
      <p style="color:var(--c-ink-500);margin:0 0 16px" id="manageRecTitle"></p>

      <form action="<?= BASE_URL ?>/admin/reclamaciones/update-estado" method="POST" id="manageRecForm">
        <?= App\Helper\Csrf::insertInput() ?>
        <input type="hidden" name="id" id="manageRecId">

        <div class="field">
          <label>Estado</label>
          <select class="input" name="estado" id="manageRecEstado" required style="margin-top:6px">
            <option value="nuevo">Nuevo</option>
            <option value="en_revision">En revisión</option>
            <option value="respondido">Respondido</option>
            <option value="cerrado">Cerrado</option>
          </select>
        </div>

        <div class="field" style="margin-top:12px">
          <label>Respuesta del administrador</label>
          <textarea class="input" name="respuesta_admin" id="manageRecRespuesta" rows="4" placeholder="Escriba la respuesta o acción tomada..." style="margin-top:6px;resize:vertical"></textarea>
        </div>

        <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:22px">
          <button type="button" class="btn btn--ghost" data-close>Cancelar</button>
          <button type="submit" class="btn btn--primary">Guardar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script nonce="<?= htmlspecialchars(\App\Helper\SecurityHeaders::getNonce(), ENT_QUOTES, 'UTF-8') ?>">
document.addEventListener('DOMContentLoaded', () => {
  const searchInput = document.getElementById('recAdminSearch');
  const tableBody = document.getElementById('recTableBody');
  const filterTipo = document.getElementById('filterTipo');
  const filterEstado = document.getElementById('filterEstado');

  // Funcion: Aplica los filtros visibles a la tabla actual.
  function applyFilters() {
    const query = searchInput.value.trim().toLowerCase();
    const tipo = filterTipo.value;
    const estado = filterEstado.value;
    tableBody.querySelectorAll('tr').forEach(row => {
      const matchesSearch = !query || row.textContent.toLowerCase().includes(query);
      const matchesTipo = !tipo || row.dataset.tipo === tipo;
      const matchesEstado = !estado || row.dataset.estado === estado;
      row.style.display = (matchesSearch && matchesTipo && matchesEstado) ? '' : 'none';
    });
  }

  if (searchInput) searchInput.addEventListener('input', applyFilters);
  if (filterTipo) filterTipo.addEventListener('change', applyFilters);
  if (filterEstado) filterEstado.addEventListener('change', applyFilters);

  // Detail modal
  const detailModal = document.getElementById('detailRecModal');
  document.querySelectorAll('.js-detail-rec').forEach(btn => {
    btn.addEventListener('click', () => {
      const d = btn.dataset;
      document.getElementById('detailRecTitle').textContent = 'Reclamación ' + d.codigo;
      document.getElementById('detailRecSubtitle').textContent = d.cliente;
      document.getElementById('detailRecCliente').textContent = d.cliente;
      document.getElementById('detailRecEmail').textContent = d.email;
      document.getElementById('detailRecTipo').textContent = d.tipo;
      document.getElementById('detailRecCategoria').textContent = d.categoria;
      document.getElementById('detailRecReserva').textContent = d.reserva !== '—' ? d.reserva + ' (' + d.paquete + ')' : 'Sin reserva';
      document.getElementById('detailRecMedio').textContent = d.medio;
      document.getElementById('detailRecFecha').textContent = d.fecha;
      document.getElementById('detailRecEstado').textContent = d.estadoLabel;
      document.getElementById('detailRecDesc').textContent = d.descripcion || '—';
      document.getElementById('detailRecPedido').textContent = d.pedido || 'No especificado';
      detailModal.hidden = false;
      document.body.style.overflow = 'hidden';
    });
  });

  // Manage modal
  const manageModal = document.getElementById('manageRecModal');
  document.querySelectorAll('.js-manage-rec').forEach(btn => {
    btn.addEventListener('click', () => {
      const d = btn.dataset;
      document.getElementById('manageRecTitle').textContent = d.cliente + ' · ' + d.codigo;
      document.getElementById('manageRecId').value = d.id;
      document.getElementById('manageRecEstado').value = d.estado;
      document.getElementById('manageRecRespuesta').value = d.respuesta || '';
      manageModal.hidden = false;
      document.body.style.overflow = 'hidden';
    });
  });

  // Close modals
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
