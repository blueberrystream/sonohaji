<?php
require_once('const.inc');

// check session
session_start();
$sid = session_id();
if (empty($sid) || !array_key_exists($sid, $_SESSION) || $_SESSION[$sid] !== SESSION_VALUE) {
    die('invalid access');
}

session_commit();

// check http method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('invalid access');
}

// put http header: Content-Type, Content-Disposition
header('Content-Type: image/png');
header('Content-Disposition: attachment; filename="' . $sid . '.png"');

// prepare to generate image
$image = imagecreatefrompng(TEMPLATE_IMAGE_FILE);
$black = imagecolorallocate($image, 0, 0, 0);
$font = GOTHIC_FONT_FILE;
if ($_POST['font'] === 'm') {
    $font = MINCHO_FONT_FILE;
}

// draw text
drawText('nyaan', 430, 73, 358, 75, 32);

// put image
imagepng($image);
imagedestroy($image);

function drawText($key, $W, $H, $x, $y, $size = 20) {
    global $image, $font, $black;

    $text = $_POST[$key];
    if (empty($text)) {
        return null;
    }

    $xyf = calc($W, $H, $size, $text);
    return imagettftext($image, $xyf[2], 0, $x + $xyf[0], $y + $xyf[1], $black, $font, $text);
}

// return: [0]: x, [1]: y, [2]: font-size
function calc($W, $H, $size, $text) {
    global $font;

    $reculc = true;
    while ($reculc) {
        // 描画されるテキストを囲う枠を考える
        $bbox = imagettfbbox($size, 0, $font, $text);
        $w = $bbox[2] - $bbox[6];
        $h = $bbox[3] - $bbox[7];

        // 描画したい枠の中に収まるかチェック
        if ($w <= $W && $h <= $H) {
            $reculc = false;

            $x = 0;//(int) (($W - $w) / 2);
            $y = (int) (($H - $h) / 2) + $h - $bbox[1];
        } else {
            // フォントサイズを2小さくして再計算
            $size -= 2;
        }
    }

    return array($x, $y, $size);
}
