<?php
// cambiar_contrato.php
session_start();

// Proteger la página
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

// Variables de estado
$mensaje_estado = '';
$es_error = false;

// =========================================
// 2. Procesar el formulario de cambio
// =========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cedula = $_POST['cedula'] ?? '';
    $nuevo_contrato = $_POST['nuevo_contrato'] ?? '';

    if (empty($cedula) || empty($nuevo_contrato)) {
        $es_error = true;
        $mensaje_estado = "⚠️ La cédula y el nuevo tipo de contrato son obligatorios.";
    } else {
        try {
            // 1. Verificar si existe un contrato ACTIVO para esa cédula
            $sql_check = "SELECT id, contrato FROM Contratos WHERE cedula = :cedula AND tipo = 'activo' LIMIT 1";
            $stmt_check = $pdo->prepare($sql_check);
            $stmt_check->bindParam(':cedula', $cedula);
            $stmt_check->execute();
            $contrato_activo = $stmt_check->fetch(PDO::FETCH_ASSOC);

            if (!$contrato_activo) {
                // Si no hay contrato activo, no se puede cambiar
                $es_error = true;
                $mensaje_estado = "⚠️ Error: No se encontró ningún contrato **activo** para la Cédula **{$cedula}**. Solo se pueden modificar contratos activos.";
            } elseif ($contrato_activo['contrato'] === $nuevo_contrato) {
                 // Si el contrato ya tiene ese tipo
                $es_error = true;
                $mensaje_estado = "⚠️ El contrato activo ya es de tipo **{$nuevo_contrato}**. No se realizó ningún cambio.";
            } else {
                // 2. Actualizar solo el contrato ACTIVO
                // Usamos el ID (si lo tienes) o la condición 'tipo = activo'
                $sql_update = "UPDATE Contratos 
                               SET contrato = :nuevo_contrato 
                               WHERE cedula = :cedula AND tipo = 'activo' 
                               LIMIT 1"; // Aseguramos que solo se actualice uno
                
                $stmt_update = $pdo->prepare($sql_update);
                $stmt_update->bindParam(':nuevo_contrato', $nuevo_contrato);
                $stmt_update->bindParam(':cedula', $cedula);
                
                $stmt_update->execute();

                if ($stmt_update->rowCount() > 0) {
                    $mensaje_estado = "✅ El tipo de contrato (de **{$contrato_activo['contrato']}** a **{$nuevo_contrato}**) del trabajador con Cédula **{$cedula}** se ha actualizado correctamente.";
                } else {
                    // Este caso es muy raro si la verificación de arriba funcionó, pero es por seguridad
                    $es_error = true;
                    $mensaje_estado = "⚠️ No se pudo actualizar la información. Revise si el contrato ya estaba en el estado deseado.";
                }
            }
        } catch (PDOException $e) {
            $es_error = true;
            $mensaje_estado = "⚠️ Error de base de datos: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cambiar Tipo de Contrato</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --color-primary: #1e8449;
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
        .mensaje-exito {
            color: green;
            font-weight: bold;
            margin-top: 20px;
            padding: 10px;
            border: 1px solid green;
            background-color: #e6ffe6;
            border-radius: 4px;
        }
        .mensaje-error {
            color: red;
            font-weight: bold;
            margin-top: 20px;
            padding: 10px;
            border: 1px solid red;
            background-color: #ffe6e6;
            border-radius: 4px;
        }
        .back-link { display: block; text-align: center; margin-top: 25px; font-size: 0.9em; color: #8caf37; text-decoration: none; font-weight: bold; }
        .back-link:hover {
            text-decoration: underline; 
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>Cambiar Tipo de Contrato</h1>

        <?php if (!empty($mensaje_estado)): ?>
            <p class="<?= $es_error ? 'mensaje-error' : 'mensaje-exito' ?>"><?= $mensaje_estado ?></p>
        <?php endif; ?>

        <form action="cambiar_contrato.php" method="post">
            <div class="form-group">
                <label for="cedula">Cédula del Trabajador:</label>
                <input type="text" id="cedula" name="cedula" required>
            </div>
            
            <div class="form-group">
                <label for="nuevo_contrato">Nuevo Tipo de Contrato:</label>
                <select id="nuevo_contrato" name="nuevo_contrato" required>
                    <option value="" disabled selected>Seleccione el nuevo tipo...</option>
                    <option value="Término Fijo">Término Fijo</option>
                    <option value="Indefinido">Indefinido</option>
                    <option value="Obra o Labor">Obra o Labor</option>
                    </select>
            </div>

            <button type="submit">Actualizar Contrato</button>
        </form>
        
        <a href="https://inserimaire.com/Certificados/formulario.php" class="back-link">
            Volver al Menú Principal
        </a>
    </div>

</body>
</html>