<?php

// Funcion del archivo: Renderiza servicios de visa y el modal de solicitud.
// Mapeo dinámico para datos complementarios de visas que no forman parte de la BD estructurada
$visaDetalles = [
    'Americana B1/B2' => [
        'flag' => '🇺🇸',
        'tiempo' => '2–8 semanas',
        'dificultad' => 'Media',
        'dificultad_class' => 'badge--warning',
        'vigencia' => '10 años',
        'descripcion' => 'Turismo y negocios'
    ],
    'Schengen (Europa 26 países)' => [
        'flag' => '🇪🇺',
        'tiempo' => '15 días',
        'dificultad' => 'Baja',
        'dificultad_class' => 'badge--success',
        'vigencia' => '1–5 años',
        'descripcion' => 'Europa 26 países'
    ],
    'Canadá ETA/Visitor' => [
        'flag' => '🇨🇦',
        'tiempo' => '4 semanas',
        'dificultad' => 'Media',
        'dificultad_class' => 'badge--warning',
        'vigencia' => '10 años',
        'descripcion' => 'Turismo'
    ],
    'Reino Unido Standard Visitor' => [
        'flag' => '🇬🇧',
        'tiempo' => '3 semanas',
        'dificultad' => 'Alta',
        'dificultad_class' => 'badge--danger',
        'vigencia' => '6 meses–10 años',
        'descripcion' => 'Standard Visitor'
    ],
    'Australia eVisitor' => [
        'flag' => '🇦🇺',
        'tiempo' => '7 días',
        'dificultad' => 'Baja',
        'dificultad_class' => 'badge--success',
        'vigencia' => '1 año',
        'descripcion' => 'Turismo electrónico'
    ],
    'Japón de Turista' => [
        'flag' => '🇯🇵',
        'tiempo' => '10 días',
        'dificultad' => 'Baja',
        'dificultad_class' => 'badge--success',
        'vigencia' => '3 meses',
        'descripcion' => 'Visa de turista'
    ]
];
?>

<style>
  .v-hero {
    background: linear-gradient(135deg, var(--c-primary-900), var(--c-primary-700));
    color: #fff;
    padding: 64px 0 88px;
    position: relative;
    overflow: hidden;
  }
  .v-hero::after {
    content: "";
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at 90% 10%, rgba(255, 214, 10, .25), transparent 40%);
    pointer-events: none;
  }
  .v-hero h1 {
    color: #fff;
    max-width: 18ch;
  }
  .v-hero p {
    color: rgba(255, 255, 255, .85);
    max-width: 60ch;
  }
  .v-kpis {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-top: -56px;
    position: relative;
    z-index: 2;
  }
  @media (max-width: 760px) {
    .v-kpis {
      grid-template-columns: repeat(2, 1fr);
    }
  }
  .v-table {
    overflow-x: auto;
  }
  .check-list {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 12px;
  }
  /* Superficies de visas adaptadas a tema para evitar texto de bajo contraste. */
  .check-item {
    display: flex;
    gap: 10px;
    align-items: flex-start;
    background: var(--c-surface);
    border: 1px solid var(--c-border);
    border-radius: var(--r-md);
    padding: 14px;
  }
  .check-item svg {
    color: var(--c-success-500);
    flex-shrink: 0;
    margin-top: 2px;
  }
  .check-item strong {
    display: block;
    color: var(--c-ink-900);
  }
  .check-item span {
    font-size: var(--fs-13);
    color: var(--c-ink-600);
  }

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
    box-shadow: 0 30px 80px -20px rgba(0, 0, 0, .4);
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
<main id="main-content">
<section class="v-hero">
  <div class="container">
    <nav class="breadcrumbs" style="margin-bottom:14px">
      <a href="<?= BASE_URL ?>/" style="color:rgba(255,255,255,.75)">Inicio</a>
      <span class="sep" style="color:rgba(255,255,255,.5)">/</span>
      <span class="current" style="color:#fff">Visas</span>
    </nav>
    <span class="eyebrow" style="background:rgba(255,255,255,.1);color:var(--c-accent-500);border:1px solid rgba(255,255,255,.15)">Asesoría especializada</span>
    <h1 style="margin-top:14px">Tu visa aprobada al primer intento</h1>
    <p class="lead">Acompañamiento integral: diagnóstico, llenado de formularios oficiales, preparación para la entrevista y seguimiento hasta el resultado.</p>
    <div class="row" style="margin-top:18px">
      <a href="<?= BASE_URL ?>/contacto" class="btn btn--accent btn--lg">Solicitar diagnóstico gratis</a>
      <a href="#tipos" class="btn btn--white btn--lg">Ver tipos de visa</a>
    </div>
  </div>
</section>

