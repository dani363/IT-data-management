<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('CSRF token validation failed');
    }

    $fields = $_POST['addmfields'];

    // Agregar mensajes de depuración
    if (empty($fields)) {
        $_SESSION['error'] = 'No fields selected';
        header('Location: index_admin.php');
        exit();
    }

    // Agregar mensajes de validacion si no esta dentro del array
    if (!is_array($fields)) {
        $_SESSION['error'] = 'Fields should be an array';
        header('Location: index_admin.php');
        exit();
    }

    // Lista de campos prohibidos
    $prohibitedFields = [
        'assetname',
        'serial_number',
        'purchase_country',
        'warranty_enddate',
        'expired',
        'new_laptop',
        'fk_id',
        'fk_assetname',
        'user_status',
        'last_user',
        'job_title',
        'status_change',
        'cedula'
    ];
    // Validar campos prohibidos
    foreach ($fields as $field) {
        if (in_array($field, $prohibitedFields)) {
            $_SESSION['error'] = 'Field ' . htmlspecialchars($field) . ' is not allowed to be added';
            header('Location: index_admin.php');
            exit();
        }
    }

    if ($fields && is_array($fields)) {
        include '../Configuration/Connection.php';

        // Obtener los tipos de datos de las columnas seleccionadas
        $fieldTypes = [];
        foreach ($fields as $field) {
            // Obtener el tipo de dato de la columna seleccionada
            $sql = "SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE COLUMN_NAME = :field AND TABLE_NAME IN ('equipos', 'usuarios_equipos')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['field' => $field]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                // Si el campo es válido, guardarlo en la sesión
                $fieldTypes[$field] = $row['DATA_TYPE'];
            } else {
                // Si no se encuentra el tipo de dato, mostrar un mensaje de error
                $_SESSION['error'] = 'Field type not found for ' . htmlspecialchars($field);
                header('Location: index_admin.php');
                exit();
            }
        }
        // Guardar los campos seleccionados en la sesión
        $_SESSION['selected_fields'] = $fields;
        $_SESSION['selected_field_types'] = $fieldTypes;

        // Mostrar un mensaje de éxito
        $_SESSION['success'] = 'Fields added to the table successfully';
    } else {
        $_SESSION['error'] = 'Invalid fields selected';
    }
    header('Location: index_admin.php');
    exit();
}
?>