<?

$w = get_request('w', 300);
$h = get_request('h', 300);
$quality = get_request('q', 75);
$url = get_request('url', '');

$img = $url;

define('MAX_WIDTH', $w);
define('MAX_HEIGHT', $h);

$image_path = $img;

$img = null;

$extensao = strtolower(end(explode('.',$image_path)));

if ($extensao == 'jpg' || $extensao == 'jpeg') {
	$img = imagecreatefromjpeg($image_path);
} else if ($extensao == 'png') {
	$img = imagecreatefrompng($image_path);
} elseif ($extensao == 'gif') {
	$img = imagecreatefromgif($image_path);
}

if ($img) {
	$width = imagesx($img);
	$height = imagesy($img);
	$scale = min(MAX_WIDTH/$width, MAX_HEIGHT/$height);

	if ($scale < 1) {
		$new_width = floor($scale * $width);
		$new_height = floor($scale * $height);
		$tmp_img = imagecreatetruecolor($new_width, $new_height);
		imageantialias($img, true);
		$white = ImageColorAllocate($img, 255, 255, 255);
		imagecopyresampled($tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
		imageantialias($img, false);
		imagedestroy($img);
		$img = $tmp_img;
	}
}

if (!$img) {
	$img = imagecreatetruecolor(MAX_WIDTH, MAX_HEIGHT);
	imagecolorallocate($img, 204, 204, 204);
	$c = imagecolorallocate($img, 153, 153, 153);
	$c1 = imagecolorallocate($img, 0, 0, 0);
	imageline($img, 0, 0, MAX_WIDTH, MAX_HEIGHT, $c);
	imageline($img, MAX_WIDTH, 0, 0, MAX_HEIGHT, $c);
	imagestring($img, 2, 12, 55, 'erro ao carregar imagem', $c1);
}

header('Content-type: image/jpeg');
imagejpeg($img,null,$quality);


function get_request( $property, $default = 0 ) {
	if(isset($_GET[$property])):
		return $_GET[$property];
	else:
		return $default;
	endif;
}

?>
