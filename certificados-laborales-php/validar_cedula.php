<?php
// Nombre del archivo: validar_cedula.php
//para consultar primero si el trabajador ya existe antes de ingresarlo como nuevo
// Configuración de la base de datos
$host = "localhost";
$db = "inserima_certificados";
$user = "inserima_certificado";
$pass = "QjpreSVN*Cp5S3R";

header('Content-Type: application/json');

if (!isset($_GET['cedula']) || empty($_GET['cedula'])) {
    echo json_encode(['success' => false, 'mensaje' => 'Cédula requerida.']);
    exit;
}

$cedula = $_GET['cedula'];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Consulta para contar contratos 'activos'
    $sql = "SELECT COUNT(*) FROM Contratos WHERE cedula = :cedula AND tipo = 'activo'";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':cedula', $cedula);
    $stmt->execute();
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        // Encontró al menos un contrato activo
        echo json_encode([
            'success' => false, 
            'mensaje' => '⛔ ¡ERROR! Ya existe un contrato ACTIVO con esta cédula. No se puede agregar.',
            'activo' => true
        ]);
    } else {
        // No encontró contratos activos
        echo json_encode([
            'success' => true, 
            'mensaje' => '✅ ¡Cédula válida! Puede continuar agregando los datos.',
            'activo' => false
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false, 
        'mensaje' => 'Error de BD: ' . $e->getMessage(),
        'activo' => true
    ]);
}
?>