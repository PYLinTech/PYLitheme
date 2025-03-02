<?php
// 定义缓存文件路径和缓存时间
$cacheFile = __DIR__ . '/wallpaper_cache.json'; // 缓存文件路径
$cacheTime = 3600; // 缓存有效期（秒），例如 3600 秒（1小时）

// 检查缓存文件是否存在且未过期
if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheTime)) {
    // 缓存有效，直接读取缓存文件
    $cachedData = json_decode(file_get_contents($cacheFile), true);
    if ($cachedData) {
        echo json_encode($cachedData);
        exit();
    }
}

// 缓存失效，从 Bing API 获取壁纸
$url = "https://cn.bing.com/HPImageArchive.aspx?format=js&idx=0&n=1&mkt=zh-CN";
$curl = curl_init($url);
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HTTPHEADER, array("Accept: application/json"));
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
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

// 缓存图片地址
$cachedData = ['imageUrl' => $imageUrl];
file_put_contents($cacheFile, json_encode($cachedData));

// 返回图片地址
echo json_encode($cachedData);
exit();
?>