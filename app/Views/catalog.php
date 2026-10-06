<!-- Funcion del archivo: Renderiza el catalogo publico con filtros, busqueda y favoritos. -->
<section class="cat-hero">
  <div class="container">
    <nav class="breadcrumbs" style="color:rgba(255,255,255,.7);margin-bottom:12px">
      <a href="<?= BASE_URL ?>/" style="color:rgba(255,255,255,.75)">Inicio</a>
      <span class="sep">/</span><span class="current" style="color:#fff">Catálogo</span>
    </nav>
    <h1>Encuentra tu próxima aventura</h1>
    <p>Paquetes turísticos reales, curados y listos para reservar.</p>
    <form class="cat-search" id="catSearchForm" role="search">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--c-primary-700);margin-left:8px"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
      <input type="search" id="catSearchInput" placeholder="Cusco, Cancún, Europa…">
      <button type="submit" class="btn btn--primary">Buscar</button>
    </form>
  </div>
</section>

<main id="main-content" class="container">
  <div class="cat-layout">
    <button type="button" class="btn btn--ghost filter-toggle" id="filterToggle" aria-expanded="false" aria-controls="filters-panel">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M7 12h10M10 18h4"/></svg>
      Filtros
    </button>
    <aside class="filters" id="filters-panel" role="dialog" aria-modal="true" aria-label="Filtros">
      <div class="filters__close"><h3 style="margin:0">Filtros</h3><button type="button">✕</button></div>
      <h3>Filtros <button type="button" id="clearFilters">Limpiar</button></h3>

      <div class="filter-group">
        <h4>Tipo</h4>
        <div class="chips" id="categoryChips">
          <button class="chip is-active" type="button" data-val="Todos">Todos</button>
          <?php foreach (($categorias ?? []) as $categoria): ?>
            <?php $categoryName = trim((string)($categoria['nombre'] ?? '')); ?>
            <?php if ($categoryName !== ''): ?>
              <button class="chip" type="button" data-val="<?= htmlspecialchars($categoryName) ?>"><?= htmlspecialchars($categoryName) ?></button>
            <?php endif; ?>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="filter-group">
        <h4>Precio (S/)</h4>
        <div class="range">
          <input type="number" id="priceMinInput" placeholder="0">
          <span>—</span>
          <input type="number" id="priceMaxInput" placeholder="5000">
        </div>
      </div>

      <div class="filter-group">
        <h4>Duración</h4>
        <label class="check"><input type="checkbox" name="durFilter" value="1-3"> 1–3 días</label><br>
        <label class="check"><input type="checkbox" name="durFilter" value="4-7"> 4–7 días</label><br>
        <label class="check"><input type="checkbox" name="durFilter" value="8-14"> 8–14 días</label><br>
        <label class="check"><input type="checkbox" name="durFilter" value="15+"> 15+ días</label>
      </div>

      <?php if (!empty($dificultades)): ?>
      <div class="filter-group">
        <h4>Dificultad</h4>
        <div class="chips" id="difficultyChips">
          <button class="chip is-active" type="button" data-val="Todos">Todos</button>
          <?php foreach ($dificultades as $difficulty): ?>
            <button class="chip" type="button" data-val="<?= htmlspecialchars($difficulty) ?>"><?= htmlspecialchars(ucfirst($difficulty)) ?></button>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- Filtros especiales (basados en datos reales de BD) -->
      <div class="filter-group">
        <h4>Características</h4>
        <label class="check"><input type="checkbox" id="filterFlight"> Vuelo incluido</label><br>
        <label class="check"><input type="checkbox" id="filterAllInclusive"> Todo incluido</label><br>
        <label class="check"><input type="checkbox" id="filterFamily"> Familiar</label>
      </div>

      <?php if (!empty($tags)): ?>
        <div class="filter-group">
          <h4>Tags</h4>
          <div class="chips" id="tagChips">
            <button class="chip is-active" type="button" data-val="Todos">Todos</button>
            <?php foreach ($tags as $tag): ?>
              <button class="chip" type="button" data-val="<?= htmlspecialchars($tag) ?>"><?= htmlspecialchars($tag) ?></button>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>

    </aside>

    <section>
      <div class="cat-toolbar">
        <div class="count">Mostrando <strong id="catCount">0</strong> de <strong><?= (int)($totalCatalogPackages ?? count($paquetes)) ?></strong> paquetes</div>
        <div class="row">
          <label class="field" style="flex-direction:row;align-items:center;gap:8px;margin:0"><span class="label" style="margin:0">Ordenar</span>
            <select class="select" id="sortSelect" style="padding:8px 12px;width:auto">
              <option value="relevant">Más relevantes</option>
              <option value="price_asc">Precio: menor a mayor</option>
              <option value="price_desc">Precio: mayor a menor</option>
            </select>
          </label>
        </div>
      </div>

      <div class="chips" id="activeFilterChips" style="margin:0 0 12px"></div>
      <div class="cat-grid" id="catGrid"></div>

      <template id="cardTpl">
        <article class="card card--hover">
          <div class="card__media">
            <img loading="lazy" decoding="async" alt="">
            <div class="card__media-top">
              <span class="badge badge--promo">-20%</span>
              <button class="fav-btn" type="button" aria-label="Favorito" data-fav="">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 1 0-7.78 7.78L12 21.23l8.84-8.84a5.5 5.5 0 0 0 0-7.78z"/></svg>
              </button>
            </div>
          </div>
          <div class="card__body">
            <div class="row row--between">
              <span class="badge bg-country"></span>
              <span class="badge bg-difficulty" style="display:none"></span>
            </div>
            <h3 class="card__title"></h3>
            <div class="card__meta-group">
              <span class="card__meta-item"><svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg><span class="dur"></span></span>
              <span class="card__meta-item card__meta-flight" style="display:none"><svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.8 19.2 16 11l3.5-3.5C21 6 21.5 4 21 3c-1-.5-3 0-4.5 1.5L13 8 4.8 6.2c-.5-.1-.9.1-1.1.5l-.3.5c-.2.5-.1 1 .3 1.3L9 12l-2 3H4l-1 1 3 2 2 3 1-1v-3l3-2 3.5 5.3c.3.4.8.5 1.3.3l.5-.2c.4-.3.6-.7.5-1.2z"/></svg><span class="flight-status"></span></span>
            </div>
            <p class="card__desc"></p>
            <div class="card__footer">
              <div class="card__price">
                <span class="card__price-old"></span>
                <span class="card__price-from">desde</span>
                <span class="card__price-value"></span>
              </div>
              <a href="" class="btn btn--primary btn--sm btn-detail">Ver paquete →</a>
            </div>
          </div>
        </article>
      </template>

      <div id="emptyState" class="state" style="display:none">
        <div class="state__icon"><svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg></div>
        <h3 class="state__title">Sin resultados</h3>
        <p class="state__text">Prueba ajustando los filtros para encontrar tu paquete ideal.</p>
        <button class="btn btn--ghost" id="btnEmptyClearFilters">Limpiar filtros</button>
      </div>
    </section>
  </div>
