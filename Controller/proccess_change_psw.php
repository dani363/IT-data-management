<?php
session_start();
include '../Configuration/Connection.php';


// Función helper para debugging
function debugLog($message, $data = null)
{
    $_SESSION['debug_messages'][] = [
        'message' => $message,
        'data' => $data !== null ? print_r($data, true) : null
    ];
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $currentPassword = trim($_POST['currentPassword'] ?? '');
        $newPassword = trim($_POST['newPassword'] ?? '');
        $confirmPassword = trim($_POST['confirmPassword'] ?? '');

        debugLog("Contraseña actual recibida", $currentPassword);

        // Validar campos vacíos
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $_SESSION['error'] = "All fields are required.";
            header("Location: ../Vista/Int_changePsw.php");
            exit;
        }

        // Obtener la contraseña almacenada
        $stmt = $pdo->prepare("SELECT clave_admin FROM configuracion_sistema WHERE id = 1");
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row || !isset($row['clave_admin'])) {
            debugLog("No se encontró la contraseña en la BD");
            $_SESSION['error'] = "Error retrieving current password.";
            header("Location: ../Vista/Int_changePsw.php");
            exit;
        }

        $storedPassword = $row['clave_admin'];
        debugLog("Contraseña almacenada en BD", $storedPassword);

        // Mejor método para detectar si es un hash
        $isHashed = (
            strlen($storedPassword) == 60 && // Longitud típica de un hash bcrypt
            preg_match('/^\$2[ayb]\$[0-9]{2}\$[A-Za-z0-9\.\/]{53}$/', $storedPassword) // Formato bcrypt
        );

        debugLog("¿Es hash?", $isHashed ? "Sí" : "No");

        // Verificar la contraseña
        $passwordIsValid = false;
        if ($isHashed) {
            debugLog("Verificando como hash");
            $passwordIsValid = password_verify($currentPassword, $storedPassword);
        } else {
            debugLog("Verificando como texto plano");
            $passwordIsValid = ($currentPassword === $storedPassword);
        }

        debugLog("¿Contraseña válida?", $passwordIsValid ? "Sí" : "No");

        if (!$passwordIsValid) {
            debugLog("invalid password");
            $_SESSION['error'] = "The current password is incorrect.";
            header("Location: ../Vista/Int_changePsw.php");
            exit;
        }

        // Verificar coincidencia entre la nueva contraseña y su confirmación
        if ($newPassword !== $confirmPassword) {
            $_SESSION['error'] = "New passwords do not match.";
            header("Location: ../Vista/Int_changePsw.php");
            exit;
        }
        // Generar hash de la nueva contraseña
        $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        debugLog("Nuevo hash generado", $newPasswordHash);

        // Actualizar la contraseña en la base de datos
        $updateStmt = $pdo->prepare("UPDATE configuracion_sistema SET clave_admin = :newPassword WHERE id = 1");
        $updateStmt->execute(['newPassword' => $newPasswordHash]);

        if ($updateStmt->rowCount() > 0) {
            debugLog("Contraseña actualizada exitosamente");
            $_SESSION['success'] = "Password changed successfully.";
        } else {
            debugLog("No se actualizó la contraseña");
            $_SESSION['error'] = "No changes were made to the password.";
        }
        header("Location: ../Vista/Int_changePsw.php");
        exit;
    }
} catch (PDOException $e) {
    debugLog("Error de PDO", $e->getMessage());
    $_SESSION['error'] = "An internal error occurred. Please try again later.";
    header("Location: ../Vista/Int_changePsw.php");
    exit;
}
?>