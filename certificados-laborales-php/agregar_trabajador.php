<?php
// Iniciar sesión para proteger la página
session_start();

// Proteger la página: si el usuario no ha iniciado sesión, redirigir al login
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login_admin.php');
    exit;
}

// =========================================
// 1. Conexión a la base de datos
// =========================================
$host = "localhost";
$db = "inserima_certificados";
$user = "inserima_certificado";
$pass = "QjpreSVN*Cp5S3R";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET NAMES utf8mb4");
} catch (Exception $e) {
    die("Error de conexión a la base de datos: " . $e->getMessage());
}

// Variables para mensajes de estado y control
$mensaje_estado = '';
$es_error = false;
$mostrar_boton_limpiar = false; 

// =========================================
// 2. Procesar el formulario
// =========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recopilar datos del formulario
    $cedula = $_POST['cedula'] ?? '';
    $tipo_documento = $_POST['tipo_documento'] ?? '';
    $nombre = $_POST['nombre_completo'] ?? '';
    $tipo = 'activo';
    $contrato = $_POST['contrato'] ?? '';
    $salario = $_POST['salario'] ?? 0;
    $fecha_ingreso = $_POST['fecha_ingreso'] ?? '';
    $cargo = $_POST['cargo'] ?? '';
    $ciudad_expedicion = $_POST['ciudad_expedicion'] ?? '';
    $fecha_expedicion = $_POST['fecha_expedicion'] ?? '';

    // Validación de Cédula Activa
    $sql_check = "SELECT COUNT(*) FROM Contratos WHERE cedula = :cedula AND tipo = 'activo'";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->bindParam(':cedula', $cedula);
    $stmt_check->execute();
    $count_activos = $stmt_check->fetchColumn();

    if ($count_activos > 0) {
        $es_error = true;
        $mensaje_estado = "⚠️ La cédula **{$cedula}** ya tiene un contrato activo registrado. No se puede agregar como nuevo trabajador.";
    } else {
        // Inserción
        $sql = "INSERT INTO Contratos (
                cedula, tipo_documento, nombre, tipo, contrato, salario, 
                fecha_ingreso, cargo, ciudad_expedicion, contrasena 
            ) VALUES (
                :cedula, :tipo_documento, :nombre, :tipo, :contrato, :salario, 
                :fecha_ingreso, :cargo, :ciudad_expedicion, :fecha_expedicion
            )";

        try {
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':cedula', $cedula);
            $stmt->bindParam(':tipo_documento', $tipo_documento);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':tipo', $tipo);
            $stmt->bindParam(':contrato', $contrato);
            $stmt->bindParam(':salario', $salario);
            $stmt->bindParam(':fecha_ingreso', $fecha_ingreso);
            $stmt->bindParam(':cargo', $cargo);
            $stmt->bindParam(':ciudad_expedicion', $ciudad_expedicion);
            $stmt->bindParam(':fecha_expedicion', $fecha_expedicion);

            $stmt->execute();
            
            // REDIRECCIÓN PARA LIMPIAR EL POST y pasar el mensaje de éxito por GET
            $_SESSION['mensaje_estado'] = "✅ Trabajador agregado correctamente a la base de datos.";
            $_SESSION['es_error'] = false;
            
            // Redirigimos al mismo archivo con un indicador de éxito
            header('Location: agregar_trabajador.php?success=1');
            exit;

        } catch (PDOException $e) {
            $es_error = true;
            $mensaje_estado = "⚠️ Error al agregar trabajador: " . $e->getMessage();
        }
    }
}

