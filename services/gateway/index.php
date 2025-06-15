<?php
$requestUri = filter_var(strtok($_SERVER['REQUEST_URI'], '?'), FILTER_SANITIZE_URL);
if (strpos($requestUri, '..') !== false) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid path']);
    return;
}
$method = $_SERVER['REQUEST_METHOD'];

if ($requestUri === '/' || $requestUri === '') {
    echo json_encode([
        'message' => 'API Gateway',
        'endpoints' => ['/tickets', '/stats']
    ]);
    return;
} elseif (strpos($requestUri, '/tickets') === 0) {
    $service = 'http://tickets:80/index.php' . substr($requestUri, 8);
} elseif (strpos($requestUri, '/stats') === 0) {
    $service = 'http://stats:80/index.php' . substr($requestUri, 6);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Rota nao encontrada']);
    return;
}

// Log simples para verificar o encaminhamento das rotas
$log = sprintf("[%s] %s %s -> %s\n", date('c'), $method, $requestUri, isset($service) ? $service : 'N/A');
file_put_contents('php://stdout', $log, FILE_APPEND);

$options = [
    'http' => [
        'method' => $method,
        'header' => '',
        'content' => file_get_contents('php://input'),
    ]
];
foreach (getallheaders() as $name => $value) {
    if ($name === 'Host') continue;
    $options['http']['header'] .= "$name: $value\r\n";
}
$context = stream_context_create($options);
$query = http_build_query($_GET);
$response = file_get_contents($service . ($query ? '?' . $query : ''), false, $context);

$httpCode = 500;
if (isset($http_response_header[0]) && preg_match('#HTTP/\d+\.\d+\s+(\d+)#', $http_response_header[0], $matches)) {
    $httpCode = (int)$matches[1];
}
// Registrar o status retornado pelo microserviço
$log = sprintf("[%s] resposta %d\n", date('c'), $httpCode);
file_put_contents('php://stdout', $log, FILE_APPEND);
http_response_code($httpCode);
echo $response;
?>
