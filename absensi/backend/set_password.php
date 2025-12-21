<?php
require "backend/db.php";

$token = $_GET['token'] ?? '';

$q = mysqli_query($conn, "
    SELECT id_user FROM users
    WHERE reset_token='$token'
    AND reset_expired > NOW()
");

if(mysqli_num_rows($q) === 0){
    die("Link tidak valid atau kadaluarsa");
}

$user = mysqli_fetch_assoc($q);

if($_SERVER['REQUEST_METHOD']=='POST'){
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    mysqli_query($conn, "
        UPDATE users 
        SET password='$password',
            reset_token=NULL,
            reset_expired=NULL
        WHERE id_user={$user['id_user']}
    ");

    header("Location: index.php?msg=Password berhasil dibuat");
}
?>
