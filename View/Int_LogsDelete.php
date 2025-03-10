<?php
ob_start();
session_start();

// Tiempo de expiración de la sesión (en segundos)
$tiempo_inactivo = 4600;

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $tiempo_inactivo) {
    setcookie('error_message', 'Sesión expirada. Vuelve a iniciar sesión.', time() + 30, '/');
    header("Location: ../index.php");
    include_once '../Controller/Cerrar_sesion.php';
    exit();
}

$_SESSION['last_activity'] = time();

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    include_once '../Controller/Cerrar_sesion.php';
    header("Location: ../index.php");
    exit("Acceso denegado.");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backups</title>
    <link rel="shortcut icon" href="../Configuration/logo.ico" type="image/x-icon">
    <link href="../Configuration/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Configuration/DataTables/datatables.min.css">
    <link rel="stylesheet" href="./Css/dark-mode.css">
    <link rel="stylesheet" href="../Configuration/JQuery/all.min.css">
    <link rel="stylesheet" href="../Configuration/JQuery/fontawesome.min.css">
</head>

<body>
    <div class="container mt-5">
        <br>
        <h2 class="mb-4 my-2 text-center">Log Files</h2>
        <div id="FORMeditingLog" class="table-responsive">
            <table id="mainTable" class="shadow-lg table table-bordered table-striped fade-in">
                <thead>
                    <tr>
                        <th>File Name</th>
                        <th>Total Size (Formatted):

                            <?php
                            $logDir = __DIR__ . '/../Model/Logs';
                            $folderSize = 0;
                            $files = scandir($logDir);
                            foreach ($files as $file) {
                                if ($file !== '.' && $file !== '..') {
                                    $filePath = $logDir . '/' . $file;
                                    $folderSize += filesize($filePath);
                                }
                            }
                            function formatFileSize($size)
                            {
                                $units = ['B', 'KB', 'MB', 'GB', 'TB'];
                                $index = 0;
                                while ($size >= 1024 && $index < count($units) - 1) {
                                    $size /= 1024;
                                    $index++;
                                }
                                return round($size, 2) . ' ' . $units[$index];
                            }
                            $formattedSize = formatFileSize($folderSize);

                            echo '(' . $formattedSize . ')';
                            ?>
                        </th>
                        <th>Last Modified</th>
                        <th>Download</th>
                        <th>View</th>
                        <th>Delete</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $logDir = __DIR__ . '/../Model/Logs';
                    if (is_dir($logDir)) {
                        $files = scandir($logDir);
                        foreach ($files as $file) {
                            if ($file !== '.' && $file !== '..') {
                                $filePath = $logDir . '/' . $file;
                                echo '<tr>';
                                echo '<td>' . htmlspecialchars($file) . '</td>';
                                echo '<td>' . round(filesize($filePath) / 1024, 2) . ' KB' . '</td>';
                                echo '<td>' . date("F d Y H:i:s.", filemtime($filePath)) . '</td>';
                                echo '<td title="Download"><a href="../Model/Logs/' . htmlspecialchars($file) . '" download class="btn btn-secondary btn-sm"><i class="fas fa-download"></i></a></td>';
                                echo '<td title="View"><a href="Int_LogsDelete.php?view=' . urlencode($file) . '" class="btn btn-secondary btn-sm"><i class="fas fa-eye"></i></a></td>';
                                echo '<td title="Delete"><a href="Int_LogsDelete.php?delete=' . urlencode($file) . '" class="btn btn-danger btn-sm" onclick="return confirm(\'Are you sure you want to delete this file?\')"><i class="fas fa-trash-alt"></i></a></td>';
                                echo '</tr>';
                            }
                        }
                    } else {
                        echo '<tr><td colspan="6">Log directory not found.</td></tr>';
                    }
                    // Handle file deletion
                    if (isset($_GET['delete'])) {
                        $fileToDelete = $logDir . '/' . $_GET['delete'];
                        if (file_exists($fileToDelete)) {
                            unlink($fileToDelete);
                            header("Location: Int_LogsDelete.php");
                            exit();
                        } else {
                            echo '<tr><td colspan="6">File not found.</td></tr>';
                        }
                    }
                    ?>
                </tbody>
            </table>
            <br>
            <a href="../Admin/index_admin.php" class="btn btn-secondary">Back to Menu</a>

            <?php
            // Handle file viewing
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['view'])) {
                if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
                    die('Unauthorized access');
                }

                $fileToEdit = $logDir . '/' . $_GET['view'];
                if (file_exists($fileToEdit)) {
                    file_put_contents($fileToEdit, $_POST['log_content']);
                    header("Location: Int_LogsDelete.php");
                    exit();
                }
            }

            if (isset($_GET['view'])) {
                $fileToView = $logDir . '/' . $_GET['view'];
                if (file_exists($fileToView)) {
                    echo '<h3 class="mt-5">Editing: ' . htmlspecialchars($_GET['view']) . '</h3>';
                    echo '<br>';
                    echo '<form method="POST" class="shadow-lg"  action="Int_LogsDelete.php?view=' . urlencode($_GET['view']) . '">';
                    echo '<textarea name="log_content" class="form-control" rows="10">' . htmlspecialchars(file_get_contents($fileToView)) . '</textarea>';
                    echo '<br></br>';
                    echo '<button type="submit" class="btn btn-secondary mt-2 me-2">Save Changes</button>';
                    echo '<a href="Int_LogsDelete.php" class="btn btn-danger mt-2 ml-2">Cancel</a>';
                    echo '</form>';
                } else {
                    echo '<div class="alert alert-danger mt-3">File not found.</div>';
                }
            }

            ?>
        </div>
</body>
<footer class="align-items-center text-center mt-5">
    <p>&copy; <?php echo date("Y"); ?> - All rights reserved.</p>
    <p>Page created on: <?php echo date("F j, Y", filectime(__FILE__)); ?></p>
    <p>Page name: <?php echo basename(__FILE__); ?></p>
</footer>
<script src="../Configuration/JQuery/jquery-3.7.1.js"></script>
<script src="../Configuration/bootstrap/js/bootstrap.min.js"></script>
<script src="../Configuration/DataTables/datatables.min.js"></script>
<script>
    $(document).ready(function () {
        $('#mainTable').DataTable({
            paging: true,
            searching: true,
            ordering: true,
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'All']],
            columnDefs: [
                { orderable: false, targets: [3, 4, 5] },
                { className: 'text-center', targets: [0, 1, 2, 3, 4, 5] },
                { width: '20%', targets: 0 },
                { width: '20%', targets: 1 },
                { width: '20%', targets: 2 },
                { width: '10%', targets: 3 },
                { width: '10%', targets: 4 },
                { width: '10%', targets: 5 }
            ]
        });
    });
</script>

</html>