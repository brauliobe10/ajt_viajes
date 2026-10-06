<!-- Funcion del archivo: Renderiza el formulario administrativo para editar paquetes e imagenes. -->
<style>
  .amain{padding-bottom:120px}
  .edit-bar{
    position:fixed;bottom:0;left:260px;right:0;z-index:80;
    min-height:64px;padding:10px 24px;
    display:flex;align-items:center;justify-content:flex-end;gap:10px;
    background:rgba(255,255,255,.97);
    backdrop-filter:blur(10px);
    border-top:1px solid var(--c-border,#e5e7eb);
    box-shadow:0 -8px 24px rgba(15,23,42,.06);
  }
  .edit-bar .btn{min-height:40px;font-size:var(--fs-14,14px);padding:0 20px}
  [data-theme="dark"] .edit-bar{background:rgba(15,23,42,.97)}
  .pk-form{grid-template-columns:2fr 1fr;gap:18px}
  .collapse-head{
    width:100%;border:0;background:transparent;padding:16px 18px;
    display:flex;justify-content:space-between;align-items:center;gap:12px;
    cursor:pointer;text-align:left;
  }
  .collapse-head strong{display:block;font-size:var(--fs-15,15px);color:var(--c-ink-900,#111)}
  .collapse-head small{display:block;margin-top:3px;color:var(--c-ink-500,#6b7280);font-size:var(--fs-13,13px)}
  .collapse-indicator{font-size:var(--fs-13,13px);font-weight:700;color:var(--c-primary,#2563eb);white-space:nowrap}
  .collapse-body{border-top:1px solid var(--c-border,#e5e7eb);padding:18px}
  @media(max-width:900px){
    .pk-form{grid-template-columns:1fr!important}
    .edit-bar{left:0;padding:10px 14px;flex-wrap:wrap}
    .edit-bar .btn,.edit-bar a{flex:1;justify-content:center}
    .amain{padding-bottom:160px}
  }
</style>

<main id="main-content" class="amain">
  <div class="atopbar">
    <div style="display:flex;align-items:center;gap:10px">
      <button class="amenu-toggle" id="aMenuToggle" aria-label="Menú">☰</button>
      <div>
        <nav class="breadcrumbs"><a href="<?= BASE_URL ?>/admin/paquetes">Paquetes</a><span class="sep">/</span><span class="current">Editar</span></nav>
        <h1>Editar paquete</h1>
      </div>
    </div>
  </div>

  <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert--error">
      <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
    </div>
  <?php endif; ?>
  <?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert--success">
      <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
    </div>
  <?php endif; ?>

  <form id="editPackageForm" action="<?= BASE_URL ?>/admin/paquetes/update/<?= $paquete['id_paquete'] ?>" method="POST" class="pk-form">
    <?= App\Helper\Csrf::insertInput() ?>
    <input type="hidden" name="id" value="<?= (int)$paquete['id_paquete'] ?>">

    <!-- Columna izquierda -->
    <div style="display:grid;gap:18px;align-content:start">
      <div class="panel">
        <div class="panel__hd"><h3>Información básica</h3></div>
        <div class="field">
          <label for="nombre">Nombre del paquete *</label>
          <input class="input" id="nombre" name="nombre" required placeholder="Ej. Cusco · Machu Picchu 4D/3N" value="<?= htmlspecialchars($_POST['nombre'] ?? $paquete['nombre']) ?>">
        </div>
        <div class="field" style="margin-top:14px">
          <label for="slug">URL Amigable (Slug) *</label>
          <input class="input" id="slug" name="slug" required placeholder="ej-cusco-machu-picchu-4d-3n" value="<?= htmlspecialchars($_POST['slug'] ?? $paquete['slug']) ?>">
          <small style="color:var(--c-ink-500);font-size:11px">Se utiliza en la URL para el SEO. Debe ser único.</small>
        </div>
        <div class="co__grid-2" style="margin-top:14px">
          <div class="field">
            <label for="id_categoria">Categoría *</label>
            <select class="select" id="id_categoria" name="id_categoria" required>
              <option value="">Seleccione...</option>
              <?php foreach ($categorias as $cat): ?>
                <option value="<?= $cat['id_categoria'] ?>" <?= (isset($_POST['id_categoria']) ? $_POST['id_categoria'] : $paquete['id_categoria']) == $cat['id_categoria'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['nombre']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="field">
            <label for="id_ciudad">Ciudad de Destino *</label>
            <select class="select" id="id_ciudad" name="id_ciudad" required>
              <option value="">Seleccione...</option>
              <?php foreach ($ciudades as $ciu): ?>
                <option value="<?= $ciu['id_ciudad'] ?>" <?= (isset($_POST['id_ciudad']) ? $_POST['id_ciudad'] : $paquete['id_ciudad']) == $ciu['id_ciudad'] ? 'selected' : '' ?>><?= htmlspecialchars($ciu['nombre']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="field">
            <label for="duracion_dias">Duración (Días) *</label>
            <input class="input" type="number" min="1" id="duracion_dias" name="duracion_dias" required value="<?= htmlspecialchars($_POST['duracion_dias'] ?? $paquete['duracion_dias']) ?>">
          </div>
          <div class="field">
            <label for="duracion_noches">Duración (Noches) *</label>
            <input class="input" type="number" min="0" id="duracion_noches" name="duracion_noches" required value="<?= htmlspecialchars($_POST['duracion_noches'] ?? $paquete['duracion_noches']) ?>">
          </div>
        </div>
        <div class="field" style="margin-top:14px">
          <label for="descripcion_corta">Descripción Corta</label>
          <input class="input" id="descripcion_corta" name="descripcion_corta" placeholder="Un resumen de 1 línea para el catálogo…" value="<?= htmlspecialchars($_POST['descripcion_corta'] ?? $paquete['descripcion_corta'] ?? '') ?>">
        </div>
        <div class="field" style="margin-top:14px">
          <label for="descripcion">Descripción del paquete</label>
          <textarea class="textarea" id="descripcion" name="descripcion" rows="6" placeholder="Descripción comercial del viaje"><?= htmlspecialchars($_POST['descripcion'] ?? $paquete['descripcion']) ?></textarea>
        </div>
        <div class="co__grid-2" style="margin-top:14px">
          <div class="field"><label for="dificultad">Dificultad</label><input class="input" id="dificultad" name="dificultad" value="<?= htmlspecialchars($_POST['dificultad'] ?? $paquete['dificultad'] ?? '') ?>"></div>
          <div class="field"><label for="hotel">Hotel</label><input class="input" id="hotel" name="hotel" value="<?= htmlspecialchars($_POST['hotel'] ?? $paquete['hotel'] ?? '') ?>"></div>
          <div class="field"><label for="habitacion">Habitación</label><input class="input" id="habitacion" name="habitacion" value="<?= htmlspecialchars($_POST['habitacion'] ?? $paquete['habitacion'] ?? '') ?>"></div>
          <div class="field"><label for="comidas">Comidas</label><input class="input" id="comidas" name="comidas" value="<?= htmlspecialchars($_POST['comidas'] ?? $paquete['comidas'] ?? '') ?>"></div>
          <div class="field"><label for="vuelos">Vuelos</label><input class="input" id="vuelos" name="vuelos" value="<?= htmlspecialchars($_POST['vuelos'] ?? $paquete['vuelos'] ?? '') ?>"></div>
          <div class="field"><label for="movilidad">Movilidad</label><input class="input" id="movilidad" name="movilidad" value="<?= htmlspecialchars($_POST['movilidad'] ?? $paquete['movilidad'] ?? '') ?>"></div>
          <div class="field"><label for="guia">Guía</label><input class="input" id="guia" name="guia" value="<?= htmlspecialchars($_POST['guia'] ?? $paquete['guia'] ?? '') ?>"></div>
        </div>
        <!-- Itinerario colapsable -->
        <div class="panel" style="margin-top:18px">
          <button type="button" class="collapse-head" data-collapse-target="#itineraryEditor" aria-expanded="false">
            <span>
              <strong>Itinerario (días del viaje)</strong>
              <small>Editar días, actividades y descripción del recorrido</small>
            </span>
            <span class="collapse-indicator">Desplegar ▾</span>
          </button>
          <div id="itineraryEditor" class="collapse-body" hidden>
            <?php $itinerarioVal = $_POST['itinerario'] ?? $paquete['itinerario'] ?? '[]'; if (is_array($itinerarioVal)) $itinerarioVal = json_encode($itinerarioVal, JSON_UNESCAPED_UNICODE); ?>
            <input type="hidden" name="itinerario" id="itinerarioJson" value="<?= htmlspecialchars($itinerarioVal) ?>">
            <div id="itinerarioBuilder" style="display:grid;gap:10px"></div>
            <button type="button" id="addDiaBtn" class="btn btn--ghost btn--sm" style="margin-top:10px">+ Agregar día</button>
            <small style="color:var(--c-ink-500);font-size:11px;display:block;margin-top:6px">Cada día se guarda con título y descripción.</small>
          </div>
        </div>

        <!-- Galería de imágenes -->
        <div class="panel" style="margin-top:18px">
          <button type="button" class="collapse-head" data-collapse-target="#galleryEditor" aria-expanded="false">
            <span>
              <strong>Galería de imágenes</strong>
              <small>Gestiona las imágenes secundarias del paquete</small>
            </span>
            <span class="collapse-indicator">Desplegar ▾</span>
          </button>
          <div id="galleryEditor" class="collapse-body" hidden>
            <div id="galleryList" style="display:grid;gap:12px">
              <?php if (!empty($galeria)): ?>
                <?php foreach ($galeria as $img): ?>
                  <div class="gallery-item" style="display:grid;grid-template-columns:80px 1fr auto auto auto;gap:10px;align-items:center;padding:10px;background:var(--c-surface-muted);border-radius:8px;border:1px solid var(--c-border)">
                    <img src="<?= htmlspecialchars($img['url_imagen']) ?>" alt="" style="width:80px;height:60px;object-fit:cover;border-radius:4px;border:1px solid var(--c-border)" data-fallback-src="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2280%22 height=%2260%22><rect fill=%22%23ddd%22 width=%2280%22 height=%2260%22/><text x=%2240%22 y=%2235%22 text-anchor=%22middle%22 fill=%22%23999%22 font-size=%2210%22>Error</text></svg>">
                    <div>
                      <input type="hidden" name="gallery_id[]" value="<?= (int)$img['id_imagen'] ?>">
                      <input class="input" name="gallery_url[]" value="<?= htmlspecialchars($img['url_imagen']) ?>" placeholder="URL de imagen" style="font-size:12px">
                    </div>
                    <div style="display:flex;align-items:center;gap:4px">
                      <label style="font-size:11px;color:var(--c-ink-500);white-space:nowrap">Orden</label>
                      <input class="input" type="number" name="gallery_orden[]" value="<?= (int)($img['orden'] ?? 0) ?>" min="0" style="width:60px;font-size:12px;text-align:center">
                    </div>
                    <label style="display:flex;align-items:center;gap:4px;font-size:11px;color:var(--c-ink-500);cursor:pointer;white-space:nowrap">
                      <input type="radio" name="principal_image" value="existing:<?= (int)$img['id_imagen'] ?>" <?= $img['es_principal'] ? 'checked' : '' ?>>
                      Principal
                    </label>
                    <button type="button" class="btn btn--ghost btn--sm gallery-remove" style="color:var(--c-danger-600);font-size:12px;padding:4px 8px" title="Eliminar">✕</button>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
            <input type="hidden" name="delete_ids[]" id="galleryDeleteIds" value="">
            <button type="button" id="addGalleryImage" class="btn btn--ghost btn--sm" style="margin-top:10px">+ Agregar imagen</button>
            <small style="color:var(--c-ink-500);font-size:11px;display:block;margin-top:6px">Selecciona una imagen como principal. El orden controla la secuencia en la galería pública.</small>
          </div>
        </div>
      </div>

      <div class="panel">
        <div class="panel__hd"><h3>Contenido del paquete</h3></div>
        <div class="co__grid-2">
          <div class="field"><label for="incluye_items">Incluye</label><textarea class="textarea" id="incluye_items" name="incluye_items" rows="6"><?= htmlspecialchars($_POST['incluye_items'] ?? App\Models\Paquete::relationText($paquete, 'incluye')) ?></textarea></div>
          <div class="field"><label for="no_incluye_items">No incluye</label><textarea class="textarea" id="no_incluye_items" name="no_incluye_items" rows="6"><?= htmlspecialchars($_POST['no_incluye_items'] ?? App\Models\Paquete::relationText($paquete, 'no_incluye')) ?></textarea></div>
          <div class="field"><label for="politicas_items">Políticas</label><textarea class="textarea" id="politicas_items" name="politicas_items" rows="6"><?= htmlspecialchars($_POST['politicas_items'] ?? App\Models\Paquete::relationText($paquete, 'politicas')) ?></textarea></div>
          <div class="field"><label for="documentos_items">Documentos</label><textarea class="textarea" id="documentos_items" name="documentos_items" rows="6"><?= htmlspecialchars($_POST['documentos_items'] ?? App\Models\Paquete::relationText($paquete, 'documentos')) ?></textarea></div>
          <div class="field"><label for="tags_items">Tags</label><textarea class="textarea" id="tags_items" name="tags_items" rows="4"><?= htmlspecialchars($_POST['tags_items'] ?? App\Models\Paquete::relationText($paquete, 'tags')) ?></textarea></div>
        </div>
      </div>
    </div>

    <!-- Columna derecha -->
    <aside style="display:grid;gap:18px;align-content:start">
      <div class="panel">
        <div class="panel__hd"><h3>Publicación</h3></div>
        <div class="field">
          <label for="disponible">Estado</label>
          <select class="select" id="disponible" name="disponible">
            <option value="1" <?= (isset($_POST['disponible']) ? $_POST['disponible'] : $paquete['disponible']) == '1' ? 'selected' : '' ?>>Activo (Público)</option>
            <option value="0" <?= (isset($_POST['disponible']) ? $_POST['disponible'] : $paquete['disponible']) == '0' ? 'selected' : '' ?>>Inactivo / Borrador</option>
          </select>
        </div>
        <div style="margin-top:14px">
          <label class="check" style="justify-content:space-between;width:100%">
            <span>Destacado</span>
            <input type="checkbox" name="destacado" value="1" <?= (isset($_POST['destacado']) ? 'checked' : ((isset($paquete['destacado']) && $paquete['destacado']) ? 'checked' : '')) ?>>
          </label>
        </div>
      </div>

      <div class="panel">
        <div class="panel__hd"><h3>Precios</h3></div>
        <div class="field">
          <label for="precio_base">Precio Base *</label>
          <input class="input" type="number" step="0.01" min="0" id="precio_base" name="precio_base" required value="<?= htmlspecialchars($_POST['precio_base'] ?? $paquete['precio_base']) ?>">
        </div>
        <div class="field" style="margin-top:10px">
          <label for="precio_anterior">Precio Anterior (tachado)</label>
          <input class="input" type="number" step="0.01" min="0" id="precio_anterior" name="precio_anterior" placeholder="Dejar vacío si no hay descuento" value="<?= htmlspecialchars($_POST['precio_anterior'] ?? $paquete['precio_anterior'] ?? '') ?>">
        </div>
        <div class="field" style="margin-top:10px">
          <label for="cupo_maximo">Cupo Máximo</label>
          <input class="input" type="number" min="0" id="cupo_maximo" name="cupo_maximo" placeholder="0 = sin límite" value="<?= htmlspecialchars($_POST['cupo_maximo'] ?? $paquete['cupo_maximo'] ?? '0') ?>">
        </div>
      </div>

      <div class="panel">
        <div class="panel__hd"><h3>Imagen Destacada</h3></div>
        <div class="field">
          <label for="url_imagen">Enlace de la imagen (URL)</label>
          <input class="input" type="url" id="url_imagen" name="url_imagen" placeholder="https://ejemplo.com/imagen.jpg" value="<?= htmlspecialchars($_POST['url_imagen'] ?? $imagen_url ?? '') ?>">
          <small style="color:var(--c-ink-500);font-size:11px">URL directa de imagen. Al guardar se vuelve a validar.</small>
        </div>
        <?php if (!empty($imagen_url)): ?>
          <div style="margin-top:14px;text-align:center">
            <img src="<?= htmlspecialchars($imagen_url) ?>" alt="Imagen actual" style="max-width:100%;max-height:150px;border-radius:6px;border:1px solid var(--c-ink-200)">
          </div>
        <?php endif; ?>
      </div>
    </aside>
  </form>

  <!-- Barra fija de acciones — fuera del form, usa attribute form= -->
  <div class="edit-bar" id="editBar">
    <a href="<?= BASE_URL ?>/admin/paquetes" class="btn btn--ghost">Cancelar</a>
    <button type="submit" name="borrador" value="1" class="btn btn--ghost" form="editPackageForm">Guardar borrador</button>
    <button type="submit" class="btn btn--primary" form="editPackageForm">Guardar cambios</button>
  </div>
</main>

<script nonce="<?= htmlspecialchars(\App\Helper\SecurityHeaders::getNonce(), ENT_QUOTES, 'UTF-8') ?>">
// === COLLAPSE SECTIONS ===
document.querySelectorAll('[data-collapse-target]').forEach(function(btn) {
  btn.addEventListener('click', function() {
    var target = document.querySelector(btn.getAttribute('data-collapse-target'));
    if (!target) return;
    var isOpen = !target.hidden;
    target.hidden = isOpen;
    btn.setAttribute('aria-expanded', String(!isOpen));
    var indicator = btn.querySelector('.collapse-indicator');
    if (indicator) indicator.textContent = isOpen ? 'Desplegar ▾' : 'Ocultar ▴';
  });
});

// === GALLERY MANAGEMENT ===
(function(){
  var galleryList = document.getElementById('galleryList');
  var addBtn = document.getElementById('addGalleryImage');
  var deleteInput = document.getElementById('galleryDeleteIds');
  var deletedIds = [];

  // Remove existing image
  if (galleryList) {
    galleryList.addEventListener('click', function(e) {
      var btn = e.target.closest('.gallery-remove');
      if (!btn) return;
      var item = btn.closest('.gallery-item');
      if (!item) return;
      var idInput = item.querySelector('input[name="gallery_id[]"]');
      if (idInput && idInput.value) {
        deletedIds.push(idInput.value);
        deleteInput.value = deletedIds.join(',');
      }
      item.remove();
    });

    // Preview on URL change
    galleryList.addEventListener('input', function(e) {
      if (!e.target.name || (e.target.name !== 'gallery_url[]' && e.target.name !== 'new_url[]')) return;
      var item = e.target.closest('.gallery-item');
      if (!item) return;
      var img = item.querySelector('img');
      if (img) img.src = e.target.value || '';
    });
  }

  // Add new image
  if (addBtn && galleryList) {
    addBtn.addEventListener('click', function() {
      var idx = galleryList.querySelectorAll('.gallery-item').length;
      var newIndex = galleryList.querySelectorAll('input[name="new_url[]"]').length;
      var div = document.createElement('div');
      div.className = 'gallery-item';
      div.style.cssText = 'display:grid;grid-template-columns:80px 1fr auto auto auto;gap:10px;align-items:center;padding:10px;background:var(--c-surface-muted);border-radius:8px;border:1px solid var(--c-border)';
      div.innerHTML =
        '<img src="" alt="" style="width:80px;height:60px;object-fit:cover;border-radius:4px;border:1px solid var(--c-border);background:#eee">' +
        '<div>' +
          '<input class="input" name="new_url[]" value="" placeholder="URL de imagen" style="font-size:12px">' +
        '</div>' +
        '<div style="display:flex;align-items:center;gap:4px">' +
          '<label style="font-size:11px;color:var(--c-ink-500);white-space:nowrap">Orden</label>' +
          '<input class="input" type="number" name="new_orden[]" value="' + (idx + 1) + '" min="0" style="width:60px;font-size:12px;text-align:center">' +
        '</div>' +
        '<label style="display:flex;align-items:center;gap:4px;font-size:11px;color:var(--c-ink-500);cursor:pointer;white-space:nowrap">' +
          '<input type="radio" name="principal_image" value="new:' + newIndex + '"> Principal' +
        '</label>' +
        '<button type="button" class="btn btn--ghost btn--sm gallery-remove" style="color:var(--c-danger-600);font-size:12px;padding:4px 8px" title="Eliminar">✕</button>';
      galleryList.appendChild(div);

      // Focus the URL input
      var urlInput = div.querySelector('input[name="new_url[]"]');
      if (urlInput) urlInput.focus();
    });
  }
})();

document.addEventListener('DOMContentLoaded', function() {
  var nombreInput = document.getElementById('nombre');
  var slugInput = document.getElementById('slug');
  var userEditedSlug = true;

  slugInput.addEventListener('input', function() { userEditedSlug = true; });
  nombreInput.addEventListener('input', function() {
    if (!userEditedSlug) slugInput.value = generateSlug(nombreInput.value);
  });

  // Funcion: Genera un slug limpio desde el nombre del paquete.
  function generateSlug(text) {
    return text.toString().toLowerCase()
      .replace(/\s+/g, '-')
      .replace(/[^\w\-]+/g, '')
      .replace(/\-\-+/g, '-')
      .replace(/^-+/, '')
      .replace(/-+$/, '');
  }

  // === ITINERARIO ===
  var itinContainer = document.getElementById('itinerarioBuilder');
  var itinJsonInput = document.getElementById('itinerarioJson');
  var addBtn = document.getElementById('addDiaBtn');
  var diaCounter = 0;

  // Funcion: Lee el itinerario inicial guardado en el formulario.
  function parseInitialItinerario() {
    try {
      var val = itinJsonInput.value;
      if (!val || val === '[]') return [];
      var parsed = JSON.parse(val);
      return Array.isArray(parsed) ? parsed : [];
    } catch(e) { return []; }
  }

  // Funcion: Dibuja los dias del itinerario en pantalla.
  function renderItinerario(dias) {
    itinContainer.innerHTML = '';
    diaCounter = 0;
    dias.forEach(function(d) { addDiaRow(d.dia || (diaCounter + 1), d.titulo || '', d.descripcion || ''); });
    if (dias.length === 0) addDiaRow(1, '', '');
  }

  // Funcion: Agrega una fila editable al itinerario.
  function addDiaRow(dia, titulo, descripcion) {
    diaCounter++;
    var row = document.createElement('div');
    row.style.cssText = 'display:flex;gap:10px;align-items:flex-start;background:var(--c-surface-muted);padding:10px 12px;border-radius:8px;border:1px solid var(--c-border)';
    row.dataset.index = diaCounter;

    var diaLabel = document.createElement('div');
    diaLabel.style.cssText = 'font-weight:700;font-size:14px;color:var(--c-primary-700);min-width:50px;padding-top:8px';
    diaLabel.textContent = 'Día ' + dia;

    var fields = document.createElement('div');
    fields.style.cssText = 'display:grid;gap:6px;flex:1';

    var tituloInput = document.createElement('input');
    tituloInput.className = 'input'; tituloInput.type = 'text';
    tituloInput.placeholder = 'Título del día (opcional)'; tituloInput.value = titulo;
    tituloInput.style.fontWeight = '600';

    var descInput = document.createElement('textarea');
    descInput.className = 'textarea'; descInput.rows = 2;
    descInput.placeholder = 'Descripción de las actividades del día'; descInput.value = descripcion;

    var removeBtn = document.createElement('button');
    removeBtn.type = 'button'; removeBtn.textContent = '×';
    removeBtn.style.cssText = 'background:none;border:0;font-size:22px;cursor:pointer;color:var(--c-danger-500);padding:4px 6px;line-height:1';
    removeBtn.title = 'Eliminar día';
    removeBtn.addEventListener('click', function() { row.remove(); syncItinerario(); });

    fields.appendChild(tituloInput); fields.appendChild(descInput);
    row.appendChild(diaLabel); row.appendChild(fields); row.appendChild(removeBtn);
    itinContainer.appendChild(row);
    tituloInput.addEventListener('input', syncItinerario);
    descInput.addEventListener('input', syncItinerario);
  }

  // Funcion: Sincroniza el itinerario editable con el campo oculto.
  function syncItinerario() {
    var rows = itinContainer.querySelectorAll('div[data-index]');
    var data = [];
    rows.forEach(function(row, i) {
      var inputs = row.querySelectorAll('input, textarea');
      if (inputs.length >= 2) data.push({ dia: i+1, titulo: inputs[0].value, descripcion: inputs[1].value });
    });
    rows.forEach(function(row, i) { var label = row.querySelector('div:first-child'); if (label) label.textContent = 'Día ' + (i+1); });
    itinJsonInput.value = JSON.stringify(data);
  }

  renderItinerario(parseInitialItinerario());
  syncItinerario();
  addBtn.addEventListener('click', function() {
    var rows = itinContainer.querySelectorAll('div[data-index]');
    addDiaRow(rows.length + 1, '', '');
    syncItinerario();
  });
});
</script>
