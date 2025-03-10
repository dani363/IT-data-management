<?php
require('./fpdf.php');
include '../Configuration/Connection.php';

function convertirTexto($texto)
{
    return mb_convert_encoding($texto, 'ISO-8859-1', 'UTF-8');
}

class PDF extends FPDF
{
    function Header()
    {
        $this->Image('logo.png', 155, 5, 45);
        $this->Ln(10);
        $fecha_actual = date('d/m/Y');
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(0, 10, 'Fecha: ' . $fecha_actual, 0, 1, 'L');
        $this->Ln(5);
        $this->SetFont('Arial', 'B', 20);
        $this->Ln(10);
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
        $this->SetFont('Arial', '', 12);

        // Conditional text based on $tipo_id
        switch ($tipo_id) {
            case 'CE':
                $tipo_text = " identificado(a) con Cédula de Extranjeria: $cedula, del cargo: $cargo se le hace entrega de los siguientes activos que manejara en 3Shape.";
                break;
            case 'PP':
                $tipo_text = " identificado(a) con Pasaporte: $cedula, del cargo: $cargo se le hace entrega de los siguientes activos que manejara en 3Shape.";
                break;
            case 'RC':
                $tipo_text = " identificado(a) con Cédula de Residencia: $cedula, del cargo: $cargo  se le hace entrega de los siguientes activos que manejara en 3Shape.";
            case 'CC':
                $tipo_text = " identificado(a) con Cédula de Ciudadania: $cedula, del cargo: $cargo  se le hace entrega de los siguientes activos que manejara en 3Shape.";
                break;
            case 'TI';
                $tipo_text = " identificado(a) con Tarjeta de Identidad: $cedula, del cargo: $cargo se le hace entrega de los siguintes activos que manejara en 3Shape.";
                break;
            default:
                $tipo_text = " identificado(a) con Cédula de Ciudadania: $cedula, del cargo: $cargo se le hace entrega de los siguientes activos que manejara en 3Shape.";
                break;
        }

        $this->Write(8, convertirTexto($tipo_text));
        $this->Ln(18);

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

ob_start();
$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'I', 10);

try {
    // Consultas integradas en base a el 
    $sql = "SELECT * FROM equipos LEFT JOIN usuarios_equipos ON equipos.assetname = usuarios_equipos.fk_assetname";
    $where = [];
    $params = [];

    // Filtros de fechas
    if (!empty($_GET['search_fecha_inicio']) && !empty($_GET['search_fecha_fin'])) {
        $fecha_inicio = $_GET['search_fecha_inicio'];
        $fecha_fin = $_GET['search_fecha_fin'];
        $fecha_inicio_mysql = date('Y-m-d', strtotime($fecha_inicio));
        $fecha_fin_mysql = date('Y-m-d', strtotime($fecha_fin));
        $where[] = "STR_TO_DATE(equipos.warranty_enddate, '%d/%m/%Y') BETWEEN :fecha_inicio AND :fecha_fin";
        $params[':fecha_inicio'] = $fecha_inicio_mysql;
        $params[':fecha_fin'] = $fecha_fin_mysql;
    }

    // Filtro de nombre de activo
    if (!empty($_GET['search_assetname'])) {
        $where[] = "LOWER(equipos.assetname) LIKE :assetname";
        $params[':assetname'] = '%' . strtolower(trim($_GET['search_assetname'])) . '%';
    }

    // Filtro de número de serie
    if (!empty($_GET['search_serial'])) {
        $where[] = "LOWER(equipos.serial_number) LIKE :serial";
        $params[':serial'] = '%' . strtolower(trim($_GET['search_serial'])) . '%';
    }

    // Filtro de cédula
    if (!empty($_GET['search_cedula'])) {
        $where[] = "LOWER(usuarios_equipos.cedula) LIKE :cedula";
        $params[':cedula'] = '%' . strtolower(trim($_GET['search_cedula'])) . '%';
    }

    // Filtro de nombre de usuario
    if (!empty($_GET['search_user'])) {
        $where[] = "LOWER(usuarios_equipos.last_user) LIKE :usuario";
        $params[':usuario'] = '%' . strtolower(trim($_GET['search_user'])) . '%';
    }

    // Filtro de cambio de estado
    if (!empty($_GET['search_status_change'])) {
        $where[] = "usuarios_equipos.status_change LIKE :status_change";
        $params[':status_change'] = '%' . $_GET['search_status_change'] . '%';
    }

    // Filtro de estado de usuario (selección múltiple)
    if (!empty($_GET['search_user_status'])) {
        $filtered_statuses = array_filter($_GET['search_user_status'], function ($status) {
            return $status != 0;
        });

        if (!empty($filtered_statuses)) {
            $user_status_conditions = [];
            foreach ($filtered_statuses as $index => $status) {
                $param_name = ":status_$index";
                $user_status_conditions[] = "usuarios_equipos.user_status = $param_name";
                $params[$param_name] = $status;
            }
            $where[] = '(' . implode(' OR ', $user_status_conditions) . ')';
        }
    }

    // Concatenar los filtros en la consulta final
    if (count($where) > 0) {
        $sql .= " WHERE " . implode(" AND ", $where);
    }

    // Preparar y ejecutar consulta
    $stmt = $pdo->prepare($sql);

    // Asociar parámetros con valores
    foreach ($params as $param_name => $value) {
        $stmt->bindValue($param_name, $value);
    }

    $stmt->execute();
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Generar PDF
    if ($resultados) {
        $count = 0;
        $validRecords = array_filter($resultados, function ($row) {
            return $row['cedula'] != 0 && $row['user_status'] != 'Stock';
        });
        $totalValidRecords = count($validRecords);

        foreach ($resultados as $index => $row) {
            if ($row['cedula'] == 0 || $row['user_status'] == 'Stock') {
                continue;
            }
            if ($count >= 15)
                break;

            if ($count > 0) {
                $pdf->AddPage();
            }

            $last_user = $row['last_user'];
            $tipo_id = $row['Tipo_ID'];
            $cedula = $row['cedula'];
            $serial = $row['serial_number'];
            $headSet = $row['HeadSet'];
            $dongle = $row['Dongle'];
            $cargo = $row['job_title'];
            $carnet = !empty($row['Carnet']) ? $row['Carnet'] : 'Pendiente';
            $llave = !empty($row['LLave']) ? $row['LLave'] : 'Pendiente';

            // Añadir texto de certificado y encabezados
            $pdf->addCertificadoTexto(
                convertirTexto($last_user),
                convertirTexto($cedula),
                convertirTexto($cargo),
                convertirTexto($tipo_id)
            );

            $itemCounter = 1;

            // Añadir la fila de datos del activo
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
            $pdf->MultiCell(0, 10, convertirTexto('Datos exclusivos Administrativos:'));
            $pdf->Ln(7);

            $pdf->SetFont('Arial', 'B', 10);
            $pdf->Cell(40, 7, convertirTexto('Carne: '), 1, 0, 'L');
            $pdf->Cell(30, 7, convertirTexto($carnet), 1, 1, 'C');
            $pdf->Cell(40, 7, convertirTexto('Llave locker: '), 1, 0, 'L');
            $pdf->Cell(30, 7, convertirTexto($llave), 1, 1, 'C');
            $pdf->Cell(40, 7, convertirTexto('SIM card: '), 1, 0, 'L');
            $pdf->Cell(30, 7, convertirTexto(''), 1, 1, 'C');
            $pdf->Ln(8);

            $pdf->SetFont('Arial', '', 12);
            $pdf->MultiCell(0, 8, convertirTexto('El abajo firmante declara haber recibido a satisfacción los elementos antes mencionados con la condición de que cuidara de ellos.'), 0, 'L');

            $pdf->Ln(8);
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->Cell(44, 15, convertirTexto('Firma Colaborador'), 1, 0, 'C');
            $pdf->Cell(44, 15, convertirTexto(''), 1, 0, 'C');
            $pdf->Cell(44, 15, convertirTexto('Vo.Bo IT'), 1, 0, 'C');
            $pdf->Cell(44, 15, convertirTexto(''), 1, 0, 'C');

            $count++;
        }
    } else {
        $pdf->Cell(0, 10, 'No data found', 1, 1, 'C');
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

$last_user_filename = isset($last_user) ? strtoupper(preg_replace('/[^a-zA-Z0-9 ]/', '_', $last_user)) : 'UNKNOWN';

$pdf->Output('I', "$last_user_filename.pdf");
ob_end_flush();
?>