</main>

<?php
  // Mapeamos los datos dinámicos a JSON para el motor interactivo
  $paquetesJSON = [];
  foreach ($paquetes as $p) {
      $currency = 'S/';
      
      $oldPrice = (!empty($p['precio_anterior']) && (float)$p['precio_anterior'] > (float)$p['precio_base']) ? (float)$p['precio_anterior'] : 0;
      $badge = $p['categoria_nombre'] ?? '';
      // Solo imagen principal del catálogo — NO cargar galería completa
      // La imagen ya viene optimizada para catálogo desde Paquete::all()
      $mainImg = $p['imagen_url'] ?? (BASE_URL . '/assets/img/package-placeholder.svg');

      $paquetesJSON[] = [
          'id' => $p['id_paquete'] ?? 0,
          'slug' => $p['slug'] ?? '',
          'category' => $p['categoria_nombre'] ?? '',
          'city' => $p['nombre'] ?? '',
          'country' => $p['ciudad_nombre'] ?? '',
          'dur' => (int)$p['duracion_dias'] . ' días · ' . (int)$p['duracion_noches'] . ' noches',
          'dur_dias' => (int)$p['duracion_dias'],
          'desc' => mb_substr(strip_tags(($p['descripcion_corta'] ?? '') ?: ($p['descripcion'] ?? '')), 0, 140),
          'img' => $mainImg,
          'fallback_img' => BASE_URL . '/assets/img/package-placeholder.svg',
          'price' => (float)$p['precio_base'],
          'old' => (float)$oldPrice,
          'currency' => $currency,
          'badge' => $badge,
          'difficulty' => $p['dificultad'] ?? '',
          'flight' => $p['vuelos'] ?? '',
          'comidas' => $p['comidas'] ?? '',
          'hotel' => $p['hotel'] ?? '',
          'tags' => array_values($p['tags'] ?? []),
          // Filtros especiales derivados de datos reales
          'vuelo_incluido' => self::isFlightIncluded($p['vuelos'] ?? ''),
          'todo_incluido' => self::isAllInclusive($p['comidas'] ?? '', $p['hotel'] ?? ''),
          'familiar' => self::isFamily($p['tags'] ?? [], $p['nombre'] ?? '', $p['descripcion_corta'] ?? ''),
      ];
  }
