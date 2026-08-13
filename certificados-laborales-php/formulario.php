<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Generar Certificado Laboral</title>
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
            text-align: center;
        }
        .logo-container { text-align: center; margin-bottom: 20px; }
        .logo { max-width: 250px; height: auto; }
        h2 { color: #8caf37; margin-bottom: 25px; font-size: 1.8em; font-weight: 700; }
        .opcion-link {
            display: block;
            background: #8caf37;
            color: var(--color-text-light);
            padding: 15px;
            margin-top: 15px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 1.1em;
            font-weight: 700;
            transition: background-color 0.3s, transform 0.2s;
        }
        
        .admin-link { display: block; text-align: center; margin-top: 25px; font-size: 0.9em; color: #8caf37; text-decoration: none; font-weight: bold; }
        .admin-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo-container">
            <img src="https://inserimaire.com/Certificados/logo.png" alt="Logo de INSERIMAIRE S.A.S." class="logo">
        </div>

        <h2>Generar Certificado Laboral</h2>
        <p>Por favor, seleccione su estado laboral actual:</p>

        <a href="formulario_activo.php" class="opcion-link">Soy un Trabajador Activo</a>
        <a href="formulario_retirado.php" class="opcion-link">Soy un Trabajador Retirado</a>
        
        <a href="login_admin.php" class="admin-link">Acceso para Administradores</a>
    </div>
</body>
</html>