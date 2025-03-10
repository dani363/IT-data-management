
<?php

header('Content-Type: application/json');

if (isset($_GET['assetname'])) {
    $assetname = $_GET['assetname'];

    try {
        $conn = new PDO("mysql:host=localhost; dbname=garantias", "root", "1213123Shape");
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM equipos WHERE UPPER(TRIM(assetname)) = UPPER(TRIM(:assetname))");
        $stmt->bindParam(':assetname', $assetname, PDO::PARAM_STR);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        // Depuración temporal: muestra el conteo en bruto
        echo json_encode([
            "exists" => $row['count'] > 0,
            "debug_count" => $row['count'], // <-- Depuración para confirmar el resultado
            "input_serial" => $assetname // <-- Depuración para verificar el número recibido
        ]);

    } catch (PDOException $e) {
        echo json_encode(["error" => "Error de conexión a la base de datos: " . $e->getMessage()]);
    }
    exit;
}

?>