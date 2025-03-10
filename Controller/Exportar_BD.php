<?php
// Start output buffering to prevent any accidental output
ob_start();
session_start();
include "../Configuration/Connection.php";


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('CSRF token validation failed');
    }

    $selectedColumns = $_POST['columns'] ?? [];

    if (empty($selectedColumns)) {
        $_SESSION['error'] = 'No columns selected';
        header('Location: ../Admin/index_admin.php');
        exit();
    }

    $columns = implode(", ", $selectedColumns);
    $sql = "SELECT $columns FROM equipos LEFT JOIN usuarios_equipos ON equipos.assetname = usuarios_equipos.fk_assetname";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Verifica si existen datos
    if ($data) {
        // Generacion del nombre del archivo
        $filename_csv = "exportData_" . date('Y-m-d') . ".csv";
        // Encabezados HTTP
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename_csv . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Apertura del flujo de salida con manejo de errores
        $output_csv = fopen('php://output', 'w');
        if ($output_csv === false) {
            throw new RuntimeException('Unable to open output stream');
        }
        /**
         * Se escribe la primera fila del csv con las claves del primer elemento del array $data que se 
         * asumen como nombnres de las columnas
         */
        fputcsv($output_csv, array_keys($data[0]));
        // se itera  sobre cada fila de datos en $data y se escribe en el arhivo CSV
        foreach ($data as $row) {
            fputcsv($output_csv, $row);
        }
        // Se cierra el flujo de salida
        fclose($output_csv);

        // Limpiar buffer de salida y terminar ejecución
        ob_end_flush();
        exit();
    } else {
        $_SESSION['error'] = 'No data found';
        header('Location: ../Admin/index_admin.php');
        exit();
    }
} else {
    $_SESSION['error'] = 'Invalid request';
    header('Location: ../Admin/index_admin.php');
    exit();
}
