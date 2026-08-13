<?php
header("Content-Type: text/html; charset=UTF-8");
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/libs/dompdf/autoload.inc.php';
require_once __DIR__ . '/libs/PHPWord/src/PhpWord/Autoloader.php';
\PhpOffice\PhpWord\Autoloader::register();
require_once __DIR__ . '/libs/PHPMailer-master/src/PHPMailer.php';
require_once __DIR__ . '/libs/PHPMailer-master/src/SMTP.php';
require_once __DIR__ . '/libs/PHPMailer-master/src/Exception.php';

use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\IOFactory;
use Dompdf\Dompdf;
use Dompdf\Options;
// Incluye las clases de PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// =========================================
// 1. Recibir datos del formulario
// =========================================
$cedula = $_POST['cedula'] ?? '';
$fecha_expedicion_form = $_POST['fecha'] ?? ''; // Esta fecha se usa como validación
$tipo_seleccionado = $_POST['tipo'] ?? ''; // 'activo' o 'retirado'
$motivo = $_POST['motivo'] ?? ''; // Solo para activos
$solicitud = $_POST['solicitud'] ?? ''; // Solo para activos

if (!$cedula || !$fecha_expedicion_form) {
    die("Faltan datos obligatorios. Vuelva al formulario.");
}

// =========================================
// 2. Conexión a la base de datos
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
    die("Error de conexión: " . $e->getMessage());
}

// =========================================
// 3. Validar la fecha de expedición para TODOS los registros del empleado.
// =========================================
// Buscamos la fecha de expedición registrada en la tabla `Contratos` para la cédula dada.
// La columna 'contrasena' en tu tabla Contratos es la fecha de expedición.
$sql_auth = "SELECT contrasena FROM `Contratos` WHERE cedula = ? LIMIT 1";
$stmt_auth = $pdo->prepare($sql_auth);
$stmt_auth->execute([$cedula]);
$auth_data = $stmt_auth->fetch(PDO::FETCH_ASSOC);

function mostrarError($mensaje) {
    echo "
    <div style='width: 80%; margin: 40px auto; padding: 15px; border: 2px solid #cc0000; background-color: #ffe6e6; color: #cc0000; font-family: Arial, sans-serif; font-size: 14px; border-radius: 6px; text-align: center;'>
        <strong>Error:</strong> $mensaje
    </div>
    <div style='text-align:center; margin-top:20px;'>
        <a href=https://inserimaire.com/Certificados/formulario.php' style='display:inline-block; padding:10px 20px; background:#cc0000; color:#fff; text-decoration:none; border-radius:4px;'>
            Volver al formulario
        </a>
    </div>";
    exit;
}

if (!$auth_data) {
    mostrarError("No se encontró empleado con cédula $cedula.");
}

// Comprobamos si la fecha ingresada en el formulario coincide con la guardada en la base de datos.
if ($auth_data['contrasena'] !== $fecha_expedicion_form) {
    mostrarError("La fecha de expedición no coincide con la registrada en la base de datos.");
}

// =========================================
// 4. Consultar TODOS los contratos del empleado
// =========================================
// Si la autenticación fue exitosa, ahora traemos todos los contratos para esa cédula.
// La consulta trae todos los contratos, independientemente de su estado.
$sql_contratos = "SELECT nombre, tipo_documento, ciudad_expedicion, cargo, fecha_ingreso, fecha_retiro, contrato, salario, tipo AS estado_contrato, bono
                 FROM `Contratos`
                 WHERE cedula = ?
                 ORDER BY fecha_ingreso DESC"; // Ordenamos por fecha de ingreso para que el más reciente (activo) aparezca primero.

$stmt_contratos = $pdo->prepare($sql_contratos);
$stmt_contratos->execute([$cedula]);
$contratos = $stmt_contratos->fetchAll(PDO::FETCH_ASSOC);

if (empty($contratos)) {
    mostrarError("No se encontraron contratos para el empleado con cédula $cedula.");
}

// =========================================
// 5. Funciones para formatear fechas y números a letras
// =========================================
function formatearFecha($fecha) {
    if (!$fecha) return "";
    try {
        $timestamp = strtotime($fecha);
        if ($timestamp === false) return ""; // Manejo de error si la fecha es inválida
        
        $formatter = new IntlDateFormatter('es_ES', IntlDateFormatter::FULL, IntlDateFormatter::NONE);
        $formatter->setPattern('d \'de\' MMMM \'de\' yyyy');
        return ucfirst($formatter->format($timestamp));
    } catch (Exception $e) {
        // Loggear el error si es necesario, y devolver vacío o un mensaje
        return "";
    }
}

