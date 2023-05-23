<?php
function put_content(){ //テキストファイルの内容を返す関数
    
    $file = '../data/data.json'; //ファイルパス取得
    $contents = file_get_contents($file); //ファイル内容取得
    $json = mb_convert_encoding($contents, 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');
    $jsonData = json_decode($json,true);
    $count = count($jsonData['message']);
    $jsonData['message'][$count]['name'] = $_POST['name'];
    $jsonData['message'][$count]['text'] = $_POST['text'];

    $jsonData = json_encode($jsonData, JSON_UNESCAPED_UNICODE);
    file_put_contents("../data/data.json", $jsonData);

}

function get_content(){ //テキストファイルの内容を返す関数
    
    $file = '../data/data.json'; //ファイルパス取得
    $contents = file_get_contents($file); //ファイル内容取得
    
    return $contents;
}

$func = $_POST['func'];
echo $func();