<?php
session_start();

include "../Configuration/Connection.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_usuario = $_POST['nombre_usuario'] ?? null;
    $serial = $_POST['serial'] ?? null;
    $compra = $_POST['compra'] ?? null;
    $garantia = $_POST['garantia'] ?? null;
    $newl = $_POST['newl'];
    $usrStts = $_POST['usrStts'] ?? null;
    $job_title = $_POST['job_title'];
    $stats_change = $_POST['status_change'] ?? null;
    $cedula = $_POST['id'] ?? null;
    $carnet = $_POST['selectCard'] ?? null;
    $llave = $_POST['selectKEY'] ?? null;
    $tipoID = $_POST['selectID'] ?? null;
    $assetname = $_POST['assetname'] ?? null;
    $fk_assetname = $_POST['fk_assetname'] ?? null;

    // Check if assetname and fk_assetname are already set
    if ($assetname !== null) {
        // Do not update assetname if it already exists
        unset($_POST['assetname']);
    }
    if ($fk_assetname !== null) {
        // Do not update fk_assetname if it already exists
        unset($_POST['fk_assetname']);
    }

    $fecha_actual = date('Y-m-d');
    $expira = (strtotime($garantia) < strtotime($fecha_actual)) ? 'yes' : 'no';

    // Mejorada la gestión de campos dinámicos
    $equipos_fields = [];
    $usuarios_equipos_fields = [];
    $bind_params = [];

    // Obtener la estructura de las tablas
    $tables_structure = [
        'equipos' => [],
        'usuarios_equipos' => []
    ];

    // Obtener columnas de la tabla equipos
    $stmt = $pdo->query("SHOW COLUMNS FROM equipos");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $tables_structure['equipos'][] = $row['Field'];
    }

    // Obtener columnas de la tabla usuarios_equipos
    $stmt = $pdo->query("SHOW COLUMNS FROM usuarios_equipos");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $tables_structure['usuarios_equipos'][] = $row['Field'];
    }

    // Procesar campos dinámicos
    $new_fields = $_SESSION['new_upd_fields'] ?? [];
    $new_field_types = $_SESSION['new_fieldU_types'] ?? [];

    foreach ($new_fields as $field_name) {
        $field_value = $_POST[$field_name] ?? null;
        
        if ($field_value !== null) {
            // Determinar a qué tabla pertenece el campo
            if (in_array($field_name, $tables_structure['equipos'])) {
                $equipos_fields[] = "$field_name = :$field_name";
                $bind_params[$field_name] = $field_value;
            } elseif (in_array($field_name, $tables_structure['usuarios_equipos'])) {
                $usuarios_equipos_fields[] = "$field_name = :$field_name";
                $bind_params[$field_name] = $field_value;
            }
        }
    }

 if ($nombre_usuario) {
    try {
        // Iniciar una transacción
        $pdo->beginTransaction();

        // Desactivar la verificación de claves foráneas
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");

        // Obtener el valor actual de `assetname` desde la sesión
        $old_assetname = $_SESSION['asset_data']['assetname'];

        // Construir la consulta SQL para actualizar `equipos`
        $sql_update_equipos = "UPDATE equipos SET
                              assetname = :new_assetname,
                              serial_number = :serial_number,
                              purchase_country = :purchase_country,
                              warranty_enddate = :warranty_enddate,
                              expired = :expired,
                              new_laptop = :new_laptop";

        // Añadir campos dinámicos para la tabla `equipos`
        if (!empty($equipos_fields)) {
            $sql_update_equipos .= ", " . implode(", ", $equipos_fields);
        }

        $sql_update_equipos .= " WHERE assetname = :old_assetname";

        // Preparar y ejecutar la consulta para `equipos`
        $stmt_update_equipos = $pdo->prepare($sql_update_equipos);
        $stmt_update_equipos->bindValue(':new_assetname', $assetname, PDO::PARAM_STR);
        $stmt_update_equipos->bindValue(':old_assetname', $old_assetname, PDO::PARAM_STR);
        $stmt_update_equipos->bindValue(':serial_number', $serial, PDO::PARAM_STR);
        $stmt_update_equipos->bindValue(':purchase_country', $compra, PDO::PARAM_STR);
        $stmt_update_equipos->bindValue(':warranty_enddate', $garantia, PDO::PARAM_STR);
        $stmt_update_equipos->bindValue(':expired', $expira, PDO::PARAM_STR);
        $stmt_update_equipos->bindValue(':new_laptop', $newl, PDO::PARAM_STR);

        // Vincular parámetros dinámicos para `equipos`
        foreach ($bind_params as $param => $value) {
            if (in_array($param, $tables_structure['equipos'])) {
                $stmt_update_equipos->bindValue(":$param", $value, PDO::PARAM_STR);
            }
        }

        // Ejecutar la consulta para `equipos`
        if ($stmt_update_equipos->execute()) {
            // Construir la consulta SQL para actualizar `usuarios_equipos`
            $sql_update_usuarios_equipos = "UPDATE usuarios_equipos SET
                                           fk_assetname = :new_assetname,
                                           user_status = :user_status,
                                           last_user = :last_user,
                                           job_title = :job_title,
                                           status_change = :status_change,
                                           cedula = :cedula,
                                           Carnet = :carnet,
                                           LLave = :llave,
                                           Tipo_ID = :tipoID";

            // Añadir campos dinámicos para la tabla `usuarios_equipos`
            if (!empty($usuarios_equipos_fields)) {
                $sql_update_usuarios_equipos .= ", " . implode(", ", $usuarios_equipos_fields);
            }

            $sql_update_usuarios_equipos .= " WHERE fk_assetname = :old_assetname";

            // Preparar y ejecutar la consulta para `usuarios_equipos`
            $stmt_update_usuarios_equipos = $pdo->prepare($sql_update_usuarios_equipos);
            $stmt_update_usuarios_equipos->bindValue(':new_assetname', $assetname, PDO::PARAM_STR);
            $stmt_update_usuarios_equipos->bindValue(':old_assetname', $old_assetname, PDO::PARAM_STR);
            $stmt_update_usuarios_equipos->bindValue(':user_status', $usrStts, PDO::PARAM_STR);
            $stmt_update_usuarios_equipos->bindValue(':last_user', $nombre_usuario, PDO::PARAM_STR);
            $stmt_update_usuarios_equipos->bindValue(':job_title', $job_title, PDO::PARAM_STR);
            $stmt_update_usuarios_equipos->bindValue(':status_change', $stats_change, PDO::PARAM_STR);
            $stmt_update_usuarios_equipos->bindValue(':cedula', $cedula, PDO::PARAM_STR);
            $stmt_update_usuarios_equipos->bindValue(':carnet', $carnet, PDO::PARAM_STR);
            $stmt_update_usuarios_equipos->bindValue(':llave', $llave, PDO::PARAM_STR);
            $stmt_update_usuarios_equipos->bindValue(':tipoID', $tipoID, PDO::PARAM_STR);

            // Vincular parámetros dinámicos para `usuarios_equipos`
            foreach ($bind_params as $param => $value) {
                if (in_array($param, $tables_structure['usuarios_equipos'])) {
                    $stmt_update_usuarios_equipos->bindValue(":$param", $value, PDO::PARAM_STR);
                }
            }

            // Ejecutar la consulta para `usuarios_equipos`
            if ($stmt_update_usuarios_equipos->execute()) {
                // Confirmar la transacción
                $pdo->commit();
                $_SESSION["success"] = "Correctly updated data.";
            } else {
                // Revertir la transacción en caso de error
                $pdo->rollBack();
                $errorInfo = $stmt_update_usuarios_equipos->errorInfo();
                $_SESSION["error"] = "Error updating usuarios_equipos: " . $errorInfo[2];
            }
        } else {
            // Revertir la transacción en caso de error
            $pdo->rollBack();
            $errorInfo = $stmt_update_equipos->errorInfo();
            $_SESSION["error"] = "Error updating equipos: " . $errorInfo[2];
        }

        // Reactivar la verificación de claves foráneas
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

        // Redirigir al usuario
        header("Location: ../index.php");
        exit();
    } catch (PDOException $e) {
        // Revertir la transacción en caso de error
        $pdo->rollBack();
        $_SESSION["error"] = "Database error: " . $e->getMessage();
        header("Location: ../index.php");
        exit();
    }
} else {
    $_SESSION["error"] = "Faltan datos obligatorios.";
    header("Location: ../index.php");
    exit();
}
} else {
    $_SESSION["error"] = "Acceso no válido.";
    header("Location: ../index.php");
}
