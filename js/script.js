
//コメントを外したら動くようになる
// $(function() {
//     get_data();
// });

var jsonData = "";
var jsonCount = 0;
var baseCount = 0;

// function get_data() {
//     $.ajax({
//         type: 'post',
//         url: 'php/file.php',
//         data: {'func':'get_content'},
//         success: data => {
//             console.log(data);
//             jsonData = JSON.parse(data);
//             console.log('name:' + jsonData.message[0].name + 'text:' + jsonData.message[1].text);
//             jsonCount = jsonData.message.length;
//             $(function() {
//                 if(baseCount < jsonCount){
//                     open_data(jsonData);
//                     baseCount = jsonCount;
//                 }
//             });
//         },
//         error: () => {
//             alert("ajax Error");
//         }
//     });

//     setTimeout("get_data()", 5000);
// }

function open_data(jsonData) {
    var message = document.getElementById('message_box');
    var maxCount = jsonCount - baseCount;
    for(var i = 0; maxCount > i; i++){
        console.log(jsonData.message[i].name);
        message.innerHTML += '<h3>' + jsonData.message[baseCount].name + '</h3>';
        message.innerHTML += '<p>' + jsonData.message[baseCount].text + '</p>';
        baseCount++;
    }
}

function add_data(){
    var postname = document.getElementById('name').value;
    var postText = document.getElementById('text').value;
    jsonCount = jsonData.message.length;
    jsonData.message[jsonCount] = {'name': postname, 'text': postText};
    console.log(jsonData);

    $.ajax({
        type: 'post',
        url: 'php/file.php',
        data: { 'name':postname, 'text': postText,
                'func':'put_content'},
        success: data => {
            console.log(data);
            document.getElementById('name').value = '';
            document.getElementById('text').value = '';
        }
    })
}


//===============================================
//連絡詳細画面をリアルタイムに更新する処理
//===============================================
var create = '';
var ID = '';
var userID = '';
function get_data(id,created,userID) {
    if(create == ''){
        create = created;
        userID = userID;
    }
    console.log(userID);
    console.log('s'+created);
    ID = id;
    $.ajax({
        type: 'post',
        url: 'https://ltconnection-aimachi.com/koteki-attendance/wp-content/themes/koteki/php/db_access.php',
        data: {
                'id':id,
                'created':created,
                'func':'getNewReply'
            },
        success: data => {
            console.log(data);
            jsonData = JSON.parse(data);
            console.log(jsonData);
            jsonCount = jsonData.length;
            console.log(jsonCount);
            if(baseCount < jsonCount){
                openData(jsonData, userID)
                create = jsonData[jsonCount-1].created;
                jsonCount = 0;
                console.log(jsonCount);
                console.log(create);
            }
            console.log(create);

            setTimeout(get_data, 2500, id, create, userID);
        },
        error: () => {
            alert("ajax Error");
        }
    });
    console.log(create);
}

//===============================================
//画面を読み込んだ際に一番下までスクロールする
//===============================================

function openData(jsonData, userID) {
    var message = document.getElementById('confirm');
    var rigthName;
    var rigthMessage;
    for (var i=0; i<jsonData.length; i++) {
        console.log(userID);
        console.log(jsonData[i].user_id);
        if (userID == jsonData[i].user_id) {
            rigthName = 'my_post_name';
            rigthMessage = 'my_post_message';
        }
        message.innerHTML += '<li class="contact_list confirm_list">' +
                                '<div class="list_name_content ' + rigthName + '">' +
                                    '<h3>' + jsonData[i].name + '</h3>' +
                                    '<p>' + jsonData[i].time + '</p>' +
                                '</div>' +
                                '<div class="message ' + rigthMessage + '">' +
                                    '<p style="background: #fff3f3;padding: 5px;border-radius: 10px;">' + jsonData[i].reply_message + '</p>'
                                '</div>' +
                             '</li>';
    }
    let chatArea = document.getElementById('confirm'),
    chatAreaHeight = chatArea.scrollHeight;
    chatArea.scrollTop = chatAreaHeight;
}

//===============================================
//画面を読み込んだ際に一番下までスクロールする
//===============================================

window.addEventListener('DOMContentLoaded', function() {
    let chatArea = document.getElementById('confirm'),
    chatAreaHeight = chatArea.scrollHeight;
    chatArea.scrollTop = chatAreaHeight;
  })


//===============================================
//テキストエリアがフォーカス状態になった時の処理
//===============================================

// const textarea = document.getElementById('contact_reply');
// const confirm = document.getElementById('confirm');
// function focusEvent() {
// 	// 処理内容
//     confirm.classList.add('focus_height');
// }

// textarea.addEventListener('blur', () => {
//   confirm.classList.remove('focus_height');
// }, true);








