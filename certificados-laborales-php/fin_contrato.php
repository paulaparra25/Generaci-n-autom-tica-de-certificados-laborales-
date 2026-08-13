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

// Variable para mensajes de estado
$mensaje_estado = '';
$es_error = false;

// =========================================
// 2. Procesar el formulario
// =========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cedula = $_POST['cedula'] ?? '';
    $fecha_retiro = $_POST['fecha_retiro'] ?? '';

    // Validar que la cédula y la fecha de retiro no estén vacías
    if (empty($cedula) || empty($fecha_retiro)) {
        $es_error = true;
        $mensaje_estado = "⚠️ La cédula y la fecha de retiro son obligatorias.";
    } else {
        try {
            // Iniciar una transacción para asegurar que la operación sea segura
            $pdo->beginTransaction();

            // 1. Verificar si el trabajador existe y si su estado es 'activo'
            $sql_select = "SELECT tipo FROM Contratos WHERE cedula = :cedula";
            $stmt_select = $pdo->prepare($sql_select);
            $stmt_select->bindParam(':cedula', $cedula);
            $stmt_select->execute();
            $trabajador = $stmt_select->fetch(PDO::FETCH_ASSOC);

            if (!$trabajador) {
                // El trabajador no se encontró en la base de datos
                $es_error = true;
                $mensaje_estado = "⚠️ No se encontró ningún trabajador con esa cédula.";
                $pdo->rollBack();
            } elseif ($trabajador['tipo'] === 'retirado') {
                // El trabajador ya está marcado como retirado
                $es_error = true;
                $mensaje_estado = "⚠️ El trabajador ya está registrado como retirado. No se realizaron cambios.";
                $pdo->rollBack();
            } else {
                // 2. Actualizar la fecha de retiro y el estado a 'retirado'
                $sql_update = "UPDATE Contratos SET fecha_retiro = :fecha_retiro, tipo = 'retirado' WHERE cedula = :cedula AND fecha_retiro IS NULL";
                $stmt_update = $pdo->prepare($sql_update);
                $stmt_update->bindParam(':fecha_retiro', $fecha_retiro);
                $stmt_update->bindParam(':cedula', $cedula);
                $stmt_update->execute();

                if ($stmt_update->rowCount() > 0) {
                    $pdo->commit();
                    $mensaje_estado = "✅ La fecha de retiro se ha actualizado correctamente para el trabajador con cédula " . htmlspecialchars($cedula) . ".";
                } else {
                    $es_error = true;
                    $mensaje_estado = "⚠️ No se pudo actualizar la información. El trabajador podría tener ya una fecha de retiro.";
                    $pdo->rollBack();
                }
            }
        } catch (PDOException $e) {
            $es_error = true;
            $mensaje_estado = "⚠️ Error de la base de datos: " . $e->getMessage();
            $pdo->rollBack();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Finalizar Contrato</title>
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
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        
        .container {
            width: 90%;
            max-width: 500px;
            background-color: var(--color-text-light);
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
            text-align: center;
        }

        h1 {
            color: #8caf37;
            margin-bottom: 25px;
            font-size: 2em;
            font-weight: 700;
        }
        
        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: var(--color-text-dark);
        }

        input {
            width: 100%;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid var(--color-border);
            box-sizing: border-box;
            font-size: 1em;
            transition: border-color 0.3s;
        }

        input:focus {
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

        .mensaje-exito {
            color: green;
            font-weight: bold;
            margin-top: 20px;
        }

        .mensaje-error {
            color: red;
            font-weight: bold;
            margin-top: 20px;
        }
         /* NUEVOS ESTILOS PARA EL BOTÓN DE VOLVER AL MENÚ */
        .back-link { display: block; text-align: center; margin-top: 25px; font-size: 0.9em; color: #8caf37; text-decoration: none; font-weight: bold; }

        .back-link:hover {
            text-decoration: underline; 
        }
        
        /* Estilos para el nuevo botón de DESCARGA (distinto al de Submit) */
           .descargar-zip-btn {
            display: block; 
            width: 100%;
            padding: 12px 15px;
            margin-top: 20px;
            background: #2E6AA7; /* Color azul */
            color: var(--color-text-light);
            border: none;
            border-radius: 6px;
            font-size: 1em;
            font-weight: 700;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.2s;
            text-decoration: none; /* Como es un <a>, quitamos el subrayado */
        }
        .descargar-zip-btn:hover {
            background: #1e5c8e;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
     <div class="container">
      <h1>Finalizar Contrato de Trabajador</h1>

     <?php if (!empty($mensaje_estado)): ?>
<p class="<?= $es_error ? 'mensaje-error' : 'mensaje-exito' ?>"><?= $mensaje_estado ?></p>
 <?php endif; ?>

        <?php if ($cedula_finalizada && !$es_error): ?>
            <a href="generar_zip.php?cedula=<?= htmlspecialchars($cedula_finalizada) ?>" class="descargar-zip-btn">
                Descargar Certificado de Recorrido (ZIP)
            </a>
            <p style="text-align: center; margin-top: 10px; font-size: 0.9em; color: #555;">
                Haz clic para obtener el Certificado de Retiro y otros documentos en un ZIP.
            </p>
        <?php endif; ?>

 <form action="fin_contrato.php" method="post">
 <div class="form-group">
<label for="cedula">Cédula del Trabajador:</label>
 <input type="text" id="cedula" name="cedula" required value="<?= htmlspecialchars($_POST['cedula'] ?? '') ?>">
 </div>
<div class="form-group">
 <label for="fecha_retiro">Fecha de Retiro:</label>
<input type="date" id="fecha_retiro" name="fecha_retiro" required value="<?= htmlspecialchars($_POST['fecha_retiro'] ?? '') ?>">
 </div>

 <button type="submit">Finalizar Contrato</button>
 </form>

 <a href="https://inserimaire.com/Certificados/formulario.php" class="back-link">
Volver al Menú Principal
</a>

 </div>
</body>
</html>