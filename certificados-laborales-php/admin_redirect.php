<?php
// admin_redirect.php
session_start();

// Proteger la página
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login_admin.php');
    exit;
}

$accion = $_POST['accion'] ?? '';

switch ($accion) {
    case 'agregar_nuevo':
        header('Location: agregar_trabajador.php');
        break;
    case 'fin_contrato':
        header('Location: fin_contrato.php');
        break;
    case 'cambiar_contrato': // NUEVO CASO
        header('Location: cambiar_contrato.php');
        break;
    case 'cambiar_salario':   // NUEVO
        header('Location: cambiar_salario.php');
        break;
    case 'cambiar_bono':      // NUEVO
        header('Location: cambiar_bono.php');
        break;
    default:
        // Redirigir de vuelta al menú principal si la acción no es válida
        header('Location: admin.php'); 
        break;
}
exit;
?>

