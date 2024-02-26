<?php
#############################################################################
# IMDBPHP                                                                  #
# JCV personal and dirty class
#############################################################################

namespace Imdb;

use Psr\Log\LoggerInterface;

class ImageProcessor {

	private LoggerInterface $logger;
	private string $width;
	private string $height;

	/**
	 * $width and $height are passed in MdbBase construct 
	 * 800 for both properties by default in Config
	 */
	public function __construct(
		LoggerInterface $logger,
		string $width,
		string $height
	) {
		$this->logger = $logger;
		$this->width = $width;
		$this->height = $height;
	}

	/**
	 * Process with image_resize() ?
	 */
	public function maybe_resize_big($src, $crop=0): bool {

		 if ( is_file( $src ) && str_contains( $src, '_big' ) ) {
			$pic_type = strtolower(strrchr($src,"."));
			$path_tmp = str_replace( '_big', '_big_tmp', $src );
			$bool_result_resize = $this->image_resize($src, $path_tmp, $this->width, $this->height, 0);
			sleep(1);
			if ( $bool_result_resize === true && is_file( $path_tmp ) === true && is_file( $src ) === true ) {
				unlink( $src );
				$this->logger->debug('[ImageProcessor] Size of picture ' .  strrchr ( $src, '/' ) . ' successfully reduced.');
				rename( $path_tmp, $src );
				return true;
			}
			$this->logger->notice('[ImageProcessor] Could not reduce the size of ' . strrchr ( $src, '/' ) );
			return false;
		}
		return false;
	}

	/**
	 * Resize the pictures, new function, dirtily added here by JCV
	 * https://www.php.net/manual/en/function.imagecopyresampled.php#104028
	 * @param int $crop whether to crop to a smaller size the picture, it actually modifies it
	 * @return bool
	 */
	private function image_resize($src, $dst, $width, $height, $crop=0) {

	  if(!list($w, $h) = getimagesize($src)) {
	    		    $this->logger->notice('[ImageProcessor] Unsupported picture type ' . strrchr ( $src, '/' ) );
	    		    return false;
		};
	  $type = strtolower(substr(strrchr($src,"."),1));
	  if($type == 'jpeg') $type = 'jpg';
	  switch($type){
	    case 'bmp': $img = imagecreatefromwbmp($src); break;
	    case 'gif': $img = imagecreatefromgif($src); break;
	    case 'jpg': $img = imagecreatefromjpeg($src); break;
	    case 'png': $img = imagecreatefrompng($src); break;
	    // "Unsupported picture type!"
	    default : return false;
	  }

	  // resize
	  $x = 0;
	  if($crop === 1 ){
	    if($w < $width || $h < $height) {
	    		    $this->logger->notice('[ImageProcessor] Picture ' . strrchr ( $src, '/' ) . ' is too small to be resized');
	    		    return false;
		}
	    $ratio = max($width/$w, $height/$h);
	    $h = $height / $ratio;
	    $x = ($w - $width / $ratio) / 2;
	    $w = $width / $ratio;

	  } elseif( $crop === 0 ) {

	    if($w < $width && $h < $height) {
	    	$this->logger->notice('[ImageProcessor] Picture ' . strrchr ( $src, '/' ) . ' is too small to be resized');
	    	return false;
	    };
	    $ratio = min($width/$w, $height/$h);
	    $width = $w * $ratio;
	    $height = $h * $ratio;
	  }

	  $new = imagecreatetruecolor( (int) $width, (int) $height);

	  imagecopyresampled($new, $img, 0, 0, (int) $x, 0, (int) $width, (int) $height, (int) $w, (int) $h);

	  switch($type){
	    case 'bmp': imagewbmp($new, $dst); break;
	    case 'gif': imagegif($new, $dst); break;
	    case 'jpg': imagejpeg($new, $dst); break;
	    case 'png': imagepng($new, $dst); break;
	  }
  	  if( is_file( $dst ) ) {
		  return true;
	  }
	  return false;
	}
}
