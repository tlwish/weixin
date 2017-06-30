<?php
/**
 *
 */
class WXclass
{
    //生成签名
    private $token;

    //第三方用户唯一凭证
    private $appid;

    //由开发者手动填写或随机生成，将用作消息体加解密密钥
    private $encodingAESKey;

    //第三方用户唯一凭证密钥，即appsecret
    private $appsecret;

    private $dataArr;

    private $showapi_appid;
    private $showapi_secret;
    /**
     * [__construct description]
     * @param [string] $token          [传入token]
     * @param [string] $appid          [传入appid]
     * @param string $encodingAESKey [如果需要加密，传入encodingAESKey，默认为空]
     */
    public function __construct($token, $appid, $encodingAESKey = "")
    {
        $this->token          = $token;
        $this->appid          = $appid;
        $this->encodingAESKey = $encodingAESKey;
    }
    /**
     * [set_showip 设置易源接口]
     * @param [string] $showapi_appid  [appid]
     * @param [string] $showapi_secret [secret]
     */
    public function set_showip($showapi_appid, $showapi_secret)
    {
        $this->showapi_appid  = $showapi_appid;
        $this->showapi_secret = $showapi_secret;
    }

    /**
     * [验证消息的确来自微信服务器]
     * @param  [string] $signature [微信加密签名，signature结合了开发者填写的token参数和请求中的timestamp参数、nonce参数。]
     * @param  [string] $timestamp [时间戳]
     * @param  [string] $nonce     [随机数]
     * @return [boolean]            [是返回true，否返回false]
     */
    public function validate($signature, $timestamp, $nonce)
    {
        $arr = array($this->token, $timestamp, $nonce);
        sort($arr);
        $str = implode($arr);
        $str = sha1($str);
        if ($str == $signature) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * [set_appsecret 设置生存access_token需要的appsecret]
     * @param  [string] $appsecret [传入appsecret]
     * @return [null]            [无返回值]
     */
    public function set_appsecret($appsecret)
    {
        $this->appsecret = $appsecret;
    }

    /**
     * [get_access_token 获取access_token]
     * @return [string] [返回access_token]
     */
    public function get_access_token()
    {
        if (file_exists('access_token')) {
            $res = file_get_contents('access_token');
            if ($res === false) {
                $this->errorlog('读取文件access_token错误');
            } else {
                $res = json_decode($res, true);
                if ($res['expires_in'] >= time()) {
                    return $res['access_token'];
                }
            }

        }
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$this->appid}&secret={$this->appsecret}";
        $ch  = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        if (curl_exec($ch)) {
            $res = curl_multi_getcontent($ch);
            $res = json_decode($res, true);
            if (array_key_exists('errcode', $res) && $res['errcode'] != 0) {
                $this->errorlog(json_encode($res));
            } else {
                $access_token      = $res['access_token'];
                $res['expires_in'] = $res['expires_in'] + time();
                file_put_contents('access_token', json_encode($res));
            }
        } else {
            $this->errorlog(curl_error($ch));
        }
        curl_close($ch);
        return $access_token;
    }

    /**
     * [errorlog 保存错误信息]
     * @param  [string] $error [错误信息]
     * @return [null]        [无返回值]
     */
    public function errorlog($error)
    {
        file_put_contents('error_log', date('Y-m-d H:i:s') . ':' . $error . "\n", FILE_APPEND);
    }

    /**
     * [parseReceive 解析数据返回数组]
     * @param  [string] $xml [传入XML字符串]
     * @return [array]      [返回数组]
     */
    public function parseReceive($xml)
    {
        $this->dataArr = $this->xml2array($xml);
        return $this->dataArr;
    }

    /**
     * [xml2array 将xml格式转换成数组]
     * @param  [string]  $xml       [传入XML字符串]
     * @param  boolean $recursive [不需要输入]
     * @return [array]             [返回数组]
     */
    private function xml2array($xml, $recursive = false)
    {
        if (!$recursive) {
            $array = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        } else {
            $array = $xml;

        }
        $newArray = array();
        $array    = (array) $array;
        foreach ($array as $key => $value) {
            if (!is_object($value) && !is_array($value)) {
                $newArray[$key] = $value;
            } else {
                $newArray[$key] = $this->xml2array($value, true);

            }
        }
        return $newArray;
    }

    public function resText($content)
    {
        $textTpl = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[text]]></MsgType>
                    <Content><![CDATA[%s]]></Content>
                    </xml>";
        $text = sprintf($textTpl, $this->dataArr['FromUserName'], $this->dataArr['ToUserName'], time(), $content);
        return $text;
    }

    public function resMusic($content)
    {
        $textTpl = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[music]]></MsgType>
                    <Music>
                    <Title><![CDATA[%s]]></Title>
                    <Description><![CDATA[%s]]></Description>
                    <MusicUrl><![CDATA[%s]]></MusicUrl>
                    <HQMusicUrl><![CDATA[%s]]></HQMusicUrl>
                    </Music>
                    </xml>";
        $url = 'http://route.showapi.com/213-1?page=1&showapi_appid=' . $this->showapi_appid . '&showapi_sign=' . $this->showapi_secret . '&keyword=' . $content;
        $ch  = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if (curl_exec($ch)) {
            $res                = curl_multi_getcontent($ch);
            $res                = json_decode($res, true);
            $arr                = array();
            $arr['title']       = $res['showapi_res_body']['pagebean']['contentlist'][0]['songname'];
            $arr['description'] = $res['showapi_res_body']['pagebean']['contentlist'][0]['singername'];
            $arr['musicurl']    = $res['showapi_res_body']['pagebean']['contentlist'][0]['m4a'];
            $arr['hqmusicurl']  = $arr['musicurl'];
        } else {
            $this->errorlog(curl_error($ch));
            echo $this->resText('未找到音乐');
            exit;
        }
        curl_close($ch);

        $text = sprintf($textTpl, $this->dataArr['FromUserName'], $this->dataArr['ToUserName'], time(), $arr['title'], $arr['description'], $arr['musicurl'], $arr['hqmusicurl']);
        return $text;
    }

    public function resWeather($content)
    {
        $url = 'http://route.showapi.com/9-2?area=' . $content . '&showapi_appid=' . $this->showapi_appid . '&showapi_sign=' . $this->showapi_secret;
        $ch  = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        if (curl_exec($ch)) {
            $res = curl_multi_getcontent($ch);
            $res = json_decode($res, true);
            if (!$res['showapi_res_body']['ret_code']) {
                $str = '';
                $str .= $res['showapi_res_body']['now']['aqiDetail']['area'] . "\n";
                $str .= '温度:' . $res['showapi_res_body']['now']['temperature'] . "℃\n";
                $str .= $res['showapi_res_body']['now']['weather'] . "\n";
                $str .= '风速 ' . $res['showapi_res_body']['now']['wind_direction'] . $res['showapi_res_body']['now']['wind_power'] . "\n";
                return $this->resText($str);
            } else {
                return $this->resText($res['showapi_res_body']['remark']);
            }

        } else {
            $this->errorlog(curl_error($ch));
        }
    }
}
