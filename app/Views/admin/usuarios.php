<?php

// Funcion del archivo: Renderiza la gesti?n de clientes y administradores.
  $showAdminTab = ($_GET['tab'] ?? '') === 'admins';
  $openNewAdmin = $showAdminTab && ($_GET['new'] ?? '') === '1';
  $adminFormData = $_SESSION['admin_form_data'] ?? [];
  unset($_SESSION['admin_form_data']);
?>
<style>
  /* Modal admin adaptado a claro/oscuro sin alterar la lógica de edición. */
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
        <h1>Usuarios</h1>
        <p>Clientes y administradores registrados en la plataforma</p>
      </div>
    </div>
    <div class="atopbar__actions">
      <input class="input" type="search" id="userSearch" placeholder="Buscar usuario..." style="width:280px">
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

  <div class="kpi-grid">
    <div class="kpi">
      <div class="kpi__head"><span class="kpi__label">Clientes</span><div class="kpi__icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg></div></div>
      <div class="kpi__value"><?= $totalClientes ?></div>
      <span class="kpi__delta">cuentas de clientes</span>
    </div>
    <div class="kpi">
      <div class="kpi__head"><span class="kpi__label">Administradores</span><div class="kpi__icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg></div></div>
      <div class="kpi__value"><?= $totalAdmins ?></div>
      <span class="kpi__delta">cuentas administrativas</span>
    </div>
    <div class="kpi">
      <div class="kpi__head"><span class="kpi__label">Total Usuarios</span><div class="kpi__icon">📊</div></div>
      <div class="kpi__value"><?= count($usuarios) ?></div>
      <span class="kpi__delta">registros en BD</span>
    </div>
    <div class="kpi">
      <div class="kpi__head"><span class="kpi__label">Seguridad</span><div class="kpi__icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></div></div>
      <div class="kpi__value">Activo</div>
      <span class="kpi__delta">verificación de roles</span>
    </div>
  </div>

  <div class="panel">
    <div class="panel__hd">
      <h3 id="userListTitle"><?= $showAdminTab ? 'Administradores' : 'Clientes' ?></h3>
      <button type="button" class="btn btn--primary btn--sm" id="newAdminButton"<?= $showAdminTab ? '' : ' hidden' ?>>+ Nuevo administrador</button>
    </div>
    <div class="tabs" role="tablist" aria-label="Tipo de usuario" style="padding:0 18px">
      <button type="button" class="tab<?= $showAdminTab ? '' : ' is-active' ?>" role="tab" aria-selected="<?= $showAdminTab ? 'false' : 'true' ?>" data-user-role="1">
        Clientes (<?= $totalClientes ?>)
      </button>
      <button type="button" class="tab<?= $showAdminTab ? ' is-active' : '' ?>" role="tab" aria-selected="<?= $showAdminTab ? 'true' : 'false' ?>" data-user-role="2">
        Administradores (<?= $totalAdmins ?>)
      </button>
    </div>
    <div class="table-wrap" style="border:0;box-shadow:none">
      <table class="table">
        <thead>
          <tr>
            <th>Usuario</th>
            <th>Correo</th>
            <th>Rol</th>
            <th>Nro Reservas</th>
            <th>Registro</th>
            <th>Estado</th>
            <th>Acción</th>
          </tr>
        </thead>
        <tbody id="userTableBody">
          <?php if (!empty($usuarios)): ?>
            <?php foreach ($usuarios as $u): ?>
              <?php 
                $rolClass = ((int)$u['id_rol'] === 2) ? 'badge--warn' : 'badge--info';
              ?>
              <tr class="user-row" data-role="<?= (int)$u['id_rol'] ?>">
                <td><strong><?= htmlspecialchars($u['nombres'] . ' ' . $u['apellidos']) ?></strong></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><span class="badge <?= $rolClass ?>"><?= htmlspecialchars($u['nombre_rol']) ?></span></td>
                <td><?= (int)$u['reservas_count'] ?></td>
                <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                <td><span class="status-pill s-active">Activo</span></td>
                <td class="actions">
                  <button type="button" 
                          class="btn btn--ghost btn--sm js-edit-user"
                          data-id="<?= $u['id_usuario'] ?>"
                          data-nombre="<?= htmlspecialchars($u['nombres'] . ' ' . $u['apellidos']) ?>"
                          data-email="<?= htmlspecialchars($u['email']) ?>"
                          data-rol="<?= $u['id_rol'] ?>">
                    Editar Rol
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
          <tr id="noUsersRow" style="display:none">
            <td colspan="7" style="text-align:center;color:var(--c-ink-500);padding:30px">No se encontraron clientes.</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</main>

