<?php
include './lib/init.php';

function getName($min, $max, $row) {
    $n = rand($min, $max);
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $n; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    if (Mysql::alreadyExists($row, $randomString)) {
        getName($n, $row);
    } else {
        return $randomString;
    }
}

$temp = explode(".", $_FILES["file"]["name"]);
$extension = end($temp);
$filename = $_FILES['file']['name'];

if ($_FILES["file"]["error"] > 0) {
    echo "Return Code: " . $_FILES["file"]["error"] . "<br>";
} else {
    $tmpName = $_FILES['file']['tmp_name'];
    $fp = fopen($tmpName, 'r');
    $content = fread($fp, filesize($tmpName));
    $content = addslashes($content);
    fclose($fp);

    $password = NULL;
    if ($_POST['password'] != "") {
        include 'lib/Encryption.php';
        $salt = Encryption::generateSalt();
        $content = Encryption::encrypt($content, $_POST['password'], $salt);
        $password = $_POST['password'];
        $filename = Encryption::encrypt($filename, $_POST['password'], $salt);
    }

    $name = getName(10, 20, "name") . "." . $extension;
    $rmcode = getName(32, 64, "removalcode");

    $options = array('cost' => 12);
    require __DIR__."/lib/password.php";
    $hashrmcode = password_hash($rmcode, PASSWORD_BCRYPT, $options);

    $q = "INSERT INTO `" . $config['mysql-table'] . "` (`name`, `size`, `type`, `content`, `file-name`, `removalcode`) VALUES (?, ?, ?, ?, ?, ?);";
    $query = $database->prepare($q);
    $query->bind_param("ssssss", $name, $_FILES['file']['size'], $_FILES['file']['type'], $content, $filename, $hashrmcode);
    $query->execute();

    if ($_POST['password'] != "") {
        $m = $database->prepare("UPDATE `" . $config['mysql-table'] . "` SET `salt`=?, `encryption`=? WHERE `name`=?");
        $g = TRUE;
        $m->bind_param("sss", $salt, $g, $name);
        $m->execute();
    }

    session_start();
    $_SESSION["rmcode"] = $rmcode;
    header('Location: done.php?file=' . $name);
}
