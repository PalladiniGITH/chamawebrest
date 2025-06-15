<?php
// Include the shared connection script using the correct path inside the
// container image. When the service is copied to /app, the shared folder sits
// at /app/shared, so we go one directory up from this file's location.
require_once __DIR__ . '/../shared/connect.php';

