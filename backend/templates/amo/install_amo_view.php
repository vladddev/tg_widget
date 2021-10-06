<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Установка интеграции <?= INTEGRATION_NAME ?></title>
    <link href="css/root.css" rel="stylesheet">
</head>
<body>
<form method="post" action="/install_amo.php" id="reg-form" >
    <input placeholder="Домен amoCrm" name="domain" required>
    <input placeholder="Секретный ключ" name="secKey" required>
    <input placeholder="ID интеграции" name="integrationId" required>
    <textarea placeholder="Код авторизации" name="authCode" required></textarea>
    <button type="submit">Зарегистрировать</button>
</form>
</body>
</html>
