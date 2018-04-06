<?php
/*
Plugin Name: DiviSpace
Description: This is where all the magic happens.
Author: Bardi Harborow
Version: 0.1.0
Author URI: https://bardiharborow.com/
*/

// Yoast SEO
function bypass_yoast_seo_licence() {
	return array('key' => 'yoast-dummy-license', 'status' => 'valid', 'expiry_date' => '+1 year' );
}
add_filter('pre_option_yoast-seo-premium_license', 'bypass_yoast_seo_licence');
add_filter('pre_site_option_yoast-seo-premium_license', 'bypass_yoast_seo_licence');

//namespace com\bardiharborow\divispace\internal;

require_once ABSPATH . '/wp-includes/class-wp-image-editor.php';
require_once ABSPATH . '/wp-includes/class-wp-image-editor-gd.php';

use google\appengine\api\cloud_storage\CloudStorageTools;
use google\appengine\api\cloud_storage\CloudStorageException;

class Internal {

	/**
	 * Image sizes cache
	 *
	 * @see self::image_sizes()
	 * @var array|null
	 */
	protected static $image_sizes = null;

	/**
	 * Should we skip filtering the image data?
	 *
	 * This ensures we don't filter recursively when falling back
	 * @var boolean
	 */
	protected static $skip_image_filters = false;

	public static function bootstrap() {
		self::setup_mail();
		self::setup_uploads();
	}

	protected static function setup_mail() {
		add_filter('wp_mail_from', function () {
			return $_SERVER['EMAIL_FROM'];
		});

		add_filter('wp_mail_from_name', function () {
			return $_SERVER['EMAIL_NAME'];
		});
	}

	public static function wrap_upload_url( $url ) {
		return CloudStorageTools::createUploadUrl( $url, [
			'gs_bucket_name' => $_SERVER['GCS_BUCKET'],
			'max_bytes_per_blob' => wp_max_upload_size(),
			'url_expiry_time_seconds' => HOUR_IN_SECONDS,
		]);
	}

	protected static function setup_uploads() {
		add_filter( 'upload_dir', __CLASS__ . '::filter_upload_dir' );
		add_filter( 'wp_delete_file', __CLASS__ . '::filter_delete_file' );
		add_filter( 'media_upload_form_url', __CLASS__ . '::wrap_upload_url');
		// We have to return null here rather than false, since pre_option_...
		// only applies when `false !== $pre`
		//
		// TODO: Remove this once GCS streams support listdir
		add_filter( 'pre_option_uploads_use_yearmonth_folders', '__return_null' );

		add_filter( 'image_downsize', __CLASS__ . '::get_intermediate_url', 10, 3 );
		add_filter( 'wp_image_editors', function ($editors) {
			$editors = [ 'GAEEditor' ] + $editors;
			return $editors;
		});
	}

	/**
	 * Swap the upload dir with gs:// path in the GCS bucket.
	 *
	 * @author Google (https://github.com/GoogleCloudPlatform/wordpress-plugins)
	 */
	public static function filter_upload_dir( $values ) {
		if ( self::$skip_image_filters ) {
			return $values;
		}

		$basedir = 'gs://' . $_SERVER['GCS_BUCKET'] . '/' . get_current_blog_id();
		$values = array(
			'path' => $basedir,
			'subdir' => '',
			'error' => false,
		);
		$public_url = CloudStorageTools::getPublicUrl($basedir, true);
		$values['url'] = rtrim($public_url, '/');
		$values['basedir'] = $values['path'];
		$values['baseurl'] = $values['url'];
		return $values;
	}

	/**
	 * Unlink files starts with 'gs://'
	 *
	 * This is needed because WordPress thinks a path starts with 'gs://' is
	 * not an absolute path and manipulate it in a wrong way before unlinking
	 * intermediate files.
	 *
	 * @author Google (https://github.com/GoogleCloudPlatform/wordpress-plugins)
	 *
	 * TODO: Use `path_is_absolute` filter when a bug below is resolved:
	 *       https://core.trac.wordpress.org/ticket/38907#ticket
	 */
	public static function filter_delete_file($file) {
			$prefix = 'gs://';
			if (substr($file, 0, strlen($prefix)) === $prefix) {
					@ unlink($file);
			}
			return $file;
	}

