<?php
ob_start(); // Iniciar buffer
session_start();
include('../Configuration/Connection.php');

// Validación de archivo solo txt y csv
function validateFile($file)
{
    $allowedExtensions = ['csv', 'txt'];
    $allowedMimeTypes = ['text/csv', 'application/vnd.ms-excel', 'text/plain'];
    $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);

    return in_array($fileExtension, $allowedExtensions) && in_array($file['type'], $allowedMimeTypes);
}

function generateUniqueAssetName($pdo)
{
    $maxAttempts = 5;
    for ($i = 0; $i < $maxAttempts; $i++) {
        $assetname = 'CO-LPT-' . bin2hex(random_bytes(3));
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM equipos WHERE assetname = ?");
        $stmt->execute([$assetname]);
        if ($stmt->fetchColumn() == 0) {
            return $assetname;
        }
    }
    throw new Exception("Failed to generate a unique assetname after $maxAttempts attempts");
}

function insertData($pdo, $data)
{
    // Valores por defecto
    $defaults = [
        'Tipo_ID' => 'CC',
        'Dongle' => 0,
        'serial_number' => 0,
        'Carnet' => 'Pendiente',
        'LLave' => 'Pendiente',
        'HeadSet' => 0,
        'job_title' => 'unknown',
    ];

    // Combinar solo si el valor en $data está vacío o no existe
    foreach ($defaults as $key => $value) {
        if (!isset($data[$key]) || empty($data[$key])) {
            $data[$key] = $value;
        }
    }

    // Generar assetname si está vacío
    if (empty($data['assetname'])) {
        $data['assetname'] = generateUniqueAssetName($pdo);
    } else {
        // Verificar si el assetname proporcionado ya existe
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM equipos WHERE assetname = ?");
        $checkStmt->execute([$data['assetname']]);
        if ($checkStmt->fetchColumn() > 0) {
            throw new PDOException("Duplicate Assetname", 1062);
        }
    }

    // Check for duplicate serial_number
    if (!empty($data['serial_number'])) {
        $serialCheckStmt = $pdo->prepare("SELECT COUNT(*) FROM equipos WHERE serial_number = ?");
        $serialCheckStmt->execute([$data['serial_number']]);
        if ($serialCheckStmt->fetchColumn() > 0) {
            throw new PDOException("Duplicate Serial Number", 1062);
        }
    }

    // Forzar la insersion de datos
    $data['Dongle'] = $data['Dongle'] ?? 0; // Si es null, usa 0
    $data['Dongle'] = (int)$data['Dongle'];
    $data['job_title'] = $data['job_title'] ?? 'unknown'; // Si es null, usa 'unknown'
    $data['job_title'] = (string)$data['job_title'];
    $data['HeadSet'] = $data['HeadSet'] ?? 'unknown'; // Si
    $data['HeadSet'] = (int)$data['HeadSet'];

    try {
        $pdo->beginTransaction();
        // Insertar en equipos
        $stmtEquipos = $pdo->prepare("
        INSERT INTO equipos 
        (assetname, serial_number, purchase_country, warranty_enddate, expired, new_laptop, HeadSet)
        VALUES 
        (:assetname, :serial_number, :purchase_country, :warranty_enddate, :expired, :new_laptop, :HeadSet)
         ");

        $stmtEquipos->execute([
            ':assetname' => $data['assetname'],
            ':serial_number' => $data['serial_number'],
            ':purchase_country' => $data['purchase_country'],
            ':warranty_enddate' => $data['warranty_enddate'],
            ':expired' => $data['expired'],
            ':new_laptop' => $data['new_laptop'],
            ':HeadSet' => $data['HeadSet']
        ]);

        // Insertar en usuarios_equipos
        $stmtUsuarios = $pdo->prepare("
        INSERT INTO usuarios_equipos 
        (fk_assetname, user_status, last_user, job_title, status_change, cedula, Dongle, Carnet, LLave, Tipo_ID, fecha_salida)
        VALUES 
        (:fk_assetname, :user_status, :last_user, :job_title, :status_change, :cedula, :Dongle, :Carnet, :LLave, :Tipo_ID, :fecha_salida)
        ");

        $stmtUsuarios->execute([
            ':fk_assetname' => $data['assetname'],
            ':user_status' => $data['user_status'],
            ':last_user' => $data['last_user'],
            ':job_title' => $data['job_title'],
            ':status_change' => $data['status_change'],
            ':cedula' => $data['cedula'],
            ':Dongle' => $data['Dongle'],
            ':Carnet' => $data['Carnet'] ?? 'Pendiente',
            ':LLave' => $data['LLave'] ?? 'Pendiente',
            ':Tipo_ID' => $data['Tipo_ID'],
            ':fecha_salida' => $data['fecha_salida']
        ]);
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack(); // Revertir en caso de error
        throw $e;
    }
}

function processCSV($pdo, $filePath)
{
    $file = fopen($filePath, "r");
    $headers = fgetcsv($file);
    $warnings = [];

    // Normalizar nombres de columnas
    $normalizedHeaders = array_map(function ($header) {
        return strtolower(preg_replace('/[^a-z0-9]/', '', $header));
    }, $headers);

    include_once '../Configuration/Config_map.php';
    // Crear mapeo de columnas
    $columnMapping = [];
    foreach ($fieldMap as $dbField => $possibleNames) {
        foreach ($possibleNames as $name) {
            $normalizedName = strtolower(preg_replace('/[^a-z0-9]/', '', $name));
            $index = array_search($normalizedName, $normalizedHeaders);
            if ($index !== false) {
                $columnMapping[$dbField] = $index;
                break;
            }
        }
    }

    // Campos obligatorios
    $requiredFields = ['serial_number'];
    foreach ($requiredFields as $field) {
        if (!isset($columnMapping[$field])) {
            throw new Exception("Missing required column: " . $fieldMap[$field][0]);
        }
    }

    // Procesar filas
    while (($row = fgetcsv($file)) !== false) {
        $rowData = [];
        foreach ($columnMapping as $field => $index) {
            $value = trim($row[$index] ?? '');

            // Manejo especial para campos numéricos
            switch ($field) {
                case 'Dongle':
                case 'Carnet':
                    $rowData[$field] = ($value === '') ? null : (int)$value;
                    break;
                default:
                    $rowData[$field] = $value;
            }
        }

        // Validar campos obligatorios
        foreach ($requiredFields as $field) {
            if (empty($rowData[$field])) {
                $warnings[] = "Missing required field '{$field}' in row" . (ftell($file) + 1);
                continue 2;
            }
        }

        $originalAssetName = $rowData['assetname'] ?? '';
        // Insersion y validacion de registros con id unico en la columna 'assetname'
        try {
            $result = insertData($pdo, $rowData);
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                $warnings[] = "Duplicate Log: " . ($originalAssetName ?: 'Assetname') . ' - ' . $rowData['serial_number'];
            }
        }

        try {
            insertData($pdo, $rowData);
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) { // Código de error para duplicados
                $warnings[] = "Duplicate Log: " . $rowData['assetname'];
            } else {
                $warnings[] = "Error in rowfile " . (ftell($file) + 1) . ": " . $e->getMessage();
            }
        }
    }

    fclose($file);
    return $warnings;
}

