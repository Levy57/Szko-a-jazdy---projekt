<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('memory_limit', -1);
set_time_limit(0);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') exit;

function slugify($string) {
    $slug = strtolower($string);
    $slug = preg_replace("/[^a-z0-9-]+/", "-", $string);
    return trim($slug, "-");
}

$firma = trim($_POST['nazwa_firmy']);
$domena = slugify(trim($_POST['nazwa_domeny']));
$imie = trim($_POST['imie']);
$nazwisko = trim($_POST['nazwisko']);
$numer_telefonu = trim($_POST['numer_telefonu']);
$email = trim($_POST['email']);
$kategorie = $_POST['kategorie'];
$kategorie = implode(',', $kategorie);
$haslo = trim($_POST['haslo']);
$password = password_hash($haslo, PASSWORD_BCRYPT, ['cost' => 13]);

$username = "P-" . strtoupper(substr($imie, 0, 1)) . strtoupper(substr($nazwisko, 0, 1)) . str_pad(1, 2, '0', STR_PAD_LEFT);

$sciezka = escapeshellarg("../firmy/$domena");

$databases = file_get_contents('databases.json');
$databases = json_decode($databases);
$database = $databases[0];
unset($databases[0]);
$file = fopen('./databases.json', 'w');
fwrite($file, json_encode($databases));
exit;
$env = fopen('env.sample.txt', 'r');
$env = fread($env, filesize('env.sample.txt'));
$env = str_replace('<database_name>', $database->database_name, $env);
$env = str_replace('<database_user>', $database->database_user, $env);
$env = str_replace('<database_host>', $database->database_host, $env);
$env = str_replace('<database_password>', $database->database_password, $env);

$domenaShell = escapeshellarg($domena);
$env = escapeshellarg($env);
$output = shell_exec("bash ./generuj.sh $domenaShell $env 2>&1");
// echo "<pre>$output</pre>";

$db = new mysqli($database->database_host, $database->database_user, $database->database_password, $database->database_name);

$role = json_encode(['ROLE_ADMIN', 'ROLE_PRACOWNIK_TEORIA', 'ROLE_PRACOWNIK_PRAKTYKA']);
$stmt = $db->prepare("INSERT INTO 
`user`(`username`, `roles`, `password`, `imie`, `nazwisko`, `numer_telefonu`, `kategoria_uprawnien`, `email`) VALUES 
(?,?,?,?,?,?,?,?)");
$stmt->bind_param("ssssssss", $username, $role, $password, $imie, $nazwisko, $numer_telefonu, $kategorie, $email);
$stmt->execute();

?>

Strona została utworzona.<br />
Twój login: <?= $username ?><br />
<br />
<br />
<a href="https://<?= $domena ?>.osk.solvant.pl">Zaloguj się</a>

<?php

$file = fopen('./databases.json', 'w');
fwrite($file, json_encode(array_values($databases)));
