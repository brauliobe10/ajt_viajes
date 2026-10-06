// Funcion del archivo: Controla interacciones globales via jQuery — tema, favoritos, menus, validaciones.
/* UI utilities: theme, reveal on scroll, tabs ARIA, toast, favoritos, counters, validations, filter drawer */
$(function() {
  var $root = $(document.documentElement);
  var THEME_KEY = 'ajt_theme';

  /* ─── Theme switcher: aplica claro/oscuro y guarda la preferencia ─── */
  function getStoredTheme() {
    try { return localStorage.getItem(THEME_KEY); } catch(e) { return null; }
  }

  function getSystemTheme() {
    return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
  }

  function setTheme(theme, persist) {
    var nextTheme = theme === 'dark' ? 'dark' : 'light';
    $root.attr('data-theme', nextTheme).css('color-scheme', nextTheme);
    if (persist) {
      try { localStorage.setItem(THEME_KEY, nextTheme); } catch(e) {}
    }
    $('[data-theme-toggle]').each(function() {
      var isDark = nextTheme === 'dark';
      $(this).attr({
        'aria-pressed': isDark ? 'true' : 'false',
        'aria-label': isDark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro',
        'title': 'Tema'
      });
      $(this).find('[data-theme-label]').text('Tema');
    });
  }

  setTheme($root.attr('data-theme') || getStoredTheme() || getSystemTheme(), false);

  // Click delegation via jQuery para theme toggle
  $(document).on('click', '[data-theme-toggle]', function() {
    setTheme($root.attr('data-theme') === 'dark' ? 'light' : 'dark', true);
  });

  setTheme($root.attr('data-theme') || getStoredTheme() || getSystemTheme(), false);

  /* ─── Mostrar/ocultar contraseña via jQuery DOM manipulation ─── */
  $('input[type="password"]').each(function() {
    var $input = $(this);
    if ($input.data('passwordToggleReady') === true) return;

    var $wrapper = $('<div class="password-field"></div>');
    $input.before($wrapper).appendTo($wrapper);

    var $button = $('<button type="button" class="password-toggle"></button>')
      .attr({
        'aria-label': 'Mostrar contraseña',
        'aria-pressed': 'false',
        'title': 'Mostrar contraseña'
      })
      .html('<svg class="password-icon--show" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg><svg class="password-icon--hide" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m3 3 18 18M10.6 10.7a2 2 0 0 0 2.7 2.7M9.9 4.2A10.7 10.7 0 0 1 12 4c6.5 0 10 8 10 8a18.5 18.5 0 0 1-2.1 3.2M6.6 6.6C3.6 8.6 2 12 2 12s3.5 8 10 8a9.8 9.8 0 0 0 4.1-.9"/></svg>');
    $wrapper.append($button);
    $input.data('passwordToggleReady', true);

    $button.on('click', function() {
      var showing = $input.attr('type') === 'text';
      $input.attr('type', showing ? 'password' : 'text');
      $button.attr({
        'aria-pressed': showing ? 'false' : 'true',
        'aria-label': showing ? 'Mostrar contraseña' : 'Ocultar contraseña',
        'title': showing ? 'Mostrar contraseña' : 'Ocultar contraseña'
      });
      $input.trigger('focus');
    });
  });

  /* ─── Image fallback via jQuery ─── */
  function fallbackImagePath($img) {
    var custom = $img.attr('data-fallback-src');
    if (custom) return custom;
    var assets = $('body').attr('data-paths-assets');
    return (assets || './assets/') + 'img/package-placeholder.svg';
  }

  window.AJTFallbackImage = function(img) {
    var $img = $(img);
    if (!$img.length || $img.data('fallbackApplied')) return;
    $img.data('fallbackApplied', true).attr('src', fallbackImagePath($img));
  };

  $(document).on('error', 'img', function() {
    var $img = $(this);
    if ($img.data('fallbackApplied')) return;
    window.AJTFallbackImage(this);
  });

  /* ─── Reveal on scroll via jQuery + IntersectionObserver ─── */
  if ('IntersectionObserver' in window) {
    var revealObserver = new IntersectionObserver(function(entries) {
      $(entries).each(function() {
        if (this.isIntersecting) {
          $(this.target).addClass('is-visible');
          revealObserver.unobserve(this.target);
        }
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

    $('.reveal').each(function() { revealObserver.observe(this); });
  }

  /* ─── Tabs ARIA via jQuery selectores ─── */
  $('[data-tabs]').each(function() {
    var $root = $(this);
    var $tabs = $root.find('.tab');
    var $panels = $('[data-tab-panel]');

    $tabs.attr('role', 'tab').each(function() {
      var $tab = $(this);
      $tab.attr('tabindex', $tab.hasClass('is-active') ? '0' : '-1');

      $tab.on('click', function() {
        $tabs.removeClass('is-active').attr({ 'aria-selected': 'false', 'tabindex': '-1' });
        $tab.addClass('is-active').attr({ 'aria-selected': 'true', 'tabindex': '0' });
        var tgt = $tab.attr('data-tab');
        $panels.each(function() {
          var match = $(this).attr('data-tab-panel') === tgt;
          $(this).toggleClass('is-active', match).attr('aria-hidden', match ? 'false' : 'true');
        });
      });

      $tab.on('keydown', function(e) {
        var dir = e.key === 'ArrowRight' ? 1 : e.key === 'ArrowLeft' ? -1 : 0;
        if (!dir) return;
        e.preventDefault();
        var idx = $tabs.index($tab);
        var $next = $tabs.eq(idx + dir);
        if (!$next.length) $next = $tabs.eq(dir > 0 ? 0 : $tabs.length - 1);
        $next.focus().trigger('click');
      });
    });
  });

  /* ─── Toast notifications via jQuery ─── */
  function ensureWrap() {
    var $w = $('.toast-wrap');
    if (!$w.length) { $w = $('<div class="toast-wrap"></div>').appendTo('body'); }
    return $w;
  }

  window.toast = function(opts) {
    opts = typeof opts === 'string' ? { text: opts } : (opts || {});
    var $el = $('<div class="toast"></div>').addClass('toast--' + (opts.type || 'info'));
    $el.html('<svg class="toast__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg><div class="toast__body">' + (opts.title ? '<strong>' + opts.title + '</strong>' : '') + (opts.text || '') + '</div>');
    ensureWrap().append($el);
    setTimeout(function() {
      $el.addClass('is-out');
      setTimeout(function() { $el.remove(); }, 280);
    }, opts.duration || 3200);
  };

  /* ─── Counter animation via jQuery ─── */
  function animateCount(el) {
    var $el = $(el);
    var target = parseFloat($el.attr('data-counter')) || 0;
    var dur = parseInt($el.attr('data-counter-duration') || '1400', 10);
    var prefix = $el.attr('data-prefix') || '';
    var suffix = $el.attr('data-suffix') || '';
    var start = performance.now();
    function tick(now) {
      var t = Math.min(1, (now - start) / dur);
      var e = 1 - Math.pow(1 - t, 3);
      var v = target * e;
      $el.text(prefix + (target >= 1000 ? Math.round(v).toLocaleString('es-PE') : Math.round(v).toString()) + suffix);
      if (t < 1) requestAnimationFrame(tick);
    }
    requestAnimationFrame(tick);
  }

  if ('IntersectionObserver' in window) {
    var counterObserver = new IntersectionObserver(function(entries) {
      $(entries).each(function() {
        if (this.isIntersecting) {
          animateCount(this.target);
          counterObserver.unobserve(this.target);
        }
      });
    }, { threshold: 0.4 });

    $('[data-counter]').each(function() { counterObserver.observe(this); });
  }

  /* ─── Favorites via jQuery delegation ─── */
  function readFav() {
    try { return JSON.parse(localStorage.getItem('ajt_favs') || '[]'); } catch(e) { return []; }
  }
  function writeFav(arr) { localStorage.setItem('ajt_favs', JSON.stringify(arr)); }

  function syncFav() {
    var favs = readFav();
    $('[data-fav]').each(function() {
      var $btn = $(this);
      var on = favs.indexOf($btn.attr('data-fav')) !== -1;
      $btn.toggleClass('is-active', on).attr('aria-pressed', on ? 'true' : 'false');
    });
  }

  $(document).on('click', '[data-fav]', function(e) {
    e.preventDefault();
    var $btn = $(this);
    var id = $btn.attr('data-fav');
    var favs = readFav();
    var idx = $.inArray(id, favs);
    if (idx > -1) {
      favs.splice(idx, 1);
      window.toast && toast({ type: 'info', text: 'Quitado de favoritos' });
    } else {
      favs.push(id);
      window.toast && toast({ type: 'success', text: 'Agregado a favoritos' });
    }
    writeFav(favs);
    syncFav();
    document.dispatchEvent(new CustomEvent('ajt:favorites-changed', { detail: { favorites: favs.slice() } }));
  });

  syncFav();

  /* ─── Form validation via jQuery ─── */
  $(document).on('submit', 'form[data-validate]', function(e) {
    var $form = $(this);
    var ok = true;
    $form.find('[required]').each(function() {
      var $inp = $(this);
      var $field = $inp.closest('.field');
      var bad = false;
      if (!$.trim($inp.val())) bad = true;
      if ($inp.attr('type') === 'email' && $inp.val() && !/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test($inp.val())) bad = true;
      $field.toggleClass('is-error', bad);
      if (bad) ok = false;
    });
    if (!ok) {
      e.preventDefault();
      window.toast && toast({ type: 'error', title: 'Faltan datos', text: 'Revisa los campos marcados.' });
    }
  });

  /* ─── Filter drawer a11y: Escape, focus trap, aria-expanded via jQuery ─── */
  var $toggle = $('#filterToggle');
  var $panel = $('#filters-panel');
  if ($toggle.length && $panel.length) {
    var $mainContent = $('main').first() || $('.cat-layout');

    function openDrawer() {
      $panel.addClass('is-open');
      $('body').addClass('filter-open');
      $toggle.attr('aria-expanded', 'true');
      $mainContent.attr('aria-hidden', 'true');
      $panel.find('button').first().focus();
    }
    function closeDrawer() {
      $panel.removeClass('is-open');
      $('body').removeClass('filter-open');
      $toggle.attr('aria-expanded', 'false');
      $mainContent.removeAttr('aria-hidden');
      $toggle.focus();
    }
    function isDrawerOpen() { return $panel.hasClass('is-open'); }

    $toggle.on('click', function(e) {
      e.stopPropagation();
      isDrawerOpen() ? closeDrawer() : openDrawer();
    });

    $panel.find('.filters__close button').on('click', function(e) {
      e.stopPropagation();
      closeDrawer();
    });

    $(document).on('keydown', function(e) {
      if (e.key === 'Escape' && isDrawerOpen()) {
        e.preventDefault();
        closeDrawer();
      }
    });

    $panel.on('keydown', function(e) {
      if (e.key !== 'Tab' || !isDrawerOpen()) return;
      var $focusable = $panel.find('button, input, select, a[href], [tabindex]:not([tabindex="-1"])');
      if (!$focusable.length) return;
      var $first = $focusable.first();
      var $last = $focusable.last();
      if (e.shiftKey && document.activeElement === $first[0]) {
        e.preventDefault();
        $last.focus();
      } else if (!e.shiftKey && document.activeElement === $last[0]) {
        e.preventDefault();
        $first.focus();
      }
    });
  }

  /* ─── Navbar toggle via jQuery ─── */
  var $nav = $('#siteNav');
  var $navBtn = $('#navToggle');
  if ($navBtn.length) {
    $navBtn.on('click', function() {
      var open = $nav.toggleClass('is-open').hasClass('is-open');
      $navBtn.attr('aria-expanded', open ? 'true' : 'false');
      $('body').toggleClass('nav-open', open);
    });
  }

  /* ─── Active nav link via jQuery ─── */
  var page = $('body').attr('data-page');
  if (page) {
    $('.nav__link[data-nav="' + page + '"]').addClass('is-active');
  }
});
