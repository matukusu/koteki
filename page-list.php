<?php
if (!is_user_logged_in()) {
    echo "ログインしてからき～や";
    die();
}

if(empty($_REQUEST['id'])){
	header('Location: https://ltconnection-aimachi.com/koteki-attendance/');
	exit();
}

require('database/dbconnect_hp.php');
require('database/dbconnect_attendance.php');

$user = wp_get_current_user( );
//$user->ID;

$schedules = $db_attend->prepare('SELECT title FROM schedules WHERE id = ?');
$schedules->execute( array( $_REQUEST['id'] ) );
$schedule = $schedules->fetch();



$answers = $db_attend->prepare('SELECT *, (attendance_asa + attendance_hiru + attendance_yuu + attendance_tomari) as answer_num
                                FROM answers a
                                INNER JOIN answer_days ad ON a.id = ad.answer_id
                                INNER JOIN schedule_days sd ON a.schedule_id = sd.schedule_id and ad.answer_date = sd.schedule_date
                                WHERE a.schedule_id = ?
                                GROUP BY ad.id
                                ORDER BY a.id, ad.id
                                ');
$answers->execute( array( $_REQUEST['id'] ) );
$answers = $answers->fetchAll();


if(!empty($_POST['csv'])) {
    //CSVファイル作成
    $fileName = "test";

    $header = [];
    foreach ($answers as $answer) {
        if (!isset($answer_id_csv)) {
            $answer_id_csv = $answer['answer_id'];
        }
        if ($answer_id_csv == $answer['answer_id']) {
            $header[] = '出欠';
            $answer['attendance_asa'] == 1 ? $header[] = '朝' : '';
            $answer['attendance_hiru'] == 1 ? $header[] = '昼' : '';
            $answer['attendance_yuu'] == 1 ? $header[] = '夕' : '';
            $answer['attendance_tomari'] == 1 ? $header[] = '泊' : '';
        } else {
            break;
        }
        $answer_id_csv = $answer['answer_id'];
    }
    $i = 0;
    foreach ($answers as $answer) {
        //一回目のみ
        if (empty($answer_id)) {
            $answer_id = $answer['answer_id'];
        }
        //違ったら次の行
        if ($answer_id != $answer['answer_id']) {
            $i++;
        }
        $answer_id = $answer['answer_id'];

        $records[$i][] = $answer['answer'] == 1 ? '○' : '×';
        if ($answer['attendance_asa'] == 1) {$records[$i][] =  $answer['answer_asa'] == 1 ? '○' : '×';}
        if ($answer['attendance_hiru'] == 1) {$records[$i][] =  $answer['answer_hiru'] == 1 ? '○' : '×';}
        if ($answer['attendance_yuu'] == 1) {$records[$i][] =  $answer['answer_yuu'] == 1 ?  '○' : '×';}
        if ($answer['attendance_tomari'] == 1) {$records[$i][] =  $answer['answer_tomari'] == 1 ? '○' : '×';}
    }
    // $records = array(
    //     array("1行目1列目","1行目2列目","1行目3列目"),
    //     array("2行目1列目","2行目2列目","2行目3列目"),
    //     array("3行目1列目","3行目2列目","3行目3列目"),
    // );
    createCsv($fileName, $header, $records);

}
//csv出力
function createCsv($filename,$header,$records)
{
    header('Content-Type: application/octet-stream');
    header("Content-Disposition: attachment; filename=$filename.csv");

    mb_convert_variables('SJIS','UTF-8',$header);   //SJISで開くことが多い場合いれるといいかも
    mb_convert_variables('SJIS','UTF-8',$records);  //SJISで開くことが多い場合いれるといいかも

    //ファイルを開く
    $stream = fopen('php://output', 'w');
    
    //ヘッダーを書き込み
    fputcsv($stream, $header);

    //レコードを書き込み
    foreach($records as $record){
        fputcsv($stream, $record);
    }
    exit();
}




?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>鼓笛出欠システム | 回答一覧ページ</title>
    <link rel="stylesheet" href="https://cdn.skypack.dev/sanitize.css">
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/css/style.css">
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/css/table.css">
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/css/list.css">
    <script src="<?php echo get_template_directory_uri(); ?>/js/script.js"></script>
</head>
<body>
    <div class="header_contents">
        <div class="header_content">
            <h1>回答一覧ページ</h1>
            <?php require('php/hamburger.php'); ?>
        </div>
    </div>
    <div class="main_content">
        <h2><?php print(htmlspecialchars($schedule['title'], ENT_QUOTES)); ?></h2>
        <div class="list_content content">
            <h3>回答一覧</h3>
            <div class="table">
                <table>
                    <?php if(!empty($answers)): ?>
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
                            $answer_date_cnt;
                            $count = count($answers);
                            $a = 0;
                            $c = 0;
                        ?>
                        <?php foreach ($answers as $answer):
                            $a++;

                            if ($answer_id == $answer['answer_id']) 
                            {

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
                                if(empty($answer_id))
                                {
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
                    <tfoot>
                        <tr>
                            <th>集計</th>
                            <td></td>
                            <?php 
                            $answer_id = 0;
                            foreach ($answers as $answer)
                            {
                                $answer_cnt[$answer['answer_date']] = $answer_cnt[$answer['answer_date']] + $answer['answer'];
                                $answer_asa_cnt[$answer['answer_date']] = $answer_asa_cnt[$answer['answer_date']] + $answer['answer_asa'];
                                $answer_hiru_cnt[$answer['answer_date']] = $answer_hiru_cnt[$answer['answer_date']] + $answer['answer_hiru'];
                                $answer_yuu_cnt[$answer['answer_date']] = $answer_yuu_cnt[$answer['answer_date']] + $answer['answer_yuu'];
                                $answer_tomari_cnt[$answer['answer_date']] = $answer_tomari_cnt[$answer['answer_date']] + $answer['answer_tomari'];
                            }
                            $i = 0;
                            foreach ($answer_cnt as $answer)
                            {
                                echo '<td>' . current( array_slice($answer_cnt, $i, 1, true) ) . '</td>';
                                echo $answers[$i]['attendance_asa'] == 1 ? '<td>' . current( array_slice($answer_asa_cnt, $i, 1, true) ) . '</td>' : '';
                                echo $answers[$i]['attendance_hiru'] == 1 ? '<td>' . current( array_slice($answer_hiru_cnt, $i, 1, true) ) . '</td>' : '';
                                echo $answers[$i]['attendance_yuu'] == 1 ? '<td>' . current( array_slice($answer_yuu_cnt, $i, 1, true) ) . '</td>' : '';
                                echo $answers[$i]['attendance_tomari'] == 1 ? '<td>' . current( array_slice($answer_tomari_cnt, $i, 1, true) ) . '</td>' : '';
                                $i++;
                            }
                            ?>
                            <td></td>
                        </tr>
                    </tfoot>
                    <?php else: ?>
                        <p>まだ回答はありません</p>
                    <?php endif; ?>
                </table>
            </div>
        </div>
        <div class="button_content content">
            <form action="" method="post" name="csv">
                <input style="border: none;" class="button" type="submit" name="csv" value=".csv出力">
            </form>
            <a class="button" href="https://ltconnection-aimachi.com/koteki-attendance/confirmation/?id=<?php echo $_REQUEST['id'] ?>">戻る</a>
        </div>
        <div class="content_after"></div>
    </div>
    
</body>
</html>