<!-- Modal Registrar Administrador -->
<div id="newAdminModal" class="trip-modal" hidden>
  <div class="trip-modal__backdrop" data-close></div>
  <div class="trip-modal__panel" role="dialog" aria-modal="true" aria-labelledby="newAdminTitle" style="max-width:620px">
    <button type="button" class="trip-modal__x" data-close aria-label="Cerrar">×</button>
    <div class="trip-modal__cnt">
      <h2 id="newAdminTitle">Registrar administrador</h2>
      <p style="color:var(--c-ink-500);margin:0 0 20px">La nueva cuenta tendrá acceso al panel administrativo.</p>

      <form action="<?= BASE_URL ?>/admin/usuarios/registrar-administrador" method="POST" id="newAdminForm">
        <?= App\Helper\Csrf::insertInput() ?>
        <div class="co__grid-2">
          <div class="field">
            <label for="adminNombres">Nombres *</label>
            <input class="input" id="adminNombres" name="nombres" required maxlength="100" autocomplete="given-name" value="<?= htmlspecialchars($adminFormData['nombres'] ?? '') ?>">
          </div>
          <div class="field">
            <label for="adminApellidos">Apellidos *</label>
            <input class="input" id="adminApellidos" name="apellidos" required maxlength="100" autocomplete="family-name" value="<?= htmlspecialchars($adminFormData['apellidos'] ?? '') ?>">
          </div>
        </div>
        <div class="field" style="margin-top:14px">
          <label for="adminEmail">Correo electrónico *</label>
          <input class="input" type="email" id="adminEmail" name="email" required autocomplete="email" value="<?= htmlspecialchars($adminFormData['email'] ?? '') ?>">
        </div>
        <div class="co__grid-2" style="margin-top:14px">
          <div class="field">
            <label for="adminTelefono">Teléfono</label>
            <input class="input" type="tel" id="adminTelefono" name="telefono" autocomplete="tel" value="<?= htmlspecialchars($adminFormData['telefono'] ?? '') ?>">
          </div>
          <div class="field">
            <label for="adminDni">DNI / Pasaporte *</label>
            <input class="input" id="adminDni" name="dni_pasaporte" required minlength="6" maxlength="20" value="<?= htmlspecialchars($adminFormData['dni'] ?? '') ?>">
          </div>
        </div>
        <div class="co__grid-2" style="margin-top:14px">
          <div class="field">
            <label for="adminPassword">Contraseña *</label>
            <input class="input" type="password" id="adminPassword" name="password" required minlength="8" autocomplete="new-password">
          </div>
          <div class="field">
            <label for="adminPasswordConfirm">Confirmar contraseña *</label>
            <input class="input" type="password" id="adminPasswordConfirm" name="password_confirm" required minlength="8" autocomplete="new-password">
          </div>
        </div>
        <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:22px">
          <button type="button" class="btn btn--ghost" data-close>Cancelar</button>
          <button type="submit" class="btn btn--primary">Registrar administrador</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Editar Rol de Usuario -->
