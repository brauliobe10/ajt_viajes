<!-- Funcion del archivo: Renderiza los terminos y condiciones configurados. -->
<main id="main-content" class="container section" style="padding-top:48px;padding-bottom:48px">
  <nav class="breadcrumbs" style="margin-bottom:18px">
    <a href="<?= BASE_URL ?>/">Inicio</a>
    <span class="sep">/</span>
    <span class="current">Términos y Condiciones</span>
  </nav>

  <article style="max-width:800px">
    <h1>Términos y Condiciones</h1>
    <div style="line-height:1.8;color:var(--c-ink-700)">
      <?= $contenido ?>
    </div>
    <p style="margin-top:32px">
      <a href="<?= BASE_URL ?>/" class="btn btn--ghost">← Volver al inicio</a>
    </p>
  </article>
</main>
