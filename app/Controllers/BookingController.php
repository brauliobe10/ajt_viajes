<?php

declare(strict_types=1);

// Funcion del archivo: Gestiona el checkout, la creacion de reservas y el registro de pagos.
namespace App\Controllers;

use App\Config\Database;
use App\Models\Paquete;
use App\Models\Reserva;
use Exception;

/**
 * Clase BookingController
 * Gestiona el proceso transaccional de reservas y checkout.
 */
class BookingController
{
    /**
     * Calcula el desglose financiero: subtotal, descuento, IGV y total.
     *
     * @param float $precioUnitario Precio unitario del paquete
     * @param int $pax Cantidad de pasajeros
     * @return array ['subtotal', 'descuento', 'igv', 'total']
     */
    private function calcularDesglose(float $precioUnitario, int $pax): array
    {
        $subtotal = $precioUnitario * $pax;
        $descuento = 0.00;
        if ($pax >= 2) {
            $descuento = round($subtotal * 0.10, 2);
        }
        $baseImponible = $subtotal - $descuento;
        $igv = round($baseImponible * 0.18, 2);
        $total = round($baseImponible + $igv, 2);

        return [
            'subtotal' => $subtotal,
            'descuento' => $descuento,
            'igv' => $igv,
            'total' => $total
        ];
    }

    /**
     * Carga la configuración de pago desde la tabla configuracion.
     *
     * @return array Asociación clave => valor
     */
    private function cargarConfigPago(): array
    {
        $keys = [
            'terminos_condiciones', 'politica_cancelacion',
            'yape_qr_url', 'yape_numero', 'yape_titular',
            'plin_qr_url', 'plin_numero', 'plin_titular',
            'banco_nombre', 'banco_cuenta', 'banco_cci',
            'contacto_telefono', 'contacto_email', 'contacto_whatsapp',
            'moneda_simbolo'
        ];

        $config = [];
        try {
            $db = Database::getConnection();
            $placeholders = implode(',', array_fill(0, count($keys), '?'));
            $stmt = $db->prepare("SELECT clave, valor FROM configuracion WHERE clave IN ($placeholders)");
            $stmt->execute($keys);
            while ($row = $stmt->fetch()) {
                $config[$row['clave']] = $row['valor'];
            }
        } catch (\PDOException $e) {
            error_log("Error cargando configuración de pago: " . $e->getMessage());
        }

        // Defaults
        $defaults = [
            'terminos_condiciones' => 'Términos y condiciones pendientes de configurar.',
            'politica_cancelacion' => 'Política de cancelación pendiente de configurar.',
            'yape_qr_url' => '',
            'yape_numero' => '',
            'yape_titular' => '',
            'plin_qr_url' => '',
            'plin_numero' => '',
            'plin_titular' => '',
            'banco_nombre' => '',
            'banco_cuenta' => '',
            'banco_cci' => '',
            'contacto_telefono' => '',
            'contacto_email' => '',
            'contacto_whatsapp' => '',
            'moneda_simbolo' => 'S/'
        ];

        foreach ($defaults as $key => $default) {
            if (!isset($config[$key]) || $config[$key] === '') {
                $config[$key] = $default;
            }
        }

        return $config;
    }

    /**
     * Muestra el formulario de checkout.
     *
     * @return void
     */
    public function checkout(): void
    {
        // 1. Validar autenticación
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $slug = isset($_GET['paquete']) ? trim($_GET['paquete']) : '';
        $fecha_viaje = isset($_GET['fecha_viaje']) ? trim($_GET['fecha_viaje']) : '';
        $viajeros = isset($_GET['viajeros']) ? (int)$_GET['viajeros'] : 1;

        if (!isset($_SESSION['usuario'])) {
            // Guardar reserva pendiente en sesión para redirigir tras el login
            $_SESSION['pending_booking'] = [
                'paquete' => $slug,
                'fecha_viaje' => $fecha_viaje,
                'viajeros' => $viajeros
            ];
            $_SESSION['error'] = "Debe iniciar sesión para realizar una reserva.";
            header('Location: ' . BASE_URL . '/login');
            exit();
        }

        if (empty($slug)) {
            $_SESSION['error'] = "Por favor, seleccione un paquete para reservar.";
            header('Location: ' . BASE_URL . '/catalog');
            exit();
        }

        $usuarioLogueado = null;
        try {
            $paquete = Paquete::findBySlug($slug);
            if (!$paquete) {
                $_SESSION['error'] = "El paquete solicitado no existe.";
                header('Location: ' . BASE_URL . '/catalog');
                exit();
            }
            $usuarioLogueado = \App\Models\Usuario::findById($_SESSION['usuario']['id_usuario']);
        } catch (Exception $e) {
            error_log("Error en BookingController::checkout: " . $e->getMessage());
            $_SESSION['error'] = "Error al recuperar datos de la reserva.";
            header('Location: ' . BASE_URL . '/catalog');
            exit();
        }

        // Cargar configuración de pago
        $configPago = $this->cargarConfigPago();

        // Calcular desglose financiero
        $viajerosCount = (int)$viajeros;
        $precioUnitario = (float)$paquete['precio_base'];
        $desglose = $this->calcularDesglose($precioUnitario, $viajerosCount);

        $title = "Reserva — Viajes AJT";
        $description = "Completa tu reserva de forma segura con Viajes AJT.";
        $pageKey = "catalog";
        
        $baseViewsDir = dirname(__DIR__) . '/Views/';
        require_once $baseViewsDir . 'layouts/header.php';
        require_once $baseViewsDir . 'checkout.php';
        require_once $baseViewsDir . 'layouts/footer.php';
    }