<div class="container">
  <!-- Disclaimer de asesoría -->
  <div class="alert alert--info" style="margin-top:20px;margin-bottom:72px">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
    <div>
      <strong>Importante:</strong> La solicitud de asesoría no inicia automáticamente un trámite migratorio. Un asesor revisará tus datos y se comunicará contigo para indicarte requisitos, costos, tiempos y documentos necesarios.
    </div>
  </div>

  <!-- Alerts de sesión -->
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

  <div class="v-kpis">
    <div class="kpi">
      <div class="kpi__head"><span class="kpi__label">Visas tramitadas</span><div class="kpi__icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg></div></div>
      <div class="kpi__value"><span data-counter="3800" data-suffix="+">3800+</span></div>
    </div>
    <div class="kpi">
      <div class="kpi__head"><span class="kpi__label">% Aprobación</span><div class="kpi__icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></div></div>
      <div class="kpi__value"><span data-counter="95" data-suffix="%">95%</span></div>
    </div>
    <div class="kpi">
      <div class="kpi__head"><span class="kpi__label">Países cubiertos</span><div class="kpi__icon">🌍</div></div>
      <div class="kpi__value"><span data-counter="42">42</span></div>
    </div>
    <div class="kpi">
      <div class="kpi__head"><span class="kpi__label">Tiempo promedio</span><div class="kpi__icon">⏱</div></div>
      <div class="kpi__value">14 d</div>
    </div>
  </div>
</div>

<section class="section" id="tipos">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow">Tipos de visa</span>
      <h2>Compara y elige tu destino</h2>
    </div>
    <div class="table-wrap v-table">
      <table class="table">
        <thead>
          <tr>
            <th>Visa</th>
            <th>Tiempo estimado</th>
            <th>Dificultad</th>
            <th>Vigencia</th>
            <th>Costo Asesoría</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($servicios)): ?>
            <?php foreach ($servicios as $s): ?>
              <?php 
                $det = $visaDetalles[$s['tipo_visa']] ?? [
                  'flag' => '🌍',
                  'tiempo' => '3–4 semanas',
                  'dificultad' => 'Media',
                  'dificultad_class' => 'badge--warning',
                  'vigencia' => 'Variable',
                  'descripcion' => 'Asesoría especializada'
                ];
              ?>
              <tr>
                <td>
                  <strong><?= $det['flag'] ?> <?= htmlspecialchars($s['tipo_visa']) ?></strong>
                  <div class="small"><?= htmlspecialchars($det['descripcion']) ?></div>
                </td>
                <td><?= htmlspecialchars($det['tiempo']) ?></td>
                <td><span class="badge <?= $det['dificultad_class'] ?>"><?= htmlspecialchars($det['dificultad']) ?></span></td>
                <td><?= htmlspecialchars($det['vigencia']) ?></td>
                <td><strong>$ <?= number_format($s['precio_asesoria'], 0) ?></strong></td>
                <td>
                  <button type="button" 
                          class="btn btn--primary btn--sm js-solicitar-visa"
                          data-id="<?= $s['id_servicio'] ?>"
                          data-tipo="<?= htmlspecialchars($s['tipo_visa']) ?>"
                          data-pais="<?= htmlspecialchars($s['pais_nombre']) ?>"
                          data-precio="<?= $s['precio_asesoria'] ?>">
                    Solicitar
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="6" style="text-align: center; color: var(--c-ink-500)">No hay servicios de visa disponibles por el momento.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<section class="section section--tight">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow">Requisitos generales</span>
      <h2>Documentación que necesitas preparar</h2>
    </div>
    <div class="check-list">
      <div class="check-item">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m20 6-11 11-5-5"/></svg>
        <div><strong>Pasaporte vigente</strong><span>Mínimo 6 meses de validez</span></div>
      </div>
      <div class="check-item">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m20 6-11 11-5-5"/></svg>
        <div><strong>Foto reciente</strong><span>Tamaño visa, fondo blanco</span></div>
      </div>
      <div class="check-item">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m20 6-11 11-5-5"/></svg>
        <div><strong>Estados de cuenta</strong><span>Últimos 3 meses bancarios</span></div>
      </div>
      <div class="check-item">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m20 6-11 11-5-5"/></svg>
        <div><strong>Carta laboral</strong><span>Cargo, sueldo, antigüedad</span></div>
      </div>
      <div class="check-item">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m20 6-11 11-5-5"/></svg>
        <div><strong>Itinerario tentativo</strong><span>Vuelos y reservas hotel</span></div>
      </div>
      <div class="check-item">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m20 6-11 11-5-5"/></svg>
        <div><strong>Antecedentes</strong><span>Cuando el país lo solicite</span></div>
      </div>
    </div>
  </div>
</section>

