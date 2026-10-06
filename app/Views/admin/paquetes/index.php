<!-- Funcion del archivo: Renderiza el listado administrativo de paquetes y su disponibilidad. -->
<main id="main-content" class="amain">
  <div class="atopbar">
    <div style="display:flex;align-items:center;gap:10px">
      <button class="amenu-toggle" id="aMenuToggle" aria-label="Menú">☰</button>
      <div>
        <h1>Paquetes turísticos</h1>
        <p>Gestiona el catálogo de la agencia</p>
      </div>
    </div>
    <div class="atopbar__actions">
      <a href="<?= BASE_URL ?>/admin/paquetes/create" class="btn btn--primary">+ Nuevo paquete</a>
    </div>
  </div>

  <!-- Mensajes Flash de Éxito / Error -->
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

  <div class="panel" style="margin-bottom:18px">
    <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
      <input class="input" id="pkgSearch" placeholder="🔍 Buscar paquete..." style="flex:1;min-width:220px">
      

      <select class="select" id="pkgCategoryFilter" style="width:auto">
        <option value="">Todas las categorías</option>
        <?php if (!empty($categorias)): ?>
          <?php foreach ($categorias as $c): ?>
            <option value="<?= htmlspecialchars(mb_strtolower($c['nombre'])) ?>"><?= htmlspecialchars($c['nombre']) ?></option>
          <?php endforeach; ?>
        <?php endif; ?>
      </select>
      
      <select class="select" id="pkgStatusFilter" style="width:auto">
        <option value="">Todos los estados</option>
        <option value="active">Activo</option>
        <option value="cancel">Inactivo</option>
      </select>
    </div>
  </div>

  <div class="panel" style="padding:0">
    <div class="table-wrap" style="border:0;box-shadow:none;border-radius:var(--r-lg)">
      <table class="table">
        <thead>
          <tr>
            <th></th>
            <th>Paquete</th>
            <th>Categoría</th>
            <th>Ciudad / Destino</th>
            <th>Duración</th>
            <th>Precio</th>
            <th>Reservas</th>
            <th>Estado</th>
            <th></th>
          </tr>
        </thead>
        <tbody id="pkTbody">
          <?php if (empty($paquetes)): ?>
            <tr>
              <td colspan="9" style="text-align:center; padding: 30px; color: var(--c-ink-400);">
                No hay paquetes registrados en el catálogo.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($paquetes as $p): ?>
              <?php 
                $imgUrl = !empty($p['imagen_url']) ? htmlspecialchars($p['imagen_url']) : BASE_URL . '/assets/img/hero/hero-home.jpg';
                $statusClass = $p['disponible'] ? 'active' : 'cancel';
                $statusText = $p['disponible'] ? 'Activo' : 'Inactivo';
                $destType = ((int)($p['id_pais'] ?? 0) === 1) ? 'nacionales' : 'internacionales';
              ?>
              <tr data-name="<?= htmlspecialchars($p['nombre']) ?>" 
                  data-slug="<?= htmlspecialchars($p['slug']) ?>" 
                  data-category="<?= htmlspecialchars($p['categoria_nombre']) ?>" 
                  data-dest="<?= $destType ?>"
                  data-status="<?= $statusClass ?>">
                <td>
                  <img class="thumb" src="<?= $imgUrl ?>" data-fallback-src="<?= BASE_URL ?>/assets/img/hero/hero-home.jpg" alt="">
                </td>
                <td>
                  <strong><?= htmlspecialchars($p['nombre']) ?></strong><br>
                  <span style="color:var(--c-ink-500);font-size:12px"><?= htmlspecialchars($p['slug']) ?></span>
                </td>
                <td><?= htmlspecialchars($p['categoria_nombre']) ?></td>
                <td><?= htmlspecialchars($p['ciudad_nombre']) ?></td>
                <td><?= (int)$p['duracion_dias'] ?>D / <?= (int)$p['duracion_noches'] ?>N</td>
                <td><strong>S/ <?= number_format($p['precio_base'], 2) ?></strong></td>
                <td><?= (int)$p['reservas_count'] ?></td>
                <td>
                  <span class="status-pill s-<?= $statusClass ?>"><?= $statusText ?></span>
                </td>
                <td style="vertical-align:middle;white-space:nowrap">
                  <div class="actions">
                    <a class="btn btn--ghost btn--sm" href="<?= BASE_URL ?>/admin/paquetes/edit/<?= $p['id_paquete'] ?>">Editar</a>
                    <form method="POST" action="<?= BASE_URL ?>/admin/paquetes/toggle-disponible/<?= $p['id_paquete'] ?>" style="margin:0;" class="toggle-form" data-nombre="<?= htmlspecialchars($p['nombre']) ?>" data-estado="<?= $p['disponible'] ?>">
                      <?= App\Helper\Csrf::insertInput() ?>
                      <button class="btn btn--ghost btn--sm btn-toggle-disponible <?= $p['disponible'] ? 'btn--danger' : 'btn--primary' ?>" type="button">
                        <?= $p['disponible'] ? 'Desactivar' : 'Activar' ?>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    
    <div style="display:flex;justify-content:between;align-items:center;padding:14px 20px;color:var(--c-ink-500);font-size:var(--fs-13);border-top:1px solid var(--c-ink-100)">
      <span id="pkgCount">Mostrando <?= count($paquetes) ?> paquetes en total</span>
    </div>
  </div>
</main>

