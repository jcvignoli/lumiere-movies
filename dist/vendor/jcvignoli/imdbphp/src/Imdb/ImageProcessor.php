<?php
#############################################################################
# IMDBPHP                                                                  #
# JCV personal and dirty class						   #
# Reduce the size of the big pictures versions				   #
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
	public function maybe_resize_big( $big_img, $crop=0 ): bool {

		 if ( is_file( $big_img ) && str_contains( $big_img, '_big' ) ) {
			
			$pic_type = strtolower( strrchr( $big_img, "." ) );
			$tmp_big_img = str_replace( '_big', '_big_tmp', $big_img );
			$bool_result_resize = $this->image_resize( $big_img, $tmp_big_img, $this->width, $this->height, 0 );
			
			sleep(1);
			
			if ( $bool_result_resize === true && is_file( $tmp_big_img ) === true && is_file( $big_img ) === true ) {
				if ( unlink( $big_img ) ) {
					$this->logger->debug( '[ImageProcessor] Size of picture ' .  strrchr ( $big_img, '/' ) . ' successfully reduced.' );
					rename( $tmp_big_img, $big_img );
					return true;
				}
			}
			$this->logger->notice( '[ImageProcessor] Could not reduce the size of ' . strrchr ( $big_img, '/' ) );
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
	private function image_resize( $big_img, $tmp_big_img, $width, $height, $crop=0) {

		if( !list( $w, $h ) = getimagesize( $big_img ) ) {
			$this->logger->error('[ImageProcessor] Unsupported picture type ' . strrchr ( $big_img, '/' ) );
			return false;
		};
		$type = strtolower( substr( strrchr( $big_img, "." ), 1 ) );
		
		if($type === 'jpeg') {
			$type = 'jpg';
		}
		
		switch($type){
			case 'bmp': $img = imagecreatefromwbmp( $big_img ); break;
			case 'gif': $img = imagecreatefromgif( $big_img ); break;
			case 'jpg': $img = imagecreatefromjpeg( $big_img ); break;
			case 'png': $img = imagecreatefrompng( $big_img ); break;
			// "Unsupported picture type!"
			default : return false;
		}

		// resize
		$x = 0;
		if($crop === 1 ){
			if($w < $width || $h < $height) {
				$this->logger->debug('[ImageProcessor] Picture ' . strrchr ( $big_img, '/' ) . ' is too small to be resized');
				return false;
			}
		
			$ratio = max($width/$w, $height/$h);
			$h = $height / $ratio;
			$x = ($w - $width / $ratio) / 2;
			$w = $width / $ratio;

		} elseif( $crop === 0 ) {
			if($w < $width && $h < $height) {
				$this->logger->debug('[ImageProcessor] Picture ' . strrchr ( $big_img, '/' ) . ' is too small to be resized');
				return false;
			};
			$ratio = min($width/$w, $height/$h);
			$width = $w * $ratio;
			$height = $h * $ratio;
	  	}

		$new = imagecreatetruecolor( (int) $width, (int) $height);

		imagecopyresampled($new, $img, 0, 0, (int) $x, 0, (int) $width, (int) $height, (int) $w, (int) $h);

		switch($type){
			case 'bmp': imagewbmp( $new, $tmp_big_img ); break;
			case 'gif': imagegif( $new, $tmp_big_img ); break;
			case 'jpg': imagejpeg( $new, $tmp_big_img ); break;
			case 'png': imagepng( $new, $tmp_big_img ); break;
		}
		
		if( is_file( $tmp_big_img ) ) {
			return true;
		}
		
		return false;
	}
}
