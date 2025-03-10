<?php
if ($resultados) {
    ob_start(); // Start output buffering
    foreach ($resultados as $row) {

        $assetname = htmlspecialchars($row["assetname"]);
        $serial_number = htmlspecialchars($row["serial_number"]);
        $user_status = htmlspecialchars($row["user_status"]);
        $last_user = htmlspecialchars($row["last_user"]);
        $job_title = htmlspecialchars($row["job_title"]);
        $status_change = htmlspecialchars($row["status_change"]);
        $purchase_country = htmlspecialchars($row["purchase_country"]);
        $warranty_enddate = htmlspecialchars($row["warranty_enddate"]);
        $expired = htmlspecialchars($row["expired"]);
        $new_laptop = htmlspecialchars($row["new_laptop"]);
        $cedula = htmlspecialchars($row["cedula"]);
        // Check warranty end date
        $warranty_class = (strtotime($row["warranty_enddate"]) < time()) ? 'text-danger' : 'text-success';
        echo "<tr>";
        // check box
        echo '<td><input type="checkbox" id="selected_assets[]" name="selected_assets[]" value="' . $assetname . '"></td>';
        echo "<td>$assetname</td>";
        echo "<td>$serial_number</td>";
        echo "<td>$user_status</td>";
        echo "<td>$last_user</td>";
        echo "<td>$job_title</td>";
        echo "<td>$status_change</td>";
        echo "<td>$purchase_country</td>";
        echo "<td><span class='$warranty_class'>$warranty_enddate</span></td>";
        echo "<td>$expired</td>";
        echo "<td>$new_laptop</td>";
        echo "<td>$cedula</td>";
        // Datos dianmicos en caso de que se requieran
        foreach ($selectedColumns as $column) {
            if (isset($row[$column])) {
                // Check if the column is a state
                $value = is_numeric($row[$column]) && in_array($row[$column], [0, 1]) ? ($row[$column] == 1 ? "Yes" : "No") : htmlspecialchars($row[$column]);
                echo "<td>$value</td>";
            } else {
                echo "<td></td>";
            }
        }
        // Boton de actualizacion
        echo '<td><a href="./Model/act_registro.php?assetname=' . $assetname . '" class="btn btn-secondary btn-sm"><i class="fas fa-sync-alt"></i></a></td>';
        // Boton de eliminacion de registro
        echo '<td><a href="./Model/elim_registro.php?assetname=' . $assetname . '" class="btn btn-danger btn-sm" onclick="return confirm(\'Are you sure you want to delete this item?\')"><i class="fa fa-times"></i></a></td>';
        echo "</tr>";
    }
    echo ob_get_clean(); // Output the buffered content
} else {
    echo "<tr><td colspan='" . (count($selectedColumns) + 12) . "'>No data found</td></tr>";
}
?>