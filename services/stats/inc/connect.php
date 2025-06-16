<?php
// Include the shared connection script. Within the container the shared folder
// lives at /app/shared, so from this directory we go one level up. When running
// the service directly from the repository the structure is services/stats/inc/
// which requires going two directories up instead.
$shared = __DIR__ . '/../shared/connect.php';
if (!file_exists($shared)) {
    $shared = __DIR__ . '/../../shared/connect.php';
}
require_once $shared;


