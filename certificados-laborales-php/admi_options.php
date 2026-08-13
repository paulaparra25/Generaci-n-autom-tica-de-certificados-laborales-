<?php
session_start();

// Proteger la página: si el usuario no ha iniciado sesión, redirigir al login
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
 header('Location: login_admin.php');
 exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
 <meta charset="UTF-8">
<title>Modificación de Trabajadores</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
 <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
 <style>
/* ... Tus estilos CSS (sin cambios) ... */
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
            min-height: 90vh;
            margin: 0;
            padding: 20px;
        }
        
        .options-container {
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
            font-size: 2em;
            font-weight: 700;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            text-align: left;
            margin-bottom: 8px;
            font-weight: bold;
            color: var(--color-text-dark);
        }
        
        select, button {
            width: 100%;
            padding: 12px;
            border: 1px solid #8caf37;
            border-radius: 6px;
            box-sizing: border-box;
            font-size: 1em;
            transition: border-color 0.3s;
        }

        select:focus {
            border-color: var(--color-primary);
            outline: none;
        }

        button {
            padding: 15px;
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
        
        .back-link { display: block; text-align: center; margin-top: 25px; font-size: 0.9em; color: #8caf37; text-decoration: none; font-weight: bold; }

        .back-link:hover {
            text-decoration: underline; 
        }
 </style>
</head>
<body>

 <div class="options-container">
<h1>Modificación de Trabajadores</h1>

 <form action="admin_redirect.php" method="post">
 <div class="form-group">
 <label for="accion">¿Qué quiere hacer?</label>
 <select name="accion" id="accion" required>
<option value="" disabled selected>Seleccione...</option>
 <option value="agregar_nuevo">Agregar trabajador nuevo</option>
 <option value="fin_contrato">Finalizar contrato de trabajador activo</option>
<option value="cambiar_contrato">Cambiar tipo de contrato existente</option>
<option value="cambiar_salario">Modificar salario</option>
<option value="cambiar_bono">Modificar bono</option>

</select>

 </div>

 <button type="submit">Continuar</button>
 </form>
<a href="https://inserimaire.com/Certificados/formulario.php" class="back-link">
 Volver al Menú Principal
</a>
 </div>

</body>
</html>