function numeroALetras($num) {
    if (!$num) return "";
    try {
        $fmt = new NumberFormatter("es", NumberFormatter::SPELLOUT);
        return ucfirst($fmt->format($num));
    } catch (Exception $e) {
        // Loggear el error si es necesario
        return "";
    }
}

// =========================================
// 6. Array para guardar los archivos temporales generados
// =========================================
$tempFiles = [];

// =========================================
// 7. Bucle para generar UN PDF por CADA CONTRATO
// =========================================
foreach ($contratos as $index => $empleado) {
    
    // Determinar si el contrato actual está activo o retirado
    $es_activo = ($empleado['estado_contrato'] === 'activo' || empty($empleado['fecha_retiro']));

    // Seleccionar la plantilla según el tipo de contrato.
    if ($es_activo) {
        $plantillaPath = __DIR__ . "/plantillas/certificado_activo.docx";
        $tipo_certificado_nombre = "activo";
    } else {
        $plantillaPath = __DIR__ . "/plantillas/certificado_retirado.docx";
        $tipo_certificado_nombre = "retirado";
    }

    // Si la plantilla específica no existe, saltamos este contrato.
    if (!file_exists($plantillaPath)) {
        error_log("Advertencia: Plantilla no encontrada en $plantillaPath para el contrato con cédula $cedula.");
        continue;
    }

    $template = new TemplateProcessor($plantillaPath);
    
    // Mapeo de valores para la plantilla
    $salarioLetras = numeroALetras($empleado['salario']);
    $fechaIngreso = formatearFecha($empleado['fecha_ingreso']);
    $fechaRetiro_formateada = !$es_activo ? formatearFecha($empleado['fecha_retiro']) : ""; 
    
    // BONO: formato o vacío
if (!empty($empleado['bono'])) {
    $bonoValor = number_format($empleado['bono'], 0, ',', '.');
    $textoBono = " y bono de $bonoValor pesos";
} else {
    $textoBono = ""; // No se agrega nada si no tiene bono
}

// Texto final para insertar en el certificado
$complementos = "mas auxilio de transporte, horas extras" . $textoBono;

    
    $template->setValue("NOMBRE", $empleado['nombre']);
    $template->setValue("TIPO_DOCUMENTO", $empleado['tipo_documento']);
    $template->setValue("CEDULA", $cedula);
    $template->setValue("CIUDAD_EXPEDICION", $empleado['ciudad_expedicion']);
    $template->setValue("CARGO", $empleado['cargo']);
    $template->setValue("FECHA_INGRESO", $fechaIngreso);
    $template->setValue("CONTRATO", $empleado['contrato']);
    $template->setValue("SALARIO", number_format($empleado['salario'], 0, ',', '.'));
  

$template->setValue("COMPLEMENTOS", $complementos);

    $template->setValue("TEXTO_SALARIO", $salarioLetras);
    
    // Solo si el contrato es retirado, agregamos la fecha de retiro
    if (!$es_activo) {
        $template->setValue("FECHA_RETIRO", $fechaRetiro_formateada);
    } else {
         $template->setValue("FECHA_RETIRO", ''); // Asegurarse de vaciar si no aplica
    }

    // Fechas actuales - Usamos IntlDateFormatter para asegurar español
    $formatter = new IntlDateFormatter('es_ES', IntlDateFormatter::FULL, IntlDateFormatter::NONE);
    $formatter->setPattern('d');
    $template->setValue("DIA_HOY", $formatter->format(time()));
    $formatter->setPattern('MMMM');
    $template->setValue("MES_HOY", ucfirst($formatter->format(time())));
    $formatter->setPattern('yyyy');
    $template->setValue("ANO_HOY", $formatter->format(time()));
    
    // Si el contrato es activo, agregamos el motivo y la solicitud (del formulario)
    if ($es_activo) {
        $template->setValue("MOTIVO", $motivo);
        $template->setValue("SOLICITA", $solicitud);
    } else {
        // Asegurarse de que los placeholders no se muestren si es un certificado retirado.
        $template->setValue("MOTIVO", '');
        $template->setValue("SOLICITA", '');
    }
    
    // --- Generación del PDF temporal ---
    $tempDocx = tempnam(sys_get_temp_dir(), 'certificado_') . ".docx";
    $template->saveAs($tempDocx);
    
    $phpWord = IOFactory::load($tempDocx);
    $tempHtml = tempnam(sys_get_temp_dir(), 'certificado_') . ".html";
    $writer = IOFactory::createWriter($phpWord, 'HTML');
    $writer->save($tempHtml);
    $html = file_get_contents($tempHtml);
    
// Inserción de logo y membrete (rutas absolutas o URLs accesibles)
$logoPath = "https://inserimaire.com/Certificados/logo.png";
$membretePath = "https://inserimaire.com/Certificados/membrete.png";

// Generamos el HTML para el encabezado (logo)
$header = "<div style='width:100%; text-align:right; margin-bottom:10px;'><img src='$logoPath' width='180' style='margin:0;'></div>";

// El membrete se añadirá al final del contenido HTML antes de cerrarlo.
// Ya no usaremos position: fixed para el footer; en su lugar, lo incluiremos
// directamente en el flujo del contenido.

// Limpieza y unificación del HTML final
$html = preg_replace('/<style.*?<\/style>/is', '', $html); // Elimina estilos internos del DOCX
$html = preg_replace('/<p[^>]*>(&nbsp;|\s)*<\/p>/', '', $html); // Elimina párrafos vacíos

// Estructura final del HTML para DomPDF.
// Ajustamos el margin-bottom en @page para dar espacio al membrete.
// El membrete se inserta al final del div de 'content'.
$htmlFinal = "
<html>
<head>
    <meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
    <style>
        @page {
            margin-top: 80px;      /* Espacio para el header */
            margin-bottom: 120px;  /* Espacio para el footer (aumentado para el membrete) */
        }
        body { font-family: Arial, sans-serif; font-size: 12pt; line-height: 1.6; text-align: justify; }
        .header { 
            position: fixed; 
            top: 0; 
            left: 0; 
            right: 0; 
            height: 70px; 
            margin-left: 40px; /* Alinea con el margen del contenido */
            margin-right: 40px;
        }
        .footer {
            position: fixed;
            bottom: 0; /* Fija el footer en la parte inferior de la ventana */
            left: 0;
            right: 0;
            height: 60px; /* Altura para el footer (ajustar según la imagen del membrete) */
            margin-left: 40px; /* Alinea con el margen del contenido */
            margin-right: 40px;
            text-align: center; /* Centra la imagen dentro del div del footer */
        }
        .content { margin: 60px; }
        p { margin-bottom: 1em; }
    </style>
</head>
<body>
    <div class='header'>$header</div>
    <div class='content'>
        $html
        <div class='footer'>
            <img src='$membretePath' style='width: 100%; height: auto;'>
        </div>
    </div>
</body>
</html>";

// --- Conversión de HTML a PDF ---
$options = new Options();
$options->set('isRemoteEnabled', true); // Permite cargar imágenes/recursos externos como el logo
$options->set('defaultFont', 'Arial'); // Define la fuente por defecto
$options->set('chroot', __DIR__); // Para asegurar rutas relativas
$dompdf = new Dompdf($options);
$dompdf->loadHtml($htmlFinal);
$dompdf->setPaper('A4', 'portrait'); // Asegúrate que el papel sea A4, si es oficio debe ser 'Legal' o 'Letter'
$dompdf->render();

// --- Guardar el PDF en un archivo temporal ---
$tempPdfPath = tempnam(sys_get_temp_dir(), 'certificado_') . ".pdf";
file_put_contents($tempPdfPath, $dompdf->output());

// ... (resto del código para añadir a $tempFiles)
    
    // --- Guardar el PDF en un archivo temporal ---
    $tempPdfPath = tempnam(sys_get_temp_dir(), 'certificado_') . ".pdf";
    file_put_contents($tempPdfPath, $dompdf->output());
    
    // Agregar el archivo temporal a la lista para el ZIP
    $tempFiles[] = [
        'path' => $tempPdfPath,
        'name' => "Certificado_{$tipo_certificado_nombre}_contrato_" . ($index + 1) . "_" . $cedula . ".pdf"
    ];

    // Limpiar temporales de esta iteración (docx y html)
    @unlink($tempDocx);
    @unlink($tempHtml);
}

