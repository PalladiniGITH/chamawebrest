<?php
session_start();
require_once 'inc/connect.php';
require_once 'shared/log.php';

$email = strtolower(trim($_POST['email'] ?? ''));
$senha = $_POST['senha'] ?? '';

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email AND auth_provider = 'local' AND blocked = 0 LIMIT 1");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $senha_valida = false;
        if (password_verify($senha, $user['senha'])) {
            $senha_valida = true;
        }
        if ($user['senha'] === $senha) {
            $senha_valida = true;
        }
        if ($senha_valida) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role']    = $user['role'];
            $_SESSION['nome']    = $user['nome'];

            registrarLog($pdo, 'LOGIN', 'Login local', $user['id']);
            header('Location: dashboard.php');
            exit;
        } else {
            registrarLog($pdo, 'ERRO_LOGIN', 'Senha incorreta', $user['id']);
            echo "Senha inválida!";
        }
    } else {
        registrarLog($pdo, 'ERRO_LOGIN', 'Usuário inexistente: '.$email);
        echo "Nenhum usuário localizado.";
    }
} catch (PDOException $e) {
    echo "Erro de PDO: " . $e->getMessage();
}
exit;
?>
