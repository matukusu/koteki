<?php
if (!is_user_logged_in()) {
    echo "ログインしてからき～や";
    die();
}

require('database/dbconnect_hp.php');
require('database/dbconnect_attendance.php');

$user = wp_get_current_user( );
//$user->ID;

//post送信があったかどうかを判定する
$is_post = false;

//formが送信された場合
if (!empty($_POST)) {
    $is_post = true;
    //処理結果をresulteに格納する
    $resulte = [];
    //エラーをエラーに格納する
    $errors = [];

    $title = $_POST['title'];
    $type = $_POST['type'];
    $time = $_POST['time'];
    $place = $_POST['place'];
    $remarks = $_POST['bikou'];

    //日付と食事の情報を配列に格納する
    $date = []; //初期化
    $asa = []; //初期化
    $hiru = []; //初期化
    $yuu = []; //初期化
    $haku = []; //初期化
    $i = 0; //初期化
    $attend = true; //ループ用
    while($attend){
        if (empty($_POST["date"][$i])) {
            $attend = false;
            break;
        }
        $date[$i] = $_POST["date"][$i];
        $asa[$i] = is_array($_POST["asa"]) ? (in_array ($_POST["date"][$i], $_POST["asa"]) ? 1 : 0): 0;
        $hiru[$i] = is_array($_POST["hiru"]) ? (in_array ($_POST["date"][$i], $_POST["hiru"]) ? 1 : 0): 0;
        $yuu[$i] = is_array($_POST["yuu"]) ? (in_array ($_POST["date"][$i], $_POST["yuu"]) ? 1 : 0): 0;
        $haku[$i] = is_array($_POST["haku"]) ? (in_array ($_POST["date"][$i], $_POST["haku"]) ? 1 : 0): 0;
        
        //食事のチェックボックスが一つも選択されていなかった場合
        if (empty($asa[$i]) && empty($hiru[$i]) && empty($yuu[$i]) && empty($haku[$i])) {
            $errors[] = $date[$i] . 'の食事が選択されていません';
        }
        $i++;
    }
// echo '<pre>';
//     var_dump($asa);
//     var_dump($hiru);
//     var_dump($yuu);
//     var_dump($haku);
//     var_dump($date);
//     var_dump($errors);
// echo '</pre>';

//     die();
    //予定の登録処理
    if(empty($errors)){
        if($title !== ''){
            $schedules = $db_attend->prepare('INSERT INTO schedules
                                            (
                                                user_id,
                                                title,
                                                type,
                                                time,
                                                place,
                                                remarks,
                                                deleted,
                                                created
                                            )
                                            VALUE (?,?,?,?,?,?,0,NOW())');
            $schedules->execute(array(
                $user->ID,
                $title,
                $type,
                $time,
                $place,
                $remarks,
            ));
        }
        $schedule_id = $db_attend->lastInsertId();
        if($schedule_id != 0){
            $d = 0;
            foreach ($date as $day) {
                if($date[0] !== ''){
                    $schedule_days = $db_attend->prepare('INSERT INTO schedule_days
                                                        (
                                                            schedule_id,
                                                            schedule_date,
                                                            attendance_asa,
                                                            attendance_hiru,
                                                            attendance_yuu,
                                                            attendance_tomari,
                                                            created
                                                        )
                                                        VALUE (?,?,?,?,?,?,NOW())');
                    $schedule_days->execute(array(
                        $schedule_id,
                        $day,
                        empty($asa[$d]) ? 0 : $asa[$d],
                        empty($hiru[$d]) ? 0 : $hiru[$d],
                        empty($yuu[$d]) ? 0 : $yuu[$d],
                        empty($haku[$d]) ? 0 : $haku[$d],
                    ));
                }
                $d++;
            }
        }
        //作成した予定のページへ飛ぶ
        header('Location: https://ltconnection-aimachi.com/koteki-attendance/confirmation/?id='.$schedule_id); exit();
    }
}



?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>鼓笛出欠システム | 予定作成・編集ページ</title>
    <link rel="stylesheet" href="https://cdn.skypack.dev/sanitize.css">
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/css/style.css">
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/css/table.css">
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/css/form.css">
    <script src="<?php echo get_template_directory_uri(); ?>/js/script.js"></script>
</head>
<body>
    <div class="header_contents">
        <div class="header_content">
            <h1>新規予定作成ページ</h1>
            <?php require('php/hamburger.php'); ?>
        </div>
    </div>
    <div class="main_content">
        <p class="form_resulte" style="color: red;"><?php if($is_post){foreach($errors as $error){echo $error . '<br>';}} ?></p>
        <form id="form" action="" method="post">
            <div class="form_area content">
                <div class="form_content">
                    <div class="form_flex_item">
                        <h3><label for="title">タイトル</label></h3>
                        <input type="text" name="title" id="title" list="title_list" value="<?php echo $is_post ? $title: ''?>" required>
                        <datalist id="title_list">
                            <option value="○○ひのきしん">
                            <option value="鼓笛練習（Aグループ）">
                            <option value="鼓笛練習（Bグループ）">
                            <option value="鼓笛練習（Cグループ）">
                            <option value="鼓笛練習（Dグループ）">
                        </datalist>
                    </div>
                    <div class="form_flex_item">
                        <h3><label for="type">タイプ選択</label></h3>
                        <select name="type" id="type" required>
                            <option value="1">鼓笛</option>
                            <option value="2">ひのきしん</option>
                        </select>
                    </div>
                    <div class="form_flex_item" id="date_container">
                        <div class="day_content_title">
                            <h3><p>日付選択</p></h3>
                            <span class="add_day" onclick="addDate()">追加する</span>
                        </div>
                        <input type="hidden" name="count" id="count" value="<?php echo $is_post ? $_POST['count']: '2'?>">
                        <input style="width: 100%;" type="date" name="date[]" value="<?php echo $is_post ? $date[0]: ''?>" onchange="addMeel(this.value,'meel1')" required>
                        <div class="day_content_item" id="date2">
                            <input type="date" name="date[]" value="<?php echo $is_post ? $date[1]: ''?>" onchange="addMeel(this.value,'meel2')" >
                            <span class="del_day" onclick="delDate('date2','del_meel2')">削除</span>
                        </div>
                        <?php 
                            if ($is_post) : 
                            $i = 0;
                        ?>
                        <?php foreach ($date as $day) : ?>
                            <?php if ($i < 2) { $i++; continue; } ?>
                            <div class="day_content_item" id="date<?php echo $i + 1; ?>">
                                <input type="date" name="date[]" value="<?php echo $is_post ? $date[$i]: ''?>" onchange="addMeel(this.value,'meel<?php echo $i + 1; ?>')" >
                                <span class="del_day" onclick="delDate('date<?php echo $i + 1; ?>','del_meel<?php echo $i + 1; ?>')">削除</span>
                            </div>
                        <?php 
                            $i++; 
                            endforeach; 
                        ?>
                        <?php endif; ?>
                    </div>
                    <div class="form_flex_item">
                        <h3><label for="time">時間</label></h3>
                        <input style="width: 100%;" type="time" name="time" id="time" value="<?php echo $is_post ? $time: ''?>" required>
                    </div>
                    <div class="form_flex_item">
                        <h3><label for="place">場所</label></h3>
                        <input type="text" name="place" id="place" list="place_list" value="<?php echo $is_post ? $place: ''?>" required>
                        <datalist id="place_list">
                            <option value="神殿">
                            <option value="詰所">
                            <option value="用木">
                        </datalist>
                    </div>
                    <div class="form_flex_item">
                        <h3><p>食事</p></h3>
                        <div id="meel_container">
                            <div class="meal_item" id="del_meel1">
                                <p id="meel1"><?php echo $is_post ? date("m/d", strtotime($date[0])): 'mm/dd'?></p>
                                <div class="form_flex_item">
                                        <label for="asa1">朝<input type="checkbox" name="asa[]" value="<?php echo $is_post ? $date[0]: ''?>" id="asa1" <?php echo $is_post ? !empty($asa[0]) ? 'checked' : '': ''?>><span></span></label>
                                        <label for="hiru1">昼<input type="checkbox" name="hiru[]" value="<?php echo $is_post ? $date[0]: ''?>" id="hiru1" <?php echo $is_post ? !empty($hiru[0]) ? 'checked': '': ''?>><span></span></label>
                                        <label for="yuu1">夕<input type="checkbox" name="yuu[]" value="<?php echo $is_post ? $date[0]: ''?>" id="yuu1" <?php echo $is_post ? !empty($yuu[0]) ? 'checked': '': ''?>><span></span></label>
                                        <label for="haku1">泊<input type="checkbox" name="haku[]" value="<?php echo $is_post ? $date[0]: ''?>" id="haku1" <?php echo $is_post ? !empty($haku[0]) ? 'checked': '': ''?>><span></span></label>
                                </div>
                            </div>
                            <div class="meal_item" id="del_meel2">
                                <p id="meel2"><?php echo $is_post ? date("m/d", strtotime($date[1])): 'mm/dd'?></p>
                                <div class="form_flex_item">
                                        <label for="asa2">朝<input type="checkbox" name="asa[]" value="<?php echo $is_post ? $date[1]: ''?>" id="asa2" <?php echo $is_post ? !empty($asa[1]) ? 'checked' : '': ''?>><span></span></label>
                                        <label for="hiru2">昼<input type="checkbox" name="hiru[]" value="<?php echo $is_post ? $date[1]: ''?>" id="hiru2" <?php echo $is_post ? !empty($hiru[1]) ? 'checked' : '': ''?>><span></span></label>
                                        <label for="yuu2">夕<input type="checkbox" name="yuu[]" value="<?php echo $is_post ? $date[1]: ''?>" id="yuu2" <?php echo $is_post ? !empty($yuu[1]) ? 'checked' : '': ''?>><span></span></label>
                                        <label for="haku2">泊<input type="checkbox" name="haku[]" value="<?php echo $is_post ? $date[1]: ''?>" id="haku2" <?php echo $is_post ? !empty($haku[1]) ? 'checked' : '': ''?>><span></span></label>
                                </div>
                            </div>
                            <?php 
                                if ($is_post) : 
                                $i = 0;
                            ?>
                            <?php foreach ($date as $day) : ?>
                                <?php if ($i < 2) { $i++; continue; } ?>
                                <div class="meal_item" id="del_meel<?php echo $i + 1; ?>">
                                <p id="meel<?php echo $i + 1; ?>"><?php echo $is_post ? date("m/d", strtotime($date[$i])): 'mm/dd'?></p>
                                <div class="form_flex_item">
                                        <label for="asa<?php echo $i + 1; ?>">朝<input type="checkbox" name="asa[]" value="<?php echo $date[$i] ?>" id="asa<?php echo $i + 1; ?>" <?php echo $is_post ? !empty($asa[$i]) ? 'checked' : '': ''?>><span></span></label>
                                        <label for="hiru<?php echo $i + 1; ?>">昼<input type="checkbox" name="hiru[]" value="<?php echo $date[$i] ?>" id="hiru<?php echo $i + 1; ?>" <?php echo $is_post ? !empty($hiru[$i]) ? 'checked' : '': ''?>><span></span></label>
                                        <label for="yuu<?php echo $i + 1; ?>">夕<input type="checkbox" name="yuu[]" value="<?php echo $date[$i] ?>" id="yuu<?php echo $i + 1; ?>" <?php echo $is_post ? !empty($yuu[$i]) ? 'checked' : '': ''?>><span></span></label>
                                        <label for="haku<?php echo $i + 1; ?>">泊<input type="checkbox" name="haku[]" value="<?php echo $date[$i] ?>" id="haku<?php echo $i + 1; ?>" <?php echo $is_post ? !empty($haku[$i]) ? 'checked' : '': ''?>><span></span></label>
                                </div>
                            </div>
                            <?php 
                                $i++; 
                                endforeach; 
                            ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <script>
                        function addDate() {
                            var dateContainer = document.getElementById('date_container');
                            var count = document.getElementById('count');
                            // var dateItems = dateContainer.querySelectorAll("input");
                            // var dateCount = dateItems.length;
                            var dateCount = Number(count.value);
                            count.value = (dateCount+1);
                            var addDateItem = '<div class="day_content_item" id="date'+ (dateCount+1) +'">'+
                                                '<input type="date" name="date[]" value=""  onchange="addMeel(this.value,'+"'"+'meel'+ (dateCount+1) +"'"+')">'+
                                                '<span class="del_day" onclick="delDate('+"'"+'date'+ (dateCount+1) +"'"+','+"'"+'del_meel'+ (dateCount+1) +"'"+')">削除</span>'+
                                              '</div>';
                            dateContainer.innerHTML = dateContainer.innerHTML + addDateItem;

                            var meelContainer = document.getElementById('meel_container');
                            var addMeelItem = '<div class="meal_item" id="del_meel'+ (dateCount+1) +'">'+
                                                '<p id="meel'+ (dateCount+1) +'"></p>'+
                                                '<div class="form_flex_item">'+
                                                    '<label for="asa'+ (dateCount+1) +'">朝<input type="checkbox" name="asa[]" value="1" id="asa'+ (dateCount+1) +'" ><span></span></label>'+
                                                    '<label for="hiru'+ (dateCount+1) +'">昼<input type="checkbox" name="hiru[]" value="1" id="hiru'+ (dateCount+1) +'" ><span></span></label>'+
                                                    '<label for="yuu'+ (dateCount+1) +'">夕<input type="checkbox" name="yuu[]" value="1" id="yuu'+ (dateCount+1) +'" ><span></span></label>'+
                                                    '<label for="haku'+ (dateCount+1) +'">泊<input type="checkbox" name="haku[]" value="1" id="haku'+ (dateCount+1) +'" ><span></span></label>'+
                                                '</div>'+
                                              '</div>'
                            meelContainer.innerHTML = meelContainer.innerHTML + addMeelItem;
                        }
                        function delDate(date,meel) {
                            var dateItem = document.getElementById(date);
                            var meelItem = document.getElementById(meel);
                            console.log(dateItem.innerHTML);
                            console.log(meelItem.innerHTML);
                            dateItem.outerHTML = '';
                            meelItem.outerHTML = '';
                        }
                        function addMeel(value,id) {
                            var meelDate = document.getElementById(id);
                            var meelValue = document.getElementById('del_' + id);
                            var meelInput = meelValue.querySelectorAll("input");
                            for (let i = 0; i < meelInput.length; i++) {
                                meelInput[i].setAttribute('value', value);
                            }
                            let date = value;
                            date = date.replace(/(\d\d\d\d)-(\d\d)-(\d\d)/g,'$2/$3');
                            meelDate.innerHTML = date;

                        }
                        // function checkBoxCheck() {
                        //     var checkboxAsa = document.getElementsByName('asa[]');
                        //     var checkboxHiru = document.getElementsByName('hiru[]');
                        //     var checkboxYuu = document.getElementsByName('yuu[]');
                        //     var checkboxHaku = document.getElementsByName('haku[]');
                        //     console.log(checkboxAsa);
                        //     var inputAsa = '<input type="hidden" name="asa[]" value="0">';
                        //     var addElem = document.createElement('input');
                        //     addElem.setAttribute('type', 'hidden');
                        //     addElem.setAttribute('value', '0');
                        //     var inputHiru = '<input type="hidden" name="hiru[]" value="0">';
                        //     var inputYuu = '<input type="hidden" name="yuu[]" value="0">';
                        //     var inputHaku = '<input type="hidden" name="haku[]" value="0">';
                        //     for(let i = 0; i < checkboxAsa.length; i++){
                        //         if (!checkboxAsa[i].checked) {
                        //             checkboxAsa[i].parentNode.insertBefore(addElem, checkboxAsa[i]);
                        //         }
                        //     }
                            
                        // }
                    </script>
                    <div class="form_flex_item">
                        <h3><p>備考</p></h3>
                        <textarea name="bikou" id="bikou" cols="25" rows="10"><?php echo $is_post ? $remarks: ''?></textarea>
                    </div>
                </div>
            </div>
            <div class="button_content content">
                <a class="button" href="https://ltconnection-aimachi.com/koteki-attendance/">戻る</a>
                <button type="submit" class="button">送信</button>
            </div>
        </form>
        <div class="content_after"></div>
    </div>
    
</body>
</html>


