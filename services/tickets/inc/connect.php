<?php
// Include the shared connection script. Inside the container the shared folder
// sits at /app/shared so we go one directory up from this file's location.
// When running directly from the repository the structure is
// services/tickets/inc/, therefore we need to go two directories up.
$shared = __DIR__ . '/../shared/connect.php';
if (!file_exists($shared)) {
    $shared = __DIR__ . '/../../shared/connect.php';
}
require_once $shared;


