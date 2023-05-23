<?php
require('database/dbconnect_hp.php');
require('database/dbconnect_attendance.php');

$is_post = false;
if (empty($_POST['start'])) {
    $schedules = $db_attend->prepare('SELECT *, s.id as sid 
                                        FROM schedules s
                                        INNER JOIN schedule_days d ON s.id = d.schedule_id
                                        WHERE s.deleted = 0
                                        AND d.schedule_date <= NOW()
                                        GROUP BY s.id
                                        ORDER BY d.schedule_date DESC
                                        LIMIT 20
                                    ');
    $schedules->execute();

} else {

    $is_post = true;
    $end = empty($_POST['end']) ? date('Y-m-d'): $_POST['end'];
    $schedules = $db_attend->prepare('SELECT *, s.id as sid 
                                        FROM schedules s
                                        INNER JOIN schedule_days d ON s.id = d.schedule_id
                                        WHERE s.deleted = 0
                                        AND d.schedule_date BETWEEN  ? AND ?
                                        GROUP BY s.id
                                        ORDER BY d.schedule_date DESC, s.time DESC
                                    ');
    $schedules->execute(array($_POST['start'],$end));
}



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
            <h1>過去の予定ページ</h1>
            <?php require('php/hamburger.php'); ?>
        </div>
    </div>
    <div class="main_content">
        <form action="" method="post">
            <div class="button_content content">
                <label class="button past_start_date" for="start">
                    <p>開始日</p>
                    <input type="date" name="start" id="start" value="<?php echo $is_post ? $_POST['start']: ''; ?>">
                </label>
                <label class="button past_end_date" for="end">
                    <p>終了日</p>
                    <input type="date" name="end" id="end" value="<?php echo $is_post ? $end: ''; ?>">
                </label>
            </div>
            <div class="content">
                <input class="button" type="submit" value="変更する">
            </div>
        </form>
        <div class="Attendance_content content">
            <ul class="Attendance_lists">
                <?php foreach ($schedules as $schedule): ?>
                <a href="https://ltconnection-aimachi.com/koteki-attendance/confirmation/?id=<?php echo $schedule['sid'] ?>">
                    <li class="Attendance_list <?php echo $schedule['type'] == 1 ? 'koteki_list' : ($schedule['type'] == 2 ? 'hinokisin_list' : '')?>">
                        <h3><?php print(htmlspecialchars($schedule['title'], ENT_QUOTES)); ?></h3>
                        <p>日時:<?php echo date("m月d日 H:i", strtotime($schedule['schedule_date'] . $schedule['time'])); ?></p>
                    </li>
                </a>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="content_after"></div>
    </div>
</body>
</html>
