<?php
session_start();
require_once 'inc/connect.php';
require_once 'shared/log.php';
require_once 'shared/notify.php';

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
            // Gerar OTP e enviar
            $otp = random_int(100000, 999999);
            $_SESSION['pending_user_id'] = $user['id'];
            $_SESSION['otp_code'] = password_hash($otp, PASSWORD_DEFAULT);
            $_SESSION['otp_expires'] = time() + 300; // 5 minutos
            enviarNotificacao($user['id'], 'Código de acesso', "Seu código OTP é: $otp");
            registrarLog($pdo, 'LOGIN', 'Senha correta - aguardando OTP', $user['id']);
            header('Location: otp_verify.php');
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
