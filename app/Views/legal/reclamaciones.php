<!-- Funcion del archivo: Renderiza informaci?n legal del libro de reclamaciones. -->
<main id="main-content" class="container section" style="padding-top:48px;padding-bottom:48px">
  <nav class="breadcrumbs" style="margin-bottom:18px">
    <a href="<?= BASE_URL ?>/">Inicio</a>
    <span class="sep">/</span>
    <span class="current">Libro de Reclamaciones</span>
  </nav>

  <article style="max-width:800px">
    <h1>Libro de Reclamaciones</h1>
    <p style="color:var(--c-ink-600);margin-bottom:24px">De acuerdo con la Ley N.° 29571, Código de Protección y Defensa del Consumidor, ponemos a disposición nuestro libro de reclamaciones virtual.</p>

    <?php if ($libroUrl): ?>
    <p>
      <a href="<?= htmlspecialchars($libroUrl) ?>" target="_blank" rel="noopener" class="btn btn--primary btn--lg">
        Acceder al Libro de Reclamaciones →
      </a>
    </p>
    <?php endif; ?>

    <p style="margin-top:24px">
      <a href="<?= BASE_URL ?>/reclamaciones" class="btn btn--accent btn--lg">
        Registrar reclamo o sugerencia
      </a>
    </p>

    <?php if (!$libroUrl): ?>
    <div style="background:var(--c-surface-muted);border:1px solid var(--c-border);border-radius:var(--r-lg);padding:32px;text-align:center;color:var(--c-ink-600)">
      <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="var(--c-ink-400)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin:0 auto 12px;display:block"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
      <p style="font-size:var(--fs-15);margin:0 0 8px;font-weight:var(--fw-semibold)">Libro de Reclamaciones Virtual</p>
      <p style="font-size:var(--fs-13);margin:0;color:var(--c-ink-500)">El enlace al libro de reclamaciones será habilitado por el administrador. Según la Ley N.° 29571, tienes derecho a registrar tu reclamo ante cualquier inconformidad con el servicio.</p>
    </div>
    <?php endif; ?>

    <p style="margin-top:32px">
      <a href="<?= BASE_URL ?>/" class="btn btn--ghost">← Volver al inicio</a>
    </p>
  </article>
</main>
