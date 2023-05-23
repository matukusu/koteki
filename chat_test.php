<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>チャット</title>
    <link rel="stylesheet" href="https://cdn.skypack.dev/sanitize.css">
    <script src="js/script.js" defer></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
</head>
<body>
    <div class="main_content" style="width: 100%;">
        <div style="
        width: 90%;
        margin: 10px auto;
        border: solid 3px #000;">
            <div id="message_box" style="margin: 10px;">
                <h3>name</h3>
                <p>texttexttexttexttexttexttexttext</p>
            </div>
        </div>
        <div style="
        width: 90%;
        margin: 10px auto;
        border: solid 3px #000;
        display: flex;
        align-items: center;
        justify-content: center;">
            <div style="
        display: flex;
        flex-direction: column;
        align-items: center;">
                <p style="margin-bottom: 0px;">名前</p>
                <input type="text" name="name" id="name" require>
                <p style="margin-bottom: 0px;">内容</p>
                <textarea name="text" id="text" cols="30" rows="3" style="margin-bottom: 10px;" require></textarea>
            </div>
            <button style="height: 30px;" onclick="add_data()">送信</button>
        </div>
    </div>
</body>
</html>