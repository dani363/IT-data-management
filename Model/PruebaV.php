<?php
//Archivo que consulta la mayoria de los datos de los activos osea registros y los trae a el pdf principal como pdf
require './fpdf.php';
include '../Configuration/Connection.php';

// Función de conversión para texto
function convertirTexto($texto) {
    return mb_convert_encoding($texto, 'ISO-8859-1', 'UTF-8');
}
//Crear Clase en base a la Api de FPDF
class PDF extends FPDF
{
   // Cabecera de página
   function Header()
   {
    // Logo
    $this->Image('logo.png', 10, 6, 30); 
    
    // Move to the right
    $this->Cell(80);
    
    // Line break
    $this->Ln(5); 
    
    // title
    $this->SetTextColor(164, 0, 125); 
    $this->SetFont('Arial', 'B', 20);
    $this->Cell(0, 10, convertirTexto("General Report"), 0, 1, 'C');
    
    // Line break
    $this->Ln(5); 
    
    // Additional Information
    $this->SetFont('Arial', 'I', 10);
    $this->SetTextColor(100, 100, 100);
    $this->Cell(0, 10, convertirTexto("Generated on: " . date('d/m/Y')), 0, 1, 'C');
    
    // Line break
    $this->Ln(5);
    // Line break
    $this->Ln(10);

      //

      /* CAMPOS DE LA TABLA */
      $this->SetFillColor(164, 0, 125); 
      $this->SetTextColor(255, 255, 255); 
      $this->SetDrawColor(163, 163, 163); 
      $this->SetFont('Arial', 'B', 7); // Fuente más pequeña para ajustar todo

      // Ajustar el ancho de cada columna
      $this->Cell(22, 10, convertirTexto('AssetName'), 1, 0, 'C', 1);
      $this->Cell(18, 10, convertirTexto('Serial'), 1, 0, 'C', 1);
      $this->Cell(19, 10, convertirTexto('User Status'), 1, 0, 'C', 1);
      $this->Cell(30, 10, convertirTexto('Last User'), 1, 0, 'C', 1);
      $this->Cell(23, 10, convertirTexto('Status Change'), 1, 0, 'C', 1);
      $this->Cell(20, 10, convertirTexto('Purchase '), 1, 0, 'C', 1);
      $this->Cell(25, 10, convertirTexto('Warranty Enddate'), 1, 0, 'C', 1);
      $this->Cell(15, 10, convertirTexto('Expired'), 1, 0, 'C', 1);
      $this->Cell(20, 10, convertirTexto('New Laptop'), 1, 0, 'C', 1);
      $this->Cell(20, 10, convertirTexto('ID'), 1, 1, 'C', 1); // Salto de línea
   }

   // Pie de página
   function Footer()
   {
      $this->SetY(-15); 
      $this->SetFont('Arial', 'I', 7); 
      $this->Cell(0, 10, convertirTexto('Página ') . $this->PageNo() . '/{nb}', 0, 0, 'C'); 
      $this->SetY(-10); 
      $this->SetFont('Arial', 'I', 8); 
      $hoy = date('d/m/Y');
      $this->Cell(355, 10, convertirTexto($hoy), 0, 0, 'C'); 
   }
}

ob_start();
$pdf = new PDF();//CREAR PDF
$pdf->SetLeftMargin(0); // Ajusta este valor si necesitas un pequeño margen
$pdf->AddPage();
$pdf->AliasNbPages();
$pdf->SetFont('Arial', '', 5);
$pdf->SetDrawColor(163, 163, 163);

// Construir la consulta usando los filtros de búsqueda
$sql = "SELECT * FROM vista_equipos_usuarios";
$where = [];
$params = [];

