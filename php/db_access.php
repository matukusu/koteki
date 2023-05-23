<?php
require('../database/dbconnect_hp.php');
require('../database/dbconnect_attendance.php');

function getNewReply($db_attend) {
    $replys = $db_attend->prepare('SELECT *
                                    FROM contact_replys
                                    WHERE contact_id = ?
                                    AND created > ?
                                    #AND c.deleted = 0
                                ');
    $replys->execute(array($_POST['id'], $_POST['created']));
    $replys = $replys->fetchAll();
    $i = 0;
    foreach ($replys as $reply) {
        //名前を追加
        $first = get_first_name($reply['user_id'],$db_attend);
        $last = get_last_name($reply['user_id'],$db_attend);
        $name = $last['meta_value'] . $first['meta_value'];
        $replys[$i]['name'] = $name;

        //時間を追加
        $time = date("H:i", strtotime($reply['created']));
        $replys[$i]['time'] = $time;
        $i++;
    }
    $replys = json_encode($replys , JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );

    return $replys;
}

function get_first_name($user_id,$db_attend) {
    $users = $db_attend->prepare("SELECT meta_value
                                    FROM wp_usermeta
                                    WHERE user_id = ?
                                    AND meta_key = 'first_name'
                                ");
    $users->execute(array($user_id));
    $user = $users->fetch();

    return $user;
}
function get_last_name($user_id,$db_attend) {
    $users = $db_attend->prepare("SELECT meta_value
                                    FROM wp_usermeta
                                    WHERE user_id = ?
                                    AND meta_key = 'last_name'
                                ");
    $users->execute(array($user_id));
    $user = $users->fetch();

    return $user;
}

$func = $_POST['func'];
echo $func($db_attend);