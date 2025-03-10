<?php
session_start();//Inicio de la sesion para que la funcion pueda funcionar
include "../Configuration/Connection.php";
// Verificar si hay datos disponibles

if (!isset($_SESSION['asset_data'])) {
    echo "No se han encontrado datos para mostrar.";
    exit;
}

// Obtener los datos desde la sesión
$asset_data = $_SESSION['asset_data'];
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../Configuration/logo.ico" type="image/x-icon">
    <link href="../Configuration/bootstrap/css/bootstrap.min.css" rel="stylesheet"">
    <link rel=" stylesheet" href="./Css/dark-mode.css">
    <!--Archivo con la funcion para la interfaz de  actualizacion del activo-->
    <title>Update Register</title>
</head>

<body>
    <div class="container">
        <div class="row d-flex justify-content-center align-items-center vh-100">
            <div id="FORMupdateform" class="shadow-lg p-5 rounded bg-light">
                <!--Formulario de Actualizacion donde trae los datos del activo seleccionado-->
                <form id="updateform" action="../Controller/procesar_datos.php" method="post">
                    <h2 id="totalC" class="text-center">Update Register</h2>
                    <br>
                    <br>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nombre_usuario">User Name: </label>
                                <input class="form-control" type="text" id="nombre_usuario" name="nombre_usuario"
                                    value="<?php echo htmlspecialchars($asset_data['last_user']); ?>"
                                    autocomplete="off"><br>
                            </div>
                            <div class="form-group">
                                <label for="serial">Serial Number: </label>
                                <input class="form-control" type="text" id="serial" name="serial"
                                    value="<?php echo htmlspecialchars($asset_data['serial_number']); ?>" required
                                    autocomplete="off" readonly><br>
                            </div>

                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="compra">Purchase Country: </label>
                                <!--Consulta de los Paises de compra, Estado del usuario, Estado de cambio en base  a los que estan registrados-->
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
                                            $selected = ($row['purchase_country'] == $asset_data['purchase_country']) ? 'selected' : '';
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
                                <br>
                            </div>
                            <div class="form-group">
                                <label for="garantia">Warranty end date: </label>
                                <input class="form-control" type="text" id="garantia" name="garantia"
                                    value="<?php echo htmlspecialchars($asset_data['warranty_enddate']); ?>" required
                                    autocomplete="off" data-validate="true" placeholder="mm/dd/yyyy"><br>
                                <script>
                                    // Funcion reciclada para la validacion de fechas en Garantias
                                    document.addEventListener('DOMContentLoaded', function () {
                                        const garantiaInput = document.getElementById('garantia');

                                        garantiaInput.addEventListener('input', function (e) {
                                            let value = e.target.value.replace(/\D/g,
                                                ''); // Elimina todo excepto números
                                            let formattedDate = '';

                                            // Limita a 8 dígitos (mmddyyyy)
                                            if (value.length > 8) {
                                                value = value.substr(0, 8);
                                            }

                                            // Aplica el formato mm/dd/yyyy
                                            if (value.length > 0) {
                                                // Agrega primeros dos dígitos (mm)
                                                formattedDate = value.substr(0, 2);

                                                // Agrega slash y siguientes dos dígitos (dd)
                                                if (value.length > 2) {
                                                    formattedDate += '/' + value.substr(2, 2);
                                                }

                                                // Agrega slash y últimos cuatro dígitos (yyyy)
                                                if (value.length > 4) {
                                                    formattedDate += '/' + value.substr(4);
                                                }
                                            }

                                            // Validación básica de mes y día
                                            let month = parseInt(value.substr(0, 2));
                                            let day = parseInt(value.substr(2, 2));

                                            // Corrección automática del mes
                                            if (month > 12) {
                                                month = 12;
                                                formattedDate = '12' + formattedDate.substr(2);
                                            }

                                            // Corrección automática del día
                                            if (day > 31) {
                                                day = 31;
                                                formattedDate = formattedDate.substr(0, 3) + '31' +
                                                    formattedDate
                                                        .substr(5);
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
                                    });
                                </script>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="expira">Expire?: </label>
                                <input class="form-control" type="text" id="expira" name="expira"
                                    value="<?php echo htmlspecialchars($asset_data['expired']); ?>" required
                                    readonly><br>
                            </div>
                            <div class="form-group">
                                <label for="newl">New laptop?: </label>
                                <input class="form-control" type="text" id="newl" name="newl"
                                    value="<?php echo htmlspecialchars($asset_data['new_laptop']); ?>"
                                    autocomplete="off"><br>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">

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

                                            $selected = ($row['user_status'] == $asset_data['user_status']) ? 'selected' : '';
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
                                <br>
                            </div>
                            <div class="form-group">
                                <label for="status_change">Status Change</label>

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
                                        echo '<option value="0">Select a status change</option>';

                                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                            $selected = ($row['status_change'] == $asset_data['status_change']) ? 'selected' : '';
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
                                <br>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="job_title">Job title: </label>
                                <input class="form-control" type="text" id="job_title" name="job_title"
                                    value="<?php echo htmlspecialchars($asset_data['job_title']); ?>" autocomplete="off"
                                    list="job_titles">
                                <datalist id="job_titles">
                                    <?php
                                    $job_titles = $pdo->query("SELECT DISTINCT job_title FROM usuarios_equipos ORDER BY job_title ASC")->fetchAll(PDO::FETCH_COLUMN);
                                    foreach ($job_titles as $job_title) {
                                        echo "<option value='$job_title'>$job_title</option>";
                                    }
                                    ?>
                                </datalist><br>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="id">Id: </label>
                                <!-- Primero el input y el mensaje -->
                                <div class="form-group">
                                    <input class="form-control" type="text" id="id" name="id"
                                        value="<?php echo htmlspecialchars($asset_data['cedula']); ?>" required
                                        autocomplete="off">
                                    <p id="mensaje" class="text-danger"></p>
                                </div>

                                <!-- Luego el script -->
                                <script>
                                    const inputElement = document.getElementById('id');
                                    const mensaje = document.getElementById('mensaje');

                                    inputElement.addEventListener('input', function () {
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

                                    // Prevenir entrada de valores negativos o caracteres especiales
                                    inputElement.addEventListener('keydown', function (e) {
                                        if (e.key === '-' || e.key === '+' || e.key === 'e' || e.key === 'E') {
                                            e.preventDefault();
                                        }
                                    });
                                </script>
                            </div>
                        </div>
                        <?php if (isset($_SESSION['new_upd_fields'])): ?>
                            <?php foreach ($_SESSION['new_upd_fields'] as $field_name): ?>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="mb-2" for="<?php echo htmlspecialchars($field_name); ?>">
                                            <?php echo htmlspecialchars($field_name); ?>:
                                        </label>
                                        <?php
                                        // Retrieve the value of the field from the asset data, ensuring it is safe for HTML output
                                        // Recupera el valor de la columna del dato del activo, asegurando asi la segurdad de la salida por html
                                        $field_value = isset($asset_data[$field_name]) ? htmlspecialchars($asset_data[$field_name]) : '';
                                        // Determine the type of the field from the session data
                                        // Determina el tipo de la columna the la sesion 
                                        $field_type = $_SESSION['new_fieldU_types'][$field_name];
                                        ?>
                                        <?php if ($field_type === 'date'): ?>
                                            <input class="form-control" type="date"
                                                id="<?php echo htmlspecialchars($field_name); ?>"
                                                name="<?php echo htmlspecialchars($field_name); ?>"
                                                value="<?php echo $field_value; ?>" autocomplete="off"><br>
                                        <?php elseif ($field_type === 'tinyint'): ?>
                                            <select class="form-control" id="<?php echo htmlspecialchars($field_name); ?>"
                                                name="<?php echo htmlspecialchars($field_name); ?>" autocomplete="off"
                                                data-validate="true">
                                                <option value="0" <?php echo ($field_value == 0) ? 'selected' : ''; ?>>No</option>
                                                <option value="1" <?php echo ($field_value == 1) ? 'selected' : ''; ?>>Yes</option>
                                            </select>
                                        <?php elseif (in_array($field_type, ['int', 'bigint', 'smallint'])): ?>
                                            <!-- Render a number input field for integer types, ensuring non-negative values -->
                                            <!-- Renderiza un numero en base al tipo de entra de al columan entero, entrogrande..etc para evitar numero negativos -->
                                            <input class="form-control" type="number"
                                                id="<?php echo htmlspecialchars($field_name); ?>"
                                                name="<?php echo htmlspecialchars($field_name); ?>"
                                                value="<?php echo $field_value ?: 0; ?>" autocomplete="off" min="0"
                                                oninput="this.value = Math.abs(this.value)"><br>
                                        <?php else: ?>
                                            <!-- Render a text input field for other types -->
                                            <!-- Renderiza  un tipo de entrada de texto para el campo u otros tipos-->
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
                                <label for="selectCard">Card</label>
                                <select class="shadow-sm form-select form-select-sm" name="selectCard" id="selectCard">
                                    <option value="Pendiente" <?php echo ($asset_data['Carnet'] == 'Pendiente') ? 'selected' : ''; ?>>
                                        Pending</option>
                                    <option value="No" <?php echo ($asset_data['Carnet'] == 'No') ? 'selected' : ''; ?>>
                                        No</option>
                                    <option value="Si" <?php echo ($asset_data['Carnet'] == 'Si') ? 'selected' : ''; ?>>
                                        Yes</option>
                                </select>
                            </div>
                            <div class="form-group me-2">
                                <label for="selectKEY">Key</label>
                                <select class="shadow-sm form-select form-select-sm" name="selectKEY" id="selectKEY">
                                    <option value="Pendiente" <?php echo ($asset_data['LLave'] == 'Pendiente') ? 'Selected' : ''; ?>>
                                        Pending</option>
                                    <option value="No" <?php echo ($asset_data['LLave'] == 'No') ? 'selected' : ''; ?>>
                                        No</option>
                                    <option value="Si" <?php echo ($asset_data['LLave'] == 'Si') ? 'selected' : ''; ?>>
                                        Yes</option>

                                </select>
                            </div>


                            <div class="form-group me-2">
                                <label for="typeId">Type ID</label>
                                <?php
                                $typeIds = ['CC', 'CE', 'PP', 'TI', 'RC'];
                                ?>
                                <select class="shadow-sm form-select form-select-sm" name="selectID" id="selectID">
                                    <?php foreach ($typeIds as $typeId): ?>
                                        <option value="<?php echo $typeId; ?>" <?php echo ($asset_data['Tipo_ID'] == $typeId) ? 'selected' : ''; ?>>
                                            <?php echo $typeId; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                        </div>
                        <div class="col-md-6">
                            <div class="form-group me-2">
                                <label for="assetname">Asset name</label>
                                <input class="form-control" type="text" id="assetname" name="assetname"
                                    value="<?php echo htmlspecialchars($asset_data['assetname']); ?>" required
                                    oninput="this.value = this.value.toUpperCase()">
                                <small id="asset-message" class="form-text"></small>
                                <script>
                                    document.addEventListener('DOMContentLoaded', async function () {
                                        const usernameInput = document.getElementById("nombre_usuario");
                                        const assetnameInput = document.getElementById("assetname");
                                        const originalAssetName = assetnameInput.value; // Store original value

                                        const updateAssetName = async function () {
                                            const username = usernameInput.value.trim();

                                            if (username) {
                                                try {
                                                    const names = username.split(" ");
                                                    const initials = names.length > 2 ?
                                                        names[0][0].toUpperCase() + names[0][1]
                                                            .toUpperCase() +
                                                        names[2][0].toUpperCase() + names[2][1]
                                                            .toUpperCase() :
                                                        names.length > 1 ?
                                                            names[0][0].toUpperCase() + names[0][1]
                                                                .toUpperCase() +
                                                            names[1][0].toUpperCase() + names[1][1]
                                                                .toUpperCase() :
                                                            names[0].slice(0, 4).toUpperCase();

                                                    const newAssetValue = `CO-LPT-${initials}`;
                                                    assetnameInput.value = newAssetValue;
                                                } catch (error) {
                                                    console.error("Error getting user name initials:", error);
                                                    assetnameInput.value = originalAssetName; // Fallback to original
                                                }
                                            } else {
                                                assetnameInput.value = originalAssetName; // Restore original if empty
                                            }
                                        };

                                        // Only update when username changes, not on initial load
                                        usernameInput.addEventListener("input", updateAssetName);
                                    });
                                </script>
                                <script src="../Controller/GetAsset.js"></script>
                                <input class="form-control" type="hidden" id="fk_Assetname" name="fk_Assetname"
                                    value="<?php echo $asset_data['assetname']; ?>">
                            </div>
                        </div>
                    </div>
                    <br>

                    <div class="col-md-6">
                        <button id="confirmAllButton" type="submit" class="btn btn-secondary my-2">Send</button>
                        <a href="../index.php" class="btn btn-danger my-2">Return</a>
                    </div>
                </form>
                <script>
                    // Función general para validar que no se ingresen caracteres especiales
                    function validarSinCaracteresEspeciales(inputElement, mensajeElement) {
                        const regex = /^[a-zA-Z0-9\s\/\-Ññ]+$/; // Solo permite letras, números y espacios
                        inputElement.addEventListener('input', function () {
                            const inputValue = this.value;

                            if (!regex.test(inputValue)) {
                                mensajeElement.textContent = 'characteres are not allowed.';
                                this.value = this.value.replace(/[^a-zA-Z0-9\s]/g,
                                    ''); // Remover caracteres no válidos
                            } else {
                                mensajeElement.textContent = '';
                            }
                        });
                    }

                    function validarAlgunosCaracteresEspeciales(inputElement, mensajeElement) {
                        const regex = /^[a-zA-Z0-9\s\/\-Ññ]+$/; // Solo permite letras, números y espacios

                        inputElement.addEventListener('input', function () {
                            const inputValue = this.value;

                            if (!regex.test(inputValue)) {
                                mensajeElement.textContent = 'Characters are not allowed except "/" and "-".';
                                this.value = this.value.replace(/[^a-zA-Z0-9\s\/-]/g,
                                    '');
                                // Remover caracteres no válidos
                            } else {
                                mensajeElement.textContent = '';
                            }
                        });
                    }


                    document.addEventListener('DOMContentLoaded', function () {
                        const fieldsToValidate = document.querySelectorAll('[data-validate="true"]');
                        fieldsToValidate.forEach(inputElement => {
                            const mensajeElement = document.createElement('p');
                            mensajeElement.classList.add('text-danger');
                            inputElement.parentNode.appendChild(mensajeElement);

                            validarAlgunosCaracteresEspeciales(inputElement, mensajeElement);
                        });
                    });

                    // Aplicar validación al campo `nombre_usuario`
                    const nombreUsuarioInput = document.getElementById('nombre_usuario');
                    const mensajeNombreUsuario = document.createElement('p');
                    mensajeNombreUsuario.classList.add('text-danger');
                    nombreUsuarioInput.parentNode.appendChild(mensajeNombreUsuario);

                    validarSinCaracteresEspeciales(nombreUsuarioInput, mensajeNombreUsuario);

                    // Aplicar validación al campo `newl`
                    const newLaptopInput = document.getElementById('newl');
                    const mensajeNewLaptop = document.createElement('p');
                    mensajeNewLaptop.classList.add('text-danger');
                    newLaptopInput.parentNode.appendChild(mensajeNewLaptop);

                    validarSinCaracteresEspeciales(newLaptopInput, mensajeNewLaptop);

                    document.getElementById('updateform').addEventListener('submit', function (event) {
                        const select1 = document.getElementById('compra');
                        const select2 = document.getElementById('usrStts');
                        const select3 = document.getElementById('status_change');

                        const requiredSelects = [select1, select2, select3];
                        const errorMessages = [];

                        requiredSelects.forEach((select, index) => {
                            if (select.value === '0') {
                                let message;
                                switch (index) {
                                    case 0:
                                        message = "- Select a Purchase Country.";
                                        break;
                                    case 1:
                                        message = "- Select a User Status.";
                                        break;
                                    case 2:
                                        message = "- Select a Status Change.";
                                        break;
                                }
                                errorMessages.push(message);
                            }
                        });

                        if (errorMessages.length > 0) {
                            event.preventDefault();
                            alert("Please fix the following errors: \n" + errorMessages.join("\n"));
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


</body>

</html>