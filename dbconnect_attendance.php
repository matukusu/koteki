<?php
try{
    $db_attend = new PDO('mysql:dbname=xs138951_attendance;host=103.3.2.205;charset=utf8','xs138951_aimachi','amsk3123');
}catch(PDOException $e) {
    echo 'DB接続エラー: ' . $e->getMessage();
}
?>