?>


<script nonce="<?= htmlspecialchars(\App\Helper\SecurityHeaders::getNonce(), ENT_QUOTES, 'UTF-8') ?>">
var PKGS = <?= json_encode($paquetesJSON, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
var BASE_URL_JS = <?= json_encode(BASE_URL) ?>;

var ajaxResults = null;
var ajaxLoading = false;
var searchTimeout = null;

// Funcion: Busca paquetes en el servidor via $.ajax() (endpoint /api/paquetes/buscar).
function searchPackagesAPI(query) {
  if (!query || $.trim(query).length === 0) {
    ajaxResults = null;
    applyCatalogFilters();
    return;
  }

  ajaxLoading = true;
  $('#catSearchInput').addClass('is-loading');
  applyCatalogFilters();

  $.ajax({
    url: '<?= BASE_URL ?>/api/paquetes/buscar',
    method: 'GET',
    dataType: 'json',
    data: { q: $.trim(query) },
    success: function(data) {
      ajaxResults = $.map(data, function(p) {
        return {
          id: p.id,
          slug: p.slug,
          category: p.categoria_nombre || '',
          city: p.nombre || '',
          country: p.ciudad_nombre || '',
          dur: p.duracion_dias + ' días · ' + p.duracion_noches + ' noches',
          dur_dias: Number(p.duracion_dias || 0),
          desc: p.descripcion_corta || '',
          img: p.imagen_principal || (BASE_URL_JS + '/assets/img/package-placeholder.svg'),
          fallback_img: p.imagen_fallback || p.imagen_principal || (BASE_URL_JS + '/assets/img/hero/hero-home.jpg'),
          price: Number(p.precio_base || 0),
          old: (p.precio_anterior && p.precio_anterior > p.precio_base) ? Number(p.precio_anterior) : 0,
          currency: 'S/',
          badge: p.categoria_nombre || '',
          difficulty: p.dificultad || '',
          flight: p.vuelos || '',
          comidas: p.comidas || '',
          hotel: p.hotel || '',
          tags: $.isArray(p.tags) ? p.tags : [],
          vuelo_incluido: p.vuelo_incluido || false,
          todo_incluido: p.todo_incluido || false,
          familiar: p.familiar || false
        };
      });
    },
    error: function(xhr, status, error) {
      console.error('Search API error:', status, error);
      ajaxResults = [];
    },
    complete: function() {
      ajaxLoading = false;
      $('#catSearchInput').removeClass('is-loading');
      applyCatalogFilters();
    }
  });
}

// Funcion: Elige entre resultados filtrados o catalogo local.
function getActiveDataSource() {
  var q = $.trim($('#catSearchInput').val());
  if (q && ajaxResults !== null) {
    return ajaxResults;
  }
  return PKGS;
}

// Funcion: Dibuja las tarjetas de paquetes en el catalogo via jQuery DOM manipulation.
function render(list){
  var $grid = $('#catGrid');
  var tpl = document.getElementById('cardTpl');
  $grid.empty();

  if (ajaxLoading) {
    renderSkeletons(6);
    $('#emptyState').hide();
    return;
  }

  $('#emptyState').toggle(list.length === 0);

  $.each(list, function(i, p) {
    var $card = $(tpl.content.cloneNode(true));
    var $img = $card.find('img');
    $img.on('error', function(){
      $(this).off('error');
      $(this).attr('src', 'data:image/svg+xml,' + encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="750" viewBox="0 0 1200 750"><rect width="1200" height="750" fill="#e2e8f0"/><text x="600" y="375" text-anchor="middle" font-family="system-ui" font-size="28" fill="#94a3b8">Imagen no disponible</text></svg>'));
    });
    $img.attr({ src: p.img, alt: p.city });

    $card.find('.fav-btn').attr('data-fav', p.slug);
    $card.find('.bg-country').text(p.country || p.category);

    var $diffEl = $card.find('.bg-difficulty');
    if (p.difficulty) {
      $diffEl.text(p.difficulty).show();
    }

    $card.find('.card__title').text(p.city);
    $card.find('.dur').text(p.dur);
    $card.find('.card__desc').text(p.desc);
    $card.find('.card__price-value').text(p.currency + ' ' + p.price.toLocaleString('es-PE'));

    // Flight status badge
    var $flightEl = $card.find('.card__meta-flight');
    if (p.vuelo_incluido) {
      $flightEl.show().css('color', 'var(--c-success-600)');
      $card.find('.flight-status').text('Vuelo incluido');
    }

    var $oldEl = $card.find('.card__price-old');
    var $promoBadge = $card.find('.badge--promo');
    if (p.old && p.old > p.price) {
      $oldEl.text(p.currency + ' ' + p.old.toLocaleString('es-PE'));
      $promoBadge.text('-' + Math.round((1 - p.price/p.old)*100) + '%');
    } else {
      $oldEl.hide();
      $promoBadge.hide();
    }

    $card.find('a.btn-detail').attr('href', '<?= BASE_URL ?>/paquete/' + p.slug);
    $grid.append($card);
  });

  $('#catCount').text(list.length);
  syncCatalogFavs();
}

// Funcion: Obtiene el valor activo de un grupo de filtros via jQuery.
function getActiveChipValue(selector) {
  return $(selector + ' .chip.is-active').attr('data-val') || 'Todos';
}

// Funcion: Marca visualmente el filtro seleccionado via jQuery.
function setActiveChip(selector, value) {
  var target = (value || 'Todos').toLowerCase();
  var matched = false;
  $(selector + ' .chip').each(function() {
    var isMatch = $(this).attr('data-val').toLowerCase() === target;
    $(this).toggleClass('is-active', isMatch);
    matched = matched || isMatch;
  });
  if (!matched) {
    $(selector + ' .chip').first().addClass('is-active');
  }
}

// Funcion: Muestra los filtros activos aplicados al catalogo via jQuery.
function renderActiveFilterChips(filters) {
  var $box = $('#activeFilterChips');
  if (!$box.length) return;
  var items = [];
  if (filters.q) items.push('Búsqueda: ' + filters.q);
  if (filters.category !== 'Todos') items.push('Tipo: ' + filters.category);
  if (filters.difficulty !== 'Todos') items.push('Dificultad: ' + filters.difficulty);
  if (filters.tag !== 'Todos') items.push('Tag: ' + filters.tag);
  if (filters.priceMin !== null) items.push('Desde: ' + filters.priceMin);
  if (filters.priceMax !== null) items.push('Hasta: ' + filters.priceMax);
  $.each(filters.durations, function(i, d) { items.push('Duración: ' + d); });
  if (filters.flight) items.push('Vuelo incluido');
  if (filters.allInclusive) items.push('Todo incluido');
  if (filters.family) items.push('Familiar');
  $box.empty();
  $.each(items, function(i, text) {
    $box.append('<span class="chip is-active" style="cursor:default">' + text + '</span>');
  });
}

// Funcion: Muestra placeholders mientras cargan resultados via jQuery.
function renderSkeletons(count) {
  var $grid = $('#catGrid');
  var html = '';
  for (var i = 0; i < count; i++) {
    html += '<article class="card" style="pointer-events:none">' +
      '<div class="card__media"><div class="skeleton" style="width:100%;height:100%;aspect-ratio:16/10"></div></div>' +
      '<div class="card__body" style="gap:10px">' +
        '<div class="skeleton" style="width:60px;height:18px;border-radius:999px"></div>' +
        '<div class="skeleton" style="width:80%;height:20px"></div>' +
        '<div class="skeleton" style="width:50%;height:14px"></div>' +
        '<div class="skeleton" style="width:100%;height:14px"></div>' +
        '<div style="display:flex;justify-content:space-between;align-items:center;margin-top:auto;padding-top:12px;border-top:1px solid var(--c-border)">' +
          '<div class="skeleton" style="width:80px;height:24px"></div>' +
          '<div class="skeleton" style="width:90px;height:32px;border-radius:var(--r-sm)"></div>' +
        '</div>' +
      '</div>' +
    '</article>';
  }
  $grid.html(html);
}

// Funcion: Aplica busqueda, chips y filtros al catalogo via jQuery selectores.
function applyCatalogFilters(){
  var q = $.trim($('#catSearchInput').val()).toLowerCase();
  var activeType = getActiveChipValue('#categoryChips');
  var activeDifficulty = $('#difficultyChips').length ? getActiveChipValue('#difficultyChips') : 'Todos';
  var activeTag = $('#tagChips').length ? getActiveChipValue('#tagChips') : 'Todos';

  var priceMinVal = $('#priceMinInput').val();
  var priceMin = priceMinVal !== '' ? parseFloat(priceMinVal) : null;
  var priceMaxVal = $('#priceMaxInput').val();
  var priceMax = priceMaxVal !== '' ? parseFloat(priceMaxVal) : null;

  var selectedDurations = [];
  $('input[name="durFilter"]:checked').each(function() { selectedDurations.push($(this).val()); });
  var sortVal = $('#sortSelect').val();

  // Special filters (real data from BD)
  var filterFlight = $('#filterFlight').is(':checked');
  var filterAllInclusive = $('#filterAllInclusive').is(':checked');
  var filterFamily = $('#filterFamily').is(':checked');

  var list = $.grep(getActiveDataSource(), function(p) {
    var text = [p.city, p.country, p.dur, p.category, p.desc, p.difficulty, p.flight, p.comidas, p.hotel].concat(p.tags || []).join(' ').toLowerCase();
    var matchesText = !q || text.indexOf(q) !== -1;
    var matchesType = activeType === 'Todos' || p.category === activeType;
    var matchesDifficulty = activeDifficulty === 'Todos' || (p.difficulty || '').toLowerCase() === activeDifficulty.toLowerCase();
    var matchesTag = activeTag === 'Todos' || $.grep(p.tags || [], function(tag) { return tag.toLowerCase() === activeTag.toLowerCase(); }).length > 0;

    var matchesPrice = true;
    if (priceMin !== null) matchesPrice = matchesPrice && (p.price >= priceMin);
    if (priceMax !== null) matchesPrice = matchesPrice && (p.price <= priceMax);

    var matchesDuration = true;
    if (selectedDurations.length > 0) {
      matchesDuration = false;
      var d = p.dur_dias;
      $.each(selectedDurations, function(i, rango) {
        if (rango === '1-3' && d >= 1 && d <= 3) matchesDuration = true;
        if (rango === '4-7' && d >= 4 && d <= 7) matchesDuration = true;
        if (rango === '8-14' && d >= 8 && d <= 14) matchesDuration = true;
        if (rango === '15+' && d >= 15) matchesDuration = true;
      });
    }

    // Special filters
    var matchesFlight = !filterFlight || p.vuelo_incluido === true;
    var matchesAllInclusive = !filterAllInclusive || p.todo_incluido === true;
    var matchesFamily = !filterFamily || p.familiar === true;

    return matchesText && matchesType && matchesDifficulty && matchesTag && matchesPrice && matchesDuration && matchesFlight && matchesAllInclusive && matchesFamily;
  });

  if (sortVal === 'price_asc') {
    list.sort(function(a, b) { return a.price - b.price; });
  } else if (sortVal === 'price_desc') {
    list.sort(function(a, b) { return b.price - a.price; });
  }

  renderActiveFilterChips({
    q: q, category: activeType, difficulty: activeDifficulty, tag: activeTag,
    priceMin: priceMin, priceMax: priceMax, durations: selectedDurations,
    flight: filterFlight, allInclusive: filterAllInclusive, family: filterFamily
  });
  render(list);
}

// Funcion: Sincroniza favoritos visibles en el catalogo via jQuery.
function syncCatalogFavs(){
  var favs = [];
  try { favs = JSON.parse(localStorage.getItem('ajt_favs') || '[]'); } catch(e) {}
  $('[data-fav]').each(function() {
    var $btn = $(this);
    var on = favs.indexOf($btn.attr('data-fav')) !== -1;
    $btn.toggleClass('is-active', on).attr('aria-pressed', on ? 'true' : 'false');
  });
}

// ─── Inicialización del catálogo via jQuery $(document).ready ───
$(function() {
  var params = new URLSearchParams(location.search);
  $('#catSearchInput').val(params.get('destino') || params.get('q') || '');
  $('#priceMinInput').val(params.get('precio_min') || '');
  $('#priceMaxInput').val(params.get('precio_max') || '');
  setActiveChip('#categoryChips', params.get('categoria') || 'Todos');
  if ($('#difficultyChips').length) setActiveChip('#difficultyChips', params.get('dificultad') || 'Todos');
  if ($('#tagChips').length) setActiveChip('#tagChips', params.get('tag') || 'Todos');
  if (params.get('orden')) $('#sortSelect').val(params.get('orden'));
  params.getAll('duracion').forEach(function(value) {
    $('input[name="durFilter"]').each(function() {
      if ($(this).val() === value) $(this).prop('checked', true);
    });
  });

  applyCatalogFilters();

  // Búsqueda del catálogo via $.ajax() con debounce
  $('#catSearchForm').on('submit', function(e) {
    e.preventDefault();
    applyCatalogFilters();
  });

  $('#catSearchInput').on('input', function() {
    clearTimeout(searchTimeout);
    var val = $.trim($(this).val());
    searchTimeout = setTimeout(function() { searchPackagesAPI(val); }, 350);
  });

  $('#priceMaxInput, #priceMinInput').on('input', applyCatalogFilters);
  $('#sortSelect').on('change', applyCatalogFilters);
  $('input[name="durFilter"]').on('change', applyCatalogFilters);

  // Special feature filters
  $('#filterFlight, #filterAllInclusive, #filterFamily').on('change', applyCatalogFilters);

  // Chip filter clicks via jQuery
  $('#categoryChips, #difficultyChips, #tagChips').each(function() {
    var $group = $(this);
    $group.find('.chip').on('click', function() {
      $group.find('.chip').removeClass('is-active');
      $(this).addClass('is-active');
      applyCatalogFilters();
    });
  });

  $('#clearFilters').on('click', function() {
    window.location.href = '<?= BASE_URL ?>/catalog';
  });

  $('#btnEmptyClearFilters').on('click', function() {
    $('#clearFilters').trigger('click');
  });
});
</script>
