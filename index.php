<?php
require_once 'wxclass.php';
require_once 'db.php';

/**************在此之下填写相应数据***************/

$token          = "填入微信token";
$appid          = "填入微信开发者ID";
$encodingAESKey = "未使用加密，可以不填";
$showapi_appid  ='填入www.showapi.com的appid';
$showapi_secret='填入www.showapi..com的secret';

/**************在此之上填写相应数据***************/


$wx = new WXclass($token, $appid, $encodingAESKey);
$wx->set_showip($showapi_appid, $showapi_secret);
$db = new Db();
$db->setCharset('utf8');
if (isset($_GET['echostr'])) {
    $signature = $_GET['signature'];
    $timestamp = $_GET['timestamp'];
    $nonce     = $_GET['nonce'];
    if ($wx->validate($signature, $timestamp, $nonce)) {
        echo $_GET['echostr'];
    } else {
        echo "";
    }
    exit;
}

$receiveString = file_get_contents("php://input");
$array         = $wx->parseReceive($receiveString);

if ($array['MsgType'] == 'text') {

    $arr = $db->select('wxstate', ['state'], 'fromusername = "' . $array['FromUserName'] . '"');
    if (!$arr) {
        $db->insert('wxstate', ['state' => 0, 'fromusername' => $array['FromUserName']]);
        main();
    } else if ($arr[0]['state'] == 0) {
        switch ($array['Content']) {
            case '1':
                $db->update('wxstate', ['state' => 1], 'fromusername = "' . $array['FromUserName'] . '"');
                searchJoke();
                break;
            case '2':
                $db->update('wxstate', ['state' => 2], 'fromusername = "' . $array['FromUserName'] . '"');
                searchMusicTitle();
                break;
            case '3':

                $db->update('wxstate', ['state' => 3], 'fromusername = "' . $array['FromUserName'] . '"');
                searchWeatherTitle();
                break;
            default:
                main();
                break;
        }
    } else {
        switch ($arr[0]['state']) {
            case '1':
                if ($array['Content'] === '0') {
                    $db->update('wxstate', ['state' => 0], 'fromusername = "' . $array['FromUserName'] . '"');
                    main();
                } else if ($array['Content'] == 1) {
                    searchJoke();
                } else {
                    echo 'success';
                }
                break;
            case '2':
                if ($array['Content'] === '0') {
                    file_put_contents('musicerr', date('Y-m-d H:i:s') . ':' . $array['Content'] . "\n", FILE_APPEND);
                    $db->update('wxstate', ['state' => 0], 'fromusername = "' . $array['FromUserName'] . '"');
                    main();
                } else {
                    searchMusic($array['Content']);
                }
                break;
            case '3':
                if ($array['Content'] === '0') {
                    $db->update('wxstate', ['state' => 0], 'fromusername = "' . $array['FromUserName'] . '"');
                    main();
                } else {
                    searchWeather($array['Content']);
                }
                break;
            default:
                echo 'success';
                break;
        }
    }
} else {
    echo "success";
    exit;
}

function main()
{
    $str = "回复1 看笑话 \n回复2 找音乐 \n回复3 查天气";
    global $wx;
    echo $wx->resText($str);
    exit;
}

function searchJoke()
{
    global $wx, $db;
    $id   = mt_rand(1, 7000);
    $text = $db->select('joke', ['text'], 'id = ' . $id);
    $str  = ($text[0]['text']) . "\n" . "继续回复1，返回主菜单回复0";
    echo $wx->resText($str);
    exit;
}

function searchMusicTitle()
{
    $str = "请输入您要搜索的音乐，返回主菜单回复0";
    global $wx;
    echo $wx->resText($str);
    exit;
}

function searchWeatherTitle()
{
    $str = "请输入您要查询的城市，返回主菜单回复0";
    global $wx;
    echo $wx->resText($str);
    exit;
}

function searchMusic($value)
{
    global $wx;
    echo $wx->resMusic($value);
    exit;
}

function searchWeather($value)
{
    global $wx;
    echo $wx->resWeather($value);
    exit;
}
