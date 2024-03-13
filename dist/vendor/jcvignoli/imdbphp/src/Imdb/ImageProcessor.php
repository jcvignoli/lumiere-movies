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
	/** @var int $min_file_size Minimum file size before resizing in bytes */
	private $min_file_size;

	/**
	 * $width and $height are passed in MdbBase construct 
	 * 800 for both properties by default in Config
	 */
	public function __construct(
		LoggerInterface $logger,
	) {
		$this->logger = $logger;
		// Minimum file size before resizing in bytes -> not resizing if it's not worth it.
		$this->min_file_size = 80000;
	}

	/**
	 * Process with image_resize()
	 * @param string $path_img The full path to picture, including picture's name
	 * @param int $image_max_width The maximum width for the resized picture
	 * @param int $image_max_height The maximum height for the resized picture
	 * @param bool $crop Weither to crop or not the picture
	 */
	public function maybe_resize_image( $path_img, $image_max_width, $image_max_height, $crop = false ): bool {

		$file_size = filesize( $path_img );

		// Only resize if the file exists and the file is bigger than $this->min_file_size.
		if ( $file_size > $this->min_file_size && is_file( $path_img ) === true ) {

			$pic_type = strtolower( strrchr( $path_img, "." ) );
			$path_img_tmp = str_replace( '.', '_tmp.', $path_img );
			$bool_result_resize = $this->image_resize( $path_img, $path_img_tmp, $image_max_width, $image_max_height, $crop = false );
			
			if ( $bool_result_resize === true && is_file( $path_img_tmp ) === true && is_file( $path_img ) === true ) {
				if ( unlink( $path_img ) ) {
					rename( $path_img_tmp, $path_img );
					$this->logger->debug( '[ImageProcessor] Size of picture ' .  strrchr ( $path_img, '/' ) . ' successfully reduced.' );
					return true;
				}
			}
			$this->logger->notice( '[ImageProcessor] Could not reduce the size of ' . strrchr ( $path_img, '/' ) );
			return false;
		}
		return false;
	}

	/**
	 * Resize the pictures, dirty method
	 * https://www.php.net/manual/en/function.imagecopyresampled.php#104028
	 * @param bool $crop whether to crop to a smaller size the picture, it actually modifies it
	 * @return bool
	 */
	private function image_resize( $current_img, $tmp_img, $width, $height, $crop = false ): bool {

		// Can't get the picture's size, unsupported, exit
		if( !list( $w, $h ) = getimagesize( $current_img ) ) {
			$this->logger->error('[ImageProcessor] Unsupported picture type ' . strrchr ( $current_img, '/' ) );
			return false;
		};
		
		$type = strtolower( substr( strrchr( $current_img, "." ), 1 ) );
		
		if($type === 'jpeg') {
			$type = 'jpg';
		}
		
		switch($type){
			case 'bmp': $img = imagecreatefromwbmp( $current_img ); break;
			case 'gif': $img = imagecreatefromgif( $current_img ); break;
			case 'jpg': $img = imagecreatefromjpeg( $current_img ); break;
			case 'png': $img = imagecreatefrompng( $current_img ); break;
			// Unsupported picture type
			default : 
				$this->logger->error('[ImageProcessor] Unsupported picture type ' . strrchr ( $current_img, '/' ) );
				return false;
		}

		// Proportionally resize
		$x = 0;
		if($crop === true ){
			$ratio = max($width/$w, $height/$h);
			$h = $height / $ratio;
			$x = ($w - $width / $ratio) / 2;
			$w = $width / $ratio;

		} elseif( $crop === false ) {
			$ratio = min($width/$w, $height/$h);
			$width = $w * $ratio;
			$height = $h * $ratio;
	  	}

		$new = imagecreatetruecolor( (int) $width, (int) $height);

		imagecopyresampled($new, $img, 0, 0, (int) $x, 0, (int) $width, (int) $height, (int) $w, (int) $h);

		switch($type){
			case 'bmp': imagewbmp( $new, $tmp_img ); break;
			case 'gif': imagegif( $new, $tmp_img ); break;
			case 'jpg': imagejpeg( $new, $tmp_img ); break;
			case 'png': imagepng( $new, $tmp_img ); break;
		}
		
		if( is_file( $tmp_img ) ) {
			return true;
		}
		
		return false;
	}
}
