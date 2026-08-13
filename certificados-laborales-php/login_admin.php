<?php
// Iniciar la sesión para almacenar información del usuario.
session_start();

// Definir las credenciales de acceso
$usuario_admin = 'admi';
$pass_admin = 'inserim123';

// Variable para mostrar errores
$error = '';

// Verificar si se ha enviado el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'] ?? '';
    $contrasena = $_POST['contrasena'] ?? '';

    // Validar las credenciales
    if ($usuario === $usuario_admin && $contrasena === $pass_admin) {
        // Credenciales correctas, guardar en sesión y redirigir
        $_SESSION['loggedin'] = true;
        // ¡CORREGIDO! Asegúrate de que este nombre de archivo coincida EXACTAMENTE con tu archivo de opciones.
        header('Location: admi_options.php');
        exit;
    } else {
        // Credenciales incorrectas
        $error = 'Usuario o contraseña incorrectos.';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acceso de Administradores</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --color-primary: #1e8449; /* Verde oscuro del logo */
            --color-secondary: #f0f4f7; /* Un gris claro para el fondo */
            --color-text-dark: #333;
            --color-text-light: #fff;
            --color-border: #dcdcdc;
            --color-return-button: #2E6AA7; /* Azul del fondo */
        }
        
        body {
            font-family: 'Roboto', sans-serif;
            background-color: var(--color-return-button); /* Usamos el azul */
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 90vh;
            margin: 0;
            padding: 20px;
        }

        .login-container {
            width: 90%;
            max-width: 400px;
            background-color: var(--color-text-light);
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
            text-align: center;
        }

        /* Estilos para el logo */
        .logo-container {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo {
            max-width: 250px; /* Ajusta el tamaño según necesites */
            height: auto;
        }

        h2 {
            color: #8caf37;
            margin-bottom: 25px;
            font-size: 1.8em;
            font-weight: 700;
        }
        
        label {
            display: block;
            text-align: left;
            margin-top: 15px;
            font-weight: bold;
            color: var(--color-text-dark);
        }

        input {
            width: 100%;
            padding: 12px;
            margin-top: 5px;
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
        
        /* Estilo para el botón de Ingresar */
        .submit-btn {
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

        .submit-btn:hover {
            background: #8caf37;
            transform: translateY(-2px);
        }

        .error {
            color: red;
            margin-top: 15px;
            font-weight: bold;
        }

        /* NUEVOS ESTILOS PARA EL BOTÓN DE VOLVER AL MENÚ */
        .back-link { display: block; text-align: center; margin-top: 25px; font-size: 0.9em; color: #8caf37; text-decoration: none; font-weight: bold; }

        .back-link:hover {
            text-decoration: underline; 
        }

    </style>
</head>
<body>

    <div class="login-container">
        
        <div class="logo-container">
            <img src="https://inserimaire.com/Certificados/logo.png" alt="Logo de INSERIMAIRE S.A.S." class="logo">
        </div>

        <h2>Acceso de Administradores</h2>
        
        <?php if ($error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form action="login_admin.php" method="post">
            <label for="usuario">Usuario:</label>
            <input type="text" id="usuario" name="usuario" required>

            <label for="contrasena">Contraseña:</label>
            <input type="password" id="contrasena" name="contrasena" required>

            <button type="submit" class="submit-btn">Ingresar</button>
        </form>

        <a href="https://inserimaire.com/Certificados/formulario.php" class="back-link">
            Volver al Menú Principal
        </a>
    </div>

</body>
</html>