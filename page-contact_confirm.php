<?php
if (!is_user_logged_in()) {
    echo "ログインしてからき～や";
    die();
}

require('database/dbconnect_hp.php');
require('database/dbconnect_attendance.php');

$user = wp_get_current_user();


if(empty($_REQUEST['id'])){
	header('Location: https://ltconnection-aimachi.com/koteki-attendance/');
	exit();
}

$contacts = $db_attend->prepare('SELECT c.*, s.status
                                FROM contacts c
                                LEFT JOIN contact_statuses s ON c.id = s.contact_id
                                                             AND s.user_id = ?
                                WHERE c.id = ? 
                                #AND c.deleted = 0
                            ');
$contacts->execute(array($user->ID, $_REQUEST['id']));
$contact = $contacts->fetch();

//連絡ステータスの更新
//この連絡のステータスが存在しない場合にステータスを既読状態で作成
if (empty($contact['status'])) {
    $statuses = $db_attend->prepare('INSERT INTO contact_statuses
                                            (
                                                contact_id,
                                                user_id,
                                                status,
                                                created
                                            )
                                    VALUE (?,?,1,NOW())');
    $statuses->execute(array(
        $contact['id'],
        $user->ID,
    ));
}


//返信の取得
$contact_replys = $db_attend->prepare('SELECT *
                                        FROM contact_replys
                                        WHERE contact_id = ?
                                        #AND c.deleted = 0
                                    ');
$contact_replys->execute(array($_REQUEST['id']));



//返信の処理
if (!empty($_POST['contact_reply'])) {
    //返信の登録処理
    $reply = $db_attend->prepare('INSERT INTO contact_replys
                                            (
                                                contact_id,
                                                user_id,
                                                reply_message,
                                                created
                                            )
                                    VALUE (?,?,?,NOW())');
    $result = $reply->execute(array(
        $contact['id'],
        $user->ID,
        $_POST['contact_reply'],
    ));

    //連絡ステータスの更新
    //返信済になっていない場合に返信済へ更新する
    if ($result) {
        //現状のステータスを取得
        $contact_statuses = $db_attend->prepare('SELECT s.status
                                                FROM contact_statuses s 
                                                WHERE contact_id = ? 
                                                AND s.user_id = ?
                                                ');
        $contact_statuses->execute(array($_REQUEST['id'], $user->ID));
        $contact_status = $contact_statuses->fetch();

        if ($contact_status['status'] == 1) {
            $statuses = $db_attend->prepare('UPDATE contact_statuses
                                            SET status = 2
                                            WHERE contact_id = ?
                                            AND user_id = ?');
            $statuses->execute(array(
                $contact['id'],
                $user->ID,
            ));
        }
    }

    header('Location: https://ltconnection-aimachi.com/koteki-attendance/contact/contact_confirm/?id='.$_REQUEST['id']); exit();
}





$user_name = get_user_meta($contact['user_id']);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>鼓笛出欠システム | 緊急連絡ページ</title>
    <link rel="stylesheet" href="https://cdn.skypack.dev/sanitize.css">
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/css/style.css">
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/css/form.css">
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/css/contact.css">
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/css/contact_confirm.css">
    <script src="<?php echo get_template_directory_uri(); ?>/js/script.js"></script>
</head>
<body>
    <div class="header_contents">
        <div class="header_content">
            <h1>連絡掲示板</h1>
            <?php require('php/hamburger.php'); ?>
        </div>
    </div>
    <div class="main_content">
        <div class="content contact_content confirm">
            <div class="title_container">
                <div class="title_content">
                    <h3 class="contact_confirm_text"><?php echo htmlspecialchars($contact['title']); ?></h3>
                </div>
                <div class="">
                    <p style="font-size: 18px; color: #333; font-weight: bold;"><?php echo $user_name['last_name'][0] . $user_name['first_name'][0] ?></p>
                    <p class="contact_confirm_text_none"><?php echo htmlspecialchars($contact['message']); ?></p>
                </div>
                <p class="content_time"><?php echo date("n月d日 H:i", strtotime($contact['created'])); ?></p>
            </div>
            <ul class="contact_lists" id="confirm">
                <?php foreach ($contact_replys as $contact_reply) :?>
                <?php 
                    $reply_user = get_user_meta($contact_reply['user_id']); 

                    $week = ['日','月','火','水','木','金','土'];
                    $date = date("w", strtotime($contact_reply['created']));

                    if(empty($reply_date)){
                        $reply_date = date("y-m-d", strtotime($contact_reply['created']));
                        $date_bool = true;
                    } else {
                        if ($reply_date == date("y-m-d", strtotime($contact_reply['created']))) {
                            $date_bool = false;
                        } else {
                            $date_bool = true;
                            $reply_date = date("y-m-d", strtotime($contact_reply['created']));
                        }
                    }
                ?>
                <?php if ($date_bool) : ?>
                    <div class="reply_date">
                        <span>
                            <?php echo date("n月d日(".$week[$date].")", strtotime($contact_reply['created'])); ?>
                        </span>
                    </div>
                <?php endif; ?>
                <li class="contact_list confirm_list">
                    <div class="list_name_content <?php echo $contact_reply['user_id'] == $user->ID ? 'my_post_name': '' ?>">
                        <h3><?php echo $reply_user['last_name'][0] . $reply_user['first_name'][0] ?></h3>
                        <p><?php echo date("H:i", strtotime($contact_reply['created'])); ?></p>
                    </div>
                    <div class="message <?php echo $contact_reply['user_id'] == $user->ID ? 'my_post_message': '' ?>"><p style="
    background: #fff3f3;
    padding: 5px;
    border-radius: 10px;
"><?php echo htmlspecialchars($contact_reply['reply_message']); ?></p></div>
                </li>
                <?php $created = $contact_reply['created']; $id = $_REQUEST['id']; endforeach; ?>
            </ul>
            <form action="" method="post">
                <div class="reply_items">
                    <textarea class="reply_container" name="contact_reply" id="contact_reply" placeholder="返信をする。" onfocus="focusEvent()"></textarea>
                    <button type="submit" class="reply_button">送信</button>
                </div>
            </form>
    <!-- <div class="button" onclick="get_data(<?php echo $id ?>,'<?php echo $created ?>')">開始</div> -->

        </div>
        <!-- <div class="content_after"></div> -->
    </div>
    <script src="<?php echo get_template_directory_uri(); ?>/js/script.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script>
        window.addEventListener('DOMContentLoaded', function() {
            get_data(<?php echo $id ?>,'<?php echo $created ?>',<?php echo $user->ID ?>)
        })
    </script>
</body>
</html>