    /**
     * Procesa la creación de la reserva y simula la transacción de pago (POST).
     *
     * @return void
     */
    public function procesarCheckout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['usuario'])) {
            header('Location: ' . BASE_URL . '/login');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/catalog');
            exit();
        }

        $slug = isset($_POST['paquete_slug']) ? trim($_POST['paquete_slug']) : '';
        $fecha_viaje = isset($_POST['fecha_viaje']) ? trim($_POST['fecha_viaje']) : '';
        $viajeros_count = isset($_POST['viajeros_count']) ? (int)$_POST['viajeros_count'] : 1;
        $metodo_pago = isset($_POST['pay']) ? trim($_POST['pay']) : 'card';
        $card_last4 = isset($_POST['card_last4']) ? trim($_POST['card_last4']) : '';

        // --- Validación server-side antes de la transacción ---
        $errores = [];

        // Validar método de pago contra whitelist
        $metodosPermitidos = ['card', 'yape', 'trans'];
        if (!in_array($metodo_pago, $metodosPermitidos, true)) {
            $metodo_pago = 'card'; // fallback seguro
        }

        // Validar paquete_id (implícito via slug)
        if (empty($slug)) {
            $errores[] = "Paquete no especificado.";
        }

        // Validar fecha_viaje: formato YYYY-MM-DD y fecha futura
        if (empty($fecha_viaje) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_viaje)) {
            $errores[] = "La fecha de viaje no tiene un formato válido (YYYY-MM-DD).";
        } else {
            $fechaViajeObj = \DateTime::createFromFormat('Y-m-d', $fecha_viaje);
            if (!$fechaViajeObj || $fechaViajeObj->format('Y-m-d') !== $fecha_viaje) {
                $errores[] = "La fecha de viaje no es una fecha válida.";
            } elseif ($fechaViajeObj <= new \DateTime('today')) {
                $errores[] = "La fecha de viaje debe ser una fecha futura.";
            }
        }

        // Validar cantidad de viajeros: integer entre 1 y 10
        if (!is_int($viajeros_count) && !ctype_digit((string)$_POST['viajeros_count'])) {
            $errores[] = "La cantidad de viajeros debe ser un número entero.";
        } elseif ($viajeros_count < 1 || $viajeros_count > 10) {
            $errores[] = "La cantidad de viajeros debe estar entre 1 y 10.";
        }

        // Validar campos de viajeros
        $nombres_v = isset($_POST['nombres_viajero']) ? $_POST['nombres_viajero'] : [];
        $apellidos_v = isset($_POST['apellidos_viajero']) ? $_POST['apellidos_viajero'] : [];
        $documento_v = isset($_POST['documento_viajero']) ? $_POST['documento_viajero'] : [];

        for ($i = 0; $i < $viajeros_count; $i++) {
            $nom = isset($nombres_v[$i]) ? trim($nombres_v[$i]) : '';
            $ape = isset($apellidos_v[$i]) ? trim($apellidos_v[$i]) : '';
            $doc = isset($documento_v[$i]) ? trim($documento_v[$i]) : '';

            if (empty($nom)) {
                $errores[] = "El nombre del pasajero " . ($i + 1) . " es obligatorio.";
            } elseif (!preg_match('/^[\p{L}\s]+$/u', $nom)) {
                $errores[] = "El nombre del pasajero " . ($i + 1) . " solo puede contener letras y espacios.";
            } elseif (mb_strlen($nom) > 100) {
                $errores[] = "El nombre del pasajero " . ($i + 1) . " no puede exceder 100 caracteres.";
            }
            if (empty($ape)) {
                $errores[] = "Los apellidos del pasajero " . ($i + 1) . " son obligatorios.";
            } elseif (!preg_match('/^[\p{L}\s]+$/u', $ape)) {
                $errores[] = "Los apellidos del pasajero " . ($i + 1) . " solo pueden contener letras y espacios.";
            } elseif (mb_strlen($ape) > 100) {
                $errores[] = "Los apellidos del pasajero " . ($i + 1) . " no pueden exceder 100 caracteres.";
            }
            if (empty($doc)) {
                $errores[] = "El DNI/Pasaporte del pasajero " . ($i + 1) . " es obligatorio.";
            } elseif (strlen($doc) < 6 || strlen($doc) > 20) {
                $errores[] = "El DNI/Pasaporte del pasajero " . ($i + 1) . " debe tener entre 6 y 20 caracteres.";
            } elseif (!preg_match('/^[A-Za-z0-9]+$/', $doc)) {
                $errores[] = "El DNI/Pasaporte del pasajero " . ($i + 1) . " solo puede contener letras y números.";
            }
        }

        // Validar teléfono del pasajero principal
        $telefono_pasajero = isset($_POST['telefono_pasajero']) ? trim($_POST['telefono_pasajero']) : '';
        $telefono_pasajero = preg_replace('/\s+/', '', $telefono_pasajero); // Strip spaces
        if (empty($telefono_pasajero)) {
            $errores[] = "El teléfono del pasajero principal es obligatorio.";
        } elseif (!preg_match('/^[0-9]{7,15}$/', $telefono_pasajero)) {
            $errores[] = "El teléfono debe contener entre 7 y 15 dígitos numéricos.";
        }

        // Validar aceptación de términos
        $acepta_terminos = isset($_POST['acepta_terminos']) ? $_POST['acepta_terminos'] : '';
        if ($acepta_terminos !== 'on') {
            $errores[] = "Debe aceptar los términos y condiciones para continuar.";
        }

        // Generar número de reserva único de forma anticipada para usarlo en el nombre del comprobante
        $reservaNum = str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        $codigoReserva = 'AJT-' . $reservaNum;

        // Manejo de archivo de comprobante para pagos de validación manual
        $comprobanteUrl = null;
        if (in_array($metodo_pago, ['trans', 'yape'], true)) {
            if (!isset($_FILES['comprobante']) || $_FILES['comprobante']['error'] === UPLOAD_ERR_NO_FILE) {
                $errores[] = "Debe adjuntar el comprobante de pago.";
            } else {
                $file = $_FILES['comprobante'];

                // Verificar errores de upload
                if ($file['error'] !== UPLOAD_ERR_OK) {
                    $errores[] = "Error al subir el archivo. Intente nuevamente.";
                } else {
                    // Validar tamaño (5MB max)
                    $maxSize = 5 * 1024 * 1024;
                    if ($file['size'] > $maxSize) {
                        $errores[] = "El comprobante no puede superar 5MB.";
                    }

                    // Validar extensión
                    $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
                    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                    if (!in_array($ext, $allowedExtensions)) {
                        $errores[] = "Solo se permiten archivos JPG, JPEG, PNG o PDF.";
                    }

                    // Validar MIME type
                    $finfo = new \finfo(FILEINFO_MIME_TYPE);
                    $mimeType = $finfo->file($file['tmp_name']);
                    $allowedMimes = ['image/jpeg', 'image/png', 'application/pdf'];
                    if (!in_array($mimeType, $allowedMimes)) {
                        $errores[] = "Tipo de archivo no permitido. Solo JPG, JPEG, PNG o PDF.";
                    }

                    // Si no hay errores, procesar archivo
                    if (empty($errores)) {
                        $uploadDir = dirname(dirname(__DIR__)) . '/public/uploads/comprobantes/';
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0755, true);
                        }

                        if (!is_writable($uploadDir)) {
                            $errores[] = "Error del servidor: no se pudo guardar el archivo.";
                        } else {
                            $newFilename = 'C-' . $reservaNum . '.' . $ext;
                            $destPath = $uploadDir . $newFilename;

                            if (move_uploaded_file($file['tmp_name'], $destPath)) {
                                $comprobanteUrl = 'uploads/comprobantes/' . $newFilename;
                            } else {
                                $errores[] = "Error al guardar el comprobante. Intente nuevamente.";
                            }
                        }
                    }
                }
            }
        }

        if (!empty($errores)) {
            $_SESSION['error'] = implode('<br>', $errores);
            header('Location: ' . BASE_URL . '/checkout?paquete=' . urlencode($slug) . '&fecha_viaje=' . urlencode($fecha_viaje) . '&viajeros=' . $viajeros_count);
            exit();
        }
        // --- Fin de validación ---

        try {
            $paquete = Paquete::findBySlug($slug);
            if (!$paquete) {
                $_SESSION['error'] = "El paquete solicitado no existe.";
                header('Location: ' . BASE_URL . '/catalog');
                exit();
            }

            // Construir datos de viajeros
            $viajerosData = [];
            for ($i = 0; $i < $viajeros_count; $i++) {
                $viajerosData[] = [
                    'nombres' => trim($nombres_v[$i]),
                    'apellidos' => trim($apellidos_v[$i]),
                    'num_documento' => trim($documento_v[$i])
                ];
            }

            // Calcular desglose financiero (con IGV)
            $precioUnitario = (float)$paquete['precio_base'];
            $desglose = $this->calcularDesglose($precioUnitario, $viajeros_count);

            // Mapear método de pago
            // id_metodo: 1 = Tarjeta, 2 = Transferencia, 3 = Yape/Plin
            $idMetodo = 1;
            if ($metodo_pago === 'trans') $idMetodo = 2;
            if ($metodo_pago === 'yape') $idMetodo = 3;

            // Estado de pago y reserva según método
            if ($metodo_pago === 'card') {
                // Tarjeta: pago confirmado, reserva confirmada
                $estadoPago = 'validado';
                $estadoReserva = 'pagado';
            } else {
                // Yape/Plin/Transferencia: pendiente de validación manual
                $estadoPago = 'pendiente';
                $estadoReserva = 'pendiente';
            }

            // Generar número de operación según método
            if ($metodo_pago === 'card') {
                // Tarjeta: guardar método y últimos 4 dígitos (no almacenar número completo)
                $last4 = preg_replace('/[^0-9]/', '', $card_last4);
                $last4 = substr($last4, -4);
                $numOperacion = 'TARJETA' . ($last4 ? '-' . $last4 : '');
            } else {
                $numOperacion = 'TXN-' . str_pad((string)random_int(10000000, 99999999), 8, '0', STR_PAD_LEFT);
            }

            // Preparar datos para Reserva::create
            $reservaData = [
                'id_usuario' => $_SESSION['usuario']['id_usuario'],
                'descuento' => $desglose['descuento'],
                'estado' => $estadoReserva,
                'telefono_pasajero' => $telefono_pasajero,
                'codigo_reserva' => $codigoReserva
            ];

            $detalleData = [
                'id_paquete' => $paquete['id_paquete'],
                'fecha_viaje' => $fecha_viaje,
                'cantidad_pasajeros' => $viajeros_count,
                'precio_unitario' => $precioUnitario
            ];

            $pagoData = [
                'id_metodo_pago' => $idMetodo,
                'numero_operacion' => $numOperacion,
                'monto' => $desglose['total'],
                'estado' => $estadoPago,
                'comprobante_url' => $comprobanteUrl
            ];

            $codigoReserva = Reserva::create($reservaData, $detalleData, $viajerosData, $pagoData);

            if ($codigoReserva) {
                // Éxito. Cargar configuración y renderizar la confirmación (Step 3)
                $configPago = $this->cargarConfigPago();

                $title = "Confirmación de Reserva — Viajes AJT";
                $pageKey = "catalog";
                $baseViewsDir = dirname(__DIR__) . '/Views/';
                
                $step = 3;
                
                require_once $baseViewsDir . 'layouts/header.php';
                require_once $baseViewsDir . 'checkout.php';
                require_once $baseViewsDir . 'layouts/footer.php';
            } else {
                $_SESSION['error'] = "No se pudo registrar la reserva. Por favor, intente nuevamente.";
                header('Location: ' . BASE_URL . '/checkout?paquete=' . $slug . '&fecha_viaje=' . $fecha_viaje . '&viajeros=' . $viajeros_count);
                exit();
            }

        } catch (Exception $e) {
            error_log("Error en BookingController::procesarCheckout: " . $e->getMessage());
            $_SESSION['error'] = "Ocurrió un error al procesar tu reserva.";
            header('Location: ' . BASE_URL . '/checkout?paquete=' . $slug . '&fecha_viaje=' . $fecha_viaje . '&viajeros=' . $viajeros_count);
            exit();
        }
    }
}
