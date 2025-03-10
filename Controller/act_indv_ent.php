<?php
require('../Model/fpdf.php');
include "../Configuration/Connection.php";

// Funcion para convertir el texto a ISO-8859-1
function convertirTexto($texto)
{
    return mb_convert_encoding($texto, 'ISO-8859-1', 'UTF-8');
}

class PDF extends FPDF
{
    function Header()
    {
        $this->Image('../Model/logo.png', 155, 5, 45);
        $this->Ln(10);
        $fecha_actual = date('d/m/Y');
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(0, 10, 'Fecha: ' . $fecha_actual, 0, 1, 'L');
        $this->Ln(5);
        $this->SetFont('Arial', 'B', 20);
        $this->Cell(0, 10, convertirTexto('Acta de Entrega'), 0, 1, 'C');
        $this->Ln(20);
    }

    function addCertificadoTexto($last_user, $cedula, $cargo, $tipo_id)
    {
        $this->SetFont('Arial', '', 12);
        $texto = "Por medio de la presente se certifica que el señor@; ";
        $this->Write(8, convertirTexto($texto));
        $this->SetFont('Arial', 'B', 12);
        $this->Write(8, $last_user);
        $this->SetFont('Arial', '', 12); // Set back to normal font

        // Conditional text based on $tipo_id
        switch ($tipo_id) {
            case 'CE':
                $tipo_text = " identificado(a) con Cédula de Extranjeria: $cedula, del cargo: $cargo se le hace entrega de los siguientes activos que manejara en 3Shape.";
                break;
            case 'PP':
                $tipo_text = " identificado(a) con Pasaporte: $cedula, del cargo: $cargo se le hace entrega de los siguientes activos que manejara en 3Shape.";
                break;
            case 'RC':
                $tipo_text = " identificado(a) con Cédula de Residencia: $cedula, del cargo: $cargo se le hace entrega de los siguientes activos que manejara en 3Shape.";
                break;  
            case 'CC':
                $tipo_text = " identificado(a) con Cédula de Ciudadania: $cedula, del cargo: $cargo se le hace entrega de los siguientes activos que manejara en 3Shape.";
                break;
            case 'TI';
                $tipo_text = " identificado(a) con Tarjeta de Identidad: $cedula, del cargo: $cargo se le hace entrega de los siguintes activos que manejara en 3Shape.";
                break;
            default:
                $tipo_text = " identificado(a) con ID: $cedula, del cargo: $cargo se le hace entrega de los siguientes activos que manejara en 3Shape.";
                break;
        }

        $this->Write(8, convertirTexto($tipo_text)); // Write without bold
        $this->Ln(18);

        $this->SetFont('Arial', 'B', 9);
        $this->Cell(22, 10, convertirTexto('No'), 1, 0, 'C');
        $this->Cell(40, 10, convertirTexto('Descripción del Activo'), 1, 0, 'C');
        $this->Cell(29, 10, convertirTexto('Serial'), 1, 0, 'C');
        $this->Cell(29, 10, convertirTexto('Estado'), 1, 0, 'C');
        $this->Cell(38, 10, convertirTexto('Observaciones'), 1, 1, 'C');
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, convertirTexto('Página ') . $this->PageNo(), 0, 0, 'C');
    }
}

ob_start();
session_start();
$resultados = isset($_SESSION['asset_data']) ? $_SESSION['asset_data'] : [];
if (empty($resultados)) {
    die('No se encontraron datos en $_SESSION[\'asset_data\'] para generar el PDF.');
}

$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'I', 10);

$count = 0;
$totalRegistros = count($resultados);
$last_user = ''; // Inicializar la variable