function buildDateSearchQuery($searchParams) {
    $where = [];
    $params = [];
    
    $fecha_inicio = !empty($searchParams['search_fecha_inicio']) 
        ? date('Y-m-d', strtotime($searchParams['search_fecha_inicio'])) 
        : null;
    
    $fecha_fin = !empty($searchParams['search_fecha_fin']) 
        ? date('Y-m-d', strtotime($searchParams['search_fecha_fin'])) 
        : null;

    // Validación base para asegurar que las fechas sean válidas
    $baseValidation = "MONTH(STR_TO_DATE(warranty_enddate, '%m/%d/%Y')) > 0 
                       AND DAY(STR_TO_DATE(warranty_enddate, '%m/%d/%Y')) > 0";

    if ($fecha_inicio && $fecha_fin) {
        // Caso 1: Ambas fechas proporcionadas
        $where[] = "STR_TO_DATE(warranty_enddate, '%m/%d/%Y') BETWEEN :fecha_inicio AND :fecha_fin 
                    AND " . $baseValidation;
        $params[':fecha_inicio'] = $fecha_inicio;
        $params[':fecha_fin'] = $fecha_fin;
    } elseif ($fecha_inicio) {
        // Caso 2: Solo fecha de inicio
        $where[] = "STR_TO_DATE(warranty_enddate, '%m/%d/%Y') <= :fecha_inicio 
                    AND " . $baseValidation;
        $params[':fecha_inicio'] = $fecha_inicio;
    } elseif ($fecha_fin) {
        // Caso 3: Solo fecha final
        $where[] = "STR_TO_DATE(warranty_enddate, '%m/%d/%Y') >= :fecha_fin 
                    AND " . $baseValidation;
        $params[':fecha_fin'] = $fecha_fin;
    }

    return [
        'where' => $where,
        'params' => $params
    ];
}

// Ejemplo de uso:
$searchResult = buildDateSearchQuery($_GET);

// Otros filtros
if (!empty($_GET['search_assetname'])) {
    $where[] = "assetname LIKE :assetname";
    $params[':assetname'] = '%' . $_GET['search_assetname'] . '%';
}
if (!empty($_GET['search_serial'])) {
    $where[] = "serial_number LIKE :serial";
    $params[':serial'] = '%' . $_GET['search_serial'] . '%';
}
if (!empty($_GET['search_cedula'])) {
    $where[] = "cedula LIKE :cedula";
    $params[':cedula'] = '%' . $_GET['search_cedula'] . '%';
}
if (!empty($_GET['search_status_change'])) {
    $where[] = "status_change LIKE :status_change";
    $params[':status_change'] = '%' . $_GET['search_status_change'] . '%';
}

// Agregar condiciones dinámicas
if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params); // Pasar todos los parámetros de una vez
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($resultados) {
        foreach ($resultados as $row) {
            if ($row['cedula'] == 0 || $row['user_status'] == 'Stock') {
                continue; // Saltar registros según los criterios
            }
            // Generar contenido del PDF
            $pdf->Cell(22, 10, convertirTexto($row["assetname"]), 1, 0, 'C', 0);
            $pdf->Cell(18, 10, convertirTexto($row["serial_number"]), 1, 0, 'C', 0);
            $pdf->Cell(19, 10, convertirTexto($row["user_status"]), 1, 0, 'C', 0);
            $pdf->Cell(30, 10, convertirTexto($row["last_user"]), 1, 0, 'C', 0);
            $pdf->Cell(23, 10, convertirTexto($row["status_change"]), 1, 0, 'C', 0);
            $pdf->Cell(20, 10, convertirTexto($row["purchase_country"]), 1, 0, 'C', 0);
            $pdf->Cell(25, 10, convertirTexto($row["warranty_enddate"]), 1, 0, 'C', 0);
            $pdf->Cell(15, 10, convertirTexto($row["expired"]), 1, 0, 'C', 0);
            $pdf->Cell(20, 10, convertirTexto($row["new_laptop"]), 1, 0, 'C', 0);
            $pdf->Cell(20, 10, convertirTexto($row["cedula"]), 1, 1, 'C', 0);
        }
    } else {
        $pdf->Cell(0, 10, 'No data found', 1, 1, 'C', 0);
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
ob_end_flush(); 

$pdf->Output('Reporte_Equipos.pdf', 'I');
