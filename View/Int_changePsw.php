<?php
session_start();

$tiempo_inactivo = 4600;

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $tiempo_inactivo) {
    // Si la sesión ha expirado
    setcookie('error_message', 'Sesión expirada. Vuelve a iniciar sesión.', time() + 30, '/');
    header("Location: ../index.php");
    include_once '../Controller/Cerrar_sesion.php';
    exit();
}

// Verificar si el usuario es admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    include_once '../Controller/Cerrar_sesion.php';
    header("Location: ../index.php");
    exit("Acceso denegado.");
}
// Mensajes de Usuario
if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['error']) . '</div>';
    unset($_SESSION['error']); // Limpia el mensaje después de mostrarlo
}
if (isset($_SESSION['success'])) {
    echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success']) . '</div>';
    unset($_SESSION['success']); // Limpia el mensaje después de mostrarlo
}

$_SESSION['last_activity'] = time();

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Change</title>
    <link rel="shortcut icon" href="../Configuracion/logo.ico" type="image/x-icon">
    <link href="../Configuration/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <script src="./Configuration/JQuery/jquery-3.7.1.js"></script>
    <link rel="stylesheet" href="./Css/dark-mode.css">
    <style>
        .form-container {
            margin-top: 50px;
            padding: 30px;
            border: 1px solid #ccc;
            border-radius: 10px;
            background-color: #f8f9fa;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div id="FORMchange" class="form-container shadow">
                    <!-- Detalles de la pagina -->
                    <div class="text-center mb-4">
                        <h1>Settings</h1>
                    </div>

                    <div class="text-center mb-4">
                        <h2>Password Change</h2>
                    </div>

                    <?php
                    if (isset($_SESSION['error'])) {
                        echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
                        unset($_SESSION['error']);
                    }
                    if (isset($_SESSION['success'])) {
                        echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
                        unset($_SESSION['success']);
                    }
                    ?>

                    <?php if (!empty($_SESSION['debug_messages'])): ?>
                        <div class="debug-panel">
                            <?php if (!empty($_SESSION['debug_messages'])): ?>
                                <?php foreach ($_SESSION['debug_messages'] as $debug): ?>
                                    <div class="debug-item">
                                        <div class="debug-message"><?= htmlspecialchars($debug['message']) ?></div>
                                        <?php if ($debug['data'] !== null): ?>
                                            <div class="debug-data"><?= htmlspecialchars($debug['data']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                                <?php unset($_SESSION['debug_messages']); ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>


                    <!-- Formulario para cambiar la contraseña -->
                    <form action="../Controller/proccess_change_psw.php" method="POST" id="changePasswordForm"
                        onsubmit="return validateForm()">
                        <div class="form-group">
                            <label for="currentPassword">Current Password</label>
                            <input type="password" class="form-control" id="currentPassword" name="currentPassword"
                                required>
                        </div>

                        <div class="form-group">
                            <label for="newPassword">New Password</label>
                            <input type="password" class="form-control" id="newPassword" name="newPassword" required>
                        </div>
                        <div class="form-group">
                            <label for="confirmPassword">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirmPassword" name="confirmPassword"
                                required>
                        </div>
                        <br>
                        <button type="submit" class="btn btn-secondary ">Change Password</button>

                    </form>

                    <br>
                    <br>
                    <!-- Formulario para cambiar la dirección de red -->
                    <h2 class="text-center mb-4">Network Address Change</h2>
                    <form action="../Controller/ChangeAddrNetwork.php" method="post" id="networkformconifg"
                        onsubmit="return confirmNetworkChange()">
                        <div class="form-group">
                            <label for="NetworkAddress">New Network Address: </label>
                            <input type="text" class="form-control" id="NetworkAddress" name="NetworkAddress" required
                                autocomplete="off" onfocus="showNetworkAddressInfo()">
                            <div id="networkAddressInfo" class="alert alert-warning mt-2" style="display: none;">
                                This function does not work at this moment
                                <br>
                            </div>
                            <script>
                                function showNetworkAddressInfo() {
                                    document.getElementById('networkAddressInfo').style.display = 'block';
                                }
                            </script>
                        </div>
                        <br>
                        <div class="my-2">
                            <button type="submit" class="btn btn-secondary" disabled>Change Network Address</button>
                            <a href="../Admin/index_admin.php" class="btn btn-danger me-2">Return</a>

                            <script>
                                document.getElementById('NetworkAddress').addEventListener('input', function (e) {
                                    const input = e.target;
                                    const value = input.value;

                                    // Remover caracteres no permitidos (solo permitir números, puntos y ":")
                                    const sanitizedValue = value.replace(/[^0-9a-fA-F:.]/g, '');
                                    input.value = sanitizedValue;

                                    // Limitar la longitud máxima a 39 caracteres (longitud máxima de una dirección IPv6)
                                    if (sanitizedValue.length > 39) {
                                        input.value = sanitizedValue.slice(0, 39);
                                    } else {
                                        input.value = sanitizedValue;
                                    }

                                    // Validar la dirección de red
                                    if (isValidNetworkAddress(sanitizedValue)) {
                                        input.setCustomValidity('');
                                    } else {
                                        input.setCustomValidity('Invalid network address format.');
                                    }
                                });

                                function isValidNetworkAddress(address) {
                                    const ipv4Pattern =
                                        /^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/;
                                    const ipv6Pattern = /^([0-9a-fA-F]{1,4}:){7}([0-9a-fA-F]{1,4}|:)$/;

                                    return ipv4Pattern.test(address) || ipv6Pattern.test(address);
                                }
                            </script>

                        </div>
                    </form>
                </div>
                <script>
                    function validateForm() {
                        var newPassword = document.getElementById("newPassword").value;
                        var confirmPassword = document.getElementById("confirmPassword").value;
                        var passwordPattern = /^(?=.*[!@#$%^&*(),.?":{}|<>]).{8,}$/;

                        if (newPassword !== confirmPassword) {
                            alert("Passwords do not match")
                            return false;
                        }

                        if (!passwordPattern.test(newPassword)) {
                            alert(
                                "The password must contain a minimum of 8 characters, at least 1 special character and numbers"
                            );
                            return false;
                        }
                        return true;
                    }
                </script>
                <!-- Botón de Cerrar Sesión -->
                <div class="text-center mt-4">
                    <button class="btn btn-danger" onclick="confirmLogout()">Log Out</button>
                </div>

                <script>
                    function confirmLogout() {
                        if (confirm("Are you sure you want to log out?")) {
                            window.location.href = "../Controller/Cerrar_sesion.php";
                        }
                    }
                </script>
            </div>
        </div>
    </div>
    </div>
    <script src="../Configuration/JQuery/jquery-3.7.1.js"></script>
    <script src="../Configuration/bootstrap/js/bootstrap.min.js"></script>
</body>
<footer class="align-items-center text-center mt-5">
    <!--Pied de Pagina-->
    <p>&copy; <?php echo date("Y"); ?> - All rights reserved.</p>
    <p>Page created on: <?php echo date("F j, Y", filectime(__FILE__)); ?></p>
    <p>Page name: <?php echo basename(__FILE__); ?></p>
</footer>

</html>