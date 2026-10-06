<!-- Funcion del archivo: Renderiza el formulario administrativo para crear paquetes. -->
<main id="main-content" class="amain">
  <div class="atopbar">
    <div style="display:flex;align-items:center;gap:10px">
      <button class="amenu-toggle" id="aMenuToggle" aria-label="Menú">☰</button>
      <div>
        <nav class="breadcrumbs"><a href="<?= BASE_URL ?>/admin/paquetes">Paquetes</a><span class="sep">/</span><span class="current">Nuevo</span></nav>
        <h1>Crear nuevo paquete</h1>
      </div>
    </div>
  </div>

  <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert--error">
      <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
    </div>
  <?php endif; ?>

  <form action="<?= BASE_URL ?>/admin/paquetes/store" method="POST" style="display:grid;grid-template-columns:2fr 1fr;gap:18px" class="pk-form">
    <?= App\Helper\Csrf::insertInput() ?>
    <div style="display:grid;gap:18px">
      <div class="panel">
        <div class="panel__hd"><h3>Información básica</h3></div>
        
        <div class="field">
          <label for="nombre">Nombre del paquete *</label>
          <input class="input" id="nombre" name="nombre" required placeholder="Ej. Cusco · Machu Picchu 4D/3N" value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">
        </div>

        <div class="field" style="margin-top:14px">
          <label for="slug">URL Amigable (Slug) *</label>
          <input class="input" id="slug" name="slug" required placeholder="ej-cusco-machu-picchu-4d-3n" value="<?= htmlspecialchars($_POST['slug'] ?? '') ?>">
          <small style="color:var(--c-ink-500);font-size:11px">Se autogenera desde el nombre, pero puedes editarlo.</small>
        </div>

        <div class="co__grid-2" style="margin-top:14px">
          <div class="field">
            <label for="id_categoria">Categoría *</label>
            <select class="select" id="id_categoria" name="id_categoria" required>
              <option value="">Seleccione...</option>
              <?php foreach ($categorias as $cat): ?>
                <option value="<?= $cat['id_categoria'] ?>" <?= (isset($_POST['id_categoria']) && $_POST['id_categoria'] == $cat['id_categoria']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($cat['nombre']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="field">
            <label for="id_ciudad">Ciudad de Destino *</label>
            <select class="select" id="id_ciudad" name="id_ciudad" required>
              <option value="">Seleccione...</option>
              <?php foreach ($ciudades as $ciu): ?>
                <option value="<?= $ciu['id_ciudad'] ?>" <?= (isset($_POST['id_ciudad']) && $_POST['id_ciudad'] == $ciu['id_ciudad']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($ciu['nombre']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="field">
            <label for="duracion_dias">Duración (Días) *</label>
            <input class="input" type="number" min="1" id="duracion_dias" name="duracion_dias" required value="<?= htmlspecialchars($_POST['duracion_dias'] ?? '4') ?>">
          </div>

          <div class="field">
            <label for="duracion_noches">Duración (Noches) *</label>
            <input class="input" type="number" min="0" id="duracion_noches" name="duracion_noches" required value="<?= htmlspecialchars($_POST['duracion_noches'] ?? '3') ?>">
          </div>
        </div>

        <div class="field" style="margin-top:14px">
          <label for="descripcion_corta">Descripción Corta</label>
          <input class="input" id="descripcion_corta" name="descripcion_corta" placeholder="Un resumen de 1 línea para el catálogo…" value="<?= htmlspecialchars($_POST['descripcion_corta'] ?? '') ?>">
        </div>

        <div class="field" style="margin-top:14px">
          <label for="descripcion">Descripción del paquete</label>
          <textarea class="textarea" id="descripcion" name="descripcion" rows="6" placeholder="Descripción comercial del viaje"><?= htmlspecialchars($_POST['descripcion'] ?? '') ?></textarea>
        </div>

        <div class="co__grid-2" style="margin-top:14px">
          <div class="field"><label for="dificultad">Dificultad</label><input class="input" id="dificultad" name="dificultad" placeholder="Baja, media, alta" value="<?= htmlspecialchars($_POST['dificultad'] ?? '') ?>"></div>
          <div class="field"><label for="hotel">Hotel</label><input class="input" id="hotel" name="hotel" placeholder="Ej. Hotel 4★ céntrico" value="<?= htmlspecialchars($_POST['hotel'] ?? '') ?>"></div>
          <div class="field"><label for="habitacion">Habitación</label><input class="input" id="habitacion" name="habitacion" placeholder="Doble estándar, familiar..." value="<?= htmlspecialchars($_POST['habitacion'] ?? '') ?>"></div>
          <div class="field"><label for="comidas">Comidas</label><input class="input" id="comidas" name="comidas" placeholder="Desayunos, media pensión..." value="<?= htmlspecialchars($_POST['comidas'] ?? '') ?>"></div>
          <div class="field"><label for="vuelos">Vuelos</label><input class="input" id="vuelos" name="vuelos" placeholder="Incluidos/no incluidos/tramos" value="<?= htmlspecialchars($_POST['vuelos'] ?? '') ?>"></div>
          <div class="field"><label for="movilidad">Movilidad</label><input class="input" id="movilidad" name="movilidad" placeholder="Traslados privados, bus turístico..." value="<?= htmlspecialchars($_POST['movilidad'] ?? '') ?>"></div>
          <div class="field"><label for="guia">Guía</label><input class="input" id="guia" name="guia" placeholder="Guía local en español" value="<?= htmlspecialchars($_POST['guia'] ?? '') ?>"></div>
        </div>

        <div class="field" style="margin-top:14px">
          <label>Itinerario (días del viaje)</label>
          <input type="hidden" name="itinerario" id="itinerarioJson" value="<?= htmlspecialchars($_POST['itinerario'] ?? '[]') ?>">
          <div id="itinerarioBuilder" style="display:grid;gap:10px;margin-top:8px"></div>
          <button type="button" id="addDiaBtn" class="btn btn--ghost btn--sm" style="margin-top:8px">+ Agregar día</button>
          <small style="color:var(--c-ink-500);font-size:11px;display:block;margin-top:6px">Agrega el título y descripción para cada día del itinerario.</small>
        </div>
      </div>

      <div class="panel">
        <div class="panel__hd"><h3>Contenido del paquete</h3></div>
        <div class="co__grid-2">
          <div class="field"><label for="incluye_items">Incluye</label><textarea class="textarea" id="incluye_items" name="incluye_items" rows="6" placeholder="Un ítem por línea"><?= htmlspecialchars($_POST['incluye_items'] ?? '') ?></textarea></div>
          <div class="field"><label for="no_incluye_items">No incluye</label><textarea class="textarea" id="no_incluye_items" name="no_incluye_items" rows="6" placeholder="Un ítem por línea"><?= htmlspecialchars($_POST['no_incluye_items'] ?? '') ?></textarea></div>
          <div class="field"><label for="politicas_items">Políticas</label><textarea class="textarea" id="politicas_items" name="politicas_items" rows="6" placeholder="Título: detalle, una política por línea"><?= htmlspecialchars($_POST['politicas_items'] ?? '') ?></textarea></div>
          <div class="field"><label for="documentos_items">Documentos</label><textarea class="textarea" id="documentos_items" name="documentos_items" rows="6" placeholder="Un documento por línea"><?= htmlspecialchars($_POST['documentos_items'] ?? '') ?></textarea></div>
          <div class="field"><label for="tags_items">Tags</label><textarea class="textarea" id="tags_items" name="tags_items" rows="4" placeholder="Un tag por línea"><?= htmlspecialchars($_POST['tags_items'] ?? '') ?></textarea></div>
        </div>
      </div>
    </div>

    <aside style="display:grid;gap:18px;align-content:start;">
      <div class="panel">
        <div class="panel__hd"><h3>Publicación</h3></div>
        
        <div class="field">
          <label for="disponible">Estado</label>
          <select class="select" id="disponible" name="disponible">
            <option value="1" <?= (isset($_POST['disponible']) && $_POST['disponible'] == '1') ? 'selected' : '' ?>>Activo (Público)</option>
            <option value="0" <?= (isset($_POST['disponible']) && $_POST['disponible'] == '0') ? 'selected' : '' ?>>Inactivo / Borrador</option>
          </select>
        </div>

        <div style="margin-top:14px">
          <label class="check" style="justify-content:space-between;width:100%">
            <span>Destacado</span>
            <input type="checkbox" name="destacado" value="1" <?= isset($_POST['destacado']) ? 'checked' : '' ?>>
          </label>
        </div>

        <div style="margin-top:20px; display:grid; gap:10px;">
          <button type="submit" class="btn btn--primary btn--block">Publicar paquete</button>
          <button type="submit" name="borrador" value="1" class="btn btn--ghost btn--block">Guardar borrador</button>
          <a href="<?= BASE_URL ?>/admin/paquetes" class="btn btn--ghost btn--block">Cancelar</a>
        </div>
      </div>

      <div class="panel">
        <div class="panel__hd"><h3>Precios</h3></div>
        <div class="field">
          <label for="precio_base">Precio Base *</label>
          <input class="input" type="number" step="0.01" min="0" id="precio_base" name="precio_base" required value="<?= htmlspecialchars($_POST['precio_base'] ?? '1199') ?>">
        </div>
        <div class="field" style="margin-top:10px">
          <label for="precio_anterior">Precio Anterior (tachado)</label>
          <input class="input" type="number" step="0.01" min="0" id="precio_anterior" name="precio_anterior" placeholder="Dejar vacío si no hay descuento" value="<?= htmlspecialchars($_POST['precio_anterior'] ?? '') ?>">
        </div>
        <div class="field" style="margin-top:10px">
          <label for="cupo_maximo">Cupo Máximo</label>
          <input class="input" type="number" min="0" id="cupo_maximo" name="cupo_maximo" placeholder="0 = sin límite" value="<?= htmlspecialchars($_POST['cupo_maximo'] ?? '0') ?>">
        </div>
      </div>

      <div class="panel">
        <div class="panel__hd"><h3>Imagen Destacada</h3></div>
        <div class="field">
          <label for="url_imagen">Enlace de la imagen (URL)</label>
          <input class="input" type="url" id="url_imagen" name="url_imagen" placeholder="https://ejemplo.com/imagen.jpg" value="<?= htmlspecialchars($_POST['url_imagen'] ?? '') ?>">
          <small style="color:var(--c-ink-500);font-size:11px">Usa una URL directa de imagen. Se validará que responda correctamente antes de guardar.</small>
        </div>
      </div>
    </aside>
  </form>
</main>

<style>@media(max-width:980px){.pk-form{grid-template-columns:1fr!important}}</style>

<script nonce="<?= htmlspecialchars(\App\Helper\SecurityHeaders::getNonce(), ENT_QUOTES, 'UTF-8') ?>">
document.addEventListener('DOMContentLoaded', function() {
  var nombreInput = document.getElementById('nombre');
  var slugInput = document.getElementById('slug');
  var userEditedSlug = false;
  
  if (slugInput.value !== '') {
    userEditedSlug = true;
  }
  
  slugInput.addEventListener('input', function() {
    userEditedSlug = true;
  });
  
  nombreInput.addEventListener('input', function() {
    if (!userEditedSlug) {
      slugInput.value = generateSlug(nombreInput.value);
    }
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

  // === ITINERARIO DINÁMICO ===
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
      if (Array.isArray(parsed)) return parsed;
      return [];
    } catch(e) { return []; }
  }

  // Funcion: Dibuja los dias del itinerario en pantalla.
  function renderItinerario(dias) {
    itinContainer.innerHTML = '';
    diaCounter = 0;
    dias.forEach(function(d) {
      addDiaRow(d.dia || (diaCounter + 1), d.titulo || '', d.descripcion || '');
    });
    if (dias.length === 0) addDiaRow(1, '', '');
  }

  // Funcion: Agrega una fila editable al itinerario.
  function addDiaRow(dia, titulo, descripcion) {
    diaCounter++;
    var idx = diaCounter;
    var row = document.createElement('div');
    // Fila dinámica del itinerario con colores de tema.
    row.style.cssText = 'display:flex;gap:10px;align-items:flex-start;background:var(--c-surface-muted);padding:10px 12px;border-radius:8px;border:1px solid var(--c-border)';
    row.dataset.index = idx;

    var diaLabel = document.createElement('div');
    diaLabel.style.cssText = 'font-weight:700;font-size:14px;color:var(--c-primary-700);min-width:50px;padding-top:8px';
    diaLabel.textContent = 'Día ' + dia;

    var fields = document.createElement('div');
    fields.style.cssText = 'display:grid;gap:6px;flex:1';

    var tituloInput = document.createElement('input');
    tituloInput.className = 'input';
    tituloInput.type = 'text';
    tituloInput.placeholder = 'Título del día (opcional)';
    tituloInput.value = titulo;
    tituloInput.style.fontWeight = '600';

    var descInput = document.createElement('textarea');
    descInput.className = 'textarea';
    descInput.rows = 2;
    descInput.placeholder = 'Descripción de las actividades del día';
    descInput.value = descripcion;

    var removeBtn = document.createElement('button');
    removeBtn.type = 'button';
    removeBtn.textContent = '×';
    removeBtn.style.cssText = 'background:none;border:0;font-size:22px;cursor:pointer;color:var(--c-danger-500);padding:4px 6px;line-height:1';
    removeBtn.title = 'Eliminar día';
    removeBtn.addEventListener('click', function() { row.remove(); syncItinerario(); });

    fields.appendChild(tituloInput);
    fields.appendChild(descInput);
    row.appendChild(diaLabel);
    row.appendChild(fields);
    row.appendChild(removeBtn);
    itinContainer.appendChild(row);

    // Sync on input
    tituloInput.addEventListener('input', syncItinerario);
    descInput.addEventListener('input', syncItinerario);
  }

  // Funcion: Sincroniza el itinerario editable con el campo oculto.
  function syncItinerario() {
    var rows = itinContainer.querySelectorAll('div[data-index]');
    var data = [];
    rows.forEach(function(row, i) {
      var inputs = row.querySelectorAll('input, textarea');
      if (inputs.length >= 2) {
        data.push({
          dia: i + 1,
          titulo: inputs[0].value,
          descripcion: inputs[1].value
        });
      }
    });
    // Actualizar labels de día
    rows.forEach(function(row, i) {
      var label = row.querySelector('div:first-child');
      if (label) label.textContent = 'Día ' + (i + 1);
    });
    itinJsonInput.value = JSON.stringify(data);
  }

  // Init
  var initialDias = parseInitialItinerario();
  renderItinerario(initialDias);
  syncItinerario();

  addBtn.addEventListener('click', function() {
    var rows = itinContainer.querySelectorAll('div[data-index]');
    addDiaRow(rows.length + 1, '', '');
    syncItinerario();
  });
});
</script>
