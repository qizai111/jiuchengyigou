<?php

return [
	'url'=>'https://qyapi.weixin.qq.com/cgi-bin/webhook/send?key=9f690980-12fc-4595-ab00-6582277ac78a'
	];
//天气助手
function weathers(){
    $url = 'https://qyapi.weixin.qq.com/cgi-bin/webhook/send?key=9f690980-12fc-4595-ab00-6582277ac78a';
    $resdata = json_decode(getWeather('泸州'),true);
    $weatherData = $resdata['results'][0];
    $tipt = '';
    foreach($weatherData['index'] as $indexkey => $indexvalue){
        $tipt.="> ##### ".$indexvalue['tipt']."(".$indexvalue['zs'].")\n".$indexvalue['des']."\n";
    }
    $weather_data = $weatherData['weather_data'][0];
    $weatherstr = $weather_data['date']."，\n ##### ".$weather_data['weather'].'，'.$weather_data['wind']."，".$weather_data['temperature'];
    $data = array(
        "msgtype"=>"markdown",
        "markdown"=>array(
            "content"=>'#### '.$weatherData['currentCity']." ：".$weatherstr."\n".$tipt,
            "mentioned_list"=>array("@all")
        )
    );
    $res = request_post($url, json_encode($data,'320'),'json');
    print_r($res);
}

//发送文本
function text($url,$cont,$list){
    $data = array(
        "msgtype"=>"text",
        "text"=>array(
            "content"=>$cont,
            "mentioned_list"=>$list
        )
    );
    $res = request_post($url, json_encode($data,'320'),'json');
    print_r($res);
}

/**
 * 根据城市名称/ID获取详细天气预报
 * @param string $city [城市名称/ID]
 * @return array
 */
function getWeather($city){
    //百度天气接口API
    $location = $city;  //地区
    $ak = ""; //秘钥，需要申请，百度为了防止频繁请求
    $weatherURL = "http://api.map.baidu.com/telematics/v3/weather?location=$location&output=json&ak=$ak";
    $res = httpGet($weatherURL);
    return $res;
}

/**
 * 模拟get进行url请求
 * @param string $url
 * @return json
 */
function httpGet($url) {

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_TIMEOUT, 500);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_URL, $url);

    $res = curl_exec($curl);
    curl_close($curl);

    return $res;
}

    /**
     * 模拟post进行url请求
     * @param string $url
     * @param array $post_data
     * @param string $dataType
     * @return bool|mixed
     */
    function request_post($url = '', $post_data = array(),$dataType='') {
        if (empty($url) || empty($post_data)) {
            return false;
        }
        $curlPost = $post_data;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if($dataType=='json'){
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/x-www-form-urlencoded;charset=UTF-8',
                    'Content-Length: ' . strlen($curlPost)
                )
            );
        }
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        $data = curl_exec($ch);
        return $data;
    }