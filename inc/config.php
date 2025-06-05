<?php
// Base URL for API microservice
if (!defined('API_BASE_URL')) {
    define('API_BASE_URL', getenv('API_URL') ?: 'http://localhost:8080');
}
?>
