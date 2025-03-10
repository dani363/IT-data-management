<?php
session_start();

include "../Configuration/Connection.php";
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Asset</title>
    <link rel="shortcut icon" href="../Configuration/logo.ico" type="image/x-icon">
    <link href="../Configuration/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./Css/dark-mode.css">

    <!--Arhivo con la funcion de ser la Interfaz de el proceso de Registro de activos-->
</head>

<body>
    <div class="container">
        <div class="row d-flex justify-content-center align-items-center vh-100">
            <div id="FORMregistform" class="shadow-lg p-5 rounded bg-light">
                <!--Formulario de registro de equipos con sus respectivas validaciones -->
                <form id="registform" method="post" action="../Controller/RegisterAsset.php">
                    <h1 id="totalC" class="text-center">Register Asset </h1>
                    <br>
                    <br>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nombre_usuario">User name: </label>
                                <input class="shadow-sm form-control" type="text" id="nombre_usuario"
                                    name="nombre_usuario" autocomplete="off" required data-validate="true"
                                    value="<?php echo rand(10000, 99999); ?>">
                            </div>
                            <div class="form-group">
                                <label for="assetname">Asset name: </label>
                                <input class="shadow-sm form-control" type="text" id="assetname" name="assetname"
                                    autocomplete="off" required data-validate="true" value="CO-LPT"
                                    oninput="this.value = this.value.toUpperCase()">
                                <script src="../View/Js/GetAsset.js"></script>
                                <small id="asset-message" class="form-text"></small>
                                <script>
                                    document.getElementById("nombre_usuario").addEventListener("input", async function() {
                                        const username = this.value
                                            .trim(); // Obtiene y limpia el valor del input
                                        const assetnameInput = document.getElementById("assetname");

                                        if (username) {
                                            try {
                                                // Procesamiento asincrónico si fuera necesario en el futuro
                                                await new Promise(resolve => setTimeout(resolve,
                                                    0)); // Simula asincronía

                                                // Divide el nombre y genera las iniciales (dos letras de cada dos palabras )
                                                const names = username.split(" ");
                                                const initials = names.length > 2 ?
                                                    names[0][0].toUpperCase() + names[0][1].toUpperCase() +
                                                    names[2][0].toUpperCase() + names[2][1].toUpperCase() :
                                                    names.length > 1 ?
                                                    names[0][0].toUpperCase() + names[0][1].toUpperCase() +
                                                    names[1][0].toUpperCase() + names[1][1].toUpperCase() :
                                                    names[0].slice(0, 4).toUpperCase();

                                                const newAssetValue = `CO-LPT-${initials}`;

                                                // Actualiza solo si el valor no coincide para evitar repeticiones
                                                if (assetnameInput.value !== newAssetValue) {
                                                    assetnameInput.value = newAssetValue;
                                                }
                                            } catch (error) {
                                                console.error(
                                                    "Error getting user name initials:",
                                                    error);
                                            }
                                        } else {
                                            // Restablece al valor por defecto si el campo de nombre de usuario está vacío
                                            assetnameInput.value = 'CO-LPT';
                                        }
                                    });

                                    // Agregar el siguiente código para generar las iniciales con el value
                                    document.addEventListener('DOMContentLoaded', function() {
                                        const usernameInput = document.getElementById("nombre_usuario");
                                        const assetnameInput = document.getElementById("assetname");

                                        const username = usernameInput.value
                                            .trim(); // Obtiene y limpia el valor del input

                                        if (username) {
                                            try {
                                                // Divide el nombre y genera las iniciales (dos letras de cada dos palabras )
                                                const names = username.split(" ");
                                                const initials = names.length > 2 ?
                                                    names[0][0].toUpperCase() + names[0][1].toUpperCase() +
                                                    names[2][0].toUpperCase() + names[2][1].toUpperCase() :
                                                    names.length > 1 ?
                                                    names[0][0].toUpperCase() + names[0][1].toUpperCase() +
                                                    names[1][0].toUpperCase() + names[1][1].toUpperCase() :
                                                    names[0].slice(0, 4).toUpperCase();

                                                const newAssetValue = `CO-LPT-${initials}`;

                                                // Actualiza solo si el valor no coincide para evitar repeticiones
                                                if (assetnameInput.value !== newAssetValue) {
                                                    assetnameInput.value = newAssetValue;
                                                }
                                            } catch (error) {
                                                console.error(
                                                    "Error getting user name initials:",
                                                    error);
                                            }
                                        } else {
                                            // Restablece al valor por defecto si el campo de nombre de usuario está vacío
                                            assetnameInput.value = 'CO-LPT';
                                        }
                                    });
                                </script>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="seriallabel">Serial number: </label>
                                <input class="shadow-sm form-control" type="text" id="serial" name="serial"
                                    autocomplete="off" required data-validate="true"
                                    oninput="this.value = this.value.toUpperCase()" />
                                <small id="serial-message" class="form-text"></small>
                                <!--Script con la funcion de mostrar el resultado de la busqueda y validacion en este caso de el numero de serial y el nombre de el activo-->
                                <script src="../View/Js/GetSerial.js"></script>

                                </script>
                            </div>

                            <div class="form-group">
                                <label for="compra">Purchase Country: </label>
                                <?php
                                try {
                                    $sql = "SELECT DISTINCT purchase_country 
                                            FROM equipos
                                            WHERE purchase_country IS NOT NULL AND purchase_country != ''";
                                    $stmt = $pdo->prepare($sql);
                                    $stmt->execute();

                                    if ($stmt->rowCount() > 0) {
                                        echo '<select id="compra" class="shadow-sm form-select" name="compra">';
                                        echo '<option value="0">Select a computer</option>';

                                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                            $selected = ($row['purchase_country'] == 'Colombia') ? 'selected' : '';
                                            echo '<option value="' . htmlspecialchars($row['purchase_country']) . '" ' . $selected . '>' . htmlspecialchars($row['purchase_country']) . '</option>';
                                        }

                                        echo '</select>';
                                    } else {
                                        echo '<p> No data found. </p>';
                                    }
                                } catch (PDOException $e) {
                                    echo '<p>Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
                                }
                                ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="garantia">Warranty end date: </label>
                                <input class="shaodw-sm form-control" type="text" id="garantia" name="garantia"
                                    autocomplete="off" required data-validate="true" placeholder="mm/dd/yyyy">
                            </div>
                            <!--Script para la validación de ingreso de la fecha-->
                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    const garantiaInput = document.getElementById('garantia');

                                    // Establece la fecha actual como valor predeterminado
                                    const today = new Date();
                                    const formattedDate = [
                                        ('0' + (today.getMonth() + 1)).slice(-2), // Mes (mm)
                                        ('0' + today.getDate()).slice(-2), // Día (dd)
                                        today.getFullYear() // Año (yyyy)
                                    ].join('/');
                                    garantiaInput.value = formattedDate;

                                    // Formateo de la entrada del usuario
                                    garantiaInput.addEventListener('input', function(e) {
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
                                    garantiaInput.addEventListener('blur', function(e) {
                                        const datePattern =
                                            /^(0[1-9]|1[0-2])\/(0[1-9]|[12]\d|3[01])\/\d{4}$/;
                                        if (!datePattern.test(e.target.value)) {
                                            e.target.classList.add('is-invalid');
                                        } else {
                                            e.target.classList.remove('is-invalid');
                                        }
                                    });

                                    // Validación al cambiar el valor
                                    garantiaInput.addEventListener('input', function(e) {
                                        const datePattern =
                                            /^(0[1-9]|1[0-2])\/(0[1-9]|[12]\d|3[01])\/\d{4}$/;
                                        if (!datePattern.test(e.target.value)) {
                                            e.target.classList.add('is-invalid');
                                        } else {
                                            e.target.classList.remove('is-invalid');
                                        }
                                    });

                                    // Prevenir el envío del formulario si la fecha no es válida
                                    document.getElementById('registform').addEventListener('submit', function(
                                        event) {
                                        if (garantiaInput.classList.contains('is-invalid')) {
                                            event.preventDefault();
                                            alert('Please enter a valid warranty end date.');
                                        }
                                    });
                                });
                            </script>

                            <div class="form-group">
                                <label for="newl">New laptop?: </label>
                                <input class="shadow-sm form-control" type="text" id="newl" name="newl"
                                    autocomplete="off" data-validate="true">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group my-2">
                                <label for="usrStts">User Status: </label>
                                <?php
                                try {
                                    $sql = "SELECT DISTINCT user_status 
                                FROM usuarios_equipos
                                WHERE user_status IS NOT NULL AND user_status != ''";
                                    $stmt = $pdo->prepare($sql);
                                    $stmt->execute();

                                    if ($stmt->rowCount() > 0) {
                                        echo '<select id="usrStts" class="shadow-sm form-select" name="usrStts">';
                                        echo '<option value="0">Select a computer</option>';

                                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

                                            $selected = ($row['user_status'] == 'Active User') ? 'selected' : '';
                                            echo '<option value="' . htmlspecialchars($row['user_status']) . '" ' . $selected . '>' . htmlspecialchars($row['user_status']) . '</option>';
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
                            <div class="form-group my-2">
                                <label for="status_change">status change: </label>
                                <?php
                                include "../Configuration/Connection.php";
                                try {
                                    $sql = "SELECT DISTINCT status_change 
                                            FROM usuarios_equipos
                                            WHERE status_change IS NOT NULL AND status_change != ''";
                                    $stmt = $pdo->prepare($sql);
                                    $stmt->execute();

                                    if ($stmt->rowCount() > 0) {
                                        echo '<select id="status_change" class="shadow-sm form-select" name="status_change">';
                                        echo '<option value="0">Select a option</option>';

                                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                            $selected = ($row['status_change'] == 'Active Warranty') ? 'selected' : '';
                                            echo '<option value="' . htmlspecialchars($row['status_change']) . '" ' . $selected . '>' . htmlspecialchars($row['status_change']) . '</option>';
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
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="job_title">Job title: </label>
                                <input class="shadow-sm form-control" type="text" id="job_title" name="job_title"
                                    list="job_titles" data-validate="true" autocomplete="off" value="unknown">
                                <datalist id="job_titles">
                                    <?php
                                    $job_titles = $pdo->prepare("SELECT DISTINCT job_title FROM usuarios_equipos ORDER BY job_title ASC");
                                    $job_titles->execute();
                                    $job_titles = $job_titles->fetchAll(PDO::FETCH_COLUMN);
                                    foreach ($job_titles as $job_title) {
                                        echo "<option value='$job_title'>$job_title</option>";
                                    }
                                    ?>
                                </datalist>
                                <br>
                                <label for="id">Id: </label>
                                <input class="shadow-sm form-control" type="number" id="id" name="id" autocomplete="off"
                                    required value="0">
                                <p id="mensaje" class="text-danger"></p>
                                <!--Script para la validacion de caracteres especiales-->
                                <script>
                                    const inputElement = document.getElementById('id');
                                    const mensaje = document.getElementById('mensaje');

                                    inputElement.addEventListener('input', function() {
                                        const inputValue = this.value;
                                        const regex = /^[0-9]+$/;

                                        // Validar que solo sean números
                                        if (!regex.test(inputValue)) {
                                            mensaje.textContent = 'Please enter only numbers.';
                                            this.value = this.value.replace(/[^0-9]/g, '');
                                            return;
                                        }

                                        // Validar longitud
                                        if (inputValue.length > 10) {
                                            mensaje.textContent = 'Only 10 digits are allowed.';
                                            this.value = inputValue.slice(0, 10);
                                            return;
                                        }
                                        // Si pasa todas las validaciones, limpiar mensaje
                                        mensaje.textContent = '';
                                    });
                                </script>
                                <input type="hidden" class="form-control" id="expired" name="expired">

                            </div>
                        </div>

                        <!--Input relacionado algun nueva columna que se haya seleccionado-->
                        <?php if (isset($_SESSION['new_reg_fields'])): ?>
                            <?php foreach ($_SESSION['new_reg_fields'] as $field_name): ?>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="mb-2" for="<?php echo htmlspecialchars($field_name); ?>">
                                            <?php echo htmlspecialchars($field_name); ?>:
                                        </label>
                                        <?php
                                        $field_value = isset($asset_data[$field_name]) ? htmlspecialchars($asset_data[$field_name]) : '';
                                        $field_type = $_SESSION['new_fieldR_types'][$field_name];
                                        ?>
                                        <?php if ($field_type === 'date'): ?>
                                            <input class="form-control" type="date"
                                                id="<?php echo htmlspecialchars($field_name); ?>"
                                                name="<?php echo htmlspecialchars($field_name); ?>"
                                                value="<?php echo $field_value; ?>" autocomplete="off" data-validate="true"><br>
                                        <?php elseif ($field_type === 'tinyint'): ?>
                                            <select class="form-control" id="<?php echo htmlspecialchars($field_name); ?>"
                                                name="<?php echo htmlspecialchars($field_name); ?>" autocomplete="off"
                                                data-validate="true">
                                                <option value="0" <?php echo (empty($field_value) || $field_value == 0) ? 'selected' : ''; ?>>No
                                                </option>
                                                <option value="1" <?php echo ($field_value == 1) ? 'selected' : ''; ?>>Yes</option>
                                            </select>
                                        <?php elseif (in_array($field_type, ['int', 'bigint', 'smallint'])): ?>
                                            <input class="form-control" type="number"
                                                id="<?php echo htmlspecialchars($field_name); ?>"
                                                name="<?php echo htmlspecialchars($field_name); ?>"
                                                value="<?php echo $field_value ? $field_value : 0; ?>" autocomplete="off" min="0"
                                                oninput="this.value = Math.abs(this.value)"><br>
                                        <?php else: ?>
                                            <input class="form-control" type="text"
                                                id="<?php echo htmlspecialchars($field_name); ?>"
                                                name="<?php echo htmlspecialchars($field_name); ?>"
                                                value="<?php echo $field_value; ?>" autocomplete="off" data-validate="true"><br>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <div class="col-md-6 d-flex justify-content-right">
                            <div class="form-group me-2">
                                <label for="selectCard">Card: </label>
                                <select class="shadow-sm form-select form-select-sm" name="selectCard" id="selectCard">
                                    <option value="No">No</option>
                                    <option value="Si">Yes</option>
                                    <option value="Pendiente" selected>Pending</option>
                                </select>
                            </div>
                            <div class="form-group me-2">
                                <label for="selectKEY">Key: </label>
                                <select class="shadow-sm form-select form-select-sm" name="selectKEY" id="selectKEY">
                                    <option value="No">No</option>
                                    <option value="Si">Yes</option>
                                    <option value="Pendiente" selected>Pending</option>
                                </select>
                            </div>
                            <div class="form-group me-2">
                                <label for="typeId">Type ID</label>
                                <select class="shadow-sm form-select form-select-sm" name="type_id" id="type_id">
                                    <option value="CC" selected>CC</option>
                                    <option value="CE">CE</option>
                                    <option value="PP">PP</option>
                                    <option value="TI">TI</option>
                                    <option value="RC">RC</option>
                                </select>
                            </div>
                        </div>
                        <div class="my-4">
                            <button id="confirmAllButton" type="submit" class="btn btn-secondary">Send</button>
                            <a href="../index.php" class="btn btn-danger">Return</a>
                        </div>
                    </div>
                    <!-- Script para la validacion de caracteres especiales exceptuando el "/" y "-" -->
                    <script>
                        // Función general para validar que no se ingresen caracteres especiales excepto "/" y "-"
                        function validarSinCaracteresEspeciales(inputElement, mensajeElement) {
                            // Ajustar la expresión regular para incluir "/" y "-" y Ñ
                            const regex = /^[a-zA-Z0-9\s\/\-Ññ\?/]+$/; // Permite letras, números, espacios, "/" y "-"

                            inputElement.addEventListener('input', function() {
                                const inputValue = this.value;

                                if (!regex.test(inputValue)) {
                                    mensajeElement.textContent =
                                        'Characters are not allowed except "/" and "-" and Ñ.';
                                    // Remover caracteres no válidos excepto "/" y "-" y Ñ
                                    this.value = this.value.replace(/[^a-zA-Z0-9\s\/\-Ññ]/g, '');
                                } else {
                                    mensajeElement.textContent = '';
                                }
                            });
                        }
                        document.addEventListener('DOMContentLoaded', function() {
                            const fieldsToValidate = document.querySelectorAll('[data-validate="true"]');
                            fieldsToValidate.forEach(inputElement => {
                                const mensajeElement = document.createElement('p');
                                mensajeElement.classList.add('text-danger');
                                inputElement.parentNode.appendChild(mensajeElement);

                                validarSinCaracteresEspeciales(inputElement, mensajeElement);
                            });
                        });
                    </script>
                    <script>
                        // validacion reciclada para el impedimento del envio del formulario sus lescel no estan seleccionados
                        document.getElementById('registform').addEventListener('submit', function(event) {
                            const select1 = document.getElementById('compra');
                            const select2 = document.getElementById('usrStts');
                            const select3 = document.getElementById('status_change');

                            let errorMessage = "Please fix the following errors: \n";
                            let hasError = false;

                            if (select1.value === '0') {
                                errorMessage += "- Select a Purchase Country.\n";
                                hasError = true;
                            }
                            if (select2.value === '0') {
                                errorMessage += "- Select a User Status.\n";
                                hasError = true;
                            }
                            if (select3.value === '0') {
                                errorMessage += "- Select a Status Change.\n";
                                hasError = true;
                            }
                            if (hasError) {
                                event.preventDefault();
                                alert(errorMessage);
                            } else {
                                if (!confirm('Are you sure you want to update this data?')) {
                                    event.preventDefault(); // Cancelar el envío si el usuario no confirma
                                }
                            }
                        });
                    </script>
                </form>
            </div>
        </div>
    </div>


    <script src="../Configuration/bootstrap/js/bootstrap.min.js">
    </script>

</body>

</html>