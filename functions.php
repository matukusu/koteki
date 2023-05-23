<?php
/**
 * ログイン処理をまとめた関数
 */
function my_user_login() {
    $user_name = isset( $_POST['user_name'] ) ? sanitize_text_field( $_POST['user_name'] ) : '';
    $user_pass = isset( $_POST['user_pass'] ) ? sanitize_text_field( $_POST['user_pass'] ) : '';

    // ログイン認証
    $creds = array(
        'user_login' => $user_name,
        'user_password' => $user_pass,
    );
    $user = wp_signon( $creds );

    //ログイン失敗時の処理
    if ( is_wp_error( $user ) ) {
        // echo $user->get_error_message();
        $error = '?error=1';
        if(preg_match('/.*\?.*/',$_POST['my_url'])) {
            $error = '&error=1';
        }
        wp_redirect( $_POST['my_url'].$error );
        exit;
    }

    //ログイン成功時の処理 
    wp_redirect( $_POST['my_url'] );
    exit;

    return;
}
/**
 * after_setup_theme に処理をフック
 */
add_action('after_setup_theme', function() {
    if ( isset( $_POST['my_submit'] ) && $_POST['my_submit'] === 'login') {

        // nonceチェック
        if ( !isset( $_POST['my_nonce_name'] ) ) return;
        if ( !wp_verify_nonce( $_POST['my_nonce_name'], 'my_nonce_action' ) ) return;

        // ログインフォームからの送信があれば
        my_user_login();
    }
});


function logout_redirect(){
	wp_safe_redirect("https://ltconnection-aimachi.com/koteki-attendance/");
	exit();
}
add_action('wp_logout','logout_redirect');


