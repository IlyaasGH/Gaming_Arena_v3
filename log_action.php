<?php
function log_action($conn, $action, $source, $user_id = null, $admin_id = null)
{
    $sql = "INSERT INTO audit_logs (user_id, admin_id, action, source)
            VALUES (?, ?, ?, ?)";
    $params = [$user_id, $admin_id, $action, $source];
    sqlsrv_query($conn, $sql, $params);
}
