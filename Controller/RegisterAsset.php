<?php

session_start();

include "../Configuration/Connection.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Recuperar datos del formulario
    $assetname = $_POST['assetname'] ?? null;
    $serial_number = $_POST['serial'] ?? null;
    $purchase_country = $_POST['compra'] ?? null;
    $warranty_enddate = $_POST['garantia'] ?? null;
    $new_laptop = $_POST['newl'];
    $user_status = $_POST['usrStts'] ?? null;
    $last_user = $_POST['nombre_usuario'] ?? null;
    $job_title = $_POST['job_title'];
    $status_change = $_POST['status_change'] ?? null;
    $cedula = $_POST['id'] ?? null;
    $carnet = $_POST['selectCard'] ?? null;
    $llave = $_POST['selectKEY'] ?? null;
    $tipoID = $_POST['type_id'] ?? null;

    // Validar estado de expiración en base a la fecha de garantía
    $fecha_actual = date('Y-m-d');
    $expired = (strtotime($warranty_enddate) <= strtotime($fecha_actual)) ? 'yes' : 'no';

    // Inicializar arrays para campos y valores dinámicos
    $dynamic_fields_equipos = [];
    $dynamic_values_equipos = [];
    $dynamic_fields_usuarios_equipos = [];
    $dynamic_values_usuarios_equipos = [];

    // Recoger los campos dinámicos de la sesión
    $new_fields = $_SESSION['new_reg_fields'] ?? [];
    $new_field_types = $_SESSION['new_fieldR_types'] ?? [];

    foreach ($new_fields as $field_name) {
        $field_value = $_POST[$field_name] ?? null;
        $field_type = $new_field_types[$field_name] ?? null;

        if ($field_value !== null) {
            // Verificar si el campo pertenece a la tabla 'equipos' o 'usuarios_equipos'
            $query = "SHOW COLUMNS FROM equipos LIKE :field_name";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':field_name', $field_name, PDO::PARAM_STR);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $dynamic_fields_equipos[] = $field_name;
                $dynamic_values_equipos[$field_name] = $field_value;
            } else {
                $query = "SHOW COLUMNS FROM usuarios_equipos LIKE :field_name";
                $stmt = $pdo->prepare($query);
                $stmt->bindParam(':field_name', $field_name, PDO::PARAM_STR);
                $stmt->execute();
                if ($stmt->rowCount() > 0) {
                    $dynamic_fields_usuarios_equipos[] = $field_name;
                    $dynamic_values_usuarios_equipos[$field_name] = $field_value;
                }
            }
        }
    }

    try {
        // Iniciar una transacción
        $pdo->beginTransaction();

        // registro tabla equipos
        $sql_equipos = "INSERT INTO equipos (assetname, serial_number, purchase_country, warranty_enddate, expired, new_laptop";
        foreach ($dynamic_fields_equipos as $field_name) {
            $sql_equipos .= ", $field_name";
        }
        $sql_equipos .= ") VALUES (:assetname, :serial_number, :purchase_country, :warranty_enddate, :expired, :new_laptop";
        foreach ($dynamic_fields_equipos as $field_name) {
            $sql_equipos .= ", :$field_name";
        }
        $sql_equipos .= ")";

        $stmt_equipos = $pdo->prepare($sql_equipos);
        $stmt_equipos->bindParam(':assetname', $assetname);
        $stmt_equipos->bindParam(':serial_number', $serial_number);
        $stmt_equipos->bindParam(':purchase_country', $purchase_country);
        $stmt_equipos->bindParam(':warranty_enddate', $warranty_enddate);
        $stmt_equipos->bindParam(':expired', $expired);
        $stmt_equipos->bindParam(':new_laptop', $new_laptop);

        foreach ($dynamic_values_equipos as $field_name => $field_value) {
            $stmt_equipos->bindParam(":$field_name", $field_value, PDO::PARAM_STR);
        }
        $stmt_equipos->execute();

        // Insertar en la tabla `usuarios_equipos`
        $sql_usuarios_equipos = "INSERT INTO usuarios_equipos (fk_assetname, user_status, last_user, job_title, status_change, cedula, Carnet, LLave, Tipo_ID";
        foreach ($dynamic_fields_usuarios_equipos as $field_name) {
            $sql_usuarios_equipos .= ", $field_name";
        }
        $sql_usuarios_equipos .= ") VALUES (:assetname, :user_status, :last_user, :job_title, :status_change, :cedula, :carnet, :llave, :tipo_id";
        foreach ($dynamic_fields_usuarios_equipos as $field_name) {
            $sql_usuarios_equipos .= ", :$field_name";
        }
        $sql_usuarios_equipos .= ")";

        $stmt_usuarios_equipos = $pdo->prepare($sql_usuarios_equipos);
        $stmt_usuarios_equipos->bindParam(':assetname', $assetname);
        $stmt_usuarios_equipos->bindParam(':user_status', $user_status);
        $stmt_usuarios_equipos->bindParam(':last_user', $last_user);
        $stmt_usuarios_equipos->bindParam(':job_title', $job_title);
        $stmt_usuarios_equipos->bindParam(':status_change', $status_change);
        $stmt_usuarios_equipos->bindParam(':cedula', $cedula);
        $stmt_usuarios_equipos->bindParam(':carnet', $carnet);
        $stmt_usuarios_equipos->bindParam(':llave', $llave);
        $stmt_usuarios_equipos->bindParam(':tipo_id', $tipoID);

        foreach ($dynamic_values_usuarios_equipos as $field_name => $field_value) {
            $stmt_usuarios_equipos->bindParam(":$field_name", $field_value, PDO::PARAM_STR);
        }
        $stmt_usuarios_equipos->execute();

        // Confirmar la transacción
        $pdo->commit();
        $_SESSION['success'] = "Registration successful.";
        header("location: ../index.php");
    } catch (PDOException $e) {
        // Revert the transaction in case of error
        $pdo->rollBack();
        $_SESSION['error'] = "Error during registration: " . $e->getMessage();
        header("location: ../index.php");
    }
}
?>