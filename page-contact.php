<?php

if (!is_user_logged_in()) {
    require('php/login_form.php');
    die();
}

require('database/dbconnect_hp.php');
require('database/dbconnect_attendance.php');

$user = wp_get_current_user();

$contacts = $db_attend->prepare('SELECT c.*, s.status
                                FROM contacts c
                                LEFT JOIN contact_statuses s ON c.id = s.contact_id
                                                             AND s.user_id = ?
                                #WHERE c.deleted = 0
                                ORDER BY c.id DESC
                            ');
$contacts->execute(array($user->ID));

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
        <div class="content contact_content">
            <div class="select_content">
                <label for="sort" class="select_sort">
                    <span style="width: 120px;">並び替え：</span>
                    <select name="sort" id="sort">
                        <option value="desc">投稿が新しい順</option>
                        <option value="asc">投稿が古い順</option>
                    </select>
                </label>
                <div class="select_message">
                    <label for="read">既読<input type="checkbox" name="read" id="read"><span></span></label>
                    <label for="unread">未読<input type="checkbox" name="unread" id="unread"><span></span></label>
                    <label for="replyed">返信済<input type="checkbox" name="replyed" id="replyed"><span></span></label>
                </div>
            </div>
            <ul class="contact_lists">
                <?php 
                    foreach ($contacts as $contact) : 
                        $user = get_user_meta($contact['user_id']);
                ?>
                    <a href="https://ltconnection-aimachi.com/koteki-attendance/contact_confirm?id=<?php echo $contact['id'] ?>">
                        <li class="contact_list <?php echo $contact['status'] == 2 ? 'replyed': ($contact['status'] == 1 ? 'read': 'unread')?>">
                            <h3><?php echo htmlspecialchars($contact['title']) ?></h3>
                            <p>日時　:<?php echo date("m月d日 H:i", strtotime($contact['created'])); ?></p>
                            <p>投稿者:<?php echo $user['last_name'][0] . $user['first_name'][0] ?></p>
                        </li>
                    </a>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="button_content content">
            <a class="button" href="https://ltconnection-aimachi.com/koteki-attendance/">戻る</a>
            <a class="button" href="https://ltconnection-aimachi.com/koteki-attendance/contact_form">新規作成</a>
        </div>
        <div class="content_after"></div>
    </div>
    <script >
        window.onpageshow = function(event) {
            if (event.persisted) {
                window.location.reload();
            }
        };
        window.addEventListener('pageshow',()=>{
            if(window.performance.navigation.type==2) location.reload();
        });

        //=========================
        //既読、未読、送信済の処理
        //=========================
        var read = document.getElementById('read');
        var unread = document.getElementById('unread');
        var replyed = document.getElementById('replyed');
	    var readClass = document.getElementsByClassName('read');
	    var unreadClass = document.getElementsByClassName('unread');
	    var replyedClass = document.getElementsByClassName('replyed');
        read.addEventListener('click', function(){
            if(read.checked){
                if(!unread.checked){
                    for(var i=0; i<unreadClass.length; i++){
                        unreadClass[i].classList.add('display_none');
                    }
                }
                if(!replyed.checked){
                    for(var i=0; i<replyedClass.length; i++){
                        replyedClass[i].classList.add('display_none');
                    }
                }
                for(var i=0; i<readClass.length; i++){
                    readClass[i].classList.remove('display_none');
                }
            } else {
                if(unread.checked || replyed.checked){
                    for(var i=0; i<readClass.length; i++){
                        readClass[i].classList.add('display_none');
                    }
                } else {
                    for(var i=0; i<unreadClass.length; i++){
                        unreadClass[i].classList.remove('display_none');
                    }
                    for(var i=0; i<replyedClass.length; i++){
                        replyedClass[i].classList.remove('display_none');
                    }
                }
            }
        });
        unread.addEventListener('click', function(){
            if(unread.checked){
                if(!read.checked){
                    for(var i=0; i<readClass.length; i++){
                        readClass[i].classList.add('display_none');
                    }
                }
                if(!replyed.checked){
                    for(var i=0; i<replyedClass.length; i++){
                        replyedClass[i].classList.add('display_none');
                    }
                }
                for(var i=0; i<unreadClass.length; i++){
                    unreadClass[i].classList.remove('display_none');
                }
            } else {
                if(read.checked || replyed.checked){
                    for(var i=0; i<unreadClass.length; i++){
                        unreadClass[i].classList.add('display_none');
                    }
                } else {
                    for(var i=0; i<replyedClass.length; i++){
                        replyedClass[i].classList.remove('display_none');
                    }
                    for(var i=0; i<readClass.length; i++){
                        readClass[i].classList.remove('display_none');
                    }
                }
            }
        });
        replyed.addEventListener('click', function(){
            if(replyed.checked){
                if(!unread.checked){
                    for(var i=0; i<unreadClass.length; i++){
                        unreadClass[i].classList.add('display_none');
                    }
                }
                if(!read.checked){
                    for(var i=0; i<readClass.length; i++){
                        readClass[i].classList.add('display_none');
                    }
                }
                for(var i=0; i<replyedClass.length; i++){
                    replyedClass[i].classList.remove('display_none');
                }
            } else {
                if(read.checked || unread.checked){
                    for(var i=0; i<replyedClass.length; i++){
                        replyedClass[i].classList.add('display_none');
                    }
                } else {
                    for(var i=0; i<unreadClass.length; i++){
                        unreadClass[i].classList.remove('display_none');
                    }
                    for(var i=0; i<readClass.length; i++){
                        readClass[i].classList.remove('display_none');
                    }
                }
            }
        });

        //=========================
        //並び替えの処理
        //=========================
        var sort = document.getElementById('sort');
	    var lists = document.getElementsByClassName('contact_lists');
	    var list = document.getElementsByClassName('contact_list');

        sort.addEventListener('change', function(){
            //if (sort.value == 'asc') {
                for (i = 1; i < lists[0].children.length; i++) {
                    lists[0].insertBefore(lists[0].children[i],lists[0].children[0]);
                }
            //}
        });

    </script>
</body>
</html>
