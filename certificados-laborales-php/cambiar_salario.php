<?php
// cambiar_salario.php
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
// 2. Procesar el formulario de cambio de salario
// =========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cedula = $_POST['cedula'] ?? '';
    $nuevo_salario = $_POST['nuevo_salario'] ?? '';

    if (empty($cedula) || empty($nuevo_salario)) {
        $es_error = true;
        $mensaje_estado = "⚠️ La cédula y el nuevo salario son obligatorios.";
    } elseif (!is_numeric($nuevo_salario) || $nuevo_salario <= 0) {
        $es_error = true;
        $mensaje_estado = "⚠️ El salario debe ser un valor numérico válido y mayor que 0.";
    } else {
        try {
            // 1. Verificar si existe un contrato ACTIVO para esa cédula
            $sql_check = "SELECT id, salario FROM Contratos WHERE cedula = :cedula AND tipo = 'activo' LIMIT 1";
            $stmt_check = $pdo->prepare($sql_check);
            $stmt_check->bindParam(':cedula', $cedula);
            $stmt_check->execute();
            $contrato_activo = $stmt_check->fetch(PDO::FETCH_ASSOC);

            if (!$contrato_activo) {
                $es_error = true;
                $mensaje_estado = "⚠️ No se encontró un contrato activo para la cédula **{$cedula}**.";
            } elseif ($contrato_activo['salario'] == $nuevo_salario) {
                $es_error = true;
                $mensaje_estado = "⚠️ El salario ya es **{$nuevo_salario}**. No se realizó ningún cambio.";
            } else {
                // 2. Actualizar salario del contrato activo
                $sql_update = "UPDATE Contratos 
                               SET salario = :nuevo_salario 
                               WHERE cedula = :cedula AND tipo = 'activo' 
                               LIMIT 1";
                
                $stmt_update = $pdo->prepare($sql_update);
                $stmt_update->bindParam(':nuevo_salario', $nuevo_salario);
                $stmt_update->bindParam(':cedula', $cedula);
                $stmt_update->execute();

                if ($stmt_update->rowCount() > 0) {
                    $mensaje_estado = "✅ El salario (de **{$contrato_activo['salario']}** a **{$nuevo_salario}**) del trabajador con cédula **{$cedula}** ha sido actualizado correctamente.";
                } else {
                    $es_error = true;
                    $mensaje_estado = "⚠️ No se pudo actualizar el salario.";
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
    <title>Cambiar Salario</title>

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
        input {
            width: 100%;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid var(--color-border);
            box-sizing: border-box;
            font-size: 1em;
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
    </style>

</head>
<body>

    <div class="container">
        <h1>Cambiar Salario</h1>

        <?php if (!empty($mensaje_estado)): ?>
            <p class="<?= $es_error ? 'mensaje-error' : 'mensaje-exito' ?>">
                <?= $mensaje_estado ?>
            </p>
        <?php endif; ?>

        <form action="cambiar_salario.php" method="post">
            <div class="form-group">
                <label for="cedula">Cédula del Trabajador:</label>
                <input type="text" id="cedula" name="cedula" placeholder="sin puntos,ni comas"  required>
            </div>

            <div class="form-group">
                <label for="nuevo_salario">Nuevo Salario:</label>
                <input type="number" id="nuevo_salario" name="nuevo_salario" placeholder="sin puntos,ni comas" required>
            </div>

            <button type="submit">Actualizar Salario</button>
        </form>

        <a href="admin.php" class="back-link">Volver al Menú de Administrador</a>
    </div>

</body>
</html>
