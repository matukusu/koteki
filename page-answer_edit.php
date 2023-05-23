<?php 
if (!is_user_logged_in()) {
    echo "ログインしてからき～や";
    die();
}

if(empty($_POST)){
    if(empty($_REQUEST['schid'])){
        header('Location: https://ltconnection-aimachi.com/koteki-attendance/');
        exit();
    }
}

require('database/dbconnect_hp.php');
require('database/dbconnect_attendance.php');

$user = wp_get_current_user();

$schedules = $db_attend->prepare('SELECT *, s.id as sid
                                    FROM schedules s
                                    INNER JOIN schedule_days d ON s.id = d.schedule_id
                                    WHERE s.id = ?
                                ');
$schedules->execute( array( $_REQUEST['schid'] ) );
$schedule = $schedules->fetch();

$answerDatas = $db_attend->prepare('SELECT *, ad.id as answer_days_id
                                FROM answers a
                                INNER JOIN answer_days ad ON a.id = ad.answer_id
                                WHERE a.id = ?
                                GROUP BY ad.id
                                ORDER BY a.id, ad.id
                                ');
$answerDatas->execute( array( $_REQUEST['ansid'] ) );
$answerDatas = $answerDatas->fetchAll();
//初期表示かどうかを判定する
$is_start = true;

//post送信があったかどうかを判定する
$is_post = false;

//formが送信された場合
if (!empty($_POST)) {
    $is_start = false;
    $is_post = true;
    //処理結果をresulteに格納する
    $resulte = [];
    //エラーをエラーに格納する
    $errors = [];

    $tiku = $_POST['tiku'];
    $names = $_POST['names'];
    $remarks = $_POST['bikou'];

    //日付と食事の情報を配列に格納する
    $date = []; //初期化
    $syukketu = []; //初期化
    $asa = []; //初期化
    $hiru = []; //初期化
    $yuu = []; //初期化
    $haku = []; //初期化
    $i = 0; //初期化
    $j = 0; //初期化
    $attend = true; //ループ用
    while($attend){
        $j++;
        if (empty($_POST["date".$j])) {
            $attend = false;
            break;
        }
        $date[$i] = $_POST["date".$j];
        $syukketu[$i] = $_POST["syukketu".$j];
        $asa[$i] = $_POST["asa".$j];
        $hiru[$i] = $_POST["hiru".$j];
        $yuu[$i] = $_POST["yuu".$j];
        $haku[$i] = $_POST["haku".$j];
        
        //食事のチェックボックスが一つも選択されていなかった場合
        if (empty($asa[$i]) && empty($hiru[$i]) && empty($yuu[$i]) && empty($haku[$i]) && ($syukketu[$i] == 1)) {
            $errors[] = $date[$i] . 'の食事が選択されていません';
        }
        $i++;
    }

    //予定の登録処理
    if(empty($errors)){
        if($names !== '' && $tiku !== ''){
            $answers = $db_attend->prepare('UPDATE answers
                                            SET area = ?,
                                                name = ?,
                                                answer_remarks = ?
                                            WHERE id = ?');
            $answers->execute(array(
                $tiku,
                $names,
                $remarks,
                $_REQUEST['ansid']
            ));
        }

        $d = 0;
        foreach ($date as $day) {
            if($date[0] !== ''){
                $answer_days = $db_attend->prepare('UPDATE answer_days
                                                    SET answer_date = ?,
                                                        answer = ?,
                                                        answer_asa = ?,
                                                        answer_hiru = ?,
                                                        answer_yuu = ?,
                                                        answer_tomari = ?
                                                    WHERE id = ?');
                $answer_days->execute(array(
                    $day,
                    $syukketu[$d],
                    empty($asa[$d]) ? 0 : $asa[$d],
                    empty($hiru[$d]) ? 0 : $hiru[$d],
                    empty($yuu[$d]) ? 0 : $yuu[$d],
                    empty($haku[$d]) ? 0 : $haku[$d],
                    $answerDatas[$d]['answer_days_id']
                ));
            }
            $d++;
        }
        //作成した予定のページへ飛ぶ
        header('Location: https://ltconnection-aimachi.com/koteki-attendance/confirmation/?id=' . $schedule['sid']); exit();
    }
}

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>鼓笛出欠システム | 回答編集ページ</title>
    <link rel="stylesheet" href="https://cdn.skypack.dev/sanitize.css">
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/css/style.css">
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/css/table.css">
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/css/form.css">
    <script src="<?php echo get_template_directory_uri(); ?>/js/script.js"></script>
</head>
<body>
    <div class="header_contents">
        <div class="header_content">
            <h1>回答編集ページ</h1>
            <?php require('php/hamburger.php'); ?>
        </div>
    </div>
    <div class="main_content">
        <h2><?php print(htmlspecialchars($schedule['title'], ENT_QUOTES)); ?></h2>
        <p class="form_resulte" style="color: red;"><?php if($is_post){foreach($errors as $error){echo $error . '<br>';}} ?></p>
        <form action="" method="post">
            <div class="form_area content">
                <div class="form_content">
                    <div class="form_flex_item">
                        <h3><label for="tiku">地区</label></h3>
                        <input type="text" name="tiku" id="tiku" value="<?php echo $is_post ? $tiku: ''?><?php echo $is_start ? $answerDatas[0]['area']: ''?>">
                    </div>
                    <div class="form_flex_item">
                        <h3><label for="names">名前</label></h3>
                        <input type="text" id="names" name="names" value="<?php echo $is_post ? $names: ''?><?php echo $is_start ? $answerDatas[0]['name']: ''?>">
                    </div>
                    <div class="form_flex_item">
                        <h3><p>出欠</p></h3>
                        <?php 
                            $schedules = $db_attend->prepare('SELECT * 
                                                                FROM schedules s
                                                                INNER JOIN schedule_days d ON s.id = d.schedule_id
                                                                WHERE s.id = ?
                                                            ');
                                    $schedules->execute( array( $_REQUEST['schid'] ) );
                            $sn = 0;
                        ?>
                        <?php foreach($schedules as $schedule): $sn++?>
                            <input type="hidden" value="<?php echo $schedule['schedule_date'] ?>" name="date<?php echo $sn ?>">
                            <label class="syukketu" for="syukketu<?php echo $sn ?>"><?php echo date("m月d日", strtotime($schedule['schedule_date'] . $schedule['time'])); ?></label>
                            <select name="syukketu<?php echo $sn ?>" id="syukketu<?php echo $sn ?>" onchange="syukketuNo('meal<?php echo $sn ?>')">
                                <option value="0" <?php echo $is_post ? ($syukketu[$sn-1] == 0 ? 'selected' : '') : ''?><?php echo $is_start ? ($answerDatas[$sn-1]['answer'] == 0 ? 'selected' : '') : ''?>>欠席</option>
                                <option value="1" <?php echo $is_post ? ($syukketu[$sn-1] == 1 ? 'selected' : '') : ''?><?php echo $is_start ? ($answerDatas[$sn-1]['answer'] == 1 ? 'selected' : '') : ''?>>出席</option>
                            </select>
                        <?php endforeach; ?>
                    </div>
                    <div class="form_flex_item">
                        <h3><p>食事</p></h3>
                        <div>
                        <?php 
                            $schedules = $db_attend->prepare('SELECT * 
                                                                FROM schedules s
                                                                INNER JOIN schedule_days d ON s.id = d.schedule_id
                                                                WHERE s.id = ?
                                                            ');
                                    $schedules->execute( array( $_REQUEST['schid'] ) );
                            $s = 0;
                        ?>
                        <?php foreach($schedules as $schedule): $s++?>
                            <div class="meal_item">
                                <p><?php echo date("m月d日", strtotime($schedule['schedule_date'] . $schedule['time'])); ?></p>
                                <div class="form_flex_item" id="meal<?php echo $s ?>">
                                    <?php if($schedule['attendance_asa']): ?>
                                        <label for="asa<?php echo $s ?>">朝<input type="checkbox" value="1" name="asa<?php echo $s ?>" id="asa<?php echo $s ?>" <?php echo $is_post ? !empty($asa[$s-1]) ? 'checked': '': ''?><?php echo $is_start ? ($answerDatas[$s-1]['answer_asa'] == 1 ? 'checked': ''): ''?>><span></span></label>
                                    <?php endif; ?>
                                    <?php if($schedule['attendance_hiru']): ?>
                                        <label for="hiru<?php echo $s ?>">昼<input type="checkbox" value="1" name="hiru<?php echo $s ?>" id="hiru<?php echo $s ?>" <?php echo $is_post ? !empty($hiru[$s-1]) ? 'checked': '': ''?><?php echo $is_start ? ($answerDatas[$s-1]['answer_hiru'] == 1 ? 'checked': ''): ''?>><span></span></label>
                                    <?php endif; ?>
                                        <?php if($schedule['attendance_yuu']): ?>
                                        <label for="yuu<?php echo $s ?>">夕<input type="checkbox" value="1" name="yuu<?php echo $s ?>" id="yuu<?php echo $s ?>" <?php echo $is_post ? !empty($yuu[$s-1]) ? 'checked': '': ''?><?php echo $is_start ? ($answerDatas[$s-1]['answer_yuu'] == 1 ? 'checked': ''): ''?>><span></span></label>
                                    <?php endif; ?>
                                    <?php if($schedule['attendance_tomari']): ?>
                                        <label for="haku<?php echo $s ?>">泊<input type="checkbox" value="1" name="haku<?php echo $s ?>" id="haku<?php echo $s ?>" <?php echo $is_post ? !empty($haku[$s-1]) ? 'checked': '': ''?><?php echo $is_start ? ($answerDatas[$s-1]['answer_tomari'] == 1 ? 'checked': ''): ''?>><span></span></label>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="form_flex_item">
                        <h3><p>備考</p></h3>
                        <textarea name="bikou" id="bikou" cols="25" rows="10"><?php echo $is_post ? $remarks: ''?><?php echo $is_start ? $answerDatas[0]['answer_remarks']: '' ?></textarea>
                    </div>
                </div>
            </div>
            <div class="button_content content">
                <a class="button" href="https://ltconnection-aimachi.com/koteki-attendance/confirmation/?id=<?php echo $_REQUEST['schid']; ?>">戻る</a>
                <button type="submit" class="button">送信</button>
            </div>
        </form>
        <div class="content_after"></div>
    </div>
    <script>
        //画面読み込み時に出欠の状態を確認して食事項目の初期状態を変更している
        window.addEventListener('load', function() {
            var answerSelect = [];
            var selects = '';
            //var answerForm = document.forms.answerForm;
            var j = 0;
            for (let i = 0; i < <?php echo $sn ?>; i++) {
                j++;
                selects = eval("syukketu" + j);
                answerSelect[i] = selects.value;
            }

            var meal;
            var meal_items;
            j = 0;
            for (let i = 0; i < answerSelect.length; i++) {
                j++;
                meal = document.getElementById('meal'+j);
                meal_items = meal.querySelectorAll("input");
                if(answerSelect[i] === '0'){
                    for (let a = 0; a < meal_items.length; a++) {
                        meal_items[a].setAttribute("disabled", true);
                        meal_items[a].checked = false;
                        meal_items[a].nextElementSibling.style.backgroundColor = "#aaaaaa";
                    }
                }
                
            }
        });

        function syukketuNo(id) {
            var meal = document.getElementById(id);
            var meal_items = meal.querySelectorAll("input");
            for (let i = 0; i < meal_items.length; i++) {
                if (meal_items[i].disabled === true) {
                    meal_items[i].removeAttribute("disabled");
                    meal_items[i].nextElementSibling.style.backgroundColor = null;
                } else {
                    meal_items[i].setAttribute("disabled", true);
                    meal_items[i].checked = false;
                    meal_items[i].nextElementSibling.style.backgroundColor = "#aaaaaa";

                }
            }
        }
    </script>
</body>
</html>
