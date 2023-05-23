<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.skypack.dev/sanitize.css">
</head>
<body>
    <div style="padding: 10px; margin: 10px; border-radius: 10px; border: solid 3px #000; max-width: 600px;">
        <?php if(!empty($_REQUEST['error'])): ?>
            <div style="color: red">
                <p>ログインエラー<br>ユーザー名又はメールアドレス/パスワードが違います</p>
            </div>
        <?php else: ?>
            <p>ログインしていません</p>
        <?php endif; ?>
        <p>フォームに以下の情報を入力して<br>ログインして下さい。</p>
        <ul>
            <li>ユーザー名又はメールアドレス</li>
            <li>パスワード</li>
        </ul>
        <form class="my_form" name="my_login_form" id="my_login_form" action="" method="post">
            <input type="hidden" name="my_url" value="<?php echo (empty($_SERVER['HTTPS']) ? 'http://' : 'https://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
            <div style="padding: 0 10px; margin: 5px; border-radius: 5px; border: solid 1px #000;">
                <div style="display: flex; flex-direction: column; margin: 20px 0;">
                    <label for="login_user_name">ユーザ名 or メールアドレス</label>
                    <input id="login_user_name" name="user_name" type="text" required>
                </div>
                <div style="display: flex; flex-direction: column; margin: 20px 0;">
                    <label for="login_password">パスワード</label>
                    <input id="login_password" name="user_pass" id="user_pass" type="password" required>
                </div>
                <button style="margin-bottom: 20px;" type="submit" name="my_submit" class="my_submit_btn" value="login">ログイン</button>
            </div>
            
            <p class="my_forgot_pass">
                <a href="https://ltconnection-aimachi.com/koteki-attendance/wp-login.php?action=lostpassword">パスワードを忘れた方はこちらから</a>
            </p>
            <?php wp_nonce_field( 'my_nonce_action', 'my_nonce_name' );  //nonceフィールド設置 ?>
        </form>
    </div>
</body>
