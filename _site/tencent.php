<html>
<?php 
function wx_get_token() {
    static $cache = array();
    if ($cache['token']!='' && $cache['time'])
    $token = S('access_token');
    if (!$token) {
        $res = file_get_contents('https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wxd0b898302fdae1c2&secret=b6b6bf02b631128a0f41fc3606f8938b');
        $res = json_decode($res, true);
        $token = $res['access_token'];
        // 注意：这里需要将获取到的token缓存起来（或写到数据库中）
        // 不能频繁的访问https://api.weixin.qq.com/cgi-bin/token，每日有次数限制
        // 通过此接口返回的token的有效期目前为2小时。令牌失效后，JS-SDK也就不能用了。
        // 因此，这里将token值缓存1小时，比2小时小。缓存失效后，再从接口获取新的token，这样
        // 就可以避免token失效。
        // S()是ThinkPhp的缓存函数，如果使用的是不ThinkPhp框架，可以使用你的缓存函数，或使用数据库来保存。
        S('access_token', $token, 3600);
    }
    return $token;
}
?>
</html>