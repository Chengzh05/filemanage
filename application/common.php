<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
function request_curl_post_fun($url, $post_data, $timout = 10)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
    curl_setopt($ch, CURLOPT_POST, 1);
    // new -- begin

    // 当传输速度小于CURLOPT_LOW_SPEED_LIMIT时(bytes/sec)，PHP会根据CURLOPT_LOW_SPEED_TIME来判断是否因太慢而取消传输。
    curl_setopt($ch, CURLOPT_LOW_SPEED_LIMIT, 2000);

    //  当传输速度小于CURLOPT_LOW_SPEED_LIMIT时(bytes/sec)，PHP会根据CURLOPT_LOW_SPEED_TIME来判断是否因太慢而取消传输。
    curl_setopt($ch, CURLOPT_LOW_SPEED_TIME, 3000);

    // 不是CURLCLOSEPOLICY_LEAST_RECENTLY_USED就是CURLCLOSEPOLICY_OLDEST，还存在另外三个CURLCLOSEPOLICY_，但是cURL暂时还不支持。
    //curl_setopt($ch, CURLOPT_CLOSEPOLICY, CURLCLOSEPOLICY_OLDEST);

    // 在发起连接前等待的时间，如果设置为0，则无限等待。以秒为单位 (CURLOPT_CONNECTTIMEOUT_MS -- 毫秒)
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8);

    // 允许的最大连接数量，超过是会通过CURLOPT_CLOSEPOLICY决定应该停止哪些连接。
    curl_setopt($ch, CURLOPT_MAXCONNECTS, 1000);


    // 设置cURL允许执行的最长秒数。
    curl_setopt($ch, CURLOPT_TIMEOUT, $timout);

    // 在完成交互以后强迫断开连接，不能重用。
    curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);


    // -- 设定为不验证证书和host。
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    // --

    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);

    // new -- end
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}

function request_curl_get_fun($url, $paramsstr = '')
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url . $paramsstr);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
    // new -- begin

    // -- 设定为不验证证书和host。
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    // --

    curl_setopt($ch, CURLOPT_LOW_SPEED_LIMIT, 4000); //
    curl_setopt($ch, CURLOPT_LOW_SPEED_TIME, 4000); //
    // curl_setopt($ch, CURLOPT_CLOSEPOLICY, CURLCLOSEPOLICY_OLDEST);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 6); // 尝试连接等待的时间，以秒为单位。如果设置为0，则无限等待。
    curl_setopt($ch, CURLOPT_MAXCONNECTS, 1000); // 允许的最大连接数量
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    // 在完成交互以后强迫断开连接，不能重用。
    curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);

    // new -- end
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}

