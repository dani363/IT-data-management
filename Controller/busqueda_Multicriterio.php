<?php
include "./Configuration/Connection.php";
// Iniciar consulta base y array de condiciones
$sql = "SELECT *
                    FROM equipos
            INNER JOIN usuarios_equipos
        ON equipos.assetname = usuarios_equipos.fk_assetname";
$where = [];
$params = [];

//Busqueda de fechas multicirterio
function buildDateSearchQuery($searchParams)
{
    $where = [];
    $params = [];

    $fecha_inicio = !empty($searchParams['search_fecha_inicio']) ? $searchParams['search_fecha_inicio'] : null;
    $fecha_fin = !empty($searchParams['search_fecha_fin']) ? $searchParams['search_fecha_fin'] : null;

    // Validar si las fechas tienen un formato correcto antes de procesarlas
    if ($fecha_inicio && !DateTime::createFromFormat('Y-m-d', date('Y-m-d', strtotime($fecha_inicio)))) {
        $_SESSION['error'] = "start date not valid: " . $fecha_inicio;
        $fecha_inicio = null;
    }

    if ($fecha_fin && !DateTime::createFromFormat('Y-m-d', date('Y-m-d', strtotime($fecha_fin)))) {
        $_SESSION['error'] = "end date not valid: " . $fecha_fin;
        $fecha_inicio = null;
        $fecha_fin = null;
    }

    // Validación base para asegurar que warranty_enddate tiene un formato correcto
    $baseValidation = "warranty_enddate IS NOT NULL 
                        AND warranty_enddate <> '' 
                        AND STR_TO_DATE(warranty_enddate, '%m/%d/%Y') IS NOT NULL";

    if ($fecha_inicio && $fecha_fin) {
        $where[] = "STR_TO_DATE(warranty_enddate, '%m/%d/%Y') BETWEEN :fecha_inicio AND :fecha_fin 
                    AND " . $baseValidation;
        $params[':fecha_inicio'] = date('Y-m-d', strtotime($fecha_inicio));
        $params[':fecha_fin'] = date('Y-m-d', strtotime($fecha_fin));
    } elseif ($fecha_inicio) {
        $where[] = "STR_TO_DATE(warranty_enddate, '%m/%d/%Y') >= :fecha_inicio 
                    AND " . $baseValidation;
        $params[':fecha_inicio'] = date('Y-m-d', strtotime($fecha_inicio));
    } elseif ($fecha_fin) {
        $where[] = "STR_TO_DATE(warranty_enddate, '%m/%d/%Y') <= :fecha_fin 
                    AND " . $baseValidation;
        $params[':fecha_fin'] = date('Y-m-d', strtotime($fecha_fin));
    }

    error_log("SQL Query: " . implode(" AND ", $where));

    return [
        'where' => $where,
        'params' => $params
    ];
}

// Ejemplo de uso:
$searchResult = buildDateSearchQuery($_GET);
$where = $searchResult['where'];
$params = $searchResult['params'];

// Filtro de nombre de activo
if (!empty($_GET['search_assetname'])) {
    $assetname = trim($_GET['search_assetname']); // Elimina espacios en blanco al inicio y al final
}

if (!empty($assetname)) {
    $where[] = "LOWER(assetname) LIKE LOWER(:assetname)";
    $params[':assetname'] = '%' . $assetname . '%';
}

// Filtro de número de serie
if (!empty($_GET['search_serial'])) {
    $serial = trim($_GET['search_serial']); // Elimina espacios en blanco al inicio y al final
    if (!empty($serial)) {
        $where[] = "LOWER(serial_number) LIKE LOWER(:serial)";
        $params[':serial'] = '%' . $serial . '%';
    }
}
// Filtro de cédula
if (!empty($_GET['search_cedula'])) {
    $cedula = trim($_GET['search_cedula']);
    if (!empty($cedula)) {
        $where[] = "LOWER(cedula) LIKE :cedula";
        $params[':cedula'] = '%' . $cedula . '%';
    }
}
// Filtro de usuario
if (!empty($_GET['search_user'])) {
    $user = trim($_GET['search_user']); // Elimina espacios en blanco al inicio y al final
    if (!empty($user)) {
        $where[] = "LOWER(last_user) LIKE LOWER(:user)";
        $params[':user'] = '%' . $user . '%';
    }
} else {
    unset($params[':user']);
}
if (!empty($_GET['search_user'])) {
    $user = trim($_GET['search_user']); // Elimina espacios en blanco al inicio y al final
    if (!empty($user)) {
        $where[] = "LOWER(last_user) LIKE LOWER(:user)";
        $params[':user'] = '%' . $user . '%';
    }
}

// Filtro de cambio de estado
if (!empty($_GET['search_status_change'])) {
    $status_change = trim($_GET['search_status_change']); // Elimina espacios en blanco al inicio y al final
    if (!empty($status_change)) {
        $where[] = "LOWER(status_change) LIKE LOWER(:status_change)";
        $params[':status_change'] = '%' . $status_change . '%';
    }
}

// Filtro de estado de usuario (selección múltiple)
if (!empty($_GET['search_user_status'])) {
    $filtered_statuses = array_filter($_GET['search_user_status'], function ($status) {
        return $status != 0;
    });
    //Condicion en caso de que el estado del usuario no este vacio
    if (!empty($filtered_statuses)) {
        $user_status_conditions = [];
        foreach ($filtered_statuses as $index => $status) {
            $param_name = ":status_$index";
            $user_status_conditions[] = "user_status = $param_name";
            $params[$param_name] = $status;
        }
        $where[] = '(' . implode(' OR ', $user_status_conditions) . ')';
    }
}

// Agregar condiciones al SQL
if (count($where) > 0) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
// Preparar y ejecutar la consulta
$stmt = $pdo->prepare($sql);
// Asociar parámetros con valores
if (!empty($_GET['search_assetname'])) {
    $stmt->bindValue(':assetname', '%' . $_GET['search_assetname'] . '%');
}
if (!empty($_GET['search_serial'])) {
    $stmt->bindValue(':serial', '%' . $_GET['search_serial'] . '%');
}
if (!empty($_GET['search_cedula'])) {
    $stmt->bindValue(':cedula', '%' . $_GET['search_cedula'] . '%');
}
if (!empty($_GET['search_user'])) {
    $stmt->bindValue(':user', '%' . $_GET['search_user'] . '%');
}
if (!empty($_GET['search_status_change'])) {
    $stmt->bindValue(':status_change', '%' . $_GET['search_status_change'] . '%');
}
if (!empty($_GET['search_user_status'])) {
    foreach ($params as $param_name => $value) {
        $stmt->bindValue($param_name, $value);
    }
}
// Ejecutar y obtener resultados
$stmt->execute();
$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>