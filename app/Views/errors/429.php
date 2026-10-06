<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>429 — Demasiados intentos — Viajes AJT</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: #f8fafc;
            color: #1e293b;
            padding: 1.5rem;
        }
        .error-card {
            text-align: center;
            background: #ffffff;
            padding: 3rem 2.5rem;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
            max-width: 480px;
            width: 100%;
        }
        .error-code {
            font-size: 5rem;
            font-weight: 800;
            color: #dc2626;
            line-height: 1;
            margin-bottom: 0.5rem;
        }
        .error-code span {
            display: inline-block;
            animation: pulse 2s ease-in-out infinite;
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        h1 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
            color: #1e293b;
        }
        p {
            color: #64748b;
            margin-bottom: 2rem;
            line-height: 1.6;
            font-size: 1rem;
        }
        .actions {
            display: flex;
            gap: 0.75rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.95rem;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .btn-primary {
            background: #1b2c8a;
            color: #ffffff;
        }
        .btn-primary:hover {
            background: #15236e;
            transform: translateY(-1px);
        }
        .btn-secondary {
            background: #f1f5f9;
            color: #1e293b;
        }
        .btn-secondary:hover {
            background: #e2e8f0;
        }
        .icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="error-card" role="alert">
        <div class="icon">⏳</div>
        <div class="error-code"><span>429</span></div>
        <h1>Demasiados intentos de acceso</h1>
        <p>Has superado el límite de intentos permitidos en un período corto de tiempo.<br>
        Por motivos de seguridad, tu acceso ha sido suspendido temporalmente.<br>
        Por favor, intenta nuevamente en 15 minutos.</p>
        <div class="actions">
            <a href="<?= defined('BASE_URL') ? htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') : '' ?>/" class="btn btn-primary">
                ← Volver al inicio
            </a>
            <a href="<?= defined('BASE_URL') ? htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') : '' ?>/contacto" class="btn btn-secondary">
                Contactar soporte
            </a>
        </div>
    </div>
</body>
</html>
