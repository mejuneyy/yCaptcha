<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 

/**
 * 验证码
 */

class Ycaptcha {

	public $acceptedChars = 'ABCDEFGHIJKLMNPQRSTUVWXYZ123456789';

	// Number of characters in image.
	public $stringlength = 5;

	// Where to go when the correct / incorrect code is entered.
	public $success = "success.html";
	public $failure = "failure.html";

	// A value between 0 and 100 describing how much color overlap
	// there is between text and other objects.  Lower is more
	// secure against bots, but also harder to read.
	public $contrast = 60;

	// Various obfuscation techniques.
	public $num_polygons = 3; // Number of triangles to draw.  0 = none
	public $num_ellipses = 6;  // Number of ellipses to draw.  0 = none
	public $num_lines = 0;  // Number of lines to draw.  0 = none
	public $num_dots = 0;  // Number of dots to draw.  0 = none

	public $min_thickness = 2;  // Minimum thickness in pixels of lines
	public $max_thickness = 8;  // Maximum thickness in pixles of lines
	public $min_radius = 5;  // Minimum radius in pixels of ellipses
	public $max_radius = 15;  // Maximum radius in pixels of ellipses

	// How opaque should the obscuring objects be. 0 is opaque, 127
	// is transparent.
	public $object_alpha = 75; 

	public $captcha_key = '';

	protected $_image;

	public function build()
	{
		// Keep #'s reasonable.
		$this->min_thickness = max(1,$this->min_thickness);
		$this->max_thickness = min(20,$this->max_thickness);
		// Make radii into height/width
		$this->min_radius *= 2;
		$this->max_radius *= 2;
		// Renormalize contrast
		$contrast = 255 * ($this->contrast / 100.0);
		$o_contrast = 1.3 * $this->contrast;

		$width = 15 * imagefontwidth (5);
		$height = 2.5 * imagefontheight (5);
		$image = imagecreatetruecolor ($width, $height);
		//$back = imagecolorallocate($image, 255, 255, 255);
		imagealphablending($image, true);
		imagecolorallocatealpha($image,0,0,0,0);
		//imagecolorallocatealpha($image, 255, 255, 0, 75);

		// Build the  validation string
		$max = strlen($this->acceptedChars)-1;
		for($i=0; $i < $this->stringlength; $i++) {
			$cnum[$i] = $this->acceptedChars{mt_rand(0, $max)};
			$this->captcha_key .= $cnum[$i];
		}

		// Add string to image
		$rotated = imagecreatetruecolor (70, 70);
		$x = 0;
		for ($i = 0; $i < $this->stringlength; $i++) {
			$buffer = imagecreatetruecolor (20, 20);
			$buffer2 = imagecreatetruecolor (20, 20);
			
			// Get a random color
			$red = mt_rand(0,255);
			$green = mt_rand(0,255);
			$blue = 255 - sqrt($red * $red + $green * $green);
			$color = imagecolorallocate ($buffer, $red, $green, $blue);

			// Create character
			imagestring($buffer, 5, 0, 0, $cnum[$i], $color);

			// Resize character
			imagecopyresized ($buffer2, $buffer, 0, 0, 0, 0, 25 + mt_rand(0,12), 25 + mt_rand(0,12), 20, 20);

			// Rotate characters a little
			//$rotated = imagerotate($buffer, mt_rand(-25, 25)); 
			//imagecolortransparent ($rotated, imagecolorallocatealpha($rotated,0,0,0,0));

			// Move characters around a little
			$y = mt_rand(1, 3);
			$x += mt_rand(2, 6); 
			imagecopymerge ($image, $buffer2, $x, $y, 0, 0, 40, 40, 100);
			$x += 22;

			imagedestroy ($buffer); 
			imagedestroy ($buffer2); 
		}

		// Draw polygons
		if ($this->num_polygons > 0) for ($i = 0; $i < $this->num_polygons; $i++) {
			$vertices = array (
				mt_rand(-0.25*$width,$width*1.25),mt_rand(-0.25*$width,$width*1.25),
				mt_rand(-0.25*$width,$width*1.25),mt_rand(-0.25*$width,$width*1.25),
				mt_rand(-0.25*$width,$width*1.25),mt_rand(-0.25*$width,$width*1.25)
			);
			$color = imagecolorallocatealpha ($image, mt_rand(0,$o_contrast), mt_rand(0,$o_contrast), mt_rand(0,$o_contrast), $this->object_alpha);
			imagefilledpolygon($image, $vertices, 3, $color);  
		}

		// Draw random circles
		if ($this->num_ellipses > 0) for ($i = 0; $i < $this->num_ellipses; $i++) {
			$x1 = mt_rand(0,$width);
			$y1 = mt_rand(0,$height);
			$color = imagecolorallocatealpha ($image, mt_rand(0,$o_contrast), mt_rand(0,$o_contrast), mt_rand(0,$o_contrast), $this->object_alpha);
		//	$color = imagecolorallocate($image, mt_rand(0,$o_contrast), mt_rand(0,$o_contrast), mt_rand(0,$o_contrast));
			imagefilledellipse($image, $x1, $y1, mt_rand($this->min_radius,$this->max_radius), mt_rand($this->min_radius,$this->max_radius), $color);  
		}

		// Draw random lines
		if ($this->num_lines > 0) for ($i = 0; $i < $this->num_lines; $i++) {
			$x1 = mt_rand(-$width*0.25,$width*1.25);
			$y1 = mt_rand(-$height*0.25,$height*1.25);
			$x2 = mt_rand(-$width*0.25,$width*1.25);
			$y2 = mt_rand(-$height*0.25,$height*1.25);
			$color = imagecolorallocatealpha ($image, mt_rand(0,$o_contrast), mt_rand(0,$o_contrast), mt_rand(0,$o_contrast), $object_alpha);
			imagesetthickness ($image, mt_rand($min_thickness,$max_thickness));
			imageline($image, $x1, $y1, $x2, $y2 , $color);  
		}

		// Draw random dots
		if ($this->num_dots > 0) for ($i = 0; $i < $this->num_dots; $i++) {
			$x1 = mt_rand(0,$width);
			$y1 = mt_rand(0,$height);
			$color = imagecolorallocatealpha ($image, mt_rand(0,$o_contrast), mt_rand(0,$o_contrast), mt_rand(0,$o_contrast),$object_alpha);
			imagesetpixel($image, $x1, $y1, $color);
		}
		$this->_image = $image;
	}

	public function showCaptcha()
	{
		header('Content-type: image/png');
		imagejpeg($this->_image);
		imagedestroy($this->_image);
		exit;
	}
}