<?php
session_start();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../Configuration/logo.ico" type="image/x-icon">
    <script src="../Configuration/JQuery/jquery-3.7.1.js"></script>
    <link rel="stylesheet" href="../Configuration/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="./Css/dark-mode.css">
    <link rel="shortcut icon" href="../Controller/logo.ico" type="image/x-icon">
    <link rel="stylesheet" href="../Configuration/JQuery/select2.min.css">
    <title>Record of entry record</title>
    <!--Archivo de vista de proceso de salida de activos -->
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
            <div id="FORMoutput" class="col-md-6 shadow-lg rounded">
                <form id="updateForm" action="../Controller/act_salida.php" method="post" class="bg-light">
                    <div class="p-5 rounded">
                        <h1 id="totalC" class="text-center">Record of Departure Certificate</h1>
                        <hr class="my-5">
                        <h4 class="text-center my-3">Name of the Person:</h4>
                        <!--Consulta de equipos con condicionales basadas en el User Status y last user-->
                        <?php // Consulta de usuario
                        include "../Configuration/Connection.php";
                        $sql = "SELECT * FROM vista_equipos_usuarios
                        WHERE last_user NOT IN ('Stock', 'No user')
                        AND user_status = 'Active User'
                        AND last_user NOT REGEXP '^[0-9]+$';";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute();
                        try {
                            if ($stmt->rowCount() > 0) {
                                echo '<select id="equipoSelect" class="shadow-sm form-select text-center shadow select2" name="equipo" aria-label="Default select example" required>';
                                echo '<option value="0" selected>Select a Person</option>';

                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo '<option value="' . htmlspecialchars($row['assetname']) . '">' . htmlspecialchars($row['last_user']) . '</option>';
                                }
                                echo '</select><br>';
                            } else {
                                echo '<p class="text-center">No data found</p>';
                            }

                        } catch (PDOException $e) {
                            echo '<p class="text-center">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
                        }

                        ?>
                        <br>
                        <h4 class="text-center my-3">Departure date</h4>
                        <div class="form-group">
                            <input class="shaodw-sm form-control" type="text" id="fechaSalida" name="fechaSalida"
                                autocomplete="off" required data-validate="true" placeholder="mm/dd/yyyy">
                        </div>
                        <!--Script para la validación de ingreso de la fecha-->
                        <script>
                            document.addEventListener('DOMContentLoaded', function () {
                                const garantiaInput = document.getElementById('fechaSalida');
                                // Establece la fecha actual como valor predeterminado
                                const today = new Date();
                                const formattedDate = [
                                    ('0' + (today.getMonth() + 1)).slice(-2), // Mes (mm)
                                    ('0' + today.getDate()).slice(-2), // Día (dd)
                                    today.getFullYear() // Año (yyyy)
                                ].join('/');
                                garantiaInput.value = formattedDate;

                                // Formateo de la entrada del usuario
                                garantiaInput.addEventListener('input', function (e) {
                                    let value = e.target.value.replace(/\D/g,
                                        ''); // Elimina todo excepto números
                                    let formattedDate = '';

                                    if (value.length > 8) {
                                        value = value.substr(0, 8);
                                    }
                                    if (value.length > 0) {
                                        formattedDate = value.substr(0, 2);
                                        if (value.length > 2) {
                                            formattedDate += '/' + value.substr(2, 2);
                                        }
                                        if (value.length > 4) {
                                            formattedDate += '/' + value.substr(4);
                                        }
                                    }
                                    let month = parseInt(value.substr(0, 2));
                                    let day = parseInt(value.substr(2, 2));

                                    if (month > 12) {
                                        month = 12;
                                        formattedDate = '12' + formattedDate.substr(2);
                                    }

                                    if (day > 31) {
                                        day = 31;
                                        formattedDate = formattedDate.substr(0, 3) + '31' +
                                            formattedDate.substr(5);
                                    }

                                    e.target.value = formattedDate;
                                });

                                // Validación al perder el foco
                                garantiaInput.addEventListener('blur', function (e) {
                                    const datePattern =
                                        /^(0[1-9]|1[0-2])\/(0[1-9]|[12]\d|3[01])\/\d{4}$/;
                                    if (!datePattern.test(e.target.value)) {
                                        e.target.classList.add('is-invalid');
                                    } else {
                                        e.target.classList.remove('is-invalid');
                                    }
                                });

                                // Validación al cambiar el valor
                                garantiaInput.addEventListener('input', function (e) {
                                    const datePattern =
                                        /^(0[1-9]|1[0-2])\/(0[1-9]|[12]\d|3[01])\/\d{4}$/;
                                    if (!datePattern.test(e.target.value)) {
                                        e.target.classList.add('is-invalid');
                                    } else {
                                        e.target.classList.remove('is-invalid');
                                    }
                                });

                                // Prevenir el envío del formulario si la fecha no es válida
                                document.getElementById('registform').addEventListener('submit', function (
                                    event) {
                                    if (garantiaInput.classList.contains('is-invalid')) {
                                        event.preventDefault();
                                        alert('Please enter a valid date.');
                                    }
                                });
                            });
                        </script>
                    </div>
                    <br>
                    <div class="text-center">
                        <button type="submit" class="btn btn-secondary">Update</button>
                        <a href="../index.php" class="btn btn-danger">Return</a>
                    </div>
                    <br>
                    <br>
                </form>
                <!--Script Reciclado en base a la funcion de mensaje de confirmacion del formulario-->
                <script>
                    $(document).ready(function () {
                        $('#equipoSelect').select2({
                            placeholder: "Select a person",
                            allowClear: true,
                            minimumResultsForSearch: 5,
                            width: '100%'
                        });

                    });

                    // Validacion para campos vacios
                    document.getElementById('updateForm').addEventListener('submit', function (event) {
                        const select = $('#equipoSelect').select2('data')[0];
                        let errorMessage = "please fix the following errors: \n";
                        let haserror = false;

                        if (!select || select.id === '0') {
                            errorMessage += "- Please Select a person before updating: \n";
                            haserror = true;
                        }

                        if (haserror) {
                            event.preventDefault();
                            alert(errorMessage);
                        } else {
                            if (!confirm('Are you sure you want to update this data?')) {
                                event.preventDefault();
                            }
                        }
                    });
                </script>
            </div>
        </div>
    </div>
    <script src="../Configuration/bootstrap/js/bootstrap.min.js"></script>
    <script src="../Configuration/JQuery/select2.min.js"></script>

</body>

</html>