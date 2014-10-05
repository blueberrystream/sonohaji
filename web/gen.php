<?php
require_once('const.inc');

// check session
session_start();
$sid = session_id();
if (empty($sid) || !array_key_exists($sid, $_SESSION) || $_SESSION[$sid] !== SESSION_VALUE) {
    syslog(LOG_WARNING, 'invalid session from ' . $_SERVER['REMOTE_ADDR']);
    die('invalid access');
}

// destroy session
//if (isset($_COOKIE[session_name()])) {
//    setcookie(session_name(), '', 0, '/');
//}
//$_SESSION = array();
//session_destroy();
session_commit();

// check http method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    syslog(LOG_WARNING, 'invalid request method from ' . $_SERVER['REMOTE_ADDR']);
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
drawText('title', 0, 600, 68, 0, 0, 32);
drawText('sign', 1, 190, 30, 5, 223);
drawText('sign', 2, 190, 30, 205, 223);
drawText('sign', 3, 190, 30, 405, 223);
drawText('sign', 4, 190, 30, 5, 410);
drawText('sign', 5, 190, 30, 205, 410);
drawText('sign', 6, 190, 30, 405, 410);
drawText('sign', 7, 190, 30, 5, 588);
drawText('sign', 8, 190, 30, 205, 588);
drawText('sign', 9, 190, 30, 405, 588);
drawText('sign', 10, 190, 30, 5, 780);
drawText('sign', 11, 190, 30, 205, 780);
drawText('sign', 12, 190, 30, 405, 780);

// put image
imagepng($image);
imagedestroy($image);

function drawText($key, $index, $W, $H, $x, $y, $size = 20) {
    global $image, $font, $black;
    if ($key === 'title') {
        $text = $_POST[$key];
    } else {
        $text = $_POST[$key][$index];
    }

    if (empty($text)) {
        return null;
    }

    syslog(LOG_DEBUG, "drawText: $key$index");
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

            $x = (int) (($W - $w) / 2);
            $y = (int) (($H - $h) / 2) + $h - $bbox[1];
        } else {
            // フォントサイズを2小さくして再計算
            $size -= 2;
        }
    }

    syslog(LOG_DEBUG, "w:$w, h:$h, x:$x, y:$y, size:$size");
    return array($x, $y, $size);
}
