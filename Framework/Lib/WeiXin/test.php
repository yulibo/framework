<?php 
function curl_https($url,$timeout=30){ 
$ch = curl_init(); 
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查 
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // 从证书中检查SSL加密算法是否存在 
curl_setopt($ch, CURLOPT_URL, $url); 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
curl_setopt($ch, CURLOPT_TIMEOUT, $timeout); 
curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,TRUE); ;
curl_setopt($ch,CURLOPT_CAINFO,dirname(__FILE__).'/cacert.pem');

$response = curl_exec($ch); 

if($error=curl_error($ch)){ 
die($error); 
} 

curl_close($ch); 

return $response; 

} 

// 调用 
$url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wx5d6213012f4a805a&secret=abf37700f3ad762fdf117d0601e2441e';
$response = curl_https($url); 
echo $response; 
?>
