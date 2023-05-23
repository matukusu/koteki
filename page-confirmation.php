<?php
if (!is_user_logged_in()) {
    require('php/login_form.php');
    die();
}

require('database/dbconnect_hp.php');
require('database/dbconnect_attendance.php');

if(!empty($_POST['del'])){
    $delete = $db_attend->prepare('UPDATE schedules 
                                    SET deleted = 1
                                    WHERE id = ?
                                ');
    $delete->execute( array( $_POST['del'] ) );
	header('Location: https://ltconnection-aimachi.com/koteki-attendance/');
	exit();
}

if(empty($_REQUEST['id'])){
	header('Location: https://ltconnection-aimachi.com/koteki-attendance/');
	exit();
}



$user = wp_get_current_user( );
//$user->ID;

$schedules = $db_attend->prepare('SELECT *, s.id as sid
                                    FROM schedules s
                                    INNER JOIN schedule_days d ON s.id = d.schedule_id
                                    WHERE s.id = ?
                                ');
$schedules->execute( array( $_REQUEST['id'] ) );
$schedule = $schedules->fetch();

$answers = $db_attend->prepare('SELECT *, (attendance_asa + attendance_hiru + attendance_yuu + attendance_tomari) as answer_num
                                FROM answers a
                                INNER JOIN answer_days ad ON a.id = ad.answer_id
                                INNER JOIN schedule_days sd ON a.schedule_id = sd.schedule_id and ad.answer_date = sd.schedule_date
                                WHERE a.schedule_id = ?
                                AND a.user_id = ?
                                GROUP BY ad.id
                                ORDER BY a.id, ad.id
                                ');
$answers->execute( array( $_REQUEST['id'], $user->ID ) );
$answers = $answers->fetchAll();

//権限テーブル取得
$authorities = $db_attend->prepare('SELECT * FROM authorities');
$authorities->execute();

// echo'<pre>';
// var_dump( $answers);
// echo'</pre>';
// die()

//一旦必要なくなった

// //ここで必要な形の配列に取得した情報を分ける
// $answer_head = []; //テーブルのヘッダー部分でループする
// $answer_body = []; //テーブルのボディ部分でループする
// foreach ($answers as $answer) {
//     //日にち毎に出欠項目はいくつあるのかを計算する
//     $answer_num = $answer['attendance_asa'] + $answer['attendance_hiru'] + $answer['attendance_yuu'] + $answer['attendance_tomari'];
//     $answer_head = [
//         $i => [
//         'schedule_date' => $answer['schedule_date'],
//         'attendance_asa' => $answer['attendance_asa'],
//         'attendance_hiru' => $answer['attendance_hiru'],
//         'attendance_yuu' => $answer['attendance_yuu'],
//         'attendance_tomari' => $answer['attendance_tomari'],
//         'answer_num' => $answer_num
//         ]
//     ];

//     $answer_body = [
//         $i => [
//         'name' => $answer['name'],
//         'area' => $answer['area'],
//         'answer_remarks' => $answer['answer_remarks'],
//         'answer' => $answer['answer'],
//         'answer_asa' => $answer['answer_asa'],
//         'anaswer_hiru' => $answer['answer_hiru'],
//         'answer_yuu' => $answer['answer_yuu'],
//         'answer_tomari' => $answer['answer_tomari']
//         ]
//     ];
//     $i++;
// }

// var_dump($answer_head);
// var_dump($answer_body);
// die();

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>鼓笛出欠システム | 詳細確認ページ</title>
    <link rel="stylesheet" href="https://cdn.skypack.dev/sanitize.css">
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/css/style.css">
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/css/form.css">
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/css/confirmation.css">
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/css/table.css">
    <script src="<?php echo get_template_directory_uri(); ?>/js/script.js"></script>
</head>
<body>
    <div class="header_contents">
        <div class="header_content">
            <h1>詳細確認ページ</h1>
            <?php require('php/hamburger.php'); ?>
        </div>
    </div>
    <div class="main_content">
        <h2><?php print(htmlspecialchars($schedule['title'], ENT_QUOTES)); ?></h2>
        <div class="title_content content">
            <h3>日時</h3>
            <p><?php echo date("m月d日 H:i", strtotime($schedule['schedule_date'] . $schedule['time'])); ?></p>
            <h3>場所</h3>
            <p><?php print(htmlspecialchars($schedule['place'], ENT_QUOTES)); ?></p>
            <h3>詳細</h3>
            <p><?php print(htmlspecialchars($schedule['remarks'], ENT_QUOTES)); ?></p>
        </div>
        <div class="button_content content">
            <a class="button" href="https://ltconnection-aimachi.com/koteki-attendance/answer/?id=<?php echo $schedule['sid'] ?>">回答する</a>
            <a class="button delbtn1" >修正する</a>
        </div>
        <div class="confirmation_content content">
            <h3>あなたの回答</h3>
            <div class="table">
                <table>
                    <thead>
                        <tr>
                            <th rowspan="2">名前</th>
                            <th rowspan="2">地区</th>
                            <th colspan="<?php echo $answers[0]['answer_num'] +1; ?>"><?php echo date("m月d日", strtotime($answers[0]['answer_date'])); ?></th>
                            <?php 
                                $answer_date = '';
                                $span = 0; 
                            ?>
                            <?php foreach ($answers as $answer) : ?>
                                <?php if ($answer_date == $answer['answer_id']): $span++ ?>
                                    <th colspan="<?php echo $answers[$span]['answer_num'] +1; ?>"><?php echo date("m月d日", strtotime($answer['answer_date'])); ?></th>
                                <?php endif; ?>
                                <?php if (empty($answer_date)) {$answer_date = $answer['answer_id'];} ?>
                            <?php endforeach; ?>
                            <th rowspan="2">備考</th>
                        </tr>
                        <tr>
                            <th class="th_row2">出欠</th>
                            <?php echo $answers[0]['attendance_asa'] == 1 ? '<th class="th_row2 under_th">朝</th>': ''?>
                            <?php echo $answers[0]['attendance_hiru'] == 1 ? '<th class="th_row2 under_th">昼</th>': ''?>
                            <?php echo $answers[0]['attendance_yuu'] == 1 ? '<th class="th_row2 under_th">夕</th>': ''?>
                            <?php echo $answers[0]['attendance_tomari'] == 1 ? '<th class="th_row2 under_th">泊</th>': ''?>
                            <?php $answer_date = ''; ?>
                            <?php foreach ($answers as $answer) : ?>
                                <?php if ($answer_date == $answer['answer_id']): ?>
                                    <th class="th_row2">出欠</th>
                                    <?php echo $answer['attendance_asa'] == 1 ? '<th class="th_row2 under_th">朝</th>': ''?>
                                    <?php echo $answer['attendance_hiru'] == 1 ? '<th class="th_row2 under_th">昼</th>': ''?>
                                    <?php echo $answer['attendance_yuu'] == 1 ? '<th class="th_row2 under_th">夕</th>': ''?>
                                    <?php echo $answer['attendance_tomari'] == 1 ? '<th class="th_row2 under_th">泊</th>': ''?>
                                <?php endif; ?>
                                <?php if (empty($answer_date)) {$answer_date = $answer['answer_id'];} ?>
                            <?php endforeach; ?>
                                
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                            $answer_id; 
                            $count = count($answers);
                            $a = 0;
                        ?>
                        <?php foreach ($answers as $answer):
                            $a++;
                            
                            if ($answer_id == $answer['answer_id']) {
                                echo $answer['answer'] == 1 ? '<td>○</td>' : '<td>✕</td>';
                                echo $answer['attendance_asa'] == 1 ? ($answer['answer_asa'] == 1 ? '<td>○</td>' : '<td>✕</td>'): '';
                                echo $answer['attendance_hiru'] == 1 ? ($answer['answer_hiru'] == 1 ? '<td>○</td>' : '<td>✕</td>'): '';
                                echo $answer['attendance_yuu'] == 1 ? ($answer['answer_yuu'] == 1 ? '<td>○</td>' : '<td>✕</td>'): '';
                                echo $answer['attendance_tomari'] == 1 ? ($answer['answer_tomari'] == 1 ? '<td>○</td>' : '<td>✕</td>'): '';
                                if($count == $a){
                                    echo '<td>' . $answer['answer_remarks'] . '</td>';
                                    echo '</tr>';
                                }
                                continue;
                            } else {
                                if(empty($answer_id)){
                                    $answer_id = $answer['answer_id'];
                                    $remark = $answer['answer_remarks'];
                                } else {
                                    echo '<td>' . $remark . '</td>';
                                    echo '</tr>';
                                    $answer_id = $answer['answer_id'];
                                    $remark = $answer['answer_remarks'];
                                }
                            }
                        ?>
                            <tr>
                                <th><?php print(htmlspecialchars($answer['name'], ENT_QUOTES)); ?></td>
                                <td><?php print(htmlspecialchars($answer['area'], ENT_QUOTES)); ?></td>
                                <?php
                                    echo $answer['answer'] == 1 ? '<td>○</td>' : '<td>✕</td>';
                                    echo $answers[0]['attendance_asa'] == 1 ? ($answer['answer_asa'] == 1 ? '<td>○</td>' : '<td>✕</td>'): '';
                                    echo $answers[0]['attendance_hiru'] == 1 ? ($answer['answer_hiru'] == 1 ? '<td>○</td>' : '<td>✕</td>'): '';
                                    echo $answers[0]['attendance_yuu'] == 1 ? ($answer['answer_yuu'] == 1 ? '<td>○</td>' : '<td>✕</td>'): '';
                                    echo $answers[0]['attendance_tomari'] == 1 ? ($answer['answer_tomari'] == 1 ? '<td>○</td>' : '<td>✕</td>'): '';
                                ?>
                            
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="button_content content">
            <a class="button" href="https://ltconnection-aimachi.com/koteki-attendance/">戻る</a>
            <a class="button" href="https://ltconnection-aimachi.com/koteki-attendance/list/?id=<?php echo $_REQUEST['id'] ?>">確認する</a>
        </div>
        
        <?php foreach ($authorities as $authoritiy): ?>
            <?php if ($authoritiy['user_id'] == $user->ID): ?>
                <div class="button_content content">
                    <a class="button red delbtn">予定を削除</a>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
        
        <div class="content_after"></div>
    </div>
    <!--投稿をモーダルでプレビューする-->
<div class="overlay"></div>
<div class="modal">
	<div class="close">×</div>
	<div class="modal_area">
        <p class="content">この予定を本当に削除しますか？</p>
		<div class="modal_btn_area button_content content" style="padding: 20px 5px;">
            <form action="" name="del_sche" method="post">
                <input type="hidden" name="del" value="<?php echo $_REQUEST['id'] ?>">
                <button class="button red" style="padding: 15px 15px;">削除する</button>
            </form>
            <div class="button nodel" style="padding: 15px 15px;">削除しない</div>
		</div>
	</div>
</div>

<?php 
    $answers = $db_attend->prepare('SELECT *
                                    FROM answers a
                                    WHERE a.schedule_id = ?
                                    AND a.user_id = ?
                                    ORDER BY a.id
                                    ');
    $answers->execute( array( $_REQUEST['id'], $user->ID ) );
?>
<div class="overlay1"></div>
<div class="modal1">
	<div class="close1">×</div>
	<div class="modal_area">
        <p class="content">修正する回答を選択してください</p>
		<div class="modal_btn_area button_content content" style="padding: 20px 5px;text-align: center;">
            <form action="https://ltconnection-aimachi.com/koteki-attendance/answer/answer_edit" name="edit_ans" method="get">
                <input type="hidden" name="schid" value="<?php echo $_REQUEST['id'] ?>">
                <select name="ansid" style="margin-bottom: 20px;">
                    <?php foreach ($answers as $answer) : ?>
                        <option value="<?php echo $answer['id'] ?>"><?php echo $answer['name'] ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="button">修正する</button>
            </form>
		</div>
	</div>
</div>
<script>
	const delbtn = document.querySelector('.delbtn');
	const modal = document.querySelector('.modal');
	const closeBtn = document.querySelector('.close');
	const nodel = document.querySelector('.nodel');
	const overlay = document.querySelector('.overlay');
	// モダルの閉じるボタンをクリックしたら、モダルとオーバーレイのactiveクラスを外す
	closeBtn.addEventListener('click', function(){
		modal.classList.remove('active');
		overlay.classList.remove('active');
	});

	// モダルの削除しないボタンをクリックしたら、モダルとオーバーレイのactiveクラスを外す
	nodel.addEventListener('click', function(){
		modal.classList.remove('active');
		overlay.classList.remove('active');
	});

	// オーバーレイをクリックしたら、モダルとオーバーレイのactiveクラスを外す
	overlay.addEventListener('click', function() {
		modal.classList.remove('active');
		overlay.classList.remove('active');
	});

    delbtn.addEventListener('click', function() {
		modal.classList.add('active');
		overlay.classList.add('active');
    });
</script>
<script>
	const delbtn1 = document.querySelector('.delbtn1');
	const modal1 = document.querySelector('.modal1');
	const closeBtn1 = document.querySelector('.close1');
	const overlay1 = document.querySelector('.overlay1');
	// モダルの閉じるボタンをクリックしたら、モダルとオーバーレイのactiveクラスを外す
	closeBtn1.addEventListener('click', function(){
		modal1.classList.remove('active');
		overlay1.classList.remove('active');
	});
	// オーバーレイをクリックしたら、モダルとオーバーレイのactiveクラスを外す
	overlay1.addEventListener('click', function() {
		modal1.classList.remove('active');
		overlay1.classList.remove('active');
	});

    delbtn1.addEventListener('click', function() {
		modal1.classList.add('active');
		overlay1.classList.add('active');
    });
</script>
</body>
</html>