// =========================================
// 8. Empaquetar y descargar los PDFs en un archivo ZIP
// =========================================
if (!empty($tempFiles)) {
    $zipFileName = "Certificados_Laborales_{$cedula}_" . date('Ymd') . ".zip";
    $zip = new ZipArchive();
    $tempZipFile = tempnam(sys_get_temp_dir(), 'zip_') . '.zip'; // Asegúrate que tenga extensión .zip

    if ($zip->open($tempZipFile, ZipArchive::CREATE) === TRUE) {
        foreach ($tempFiles as $file) {
            // Añade el archivo al ZIP con su nombre deseado
            if ($zip->addFile($file['path'], $file['name']) === FALSE) {
                error_log("Error al añadir archivo al ZIP: " . $file['name']);
            }
        }
        $zip->close();
        
        // Llamada a la función de envío de correo
      enviarCorreoNotificacion($cedula, $tipo_seleccionado, $motivo, $solicitud);
        
        // --- Envío del archivo ZIP al navegador ---
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $zipFileName . '"');
        header('Content-Length: ' . filesize($tempZipFile));
        // Se usa readfile para enviar el contenido del archivo ZIP
        readfile($tempZipFile);

    } else {
        // Si falla la creación del ZIP, muestra un error al usuario
        mostrarError("No se pudo crear el archivo de certificados. Por favor, contacte al administrador.");
    }

    // --- Limpieza de todos los archivos temporales generados ---
    // Eliminar PDFs individuales
    foreach ($tempFiles as $file) {
        @unlink($file['path']);
    }
    // Eliminar el archivo ZIP temporal
    @unlink($tempZipFile);
    
} else {
    // Este caso ocurre si no se encontraron contratos para el estado seleccionado,
    // o si las plantillas faltaban y se saltaron todos los contratos.
    // La mayoría de estos casos ya están cubiertos por el mostrarError anterior.
    // Si llegamos aquí, es que hubo un problema más general.
    mostrarError("No se generaron certificados para descargar. Verifique que haya contratos activos o retirados para la cédula y que las plantillas existan.");
}
// =========================================
//9 Enviar mensaje al correo cada vez que pidan certificados
// =========================================
function enviarCorreoNotificacion($cedula, $tipo, $motivo, $solicitud) {
    $mail = new PHPMailer(true);

    try {
        // Configuración del servidor de tu hosting
        $mail->isSMTP();
        $mail->Host       = 'agamenon.yoursitesecure.net';
        $mail->SMTPAuth   = true; 
        $mail->Username   = 'notificaciones@inserimaire.com';
        $mail->Password   = ';8)%04*gQ18U_78Y'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        // Destinatarios y contenido del correo
        $mail->setFrom('notificaciones@inserimaire.com', 'Sistema de Certificados');
        $mail->addAddress('talentohumano@inserimaire.com', 'Recursos Humanos');
        $mail->isHTML(true);
        $mail->Subject = ' Nueva Solicitud de Certificado Laboral';
        $body = "
            <html>
            <body>
                <h2>Nueva Solicitud de Certificado</h2>
                <p>Se ha generado una nueva solicitud de certificado laboral con los siguientes detalles:</p>
                <ul>
                    <li><strong>Cedula:</strong> {$cedula}</li>
                    <li><strong>Tipo de Certificado Solicitado:</strong> {$tipo}</li>
                </ul>";
        
        if ($tipo === 'activo') {
            $body .= "
                <p>Para la solicitud de tipo <strong>'Activo'</strong> se especificaron los siguientes datos:</p>
                <ul>
                    <li><strong>Motivo:</strong> {$motivo}</li>
                    <li><strong>Solicitud para:</strong> {$solicitud}</li>
                </ul>";
        }

        $body .= "
                <p>Atentamente,<br>Sistema Automatico de Certificados</p>
            </body>
            </html>
        ";
        $mail->Body = $body;
        $mail->send();
    } catch (Exception $e) {
        // En un entorno de producción, es mejor registrar el error que mostrarlo
        error_log("El mensaje no pudo ser enviado. Mailer Error: {$mail->ErrorInfo}");
    }
}
exit; // Finaliza el script después de enviar el archivo
?>