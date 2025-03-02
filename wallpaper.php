<?php
// 定义缓存文件路径和缓存时间
$cacheFile = __DIR__ . '/wallpaper_cache.jpg'; // 缓存文件路径
$cacheTime = 3600; // 缓存有效期（秒），例如 3600 秒（1小时）

// 检查缓存文件是否存在且未过期
if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheTime)) {
    // 缓存有效，直接输出缓存文件
    header('Content-Type: image/jpeg');
    readfile($cacheFile);
    exit();
}

// 缓存失效，从 Bing API 获取壁纸
$url = "https://cn.bing.com/HPImageArchive.aspx?format=js&idx=0&n=1&mkt=zh-CN";
$curl = curl_init($url);
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HTTPHEADER, array("Accept: application/json"));
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false); // 生产环境中请启用证书验证
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($curl);
if (curl_errno($curl)) {
    exit('cURL error: ' . curl_error($curl));
}
curl_close($curl);

$data = json_decode($response);
if (json_last_error() !== JSON_ERROR_NONE) {
    exit('JSON decode error');
}

if (isset($data->images[0]->urlbase)) {
    $imageUrl = 'https://cn.bing.com' . $data->images[0]->urlbase . '_1920x1080.jpg';
} else {
    exit('Image URL not found');
}

// 下载壁纸并保存到本地缓存文件
if (@file_put_contents($cacheFile, file_get_contents($imageUrl)) === false) {
    exit('Failed to save image to cache');
}

// 输出图片内容
header('Content-Type: image/jpeg');
readfile($cacheFile);
exit();
?>