<!-- Modal de confirmación desactivar/activar -->
<div class="trip-modal" id="toggleModal" hidden>
  <div class="trip-modal__backdrop" id="toggleModalBackdrop"></div>
  <div class="trip-modal__panel" style="max-width:480px">
    <div class="trip-modal__cnt" style="text-align:center;padding:32px 28px">
      <div style="width:56px;height:56px;border-radius:50%;background:var(--c-warning-100);color:var(--c-warning-600);display:grid;place-items:center;margin:0 auto 16px;font-size:28px">!</div>
      <h2 id="toggleModalTitle" style="margin:0 0 8px;font-size:var(--fs-18)">Desactivar paquete</h2>
      <p id="toggleModalText" style="color:var(--c-ink-600);font-size:var(--fs-14);margin:0 0 24px;line-height:1.6">
        Este paquete dejará de mostrarse en el catálogo público, pero su historial de reservas y pagos se conservará.
      </p>
      <div style="display:flex;gap:10px;justify-content:center">
        <button type="button" class="btn btn--ghost btn-cancel-toggle">Cancelar</button>
        <button type="button" class="btn btn--accent" id="toggleModalConfirm">Desactivar</button>
      </div>
    </div>
  </div>
</div>

<style>
  .trip-modal{position:fixed;inset:0;z-index:100;display:grid;place-items:center;padding:20px}
  .trip-modal[hidden]{display:none}
  .trip-modal__backdrop{position:absolute;inset:0;background:var(--c-overlay);backdrop-filter:blur(4px)}
  .trip-modal__panel{position:relative;background:var(--c-surface);border-radius:var(--r-lg);max-width:480px;width:100%;box-shadow:var(--sh-3);border:1px solid var(--c-border);animation:fadeIn .25s var(--ease-out)}
</style>

<script nonce="<?= htmlspecialchars(\App\Helper\SecurityHeaders::getNonce(), ENT_QUOTES, 'UTF-8') ?>">
  document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btn-toggle-disponible').forEach(btn => {
      btn.addEventListener('click', function() { openToggleModal(this); });
    });
    var backdrop = document.getElementById('toggleModalBackdrop');
    if (backdrop) backdrop.addEventListener('click', closeToggleModal);
    document.querySelectorAll('.btn-cancel-toggle').forEach(btn => {
      btn.addEventListener('click', closeToggleModal);
    });
  });

  var currentToggleForm = null;

  // Funcion: Abre el modal para cambiar disponibilidad.
  function openToggleModal(btn) {
    var form = btn.closest('.toggle-form');
    if (!form) return;
    currentToggleForm = form;
    var nombre = form.dataset.nombre;
    var isActive = form.dataset.estado === '1';
    document.getElementById('toggleModalTitle').textContent = isActive ? 'Desactivar paquete' : 'Activar paquete';
    document.getElementById('toggleModalText').textContent = isActive
      ? '"' + nombre + '" dejará de mostrarse en el catálogo público, pero su historial de reservas y pagos se conservará.'
      : '"' + nombre + '" volverá a aparecer en el catálogo público.';
    document.getElementById('toggleModalConfirm').textContent = isActive ? 'Desactivar' : 'Activar';
    document.getElementById('toggleModalConfirm').className = isActive ? 'btn btn--accent' : 'btn btn--primary';
    document.getElementById('toggleModal').hidden = false;
  }

  // Funcion: Cierra el modal de disponibilidad.
  function closeToggleModal() {
    document.getElementById('toggleModal').hidden = true;
    currentToggleForm = null;
  }

  document.getElementById('toggleModalConfirm').addEventListener('click', function() {
    if (currentToggleForm) currentToggleForm.submit();
  });

  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && !document.getElementById('toggleModal').hidden) closeToggleModal();
  });

document.addEventListener('DOMContentLoaded', function() {
  var pkgSearch = document.getElementById('pkgSearch');
  var pkgCategoryFilter = document.getElementById('pkgCategoryFilter');
  var pkgStatusFilter = document.getElementById('pkgStatusFilter');
  var pkgCount = document.getElementById('pkgCount');
  
  // Funcion: Aplica los filtros visibles a la tabla actual.
  function applyFilters() {
    var q = pkgSearch.value.trim().toLowerCase();
    var category = pkgCategoryFilter ? pkgCategoryFilter.value.trim().toLowerCase() : '';
    var status = pkgStatusFilter.value;
    
    var rows = document.querySelectorAll('#pkTbody tr');
    if (!rows.length || rows[0].cells.length === 1) return; // Si la tabla está vacía
    
    var visibleCount = 0;
    rows.forEach(function(row) {
      var name = row.getAttribute('data-name').toLowerCase();
      var slug = row.getAttribute('data-slug').toLowerCase();
      var cat = row.getAttribute('data-category').toLowerCase();
      var st = row.getAttribute('data-status');
      
      var matchesText = name.includes(q) || slug.includes(q) || cat.includes(q);
      var matchesCategory = !category || cat === category;
      var matchesStatus = !status || st === status;
      
      if (matchesText && matchesCategory && matchesStatus) {
        row.style.display = '';
        visibleCount++;
      } else {
        row.style.display = 'none';
      }
    });
    
    pkgCount.textContent = 'Mostrando ' + visibleCount + ' de ' + rows.length + ' paquetes';
  }
  
  if (pkgSearch) pkgSearch.addEventListener('input', applyFilters);
  if (pkgCategoryFilter) pkgCategoryFilter.addEventListener('change', applyFilters);
  if (pkgStatusFilter) pkgStatusFilter.addEventListener('change', applyFilters);
});
</script>