<section class="section">
  <div class="container container--narrow">
    <div class="section-head">
      <span class="eyebrow">Nuestro proceso</span>
      <h2>5 pasos hasta tu visa aprobada</h2>
    </div>
    <ol class="timeline">
      <li class="timeline__item">
        <span class="timeline__dot">1</span><span class="timeline__time">Día 1</span>
        <h4 class="timeline__title">Diagnóstico personalizado</h4>
        <p class="timeline__text">Evaluamos tu perfil y probabilidad de aprobación sin costo.</p>
      </li>
      <li class="timeline__item">
        <span class="timeline__dot">2</span><span class="timeline__time">Día 2–7</span>
        <h4 class="timeline__title">Recolección de documentos</h4>
        <p class="timeline__text">Te guiamos uno por uno con checklist personalizado.</p>
      </li>
      <li class="timeline__item">
        <span class="timeline__dot">3</span><span class="timeline__time">Día 8–10</span>
        <h4 class="timeline__title">Llenado del formulario oficial</h4>
        <p class="timeline__text">Cada respuesta es revisada por un experto antes de enviar.</p>
      </li>
      <li class="timeline__item">
        <span class="timeline__dot">4</span><span class="timeline__time">Día 11–14</span>
        <h4 class="timeline__title">Coaching de entrevista</h4>
        <p class="timeline__text">Simulacro completo con preguntas reales del consulado.</p>
      </li>
      <li class="timeline__item">
        <span class="timeline__dot">5</span><span class="timeline__time">Resultado</span>
        <h4 class="timeline__title">Acompañamiento hasta tu visa</h4>
        <p class="timeline__text">Recepción del pasaporte y soporte post-trámite.</p>
      </li>
    </ol>
  </div>
</section>

<section class="section section--tight">
  <div class="container">
    <div class="cta-band">
      <div>
        <h2>¿Listo para iniciar tu trámite?</h2>
        <p>Diagnóstico inicial 100% gratis · respuesta en menos de 24 h.</p>
      </div>
      <div class="row">
        <a href="<?= BASE_URL ?>/contacto" class="btn btn--accent btn--lg">Solicitar diagnóstico</a>
        <?php if ($waNumber = \App\Helper\Config::getString('agencia_whatsapp')): ?>
        <a href="https://wa.me/<?= htmlspecialchars($waNumber) ?>" class="btn btn--white btn--lg" target="_blank" rel="noopener">WhatsApp directo</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<!-- Modal Solicitar Asesoría -->
<div id="solicitarVisaModal" class="trip-modal" hidden>
  <div class="trip-modal__backdrop" data-close></div>
  <div class="trip-modal__panel" role="dialog" aria-modal="true" style="max-width: 480px;">
    <button class="trip-modal__x" data-close aria-label="Cerrar">×</button>
    <div class="trip-modal__cnt">
      <h2>Solicitar Asesoría de Visa</h2>
      <p style="color:var(--c-ink-500);margin:0 0 16px" id="visaModalTitle"></p>
      
      <form action="<?= BASE_URL ?>/visas/solicitar" method="POST" id="visaModalForm">
        <?= App\Helper\Csrf::insertInput() ?>
        <input type="hidden" name="id_servicio" id="visaModalServicioId">
        
        <div class="field">
          <label>Costo de Asesoría</label>
          <input class="input" type="text" id="visaModalPrecio" readonly>
        </div>
        
        <div class="field" style="margin-top:12px">
          <label>Fecha aproximada de viaje (Opcional)</label>
          <input class="input" type="date" name="fecha_viaje_aprox" min="<?= date('Y-m-d') ?>">
        </div>
        
        <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:18px">
          <button type="button" class="btn btn--ghost" data-close>Cancelar</button>
          <button type="submit" class="btn btn--primary">Confirmar Solicitud</button>
        </div>
      </form>
    </div>
  </div>
</div>
</main>

<script nonce="<?= htmlspecialchars(\App\Helper\SecurityHeaders::getNonce(), ENT_QUOTES, 'UTF-8') ?>">
document.addEventListener('DOMContentLoaded', () => {
  const modal = document.getElementById('solicitarVisaModal');
  const modalTitle = document.getElementById('visaModalTitle');
  const modalServicioId = document.getElementById('visaModalServicioId');
  const modalPrecio = document.getElementById('visaModalPrecio');

  document.querySelectorAll('.js-solicitar-visa').forEach(btn => {
    btn.addEventListener('click', () => {
      const data = btn.dataset;
      modalTitle.textContent = 'Visa: ' + data.tipo + ' (' + data.pais + ')';
      modalServicioId.value = data.id;
      modalPrecio.value = 'S/ ' + parseFloat(data.precio).toFixed(0);
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
