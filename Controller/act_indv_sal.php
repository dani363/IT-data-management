<?php
require('../Model/fpdf.php');
include "../Configuration/Connection.php";

//Funcion reciclada para converitr texto
function convertirTexto($texto)
{
    return mb_convert_encoding($texto, 'ISO-8859-1', 'UTF-8');
}

class PDF extends FPDF
{
    public $fecha; // Add the fecha property

    function Header()
    {
        $this->Image('../Model/logo.png', 155, 5, 45);
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(10, 10, 'Fecha: ' . $this->fecha, 0, 1, 'L');
        $this->Ln(5);
        $this->SetFont('Arial', 'B', 20);
        $this->Cell(0, 10, convertirTexto('Acta de Salida'), 0, 1, 'C');
        $this->Ln(20);
    }

    function addCertificadoTexto($last_user, $cedula, $fecha, $cargo, $tipo_id)
    {
        $this->SetFont('Arial', '', 12);
        $texto1 = "Por medio de la presente se certifica que el señor@; ";
        $this->Write(8, convertirTexto($texto1));
        $this->SetFont('Arial', 'B', 12);
        $this->Write(8, $last_user);
        $this->SetFont('Arial', '', 12);

        // Conditional text based on $tipo_id
        switch ($tipo_id) {
            case 'CE':
                $tipo_text = " identificado(a) con Cédula de Extranjeria: $cedula, procede a desvincularse del cargo: $cargo que venía desempeñando, a partir de la fecha $fecha.";
                break;
            case 'PP':
                $tipo_text = " identificado(a) con Pasaporte: $cedula, procede a desvincularse del cargo: $cargo que venía desempeñando, a partir de la fecha $fecha.";
                break;
            case 'RC':
                $tipo_text = " identificado(a) con Cédula de Residencia: $cedula, procede a desvincularse del cargo: $cargo  que venía desempeñando, a partir de la fecha $fecha.";
                break;
            case 'CC':
                $tipo_text = " identificado(a) con Cédula de Ciudadania: $cedula, procede a desvincularse del cargo: $cargo  que venía desempeñando, a partir de la fecha $fecha.";
                break;
            case 'TI':
                $tipo_text = " identificado(a) con Tarjeta de Identidad: $cedula, procede a desvincularse del cargo: $cargo que venía desempeñando, a partir de la fecha $fecha.";
                break;
            default:
                $tipo_text = " identificado(a) con ID: $cedula, procede a desvincularse del cargo: $cargo que venía desempeñando, a partir de la fecha $fecha.";
                break;
        }

        $this->Write(8, convertirTexto($tipo_text));
        $this->Ln(8);
        $text3 = "adicionalmente, se declara que esta paz y salvo con la organización en cuanto a la entrega de los siguientes activos asignados para el desempeño de sus funciones: ";
        $this->MultiCell(0, 8, convertirTexto($text3), 0, 'L');

        $this->Ln(10);
        // Encabezados de la tabla
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

// Inicia el almacenamiento en búfer para evitar errores
ob_start();
session_start();
$resultados = isset($_SESSION['asset_data']) ? $_SESSION['asset_data'] : [];
if (empty($resultados)) {
    die('No se encontraron datos en $_SESSION[\'asset_data\'] para generar el PDF.');
}

// Get the fecha from the first result
// Get the fecha from the first result
$fecha = isset($resultados[0]['fecha_salida']) && !empty($resultados[0]['fecha_salida']) ?
    $resultados[0]['fecha_salida'] : date('d/m/Y');
$pdf = new PDF();
$pdf->fecha = $fecha; // Set the fecha property
$pdf->AddPage();
$pdf->SetFont('Arial', 'I', 10);

$count = 0;
$totalRegistros = count($resultados);

foreach ($resultados as $index => $row) {
    if (!isset($row['cedula']) || $row['cedula'] == 0 || !isset($row['user_status'])) {
        continue;
    }

    if ($count >= 15)
        break; // Límite de registros por página

    $tipo_id = $row['Tipo_ID'];
    $last_user = $row['last_user'];
    $cedula = $row['cedula'];
    $serial = $row['serial_number'];
    $cargo = $row['job_title'];
    $headSet = $row['HeadSet'];
    $dongle = $row['Dongle'];
    $carnet = !empty($row['Carnet']) ? $row['Carnet'] : 'Pendiente';
    $llave = !empty($row['LLave']) ? $row['LLave'] : 'Pendiente';

    $pdf->addCertificadoTexto(
        convertirTexto($last_user),
        convertirTexto($cedula),
        $fecha, // Use the retrieved fecha
        convertirTexto($cargo),
        convertirTexto($tipo_id)
    );

    $itemCounter = 1;

    // Añadir la fila de datos del acta de salida correspondiente
    $pdf->Cell(22, 10, convertirTexto($itemCounter++), 1, 0, 'C');
    $pdf->Cell(40, 10, convertirTexto('Computador Personal'), 1, 0, 'C');
    $pdf->Cell(29, 10, convertirTexto($serial), 1, 0, 'C');
    $pdf->Cell(29, 10, convertirTexto(''), 1, 0, 'C');
    $pdf->Cell(38, 10, convertirTexto(''), 1, 1, 'C');

    if (!empty($headSet)) {
        $pdf->Cell(22, 10, convertirTexto($itemCounter++), 1, 0, 'C');
        $pdf->Cell(40, 10, convertirTexto('Head Set'), 1, 0, 'C');
        $pdf->Cell(29, 10, convertirTexto($headSet), 1, 0, 'C');
        $pdf->Cell(29, 10, convertirTexto(''), 1, 0, 'C');
        $pdf->Cell(38, 10, convertirTexto(''), 1, 1, 'C');
    }
    if (!empty($dongle)) {
        $pdf->Cell(22, 10, convertirTexto($itemCounter++), 1, 0, 'C');
        $pdf->Cell(40, 10, convertirTexto('Dongle'), 1, 0, 'C');
        $pdf->Cell(29, 10, convertirTexto($dongle), 1, 0, 'C');
        $pdf->Cell(29, 10, convertirTexto(''), 1, 0, 'C');
        $pdf->Cell(38, 10, convertirTexto(''), 1, 1, 'C');
    }
    $pdf->Ln(10);

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->MultiCell(0, 8, convertirTexto('Datos exclusivos administrativos:'));
    $pdf->Ln(4);
    $pdf->SetFont('Arial', 'B', 10);

    $pdf->Ln(0);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(44, 6, convertirTexto('Carne: '), 1, 0, 'C');
    $pdf->Cell(30, 6, convertirTexto($carnet), 1, 0, 'C');
    $pdf->Cell(44, 6, convertirTexto(''), 1, 1, 'C');
    $pdf->Cell(44, 6, convertirTexto('Llave Locker: '), 1, 0, 'C');
    $pdf->Cell(30, 6, convertirTexto($llave), 1, 0, 'C');
    $pdf->Cell(44, 6, convertirTexto(''), 1, 1, 'C');
    $pdf->Cell(44, 6, convertirTexto('SIM: '), 1, 0, 'C');
    $pdf->Cell(30, 6, convertirTexto(''), 1, 0, 'C');
    $pdf->Cell(44, 6, convertirTexto(''), 1, 1, 'C');
    $pdf->Ln(12);

    $pdf->SetFont('Arial', '', 12);
    $pdf->MultiCell(0, 8, convertirTexto('El abajo firmante declara haber recibido a satisfacción los elementos antes mencionados y no tener pendiente ninguna entrega adicional de activos o documentación relevante a la empresa.'), 0, 'L');
    $pdf->Ln(6);

    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(44, 10, convertirTexto('Firma Colaborador'), 1, 0, 'C');
    $pdf->Cell(48, 10, convertirTexto('Firma del Jefe Inmediato'), 1, 0, 'C');
    $pdf->Cell(44, 10, convertirTexto('Vo.Bo.IT'), 1, 0, 'C');
    $pdf->Cell(44, 10, convertirTexto('Vo.Bo.Administrativo'), 1, 1, 'C');

    $pdf->Cell(44, 10, convertirTexto(''), 1, 0, 'C');
    $pdf->Cell(48, 10, convertirTexto(''), 1, 0, 'C');
    $pdf->Cell(44, 10, convertirTexto(''), 1, 0, 'C');
    $pdf->Cell(44, 10, convertirTexto(''), 1, 1, 'C');

    $count++;
    if ($count < $totalRegistros) {
        $pdf->AddPage();
    }
}

$last_user_filename = isset($last_user) ? strtoupper(preg_replace('/[^a-zA-Z0-9 ]/', '_', $last_user)) : 'UNKNOWN';

$pdf->Output('I', "$last_user_filename.pdf");
ob_end_flush();