// ===============================================
// MANEJO DE MENSAJE DESPUÉS DE LA REDIRECCIÓN
// ===============================================
if (isset($_SESSION['mensaje_estado'])) {
    $mensaje_estado = $_SESSION['mensaje_estado'];
    $es_error = $_SESSION['es_error'];
    unset($_SESSION['mensaje_estado']); 
    unset($_SESSION['es_error']);
    
    // Si la URL tiene '?success=1' y no hay error, mostramos el botón.
    if (isset($_GET['success']) && $_GET['success'] == 1 && !$es_error) {
        $mostrar_boton_limpiar = true;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Nuevo Trabajador</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --color-primary: #1e8449;
            --color-secondary: #f0f4f7;
            --color-text-dark: #333;
            --color-text-light: #fff;
            --color-border: #dcdcdc;
        }
        
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #2E6AA7;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            min-height: 70vh;
            margin: 0;
            padding: 20px;
        }
        
        .container {
            width: 90%;
            max-width: 600px;
            background-color: var(--color-text-light);
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }

        h1 {
            text-align: center;
            color: #8caf37;
            margin-bottom: 25px;
            font-size: 2em;
            font-weight: 700;
        }
        
        .form-row {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .form-group {
            flex: 1;
            min-width: 200px;
            margin-bottom: 15px;
        }

        .form-group.flex-2 {
            flex: 2;
            min-width: 220px;
        }

        .form-group.flex-3 {
            flex: 3;
            min-width: 280px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: var(--color-text-dark);
        }

        input, select {
            width: 100%;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid var(--color-border);
            box-sizing: border-box;
            font-size: 1em;
            transition: border-color 0.3s;
        }

        input:focus, select:focus {
            border-color: var(--color-primary);
            outline: none;
        }

        button {
            width: 100%;
            padding: 15px;
            margin-top: 20px;
            background: #8caf37;
            color: var(--color-text-light);
            border: none;
            border-radius: 6px;
            font-size: 1.1em;
            font-weight: 700;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.2s;
        }
        
        button:hover {
            background: #8caf37;
            transform: translateY(-2px);
        }
        
        /* Estilos para el botón de validación COMPACTO (Validar) */
        .validar-btn-compact {
            padding: 8px 15px;
            margin-top: 0; 
            background: #2E6AA7;
            color: var(--color-text-light);
            border: none;
            border-radius: 6px;
            font-size: 0.9em;
            font-weight: 700;
            cursor: pointer;
            transition: background-color 0.3s;
            white-space: nowrap;
            width: auto;
        }
        
        .validar-btn-compact:hover {
            background: #255a8e;
            transform: none;
        }

        /* Estilo para el ENLACE de LIMPIAR/AGREGAR OTRO */
        .limpiar-btn-link {
            /* Ahora es un enlace (<a>) pero parece botón */
            display: block; 
            width: 100%;
            padding: 10px 15px; 
            margin: 10px 0 20px 0; 
            background: none; 
            color: #2E6AA7; 
            border: 2px solid #2E6AA7; 
            border-radius: 6px;
            font-size: 1em;
            font-weight: 700;
            cursor: pointer;
            transition: background-color 0.3s, color 0.3s, transform 0.2s;
            text-align: center;
            text-decoration: none; /* Quitamos el subrayado */
        }
        .limpiar-btn-link:hover {
            background: #2E6AA7; 
            color: var(--color-text-light);
            transform: translateY(-2px);
        }


        .mensaje-exito {
            color: green;
            font-weight: bold;
            text-align: center;
            margin-top: 20px;
        }

        .mensaje-error {
            color: red;
            font-weight: bold;
            text-align: center;
            margin-top: 20px;
        }
        
        /* Estilos para el mensaje de validación AJAX */
        #validacion-estado {
            padding: 5px 0;
            text-align: center;
        }
        .validacion-mensaje-exito {
            color: green;
            font-weight: bold;
        }
        .validacion-mensaje-error {
            color: red;
            font-weight: bold;
        }
        
        .back-link { 
            display: block; 
            text-align: center; 
            margin-top: 25px; 
            font-size: 0.9em; 
            color: #8caf37; 
            text-decoration: none; 
            font-weight: bold; 
        }

        .back-link:hover {
            text-decoration: underline; 
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>Agregar Nuevo Trabajador</h1>

        <?php if (!empty($mensaje_estado)): ?>
            <p class="<?= $es_error ? 'mensaje-error' : 'mensaje-exito' ?>"><?= $mensaje_estado ?></p>
        <?php endif; ?>

        <?php if ($mostrar_boton_limpiar): ?>
            <a href="agregar_trabajador.php" class="limpiar-btn-link">
                Quiero Agregar Otro Trabajador
            </a>
        <?php endif; ?>

        <form action="agregar_trabajador.php" method="post" id="form-trabajador"> 
            
            <div class="form-row">
                <div class="form-group flex-2">
                    <label for="cedula">Cédula:</label>
                    <div style="display: flex; gap: 10px; align-items: flex-end;">
                        <input type="text" id="cedula" name="cedula" required style="flex-grow: 1;">
                        <button type="button" id="btn-validar-cedula" class="validar-btn-compact">Validar</button>
                    </div>
                </div>
                <div class="form-group flex-3">
                    <label for="nombre_completo">Nombre Completo:</label>
                    <input type="text" id="nombre_completo" name="nombre_completo" required>
                </div>
            </div>
            
            <div id="validacion-estado" style="text-align: center; margin-bottom: 5px;">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="tipo_documento">Tipo de Documento:</label>
                    <select id="tipo_documento" name="tipo_documento" required>
                        <option value="" selected disabled>Seleccione...</option>
                        <option value="CC">Cédula de Ciudadanía (CC)</option>
                        <option value="TI">Tarjeta de Identidad (TI)</option>
                        <option value="PPT">Permiso por Protección Temporal (PPT)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="ciudad_expedicion">Ciudad de Expedición:</label>
                    <input type="text" id="ciudad_expedicion" name="ciudad_expedicion" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="contrato">Contrato:</label>
                    <select id="contrato" name="contrato" required>
                        <option value="" selected disabled>Seleccione...</option>
                        <option value="indefinido">Indefinido</option>
                        <option value="obra o labor">Obra o Labor</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="salario">Salario:</label>
                    <input type="number" placeholder="sin puntos, sin comas" id="salario" name="salario" step="any" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="fecha_ingreso">Fecha de Ingreso:</label>
                    <input type="date" id="fecha_ingreso" name="fecha_ingreso" required>
                </div>
                <div class="form-group">
                    <label for="cargo">Cargo:</label>
                    <input type="text" id="cargo" name="cargo" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="fecha_expedicion">Fecha de Expedición (Documento):</label>
                    <input type="date" id="fecha_expedicion" name="fecha_expedicion" required>
                </div>
            </div>
            
            <button type="submit">Agregar Trabajador</button>
        </form>
        
        <a href="https://inserimaire.com/Certificados/formulario.php" class="back-link">
            Volver al Menú Principal
        </a>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btnValidar = document.getElementById('btn-validar-cedula');
            const cedulaInput = document.getElementById('cedula');
            const validacionDiv = document.getElementById('validacion-estado');
            const submitButton = document.querySelector('button[type="submit"]');

            // 1. Deshabilitar el botón de enviar al cargar la página
            submitButton.disabled = true;

            btnValidar.addEventListener('click', function() {
                const cedula = cedulaInput.value.trim();
                if (cedula.length < 5) {
                    validacionDiv.innerHTML = '<span class="validacion-mensaje-error">Ingrese una cédula válida.</span>';
                    submitButton.disabled = true;
                    return;
                }
                
                // Mostrar cargando
                validacionDiv.innerHTML = '<span>Cargando...</span>';

                // Llamada AJAX a validar_cedula.php
                fetch('validar_cedula.php?cedula=' + encodeURIComponent(cedula))
                    .then(response => response.json())
                    .then(data => {
                        validacionDiv.className = '';
                        
                        if (data.success) {
                            // Cédula VÁLIDA
                            validacionDiv.innerHTML = `<span class="validacion-mensaje-exito">${data.mensaje}</span>`;
                            submitButton.disabled = false; // Habilitar envío
                        } else {
                            // Cédula NO VÁLIDA (contrato activo encontrado)
                            validacionDiv.innerHTML = `<span class="validacion-mensaje-error">${data.mensaje}</span>`;
                            submitButton.disabled = true; // Mantener deshabilitado
                        }
                    })
                    .catch(error => {
                        validacionDiv.innerHTML = '<span class="validacion-mensaje-error">Error al comunicarse con el servidor.</span>';
                        submitButton.disabled = true;
                        console.error('Fetch error:', error);
                    });
            });

            // 2. Deshabilitar el botón de enviar si la cédula es modificada después de la validación
            cedulaInput.addEventListener('input', function() {
                submitButton.disabled = true;
                validacionDiv.innerHTML = ''; // Limpiar el mensaje de validación
            });
        });
    </script>
</body>
</html>