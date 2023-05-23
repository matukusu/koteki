<?php

if (!is_user_logged_in()) {
    echo "ログインしてからき～や";
    die();
}

require('database/dbconnect_hp.php');
require('database/dbconnect_attendance.php');

$user = wp_get_current_user();

//formが送信された場合
if (!empty($_POST)) {
    if (!empty($_POST['title'])) {
        $contacts = $db_attend->prepare('INSERT INTO contacts
                                            (
                                                user_id,
                                                title,
                                                message,
                                                created
                                            )
                                        VALUE (?,?,?,NOW())');
        $contacts->execute(array(
            $user->ID,
            $_POST['title'],
            $_POST['message'],
        ));
    }
    //作成した予定のページへ飛ぶ
    header('Location: https://ltconnection-aimachi.com/koteki-attendance/contact/'); exit();
}

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
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/css/contact_form.css">
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
        <form action="" method="post" name="contact">
            <div class="content contact_content confirm">
                <div class="title_container">
                    <div class="title_content">
                        <input type="text" name="title" id="title" list="title_list" placeholder="タイトルを入力または選択" required>
                            <datalist id="title_list">
                                <option value="遅刻">
                                <option value="欠席">
                                <option value="早退">
                                <option value="食事変更">
                                <option value="その他">
                            </datalist>
                    </div>
                </div>
                <div class="reply_items">
                    <textarea class="contact_form_container" name="message" id="message" placeholder="本文を入力" required></textarea>
                </div>
            </div>
            <div class="button_content content">
                <a class="button" href="https://ltconnection-aimachi.com/koteki-attendance/contact">戻る</a>
                <button class="button" type="submit">送信</button>
            </div>
        </form>
        <div class="content_after"></div>
    </div>
    
    <script src="js/script.js"></script>
</body>
</html>