<div id="editUserModal" class="trip-modal" hidden>
  <div class="trip-modal__backdrop" data-close></div>
  <div class="trip-modal__panel" role="dialog" aria-modal="true" style="max-width: 480px;">
    <button class="trip-modal__x" data-close aria-label="Cerrar">×</button>
    <div class="trip-modal__cnt">
      <h2>Modificar Rol de Usuario</h2>
      <p style="color:var(--c-ink-500);margin:0 0 16px" id="editUserModalTitle"></p>
      
      <form action="<?= BASE_URL ?>/admin/usuarios/update-rol" method="POST" id="editUserForm">
        <?= App\Helper\Csrf::insertInput() ?>
        <input type="hidden" name="id_usuario" id="editUserModalId">
        
        <div class="field">
          <label>Rol asignado</label>
          <select class="input" name="id_rol" id="editUserModalRol" required style="margin-top: 6px;">
            <option value="1">Cliente</option>
            <option value="2">Administrador</option>
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
  // Pestañas y buscador por tipo de usuario
  const searchInput = document.getElementById('userSearch');
  const tableBody = document.getElementById('userTableBody');
  const userTabs = document.querySelectorAll('[data-user-role]');
  const listTitle = document.getElementById('userListTitle');
  const noUsersRow = document.getElementById('noUsersRow');
  const newAdminButton = document.getElementById('newAdminButton');
  const newAdminModal = document.getElementById('newAdminModal');
  let activeRole = '<?= $showAdminTab ? '2' : '1' ?>';

  // Funcion: Filtra usuarios por busqueda y rol.
  function filterUsers() {
    if (!tableBody) return;

    const q = searchInput ? searchInput.value.trim().toLowerCase() : '';
    let visibleCount = 0;

    tableBody.querySelectorAll('.user-row').forEach(row => {
      const matchesRole = row.dataset.role === activeRole;
      const matchesSearch = row.textContent.toLowerCase().includes(q);
      const visible = matchesRole && matchesSearch;
      row.style.display = visible ? '' : 'none';
      if (visible) visibleCount++;
    });

    if (noUsersRow) {
      noUsersRow.style.display = visibleCount === 0 ? '' : 'none';
      noUsersRow.cells[0].textContent = activeRole === '1'
        ? 'No se encontraron clientes.'
        : 'No se encontraron administradores.';
    }
  }

  userTabs.forEach(tab => {
    tab.addEventListener('click', () => {
      activeRole = tab.dataset.userRole;
      userTabs.forEach(item => {
        const selected = item === tab;
        item.classList.toggle('is-active', selected);
        item.setAttribute('aria-selected', selected ? 'true' : 'false');
      });
      if (listTitle) {
        listTitle.textContent = activeRole === '1' ? 'Clientes' : 'Administradores';
      }
      if (newAdminButton) newAdminButton.hidden = activeRole !== '2';
      const tabUrl = new URL(window.location.href);
      tabUrl.searchParams.set('tab', activeRole === '2' ? 'admins' : 'clients');
      tabUrl.searchParams.delete('new');
      history.replaceState(null, '', tabUrl);
      filterUsers();
    });
  });

  if (searchInput) {
    searchInput.addEventListener('input', filterUsers);
  }

  filterUsers();

  if (newAdminButton && newAdminModal) {
    newAdminButton.addEventListener('click', () => {
      newAdminModal.hidden = false;
      document.body.style.overflow = 'hidden';
      document.getElementById('adminNombres')?.focus();
    });
  }

  <?php if ($openNewAdmin): ?>
  if (newAdminModal) {
    newAdminModal.hidden = false;
    document.body.style.overflow = 'hidden';
  }
  <?php endif; ?>

  // Modales
  const modal = document.getElementById('editUserModal');
  const modalTitle = document.getElementById('editUserModalTitle');
  const modalId = document.getElementById('editUserModalId');
  const modalRol = document.getElementById('editUserModalRol');

  document.querySelectorAll('.js-edit-user').forEach(btn => {
    btn.addEventListener('click', () => {
      const data = btn.dataset;
      modalTitle.textContent = data.nombre + ' (' + data.email + ')';
      modalId.value = data.id;
      modalRol.value = data.rol;
      modal.hidden = false;
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