foreach ($resultados as $index => $row) {
    if (!isset($row['cedula']) || $row['cedula'] == 0 || !isset($row['user_status']) || $row['user_status'] == 'Stock') {
        continue;
    }
    
    if ($count >= 15) break; // Limitar a 15 registros por página

    $last_user = $row['last_user']; // Asignar el valor de $last_user
    $cedula = $row['cedula'];
    $serial = $row['serial_number'];
    $headSet = $row['HeadSet'];
    $dongle = $row['Dongle'];
    $cargo = $row['job_title'];
    $tipo_id = $row['Tipo_ID'];
    $carnet = !empty($row['Carnet']) ? $row['Carnet'] : 'Pendiente';
    $llave = !empty($row['LLave']) ? $row['LLave'] : 'Pendiente';

    // Añadir texto de certificado y encabezados de la tabla para cada registro
    $pdf->addCertificadoTexto(
        convertirTexto($last_user),
        convertirTexto($cedula), 
        convertirTexto($cargo),
        convertirTexto($tipo_id)
    );

    $itemCounter = 1; // Add this at the start of your loop

    // Añadir la fila de datos del activo
    $pdf->Cell(22, 10, convertirTexto($itemCounter++), 1, 0, 'C');
    $pdf->Cell(40, 10, convertirTexto('Computador Personal'), 1, 0, 'C');
    $pdf->Cell(29, 10, convertirTexto($serial), 1, 0, 'C');
    $pdf->Cell(29,10, convertirTexto(''),1,0,'C');
    $pdf->Cell(38, 10, convertirTexto(''), 1, 1, 'C'); // Esta celda cierra la fila
    
    if (!empty($headSet)) {
        $pdf->Cell(22, 10, convertirTexto($itemCounter++), 1, 0, 'C');
        $pdf->Cell(40, 10, convertirTexto('Head Set'), 1, 0, 'C');
        $pdf->Cell(29, 10, convertirTexto($headSet), 1, 0, 'C');
        $pdf->Cell(29,10, convertirTexto(''),1,0,'C');
        $pdf->Cell(38, 10, convertirTexto(''), 1, 1, 'C'); // Esta celda cierra la fila
    }
    if (!empty($dongle)) {
        $pdf->Cell(22, 10, convertirTexto($itemCounter++), 1, 0, 'C');
        $pdf->Cell(40, 10, convertirTexto('Dongle'), 1, 0, 'C');
        $pdf->Cell(29, 10, convertirTexto($dongle), 1, 0, 'C');
        $pdf->Cell(29,10, convertirTexto(''),1,0,'C');
        $pdf->Cell(38, 10, convertirTexto(''), 1, 1, 'C'); // Esta celda cierra la fila
    }
    
    $pdf->Ln(10); // Espacio entre filas
    
    $pdf->SetFont('Arial','B',12);
    $pdf->MultiCell(0,10, convertirTexto('Datos exclusivos Administrativos:'));
    $pdf->Ln(7);

    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(40, 7, convertirTexto('Carne: '), 1,0,'L');
    $pdf->Cell(30, 7, convertirTexto($carnet), 1,1,'C');
    $pdf->Cell(40, 7, convertirTexto('Llave locker: '), 1,0,'L');
    $pdf->Cell(30, 7, convertirTexto($llave), 1,1,'C');
    $pdf->Cell(40, 7, convertirTexto('SIM card: '), 1,0,'L');
    $pdf->Cell(30, 7, convertirTexto(''), 1,1,'C');
    $pdf->Ln(8);

    $pdf->SetFont('Arial', '', 12);
    $pdf->MultiCell(0, 8, convertirTexto('El abajo firmante declara haber recibido a satisfacción los elementos antes mencionados con la condición de que cuidara de ellos.'), 0, 'L');

    $pdf->Ln(8); // Espacio para la firma
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(44, 15, convertirTexto('Firma Colaborador'), 1, 0, 'C');
    $pdf->Cell(44, 15, convertirTexto(''), 1, 0, 'C');
    $pdf->Cell(44, 15, convertirTexto('Vo.Bo IT'), 1, 0, 'C');
    $pdf->Cell(44, 15, convertirTexto(''), 1, 0, 'C');
    $count++;

    // Agregar una nueva página solo si no es la última iteración válida
    if ($count < $totalRegistros) {
        $pdf->AddPage();
    }
}

// Reemplazar espacios y caracteres especiales en $last_user para el nombre del archivo
$last_user_filename = strtoupper(preg_replace('/[^a-zA-Z0-9 ]/', '_', $last_user));

$pdf->Output('I', "$last_user_filename.pdf");
ob_end_flush();
?>
