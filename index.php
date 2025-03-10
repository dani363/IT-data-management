<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Index</title>
    <link rel="shortcut icon" href="./Configuration/logo.ico" type="image/x-icon">
    <script src="./Configuration/JQuery/jquery-3.7.1.js"></script>
    <link href="./Configuration/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./Configuration/JQuery/all.min.css">
    <link rel="stylesheet" href="./Configuration/JQuery/fontawesome.min.css">
    <link rel="stylesheet" href="./Configuration/DataTables/datatables.min.css">
    <link rel="stylesheet" href="./View/Css/dark-mode.css">
    <link rel="stylesheet" href="./View/Css/Datatable.css">
</head>

<body>
    <div class="row align-items-center justify-content-center mb-3">

        <div class="col-md-3 mb-2 my-5">
            <!--Condiciones para traer los datos en caso de error o alerta y confirmacion de subida de datos-->
            <?php
            if (isset($_COOKIE['error_message'])) {
                echo '<div class="alert alert-danger">' . htmlspecialchars($_COOKIE['error_message']) . '</div>';
                setcookie('error_message', '', time() - 4600, '/'); // Eliminar la cookie
            }

            if (isset($_SESSION['error'])) {
                echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['error']) . '</div>';
                unset($_SESSION['error']); // Limpia el mensaje después de mostrarlo
            }

            if (isset($_SESSION['success'])) {
                echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success']) . '</div>';
                unset($_SESSION['success']);
            }

            if (isset($_SESSION['warnings']) && count($_SESSION['warnings']) > 0) {
                echo '<div class="alert alert-warning">';
                echo '<ul>';
                $maxDisplayWarnings = 10; // Límite máximo de advertencias a mostrar
                $displayedWarnings = array_slice($_SESSION['warnings'], 0, $maxDisplayWarnings); // Limita las advertencias a mostrar

                foreach ($displayedWarnings as $warning) {
                    echo '<li>' . htmlspecialchars($warning) . '</li>';
                }

                if (count($_SESSION['warnings']) > $maxDisplayWarnings) {
                    echo '<li>And ' . (count($_SESSION['warnings']) - $maxDisplayWarnings) . ' more warnings...</li>';
                }
                echo '</ul>';
                echo '</div>';
                unset($_SESSION['warnings']); // Limpia las advertencias después de mostrarlas
            }
            ?>
            <script>
                //Funcion para mostar advertencias 
                function showWarnings() {
                    const hiddenWarnings = document.getElementById('hidden-warnings');
                    hiddenWarnings.classList.add('show'); // Muestra las advertencias ocultas
                    event.target.classList.add('hide'); // Oculta el botón
                }
            </script>
            <!--Formulario de la entrada de archivo CSV-->
            <form action="./Controller/Importar_BD.php" method="POST" enctype="multipart/form-data"
                class="mb-3 my-5 shadow-sm">
                <input type="file" name="Proyeccion_garan" class="form-control form-control-sm">
                <button type="submit" class="form-control form-control-sm">upload file</button>
            </form>
        </div>
        <?php
        include './Configuration/Connection.php';

        try {
            //Consulta para contar el numero de registros
            $stmt = $pdo->prepare("SELECT serial_number FROM vista_equipos_usuarios");
            $stmt->execute();
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            // Lista de CLientes (Numero de Registros)
            $Totalclient = count($resultados);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
        ?>
        <br>
        <div id="Consulta" class="mb-3">
            <div class="row mb-3">
                <form method="GET" action="">
                    <div class="container-fluid">
                        <div class="col-md-12 text-center mb-5">
                            <!--variable para traer el numero de registrso que existen-->
                            <h1 id="totalC" data-i18n="totalC" class="counter">Asset List
                                <strong id="total-client">
                                    <?php echo number_format($Totalclient, 0, ',', '.'); ?>
                                </strong>
                            </h1>
                            <script>
                                const counterElement = document.getElementById('total-client');
                                const counter = parseInt(counterElement.textContent);
                                let count = 0;
                                const intervalId = setInterval(() => {
                                    if (count < counter) {
                                        count++;
                                        counterElement.textContent = count.toString().padStart(counter.toString().length, '0');
                                    } else {
                                        clearInterval(intervalId);
                                    }
                                }, 10);
                            </script>
                        </div>
                        <!-- Barra Busqueda -->
                        <nav id="Navitem"
                            class="navbar navbar-expand-lg navbar-light bg-light fixed-top rounded shadow-sm">
                            <div class="container-fluid">

                                <a href="<?php echo $_SERVER['PHP_SELF']; ?>"
                                    class="navbar-brand d-flex align-items-center">
                                    <img src="./Configuration/logo.ico" alt="Logo" width="30" height="30">
                                    <span class="ms-2">3 Shape</span>
                                </a>

                                <!-- Botón hamburguesa para móviles -->
                                <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#navbarCollapse" aria-controls="navbarCollapse"
                                    aria-expanded="false" aria-label="Toggle navigation">
                                    <span class="navbar-toggler-icon"></span>
                                </button>

                                <!-- Contenido colapsable -->
                                <div class="collapse navbar-collapse" id="navbarCollapse">
                                    <div class="navbar-nav">
                                        <a id="generatePDF" class="nav-link me-2" data-url="./Model/PruebaV.php"
                                            target="_blank">Generate General PDF</a>
                                        <a id="generateActaSalida" class="nav-link me-2"
                                            data-url="./Model/Acta_salida.php" data-i18n="generateActaSalida">Generate
                                            Departure Certificate</a>
                                        <a id="generateActaEntrada" class="nav-link me-2"
                                            data-url="./Model/Acta_entrada.php" data-i18n="generateActaEntrada">Generate
                                            Entry Certificate</a>
                                        <a href="./View/Int_entrada.php" class="nav-link me-2">Change Asset</a>
                                        <a href="./View/Int_salida.php" class="nav-link me-2">Output Asset</a>
                                    </div>
                                </div>
                            </div>
                        </nav>

                        <!-- Formulario de Consulta -->
                        <div id="Barra_Busqueda" class="d-flex row align-items-center justify-content-center ">
                            <div class=" col-md-12 ">
                                <div class="row g-2">
                                    <div class="col-md-2 col-sm-6 mb-2">
                                        <input type="text" name="search_assetname" placeholder="Asset Name"
                                            class="form-control shadow-sm bg-light"
                                            oninput="this.value = this.value.toUpperCase()" autocomplete="off">
                                    </div>
                                    <div class="col-md-2 col-sm-6 mb-2">
                                        <input type="text" id="search_serial" name="search_serial"
                                            placeholder="Serial Number" class="form-control shadow-sm bg-light"
                                            autocomplete="off" oninput="this.value = this.value.toUpperCase()">
                                    </div>
                                    <div class="col-md-2 col-sm-6 mb-2">
                                        <select id="search_user_status" name="search_user_status[]"
                                            class="shadow-sm form-control bg-light">
                                            <option value="0">Select a option</option>

                                            <?php
                                            // Cargar opciones de estado de usuario de forma dinamica
                                            include './Configuration/Connection.php';
                                            $stmt = $pdo->prepare("SELECT DISTINCT user_status FROM vista_equipos_usuarios");
                                            $stmt->execute();
                                            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                            if (empty($resultados)) {
                                                echo '<option value=""> No results found </option>';
                                            } else {
                                                foreach ($resultados as $resultado) {
                                                    echo '<option value="' . $resultado['user_status'] . '">' . $resultado['user_status'] . '</option>';
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-md-2 col-sm-6 mb-2">
                                        <input type="text" name="search_status_change" placeholder="Status Change"
                                            class="shadow-sm form-control bg-light">
                                    </div>
                                    <div class="col-md-2 col-sm-6 mb-2">
                                        <input type="text" id="garantia" name="search_fecha_fin" placeholder="End date"
                                            class="shadow-sm form-control bg-light">
                                    </div>
                                    <div class="col-md-2 col-sm-6 mb-2">
                                        <input type="text" id="sgarantia" name="search_fecha_inicio"
                                            placeholder="Start end" class="shadow-sm form-control bg-light">
                                    </div>
                                    <div class="col-md-2 col-sm-6 mb-2">
                                        <input type="number" id="search_cedula" name="search_cedula" placeholder="ID"
                                            class="form-control shadow-sm bg-light" autocomplete="off" min="0">
                                    </div>
                                    <div class="col-md-2 col-sm-6 mb-2">
                                        <input type="text" id="search_user" name="search_user" placeholder="Last User"
                                            class="form-control shadow-sm bg-light" autocomplete="off">
                                    </div>
                                    <div class="my-2 mb-2 d-flex justify-content-left">
                                        <button type="submit" class="shadow btn btn-light border-success me-1"
                                            id="search_btn">Search</button>
                                        <button type="reset" class="shadow btn btn-light border-danger"
                                            id="reset_btn">Reset</button>
                                    </div>
                                    <script>
                                        // Boton de Restart
                                        document.getElementById('reset_btn').addEventListener('click', function() {
                                            document.querySelectorAll('input[type="text"], select').forEach(input => input.value = '');
                                            document.querySelectorAll('input[type="number"]').forEach(input => input.value = '');
                                        });
                                        // Boton de Search
                                        document.addEventListener('keydown', (event) => {
                                            if (event.key === 'Enter') {
                                                document.getElementById('search_btn').click();
                                            }
                                        });
                                    </script>
                                </div>
                                <script>
                                    //Funcion para la validacion de entrada de fecha
                                    document.addEventListener('DOMContentLoaded', function() {
                                        const garantiaInput = document.getElementById('garantia');
                                        const sgarantiaInput = document.getElementById('sgarantia');

                                        function formatInputDate(input) {
                                            input.addEventListener('input', function(e) {
                                                let value = e.target.value.replace(/\D/g, '');
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

                                            input.addEventListener('blur', function(e) {
                                                const datePattern =
                                                    /^(0[1-9]|1[0-2])\/(0[1-9]|[12]\d|3[01])\/\d{4}$/;
                                                if (!datePattern.test(e.target.value)) {
                                                    e.target.classList.add('is-invalid');
                                                } else {
                                                    e.target.classList.remove('is-invalid');
                                                }
                                            });
                                        }

                                        formatInputDate(garantiaInput);
                                        formatInputDate(sgarantiaInput);
                                    });
                                </script>
                            </div>
                            <div id="resultados3" class="text-center my-2"></div>
                            <div id="resultados2" class="text-center my-2"></div>
                            <div id="resultados" class="text-center my-2"></div>
                            <script>
                                //Script automatico para la busqueda del ultimo usuario
                                document.getElementById('search_user').addEventListener('input', function() {
                                    const value = this.value;
                                    if (value.length > 0) {
                                        this.value = this.value.replace(/[^a-zA-Z0-9\s]/g, '');
                                    }
                                });
                            </script>
                            <script>
                                //Script para la busqueda automatica de la cedula 
                                // Evitar números negativos
                                document.getElementById('search_cedula').addEventListener('input', function() {
                                    const value = this.value;
                                    if (value < 0) {
                                        this.value = value.replace('-', ''); // Elimina el signo negativo
                                    }
                                });

                                // Función de búsqueda automática de la cedula
                                $('#search_cedula').keyup(function() {
                                    var cedula = $(this).val();

                                    if (cedula.length > 3) { // Solo busca cuando hay más de 2 caracteres
                                        $.ajax({
                                            url: './Controller/buscar_id.php',
                                            type: 'POST',
                                            data: {
                                                cedula
                                            },
                                            success: function(data) {
                                                $('#resultados').html(data)
                                                    .show(); // Mostrar los resultados
                                            },
                                            error: function(xhr, status, error) {
                                                console.error("Error en AJAX:", error);
                                            }
                                        });
                                    } else {
                                        $('#resultados').hide(); // Oculta el contenedor si hay pocos caracteres
                                    }
                                });
                                // Al hacer click en un resultado de búsqueda, se selecciona la cédula correspondiente y se ocultan los resultados
                                $('#resultados').on('click', '.result-item', function() {
                                    const value = $(this).text();
                                    $('#search_cedula').val(value);
                                    $('#resultados').hide();
                                });

                                // Oculta el contenedor de resultados si se hace clic fuera de él
                                $(document).click(function(e) {
                                    if (!$(e.target).closest('#search_cedula, #resultados').length) {
                                        $('#resultados').hide();
                                    }
                                });

                                $('#search_serial').keyup(function() {
                                    const serial = $(this).val();

                                    if (serial.length > 3) {
                                        // Consulta para verificar que el numero de serie no este registrado
                                        $.ajax({
                                            url: './Controller/buscar_sr.php',
                                            type: 'POST',
                                            data: {
                                                serial
                                            },
                                            // Mostrar registros de la busqueda
                                            success: function(data) {
                                                $('#resultados2').html(data).show();
                                            },
                                            error: function(xhr, status, error) {
                                                // Validación de errores
                                                console.error("Error en AJAX:", error);
                                            }
                                        });
                                    } else {
                                        $('#resultados2').hide();
                                    }
                                });

                                $('#resultados2').on('click', '.result-item', function() {
                                    const value = $(this).text();
                                    $('#search_serial').val(value);
                                    $('#resultados2').hide();
                                });

                                $(document).click(function(e) {
                                    if (!$(e.target).closest('#search_serial, #resultados2').length) {
                                        $('#resultados2').hide();
                                    }
                                });

                                $('#search_user').keyup(function() {
                                    const user = $(this).val();

                                    if (user.length > 3) {
                                        // Consulta para verificar que el numero de serie no este registrado
                                        $.ajax({
                                            url: './Controller/buscar_user.php',
                                            type: 'POST',
                                            data: {
                                                user
                                            },
                                            // Mostrar registros de la busqueda
                                            success: function(data) {
                                                $('#resultados3').html(data).show();
                                            },
                                            error: function(xhr, status, error) {
                                                // error validation
                                                console.error("Error en AJAX:", error);
                                            }
                                        });
                                    } else {
                                        $('#resultados3').hide();
                                    }
                                });

                                $('#resultados3').on('click', '.result-item', function() {
                                    const value = $(this).text();
                                    $('#search_user').val(value);
                                    $('#resultados3').hide();
                                });

                                $(document).click(function(e) {
                                    if (!$(e.target).closest('#search_user, #resultados3').length) {
                                        $('#resultados3').hide();
                                    }
                                });
                            </script>
                        </div>
                        <script>
                            // Selecciona todos los botones de generación de documentos
                            document.querySelectorAll('[id^="generate"]').forEach(button => {
                                button.addEventListener('click', function(e) {
                                    e.preventDefault();
                                    // Obtiene la URL del archivo PHP desde el atributo data-url
                                    const url = button.getAttribute('data-url');
                                    // Recoge los valores de los filtros
                                    const params = new URLSearchParams();
                                    params.append('search_assetname', document.querySelector('input[name="search_assetname"]').value.trim());
                                    params.append('search_serial', document.querySelector('input[name="search_serial"]').value.trim());
                                    params.append('search_cedula', document.querySelector('input[name="search_cedula"]').value.trim());
                                    params.append('search_status_change', document.querySelector('input[name="search_status_change"]').value.trim());
                                    params.append('search_user', document.querySelector('input[name="search_user"]').value.trim());
                                    // Fecha de inicio y fin
                                    params.append('search_fecha_inicio', document.querySelector('input[name="search_fecha_inicio"]').value.trim());
                                    params.append('search_fecha_fin', document.querySelector('input[name="search_fecha_fin"]').value.trim());
                                    // Estado del usuario (múltiple)
                                    const userStatuses = Array.from(document.querySelectorAll('select[name="search_user_status[]"] option:checked')).map(el => el.value);
                                    userStatuses.forEach(status => params.append('search_user_status[]', status));

                                    // Redirige al archivo PHP seleccionado con los parámetros de búsqueda
                                    window.open(url + '?' + params.toString(), '_blank');
                                });
                            });
                        </script>
                        <div class="col-md-4">
                            <?php
                            include_once './Controller/busqueda_Multicriterio.php';

                            ?>
                        </div>
                    </div>
                    <!--Inputs de Busqueda-->
            </div>
        </div>
        <br>
        </form>
        <div class="table-responsive my-5">
            <!--Boton para registrar un nuevo equipo-->

            <!--Tabla Principal donde se muestran los datos en este caso los activos de la empresa-->
            <div class="table-container">
                <?php
                $selectedColumns = isset($_SESSION['selected_fields']) ? $_SESSION['selected_fields'] : [];
                ?>
                <div class="btn-group" role="group" aria-label="Basic radio toggle button group">
                    <a href="./View/Int_Registro_equipo.php" class="shadow btn btn-secondary "><i
                            class="fas fa-plus"></i>
                        Register
                        new device</a>
                    <button id="deleteAllButton" type="button" class="shadow btn btn-danger ">
                        Delete All Logs</button>
                    <script>
                        //Script para la eliminacion de todos los registros
                        document.getElementById('deleteAllButton').addEventListener('click', function() {
                            if (confirm('Are you sure you want to delete all logs?')) {
                                if (window.confirm('Are you sure you want to delete all logs?')) {
                                    window.location.href = './Controller/delete_regist.php';
                                }
                            }
                        });
                    </script>
                </div>
                <table id="mainTable" class="shadow-lg table table-bordered table-striped fade-in">

                    <thead class="text-center">
                        <tr class="align-items-center text-center">
                            <!--Columnas de la tabla-->
                            <th>@</th>
                            <th>Asset Name</th>
                            <th>Serial Number</th>
                            <th>User Status</th>
                            <th>Last User</th>
                            <th>Job Title</th>
                            <th>Status Change</th>
                            <th>Purchase Country</th>
                            <th>Warranty End Date</th>
                            <th>Expired</th>
                            <th>New Laptop</th>
                            <th>ID</th>
                            <?php
                            //Se muestran las columnas que el usuario selecciono
                            $headerTemplate = "<th class='text-center text-uppercase'>%s</th>";
                            // Se recorre el arreglo de columnas seleccionadas
                            $headers = implode('', array_map(function ($column) use ($headerTemplate) {
                                return sprintf($headerTemplate, $column);
                            }, $selectedColumns));
                            echo $headers;
                            ?>
                            <th class="text-center"><i class="fas fa-sync-alt"></i></th>
                            <th class="text-center"><i class="fa fa-times"></i></th>
                        </tr>
                    </thead>
                    <tbody class="text-center">
                        <?php
                        include './Model/Main_Table.php';
                        ?>
                    </tbody>
                    <!--Ventana emergente donde se selecciona las opcion de select process-->
                    <div id="modalConfirmacion" style="    
                           position: fixed;
                           top: 0;
                           left: 0;
                           width: 100%;
                           height: 100%;
                           background-color: rgba(0, 0, 0, 0.5);
                           z-index: 9999; /* High z-index to ensure it's on top */ 
                           display: none;">
                        <div style="  
                               position: absolute;
                               top: 50%;
                               left: 50%;
                               transform: translate(-50%, -50%);
                               background-color: #fff;
                               border-radius: 10px;
                               padding: 20px;
                               box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);">
                            <button id="btnCerrarModal"
                                style="position: absolute; top: 10px; right: 10px; background: none; border: none; font-size: 20px; cursor: pointer;">&times;</button>
                            <p id="modalMensaje" style="color: black;"></p>
                            <button id="btnAceptar" class="btn btn-secondary" target="_blank">Option 1: Generate
                                Departure</button>
                            <button id="btnCancelar" class="btn btn-secondary my-2" target="_blank">Option 2: Create
                                Entry</button>
                            <button id="btnOpcion3" class="btn btn-secondary my-2" target="_blank">Option 3:
                                Asset Information </button>
                        </div>
                    </div>
                    <script>
                        window.onload = function() {
                            // Crear botón dinámicamente
                            const procesarSeleccionadosBtn = `
                            <button id="procesarSeleccionados" class="shadow btn btn-secondary my-2" title="Process Selected Items">
                            <i class="fas fa-cog"></i> Process Selected
                            </button>`
                            const table = document.querySelector('table');
                            if (table) {
                                table.insertAdjacentHTML('beforebegin', procesarSeleccionadosBtn);
                            } else {
                                console.error('dont found a table to the DOM.');
                                return;
                            }
                            // Variable global para almacenar los parámetros seleccionados
                            let parametros = "";

                            // Manejador de clic del botón
                            document.getElementById('procesarSeleccionados').addEventListener('click', function() {
                                const checkboxesSeleccionados = document.querySelectorAll(
                                    'input[name="selected_assets[]"]:checked');
                                // Validar que haya al menos un checkbox seleccionado
                                if (checkboxesSeleccionados.length === 0) {
                                    alert('Please, select at least one item to process.');
                                    return;
                                }

                                // Construir lista de parámetros seleccionados
                                parametros = Array.from(checkboxesSeleccionados)
                                    .map(checkbox => 'ids[]=' + encodeURIComponent(checkbox.value))
                                    .join('&');

                                // Mostrar el modal
                                const modal = document.getElementById('modalConfirmacion');
                                const modalMensaje = document.getElementById('modalMensaje');
                                modalMensaje.textContent =
                                    'Do you want to process in Option 1 to generate an output record, in Option 2 to create an input record, or in Option 3 for a new option?';
                                modal.style.display = 'block';

                                // Depuración en consola
                                console.log('show modal');
                                console.log('Parameters Select:', parametros);
                            });

                            // Manejadores de botones del modal
                            document.getElementById('btnAceptar').onclick = function(event) {
                                event.preventDefault(); // Prevenir comportamiento por defecto
                                if (!parametros) {
                                    console.error("Error: Parameters not defined.");
                                    return;
                                }
                                // Redirigir a la URL de la opción 1
                                const urlOpcion1 =
                                    `${window.location.origin}/ProgramaC/Model/prcsr_indv_sal.php?${parametros}`;
                                console.log(`Redirigiendo a: ${urlOpcion1}`);
                                window.location.href = urlOpcion1;
                            };

                            document.getElementById('btnCancelar').onclick = function(event) {
                                event.preventDefault(); // Prevenir comportamiento por defecto
                                if (!parametros) {
                                    console.error("Error: Parameters not defined.")
                                    return;
                                }
                                // Redirigir a la URL de la opción 2
                                const urlOpcion2 =
                                    `${window.location.origin}/ProgramaC/Model/prcsr_indv_ent.php?${parametros}`;
                                console.log(`Redirigiendo a: ${urlOpcion2}`);
                                window.location.href = urlOpcion2;
                            };

                            document.getElementById('btnOpcion3').onclick = function(event) {
                                event.preventDefault(); // Prevenir comportamiento por defecto
                                if (!parametros) {
                                    console.error("Error: Parameters not defined.");
                                    return;
                                }
                                // Redirigir a la URL de la opción 3
                                const urlOpcion3 =
                                    `${window.location.origin}/ProgramaC/Model/prcr_nuevo_info.php?${parametros}`;
                                console.log(`Redirigiendo a: ${urlOpcion3}`);
                                window.location.href = urlOpcion3;
                            };

                            // Manejador del botón de cerrar modal
                            document.getElementById('btnCerrarModal').onclick = function() {
                                const modal = document.getElementById('modalConfirmacion');
                                modal.style.display = 'none';
                            };
                        };
                    </script>
                </table>
                <br>
                <br>
                <label>Ctrl + Q for login as <a href="#" id="ShowmiFormulario">Manager</a></label>
                <script>
                    //Funcion para mostar advertencias 
                    function showWarnings() {
                        const hiddenWarnings = document.getElementById('hidden-warnings');
                        hiddenWarnings.style.display = 'block'; // Muestra las advertencias ocultas
                        event.target.style.display = 'none'; // Oculta el botón
                    }

                    // Función para alternar la visibilidad de los formularios
                    function toggleFormVisibility(linkId, formId) {
                        document.getElementById(linkId).addEventListener('click', function(event) {
                            event.preventDefault(); // Evitar comportamiento por defecto del enlace
                            const form = document.getElementById(formId);
                            form.style.display = (form.style.display === 'block' || form.style.display === '') ?
                                'none' :
                                'block';
                        });
                    }

                    document.addEventListener('DOMContentLoaded', function() {
                        toggleFormVisibility('ShowmiFormulario', 'miFormulario');
                    });
                </script>
                <br>
            </div>
        </div>
        <div id="FormAdmin" class="container">
            <div class="row d-flex justify-content-center align-items-center mt-5">
                <div class="col-md-4 text-center">
                    <?php
                    // Generar un token CSRF si no existe
                    if (empty($_SESSION['csrf_token'])) {
                        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                    }
                    ?>
                    <form id="miFormulario" style="display: none;" action="./Admin/token.php" method="POST"
                        class="bg-light p-4 rounded shadow-lg">
                        <h2 id="totalC" class="text-center mb-4">Admin Login</h2>
                        <input type="hidden" name="csrf_token"
                            value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                        <div class="form-group mb-3">
                            <input type="password" id="passwordadmin" name="passwordadmin"
                                placeholder="Enter your password" class="form-control shadow-lg" required
                                autocomplete="off">
                            <div class="invalid-feedback">Please enter a valid password.</div>
                        </div>
                        <button type="submit" class="btn btn-secondary w-100">Send</button>
                        <div class="text-center mt-2">
                            <a href="./View/Int_forgot_pasword.php" id="ShowmiFormulario">Forgot Password?</a>
                        </div>
                    </form>
                </div>
            </div>
            <script>
                document.addEventListener('keydown', (event) => {
                    if (event.ctrlKey && (event.key === 'q' || event.key === 'Q')) {
                        const formulario = document.getElementById('miFormulario');
                        formulario.style.display = formulario.style.display === 'block' ? 'none' : 'block';
                        event.preventDefault(); // Prevent default behavior
                    }
                });
            </script>

        </div>
        <!-- Scripts -->
        <script id="Datatable" src="./View/Js/DatatableIndex.js"></script>
        <script src="./Configuration/JQuery/jquery-3.7.1.js"></script>
        <script src="./Configuration/DataTables/datatables.min.js"></script>
        <script src="./Configuration/bootstrap/js/bootstrap.min.js"></script>
        <script src="./View/Js/Change_query.js"></script>
</body>
<footer class="align-items-center text-center mt-5">
    <p class="text-muted">&copy; <?php echo date("Y"); ?> - All rights reserved.</p>
    <p class="text-muted">Page created on: <?php echo date("F j, Y", filectime(__FILE__)); ?></p>
    <p class="text-muted">Page name: <?php echo basename(__FILE__); ?></p>
</footer>

</html>