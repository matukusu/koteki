<?php
require('database/dbconnect_hp.php');
require('database/dbconnect_attendance.php');

$schedules = $db_attend->prepare('SELECT *, s.id as sid 
                                    FROM schedules s
                                    INNER JOIN schedule_days d ON s.id = d.schedule_id
                                    WHERE s.deleted = 0
                                    #AND d.schedule_date >= NOW()
                                    GROUP BY s.id
                                    ORDER BY d.created DESC
                                ');
$schedules->execute();

//権限テーブル取得
$authorities = $db_attend->prepare('SELECT * FROM authorities');
$authorities->execute();

$user = wp_get_current_user( );

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>鼓笛出欠システム | TOPページ</title>
    <link rel="stylesheet" href="https://cdn.skypack.dev/sanitize.css">
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/css/style.css">
    <script src="<?php echo get_template_directory_uri(); ?>/js/script.js"></script>
</head>
<body>
    <div class="header_contents">
        <div class="header_content">
            <h1>出欠連絡ページ</h1>
            <?php require('php/hamburger.php'); ?>
        </div>
    </div>
    <div class="main_content">
        <h2 class="heading">お知らせ</h2>
        <ul class="news_content content">
            <div class="news_items">
                <li>
                    <div class="news">
                        <p class="news_day">2022/06/27</p>
                        <span>鼓笛母</span>
                        <a href="#"><p>【ダミー】お知らせのタイトルがここに入ります。</p></a>
                    </div>
                </li>
                <li>
                    <div class="news">
                        <p class="news_day">2022/06/27</p>
                        <span>鼓笛母</span>
                        <a href="#"><p>【ダミー】お知らせのタイトル</p></a>
                    </div>
                </li>
                <li>
                    <div class="news">
                        <p class="news_day">2022/06/27</p>
                        <span>鼓笛母</span>
                        <a href="#"><p>【ダミー】お知らせのタイトルがここに
                        </p></a>
                    </div>
                </li>
            </div>
        </ul>
        <div>
            <h2 class="heading">出欠席調査</h2>

            <?php foreach ($authorities as $authoritiy): ?>
                <?php if ($authoritiy['user_id'] == $user->ID): ?>
                    <div class="content button_content">
                        <a class="button" style="padding: 15px 23px;" href="https://ltconnection-aimachi.com/koteki-attendance/past_schedule/">過去の予定</a>
                        <a class="button" style="padding: 15px 23px;" href="https://ltconnection-aimachi.com/koteki-attendance/create">予定を追加</a>
                    </div>
                    <?php endif; ?>
            <?php endforeach; ?>

            <div class="Attendance_content content">
                <ul class="Attendance_lists">
                    <?php foreach ($schedules as $schedule): ?>
                    <a href="https://ltconnection-aimachi.com/koteki-attendance/confirmation/?id=<?php echo $schedule['sid'] ?>">
                        <li class="Attendance_list <?php echo $schedule['type'] == 1 ? 'koteki_list' : ($schedule['type'] == 2 ? 'hinokisin_list' : '')?>">
                            <h3><?php print(htmlspecialchars($schedule['title'], ENT_QUOTES)); ?></h3>
                            <p>日時:<?php echo date("m月d日 H:i", strtotime($schedule['schedule_date'] . $schedule['time'])); ?></p>
                            <p>集合:<?php print(htmlspecialchars($schedule['place'], ENT_QUOTES)); ?></p>
                            <p>詳細:<?php print(htmlspecialchars($schedule['remarks'], ENT_QUOTES)); ?></p>
                        </li>
                    </a>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="content_after"></div>
        </div>
    </div>
    <footer>
        <a href="https://ltconnection-aimachi.com/koteki-attendance/contact"><p>緊急連絡はこちら</p></a>
    </footer>
</body>
</html>
