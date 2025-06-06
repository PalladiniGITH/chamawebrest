<?php
session_start();
require_once 'shared/connect.php';
require_once 'shared/log.php';
require_once 'shared/notify.php';

if (!isset($_SESSION['pending_user_id'])) {
    header('Location: index.html');
    exit;
}

$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = $_POST['otp'] ?? '';
    if (isset($_SESSION['otp_code'], $_SESSION['otp_expires']) &&
        time() < $_SESSION['otp_expires'] &&
        password_verify($code, $_SESSION['otp_code'])) {

        $uid = $_SESSION['pending_user_id'];
        $stmt = $pdo->prepare('SELECT role, nome FROM users WHERE id=?');
        $stmt->execute([$uid]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $_SESSION['user_id'] = $uid;
        $_SESSION['role'] = $user['role'];
        $_SESSION['nome'] = $user['nome'];

        unset($_SESSION['pending_user_id'], $_SESSION['otp_code'], $_SESSION['otp_expires']);

        registrarLog($pdo, 'LOGIN', 'Login via OTP', $uid);
        header('Location: dashboard.php');
        exit;
    } else {
        $erro = 'Código inválido';
        registrarLog($pdo, 'ERRO_LOGIN', 'OTP incorreto', $_SESSION['pending_user_id']);
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Verificar OTP</title>
    <link rel="stylesheet" href="css/style.css" />
</head>
<body>
    <div class="login-container">
        <h1>Verificação de Código</h1>
        <?php if ($erro): ?>
            <div class="error-message"><?php echo $erro; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-field">
                <label for="otp">Código OTP</label>
                <input type="text" id="otp" name="otp" required />
            </div>
            <button type="submit" class="button-primary">Confirmar</button>
        </form>
    </div>
</body>
</html>
