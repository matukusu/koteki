<input type="checkbox" class="menu-btn" id="menu-btn">
<label for="menu-btn" class="hamburger">
    <div class="hamburger_item ham_1"></div>
    <div class="hamburger_item ham_2"></div>
    <div class="hamburger_item ham_3"></div>
</label>
<ul class="menu">
    <a href="https://ltconnection-aimachi.com/"><li class="top">LTconnection</li></a>
    <a href="https://ltconnection-aimachi.com/koteki/"><li>愛町鼓笛隊</li></a>
    <a href="https://ltconnection-aimachi.com/koteki-attendance/"><li>出欠TOP</li></a>
    <a href="https://ltconnection-aimachi.com/koteki-attendance/contact/"><li>連絡掲示板</li></a>
    <?php if (is_user_logged_in()): ?>
    <a href="<?php echo wp_logout_url() ?>"><li>ログアウト</li></a>
    <?php endif; ?>
</ul>