<?php
// face-upload.php
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Sadece POST isteği kabul edilir.'
    ]);
    exit;
}

if (!isset($_FILES['face_file']) || $_FILES['face_file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode([
        'success' => false,
        'message' => 'Dosya yükleme hatası.'
    ]);
    exit;
}

// Geçici dosya
$tmpName = $_FILES['face_file']['tmp_name'];
$fileSize = $_FILES['face_file']['size'];
$maxFileSize = 3 * 1024 * 1024; // 3 MB
$minResolution = 32;  // Minimum 32x32 piksel
$maxResolution = 2000; // Maksimum 2000x2000 piksel

// Resim boyutlarını al
$imageInfo = getimagesize($tmpName);
if ($imageInfo === false) {
    echo json_encode([
        'success' => false,
        'message' => 'Geçersiz resim dosyası.'
    ]);
    exit;
}

list($width, $height) = $imageInfo;

// Çözünürlük kontrolü ve yeniden boyutlandırma
if ($width < $minResolution || $height < $minResolution || $width > $maxResolution || $height > $maxResolution) {
    $resizedTmpName = resizeImageToBounds($tmpName, $minResolution, $maxResolution);
    if ($resizedTmpName === false) {
        echo json_encode([
            'success' => false,
            'message' => 'Resim yeniden boyutlandırılamadı.'
        ]);
        exit;
    }
    $tmpName = $resizedTmpName;
}

// Resim boyutu 3 MB'den büyükse yeniden boyutlandır
if ($fileSize > $maxFileSize) {
    $resizedTmpName = resizeImage($tmpName, $maxFileSize);
    if ($resizedTmpName === false) {
        echo json_encode([
            'success' => false,
            'message' => 'Resim boyutu küçültülemedi.'
        ]);
        exit;
    }
    $tmpName = $resizedTmpName;
}

// API anahtarı
$apiKey = 'BuUYJzjYIEthQZ0w0HqPxMvkVUX2b13IVQCj5FE3Jr5cTyfn8mLqksPTauFZC8ye';

// API URL'si
$apiUrl = 'https://www.ailabapi.com/api/cutout/portrait/avatar-extraction';

// Dosyayı CURL ile API'ye gönder
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'ailabapi-api-key: ' . $apiKey
]);

// Dosyayı CURL'e ekle
$cfile = new CURLFile($tmpName, mime_content_type($tmpName), $_FILES['face_file']['name']);
$data = ['image' => $cfile];
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

// API yanıtını al
$response = curl_exec($ch);
curl_close($ch);

if ($response === false) {
    echo json_encode([
        'success' => false,
        'message' => 'API isteği başarısız oldu.'
    ]);
    exit;
}

// Yanıtı çözümle
$responseData = json_decode($response, true);

if (isset($responseData['error_code']) && $responseData['error_code'] === 0) {
    // Başarılı yanıt
    $imageUrl = $responseData['data']['elements'][0]['image_url'];
    echo json_encode([
        'success' => true,
        'faceUrl' => $imageUrl
    ]);
} else {
    // Hata durumu
    $errorMessage = isset($responseData['error_msg']) ? $responseData['error_msg'] : 'Bilinmeyen hata.';
    echo json_encode([
        'success' => false,
        'message' => 'API hatası: ' . $errorMessage
    ]);
}

/**
 * Resmi belirtilen çözünürlük sınırlarına göre yeniden boyutlandırır.
 */
function resizeImageToBounds($filePath, $minSize, $maxSize) {
    $imageInfo = getimagesize($filePath);
    if ($imageInfo === false) {
        return false;
    }

    list($originalWidth, $originalHeight) = $imageInfo;
    $mime = $imageInfo['mime'];

    switch ($mime) {
        case 'image/jpeg':
            $image = imagecreatefromjpeg($filePath);
            break;
        case 'image/png':
            $image = imagecreatefrompng($filePath);
            break;
        case 'image/gif':
            $image = imagecreatefromgif($filePath);
            break;
        default:
            return false;
    }

    // Yeni boyut hesaplama
    $newWidth = max(min($originalWidth, $maxSize), $minSize);
    $newHeight = max(min($originalHeight, $maxSize), $minSize);

    // Yeni resmi oluştur
    $resizedImage = imagecreatetruecolor($newWidth, $newHeight);
    imagecopyresampled($resizedImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);

    $resizedTmpName = tempnam(sys_get_temp_dir(), 'resized_');
    imagejpeg($resizedImage, $resizedTmpName, 90);

    imagedestroy($resizedImage);
    imagedestroy($image);

    return $resizedTmpName;
}

/**
 * Resmi belirtilen maksimum dosya boyutunun altına düşürecek şekilde yeniden boyutlandırır.
 */
function resizeImage($filePath, $maxFileSize) {
    $imageInfo = getimagesize($filePath);
    if ($imageInfo === false) {
        return false;
    }

    list($originalWidth, $originalHeight) = $imageInfo;
    $mime = $imageInfo['mime'];

    switch ($mime) {
        case 'image/jpeg':
            $image = imagecreatefromjpeg($filePath);
            break;
        case 'image/png':
            $image = imagecreatefrompng($filePath);
            break;
        case 'image/gif':
            $image = imagecreatefromgif($filePath);
            break;
        default:
            return false;
    }

    $scaleFactor = 0.9;
    do {
        $newWidth = $originalWidth * $scaleFactor;
        $newHeight = $originalHeight * $scaleFactor;
        $resizedImage = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($resizedImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);
        $resizedTmpName = tempnam(sys_get_temp_dir(), 'resized_');
        imagejpeg($resizedImage, $resizedTmpName, 90);
        $newFileSize = filesize($resizedTmpName);
        $scaleFactor -= 0.1;
    } while ($newFileSize > $maxFileSize && $scaleFactor > 0);

    return $resizedTmpName;
}
?>
