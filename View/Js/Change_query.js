// Funcionalidaad obsolte para la muestra y posbile edicion de sentencia SQL en base a los parametros de busqueda del indice.php
document
  .getElementById("showQueryButton")
  .addEventListener("click", function () {
    // Obtener valores de los inputs por ID
    const assetname = document.getElementById("search_assetname").value;
    const serial = document.getElementById("search_serial").value;
    const cedula = document.getElementById("search_cedula").value;
    const user = document.getElementById("search_user").value;
    const statusChange = document.getElementById("search_status_change").value;
    const fechaInicio = document.getElementById("search_fecha_inicio").value;
    const fechaFin = document.getElementById("search_fecha_fin").value;
    const userStatus = Array.from(
      document.getElementById("search_user_status").selectedOptions
    ).map((option) => option.value);
    // Construir la consulta SQL
    let sqlQuery =
      "SELECT * FROM equipos INNER JOIN usuarios_equipos ON equipos.assetname = usuarios_equipos.fk_assetname WHERE 1=1";

    // Añadir condiciones según los parámetros
    if (assetname) {
      sqlQuery += ` AND LOWER(assetname) LIKE LOWER('%${assetname}%')`;
    }
    if (serial) {
      sqlQuery += ` AND LOWER(serial_number) LIKE LOWER('%${serial}%')`;
    }
    if (cedula) {
      sqlQuery += ` AND LOWER(cedula) LIKE '%${cedula}%'`;
    }
    if (user) {
      sqlQuery += ` AND LOWER(last_user) LIKE LOWER('%${user}%')`;
    }
    if (statusChange) {
      sqlQuery += ` AND LOWER(status_change) LIKE LOWER('%${statusChange}%')`;
    }
    if (fechaInicio) {
      sqlQuery += ` AND STR_TO_DATE(warranty_enddate, '%m/%d/%Y') >= '${fechaInicio}'`;
    }
    if (fechaFin) {
      sqlQuery += ` AND STR_TO_DATE(warranty_enddate, '%m/%d/%Y') <= '${fechaFin}'`;
    }
    if (userStatus.length > 0) {
      sqlQuery += ` AND (${userStatus
        .map((status) => `user_status = '${status}'`)
        .join(" OR ")})`;
    }

    // Mostrar en el textarea
    document.getElementById("sqlQuery").value = sqlQuery;
  });
