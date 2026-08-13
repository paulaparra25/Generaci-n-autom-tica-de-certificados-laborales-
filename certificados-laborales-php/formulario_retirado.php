<?php
// PHP para manejar errores si los hay
$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Certificado Trabajador Retirado</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --color-primary: #1e8449;
            --color-background-green: #eaf6ee;
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
            min-height: 90vh;
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
        }
        .logo-container { text-align: center; margin-bottom: 20px; }
        .logo { max-width: 250px; height: auto; }
        h2 { text-align: center; color: #8caf37; margin-bottom: 25px; font-size: 1.8em; font-weight: 700; }
        label { display: block; margin-top: 15px; font-weight: bold; color: var(--color-text-dark); }
        input, select, button { width: 100%; padding: 12px; margin-top: 5px; border-radius: 6px; border: 1px solid var(--color-border); box-sizing: border-box; font-size: 1em; transition: border-color 0.3s; }
        input:focus, select:focus { border-color: var(--color-primary); outline: none; }
        button { margin-top: 20px; background: #8caf37; color: var(--color-text-light); border: none; cursor: pointer; font-size: 1.1em; font-weight: 700; transition: background-color 0.3s, transform 0.2s; }
        
        .error { margin: 20px auto; padding: 15px; border: 1px solid red; color: red; background: #ffe6e6; font-weight: bold; max-width: 500px; border-radius: 8px; text-align: center; }
        .admin-link { display: block; text-align: center; margin-top: 25px; font-size: 0.9em; color: #8caf37; text-decoration: none; font-weight: bold; }
        .admin-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo-container">
            <img src="https://inserimaire.com/Certificados/logo.png" alt="Logo de INSERIMAIRE S.A.S." class="logo">
        </div>

        <h2>Ingrese sus datos:</h2>
        <?php if ($error): ?>
            <div class="error">⚠️ <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <form action="generar.php" method="post">
            <input type="hidden" name="tipo" value="retirado">

            <label for="cedula">Cédula:</label>
            <input type="text" name="cedula" placeholder= "Sin puntos" id="cedula" value="<?= htmlspecialchars($_POST['cedula'] ?? '') ?>" required>

            <label for="fecha">Fecha de Expedición:</label>
            <input type="date" name="fecha" id="fecha" value="<?= htmlspecialchars($_POST['fecha'] ?? '') ?>" required>
            
            <button type="submit">Generar Certificado</button>
        </form>

        <a href="login_admin.php" class="admin-link">Acceso para Administradores</a>
        <a href="formulario_activo.php" class="admin-link">Generar Certificado para Activos</a>
    </div>
</body>
</html>