	/**
	 * Provide an array of available image sizes and corresponding dimensions.
	 * Similar to get_intermediate_image_sizes() except that it includes image sizes' dimensions, not just their names.
	 *
   * @author Jetpack (https://jetpack.com/)
	 *
	 * @global $wp_additional_image_sizes
	 * @uses get_option
	 * @return array
	 */
	protected static function image_sizes() {
		if ( null == self::$image_sizes ) {
			global $_wp_additional_image_sizes;
			// Populate an array matching the data structure of $_wp_additional_image_sizes so we have a consistent structure for image sizes
			$images = array(
				'thumb'  => array(
					'width'  => intval( get_option( 'thumbnail_size_w' ) ),
					'height' => intval( get_option( 'thumbnail_size_h' ) ),
					'crop'   => (bool) get_option( 'thumbnail_crop' )
				),
				'medium' => array(
					'width'  => intval( get_option( 'medium_size_w' ) ),
					'height' => intval( get_option( 'medium_size_h' ) ),
					'crop'   => false
				),
				'large'  => array(
					'width'  => intval( get_option( 'large_size_w' ) ),
					'height' => intval( get_option( 'large_size_h' ) ),
					'crop'   => false
				),
				'full'   => array(
					'width'  => null,
					'height' => null,
					'crop'   => false
				)
			);
			// Compatibility mapping as found in wp-includes/media.php
			$images['thumbnail'] = $images['thumb'];
			// Update class variable, merging in $_wp_additional_image_sizes if any are set
			if ( is_array( $_wp_additional_image_sizes ) && ! empty( $_wp_additional_image_sizes ) )
				self::$image_sizes = array_merge( $images, $_wp_additional_image_sizes );
			else
				self::$image_sizes = $images;
		}
		return is_array( self::$image_sizes ) ? self::$image_sizes : array();
	}

	/**
 	 * Get a resized image URL for an attachment image
 	 *
 	 * Uses Google Cloud Storage to resize and serve an attachment image.
 	 *
 	 * @wp-filter image_downsize
 	 *
 	 * @param null|array $data Existing data (we always override)
 	 * @param int $id Attachment ID
 	 * @param string $size Size ID
 	 * @return array Indexed array of URL, width, height, is intermediate
 	 */
 	public static function get_intermediate_url( $data, $id, $size ) {
 		$file = get_attached_file( $id );
 		if ( 0 !== strpos( $file, 'gs://' ) || self::$skip_image_filters ) {
 			return $data;
 		}

 		$sizes = self::image_sizes();
 		if ( is_array( $size ) ) {
 			$size = ['width' => $size[0], 'height' => $size[1], 'crop' => false];
 		}
 		else {
 			$size = $sizes[ $size ];
 		}
 		$options = [];

 		// If height or width is null (i.e. full size), $real_size will be
 		// null, providing us a way to tell if the size is intermediate
 		$real_size = max( $size['height'], $size['width'] );
 		if ( $real_size ) {
 			$options = [
 				'size' => $real_size,
 				'crop' => (bool) $size['crop']
 			];
 		}
 		else {
 			$options = [
 				'size' => 0,
 				'crop' => false
 			];
 		}

 		$baseurl     = get_post_meta( $id, '_appengine_imageurl', true );
 		$cached_file = get_post_meta( $id, '_appengine_imageurl_file', true );

 		if ( empty( $baseurl ) || $cached_file !== $file ) {
 			try {
				$options = ['secure_url' => true];
				$baseurl = CloudStorageTools::getImageServingUrl($file, $options);
 				update_post_meta( $id, '_appengine_imageurl', $baseurl );
 				update_post_meta( $id, '_appengine_imageurl_file', $file );
 			}
 			catch ( CloudStorageException $e ) {
         syslog(LOG_ERR,
             'There was an exception creating the Image Serving URL, details ' .
             $e->getMessage());
 				self::$skip_image_filters = true;
 				$data = image_downsize( $id, $size );
 				self::$skip_image_filters = false;

 				return $data;
 			}
 		}

 		$url = $baseurl;

		if ( ! is_null( $options['size'] ) ) {
			$url .= ( '=s' . $options['size'] );
			if ( $options['crop'] ) {
				$url .= '-c';
			}
		}
		else {
			$url .= '=s0';
		}

 		$data = [
 			$url, // URL
 			$size['width'],
 			$size['height'],
 			(bool) $real_size // image is intermediate
 		];
 		return $data;
 	}
}

class GAEEditor extends WP_Image_Editor_GD {
 /**
	* Resize to multiple sizes
	*
	* We override this to give nothing, as we handle image resizes via the
	* GCS APIs instead.
	*
	* @param array $sizes
	* @return array
	*/
 public function multi_resize( $sizes ) {
	 return [];
 }

 /**
	* Either calls editor's save function or handles file as a stream.
	*
	* @since 3.5.0
	* @access protected
	*
	* @param string|stream $filename
	* @param callable $function
	* @param array $arguments
	* @return boolean
	*/
 protected function make_image( $filename, $function, $arguments ) {
	 // Setup the stream wrapper context
	 $context = stream_context_get_options( stream_context_get_default() );

	 switch ( $function ) {
		 case 'imagepng':
			 $context['gs']['Content-Type'] = 'image/png';
			 break;

		 case 'imagejpeg':
			 $context['gs']['Content-Type'] = 'image/jpeg';
			 break;

		 case 'imagegif':
			 $context['gs']['Content-Type'] = 'image/gif';
			 break;
	 }

	 stream_context_set_default( $context );

	 $result = parent::make_image( $filename, $function, $arguments );

	 // Restore the default wrapper context
	 stream_context_set_default( $default );

	 return $result;
 }
}

Internal::bootstrap();
