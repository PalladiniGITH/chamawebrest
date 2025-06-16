<?php
session_start();
require_once 'inc/connect.php';
require_once 'shared/log.php';

if (isset($_POST['email'])) {
    // Em produção, gerar token, guardar em tabela, enviar e-mail com link
    $email = $_POST['email'];

    // Verificar se existe
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email=:e");
    $stmt->execute(['e'=>$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $nova = bin2hex(random_bytes(6));
        $novaSenhaHash = password_hash($nova, PASSWORD_DEFAULT);
        $stmtUpd = $pdo->prepare("UPDATE users SET senha=:s WHERE id=:id");
        $stmtUpd->execute(['s'=>$novaSenhaHash, 'id'=>$user['id']]);
        registrarLog($pdo, 'RESET_SENHA', 'Senha redefinida para usuario '.$email, $user['id']);
        echo "Senha redefinida. Nova senha: <strong>{$nova}</strong>. \n<a href='index.php'>Fazer login</a>";
        exit;
    } else {
        echo "E-mail não encontrado. <a href='reset_password.php'>Tentar novamente</a>";
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Recuperar Senha</title>
  <link rel="stylesheet" href="/css/style.css" />
  <link rel="stylesheet" href="/css/animations.css" />
  <link rel="stylesheet" href="/css/enhanced.css" />
  <link rel="stylesheet" href="/css/theme.css" />
</head>
<body>
    <h1>Recuperar Senha</h1>
    <form method="POST">
        <label>E-mail cadastrado:</label>
        <input type="email" name="email" required />
        <button type="submit">Recuperar</button>
    </form>
    <p><a href="index.php">Voltar ao Login</a></p>
</body>
</html>
