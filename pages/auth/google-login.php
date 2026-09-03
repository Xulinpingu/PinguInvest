<?php

require_once "../../config/connDB.php";

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método não permitido');
}

$credential = $_POST['credential'] ?? '';

if (empty($credential)) {
    http_response_code(400);
    exit('Credential não recebida');
}


/*
 * Client ID da aplicação no Google Cloud
 */
$clientId = "694866377942-unjqo2667j20i9v343f3utrsok8an4i0.apps.googleusercontent.com";


/*
 * Valida o ID Token diretamente com o Google
 */
$url = "https://oauth2.googleapis.com/tokeninfo?id_token=" . urlencode($credential);

$response = file_get_contents($url);

if ($response === false) {
    http_response_code(401);
    exit('Não foi possível validar o token do Google.');
}

$googleUser = json_decode($response, true);

if (!$googleUser) {
    http_response_code(401);
    exit('Token inválido.');
}


/*
 * Confere se o token foi emitido para o nosso Client ID
 */
if (($googleUser['aud'] ?? '') !== $clientId) {
    http_response_code(401);
    exit('Client ID inválido.');
}


/*
 * Confere o e-mail
 */
if (($googleUser['email_verified'] ?? '') !== 'true') {
    http_response_code(401);
    exit('E-mail não verificado pelo Google.');
}


$googleId = $googleUser['sub'] ?? null;
$email    = $googleUser['email'] ?? null;
$nome     = $googleUser['name'] ?? 'Usuário';
$foto     = $googleUser['picture'] ?? null;

if (!$googleId || !$email) {
    http_response_code(401);
    exit('Dados do usuário incompletos.');
}


/*
 * 1. Tenta encontrar pelo Google ID
 */
$sql = "SELECT id_usuario, email, foto
        FROM usuarios
        WHERE google_id = :google_id";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ':google_id' => $googleId
]);

$usuario = $stmt->fetch(PDO::FETCH_ASSOC);


/*
 * 2. Se não encontrou pelo Google ID,
 *    procura pelo e-mail.
 */
if (!$usuario) {

    $sql = "SELECT id_usuario, email, foto
            FROM usuarios
            WHERE email = :email";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':email' => $email
    ]);

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
}


/*
 * Usuário já existe
 */
if ($usuario) {

    $idUsuario = $usuario['id_usuario'];
    $fotoAtual = $usuario['foto'];

    /*
     * Só salva a foto do Google se o usuário
     * ainda não tiver uma foto no PinguInvest.
     */
    if (empty($fotoAtual) && !empty($foto)) {

        $sql = "UPDATE usuarios
                SET google_id = :google_id,
                    foto = :foto
                WHERE id_usuario = :id_usuario";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':google_id' => $googleId,
            ':foto' => $foto,
            ':id_usuario' => $idUsuario
        ]);

    } else {

        /*
         * Usuário já possui uma foto.
         * Mantém a foto do PinguInvest.
         */
        $sql = "UPDATE usuarios
                SET google_id = :google_id
                WHERE id_usuario = :id_usuario";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':google_id' => $googleId,
            ':id_usuario' => $idUsuario
        ]);
    }
}


/*
 * Usuário ainda não existe
 */
else {

    /*
     * Como sua coluna senha é NOT NULL,
     * criamos uma senha aleatória que o usuário
     * nunca precisará conhecer.
     */
    $senhaAleatoria = password_hash(
        bin2hex(random_bytes(32)),
        PASSWORD_DEFAULT
    );

    $sql = "INSERT INTO usuarios
            (nome, email, senha, foto, google_id)
            VALUES
            (:nome, :email, :senha, :foto, :google_id)";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':nome' => $nome,
        ':email' => $email,
        ':senha' => $senhaAleatoria,
        ':foto' => $foto,
        ':google_id' => $googleId
    ]);

    $idUsuario = $pdo->lastInsertId();
}


/*
 * Cria a mesma sessão do login normal
 */
$_SESSION['id_usuario'] = $idUsuario;
$_SESSION['email'] = $email;

echo "OK";