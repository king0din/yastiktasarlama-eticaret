<?php
// proxy.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_GET['url'])) {
    http_response_code(400);
    exit('URL parametresi belirtilmedi.');
}

$url = $_GET['url'];

// URL doğrulaması (güvenlik amacıyla, yalnızca izin verilen domainleri kullanabilirsiniz)
if (!filter_var($url, FILTER_VALIDATE_URL)) {
    http_response_code(400);
    exit('Geçersiz URL.');
}

$allowed_domains = [
    'xn--zelyastktasarlama-yzb24i.shop',
    'www.xn--zelyastktasarlama-yzb24i.shop',
    'ai-result-rapidapi.ailabtools.com'
];


// cURL ile resmi çekiyoruz:
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$data = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
curl_close($ch);

if ($http_code != 200) {
    http_response_code($http_code);
    exit('Resim çekilemedi.');
}

// Gerekli CORS başlığı ile çıktıyı gönderiyoruz:
header("Content-Type: " . $content_type);
header("Access-Control-Allow-Origin: *");
echo $data;
?>
