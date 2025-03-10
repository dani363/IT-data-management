<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./Css/dark-mode.css">
    <link rel="shortcut icon" href="../Configuration/logo.ico" type="image/x-icon">
    <link href="../Configuration/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <script src="../Configuration/JQuery/jquery-3.7.1.js"></script>
    <link href="../Configuration/JQuery/select2.min.css" rel="stylesheet" />

    <title>Record of Entry</title>
    <style>
        .select2-container--default .select2-selection--single {
            height: 38px;
            border-radius: 4px;
            border: 1px solid #ced4da;
            padding: 0 10px;
            font-size: 14px;
            line-height: 22px;
            box-sizing: border-box;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease-in-out;
        }

        .select2-container--default .select2-selection--single:hover {
            border-color: #80bdff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
            transition: border-color 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
        }

        .select2-container--default .select2-selection--single:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease-in-out;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: inherit;
        }
    </style>

</head>

<body>


    <div class="container">
        <div class="row d-flex justify-content-center align-items-center vh-100">
            <div class="col-md-6 shadow p-5 rounded">
                <div class="">
                    <div id="FORMinput" class="card-body">
                        <form id="updateForm" action="../Controller/act_entrada.php" method="post">
                            <h1 id="totalC" class="text-center mb-5">Record of Entry Record</h1>

                            <!-- Selección del número de serie -->
                            <div class="mb-3">
                                <h4 class="mb-2">Computer serial:</h4>
                                <?php
                                include "../Configuration/Connection.php";

                                try {
                                    // Seleccionar todos los equipos que no estén en uso
                                    $sql = "SELECT * FROM vista_equipos_usuarios
                            WHERE serial_number IS NOT NULL AND serial_number != '' 
                            ORDER BY serial_number ASC;";
                                    $stmt = $pdo->prepare($sql);
                                    $stmt->execute();

                                    if ($stmt->rowCount() > 0) {
                                        echo '<select id="equipoSelect" class="shadow-sm form-select select2" name="serial_number">';
                                        echo '<option value="0" selected>Select a computer</option>';

                                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                            echo '<option value="' . htmlspecialchars($row['serial_number']) . '">' . htmlspecialchars($row['serial_number']) . '</option>';
                                        }

                                        echo '</select>';
                                    } else {
                                        echo '<p>No data found.</p>';
                                    }
                                } catch (PDOException $e) {
                                    echo '<p>Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
                                }
                                ?>
                            </div>

                            <!-- Selección del nombre de la persona -->
                            <br>
                            <div class="mb-3">
                                <h4 class="mb-2">Name of the person: </h4>
                                <?php
                                try {
                                    $sql = "SELECT * FROM vista_equipos_usuarios
                            WHERE last_user NOT IN ('Stock', 'No user')
                            AND user_status = 'Active User'
                            AND last_user NOT REGEXP '^[0-9]+$'
                            ORDER BY last_user ASC
                            ;";
                                    $stmt = $pdo->prepare($sql);
                                    $stmt->execute();

                                    if ($stmt->rowCount() > 0) {
                                        echo '<select id="equipoSelect2" class="form-select shadow-sm select2" name="assetname">';
                                        echo '<option value="0" selected>Select a person</option>';

                                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                            echo '<option value="' . htmlspecialchars($row['assetname']) . '">' . htmlspecialchars($row['last_user']) . '</option>';
                                        }
                                        echo '</select>';
                                    } else {
                                        echo '<p>No data found</p>';
                                    }
                                } catch (PDOException $e) {
                                    echo '<p>Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
                                }
                                ?>
                            </div>
                            <br>

                            <!-- Botones -->
                            <div class="text-center">
                                <button type="submit" class="btn btn-secondary">Register</button>
                                <a href="../index.php" class="btn btn-danger ms-2">Return</a>
                            </div>
                        </form>
                        <script>
                            $(document).ready(function () {
                                $('#equipoSelect, #equipoSelect2').select2({
                                    placeholder: function () {
                                        return $(this).attr('placeholder');
                                    },
                                    allowClear: true,
                                    width: '100%',
                                    language: {
                                        noResults: function () {
                                            return 'No results found';
                                        }
                                    },
                                    escapeMarkup: function (markup) {
                                        return markup;
                                    }
                                });
                            });

                            //Validacion de select vacio
                            document.getElementById('updateForm').addEventListener('submit', function (event) {
                                const select1 = $('#equipoSelect').select2('data')[0];
                                const select2 = $('#equipoSelect2').select2('data')[0];

                                let errorMessage = "Please fix the following errors: \n";
                                let haserror = false;

                                // Validar select1
                                if (!select1 || select1.id === '0') {
                                    errorMessage += "- Select a computer.\n";
                                    haserror = true;
                                }

                                // Validar select2
                                if (!select2 || select2.id === '0') {
                                    errorMessage += "- Select a person.\n";
                                    haserror = true;
                                }

                                // Manejar errores
                                if (haserror) {
                                    event.preventDefault(); // Detener el envío del formulario
                                    alert(errorMessage); // Mostrar los errores
                                } else {
                                    // Confirmación si no hay errores
                                    if (!confirm('Are you sure you want to update this data?')) {
                                        event.preventDefault(); // Detener el envío si el usuario no confirma
                                    }
                                }
                            });
                        </script>

                    </div>
                </div>
            </div>
        </div>


        <script src="../Configuration/bootstrap/js/bootstrap.min.js"></script>
        <script src="../Configuration/JQuery/select2.min.js"></script>
        <script src="../View/Js/Background.js"></script>

</body>

</html>