function processTXT($pdo, $filePath)
{
    $data = json_decode(file_get_contents($filePath), true);
    $warnings = [];

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Error decodificando JSON: " . json_last_error_msg());
    }

    // Mismo mapeo de campos que en processCSV
    include_once '../Configuration/Config_map.php';

    foreach ($data as $record) {
        $rowData = [];

        // Normalizar claves del JSON
        $normalizedRecord = [];
        foreach ($record as $key => $value) {
            $normalizedKey = strtolower(preg_replace('/[^a-z0-9]/', '', $key));
            $normalizedRecord[$normalizedKey] = $value;
        }

        // Mapear campos
        foreach ($fieldMap as $dbField => $possibleNames) {
            foreach ($possibleNames as $name) {
                $normalizedName = strtolower(preg_replace('/[^a-z0-9]/', '', $name));
                if (isset($normalizedRecord[$normalizedName])) {
                    $rowData[$dbField] = trim($normalizedRecord[$normalizedName]);
                    break;
                }
            }
        }

        // Validar campos obligatorios
        $requiredFields = ['serial_number'];
        foreach ($requiredFields as $field) {
            if (empty($rowData[$field])) {
                $warnings[] = "Missing required field '$field' in record";
                continue 2;
            }
        }

        // Generar assetname si es necesario
        if (empty($rowData['assetname'])) {
            try {
                $rowData['assetname'] = generateUniqueAssetName($pdo);
            } catch (Exception $e) {
                $warnings[] = $e->getMessage();
                continue;
            }
        }

        // Insertar registro
        try {
            insertData($pdo, $rowData);
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                $warnings[] = "Duplicate Record: " . ($rowData['assetname'] ?? 'Unknown');
            } else {
                $warnings[] = "Error in Record: " . $e->getMessage();
            }
        }
    }

    return $warnings;
}

// Procesamiento principal
if (isset($_FILES['Proyeccion_garan']) && $_FILES['Proyeccion_garan']['error'] == 0) {
    try {
        if (!validateFile($_FILES['Proyeccion_garan'])) {
            throw new Exception("Invalid file format");
        }

        $filePath = $_FILES['Proyeccion_garan']['tmp_name'];
        $fileExtension = pathinfo($_FILES['Proyeccion_garan']['name'], PATHINFO_EXTENSION);

        $warnings = [];

        if ($fileExtension === 'csv') {
            $warnings = processCSV($pdo, $filePath);
        } elseif ($fileExtension === 'txt') {
            $warnings = processTXT($pdo, $filePath);
        }

        $_SESSION['warnings'] = array_slice($warnings, 0, 10); // Mostrar máximo 10 advertencias
        $_SESSION['success'] = "File processed successfully";
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
} else {
    $_SESSION['error'] = "Error uploading file";
}
ob_end_clean(); // Limpiar buffer de salida
header("Location: ../index.php");
exit();
