<?php
/**
 * Compress raster images in PHP's temporary upload location before WordPress
 * moves them into the Media Library. JPEG and PNG uploads are converted to
 * WebP when the converted file is smaller.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Prefer the GD editor when it has complete WebP support. Some ImageMagick
 * installations can decode WebP but fail while WordPress creates sub-sizes,
 * which surfaces as the generic "cannot create responsive image sizes" error.
 * Other editors remain in the list as fallbacks (for example for PDFs).
 *
 * @param string[] $editors Registered WordPress image editor classes.
 * @return string[]
 */
function wonom_prefer_webp_capable_gd_editor($editors)
{
    if (!function_exists('imagecreatefromwebp') || !function_exists('imagewebp')) {
        return $editors;
    }

    $gd_editor = 'WP_Image_Editor_GD';
    $editors = array_values(array_diff($editors, array($gd_editor)));
    array_unshift($editors, $gd_editor);

    return $editors;
}
add_filter('wp_image_editors', 'wonom_prefer_webp_capable_gd_editor', 20);

/**
 * WordPress 6.8+ rejects a REST Media Library upload before upload prefilters
 * run when its capability probe reports WebP as unsupported. Allow WebP past
 * that early guard; the validated GD pipeline below handles the actual file.
 * Keep the core guard enabled for every other unsupported image format.
 *
 * @param bool        $prevent   Whether WordPress should block the upload.
 * @param string|null $mime_type Browser-reported upload MIME type.
 * @return bool
 */
function wonom_allow_webp_rest_upload($prevent, $mime_type)
{
    return 'image/webp' === strtolower((string) $mime_type) ? false : $prevent;
}
add_filter('wp_prevent_unsupported_mime_type_uploads', 'wonom_allow_webp_rest_upload', 10, 2);

/**
 * Convert an uploaded JPEG, PNG or WebP to an optimized WebP file.
 *
 * @param array $file WordPress upload file array.
 * @return array
 */
function wonom_compress_uploaded_image($file)
{
    if (
        !empty($file['error'])
        || empty($file['tmp_name'])
        || !is_readable($file['tmp_name'])
        || !function_exists('wp_get_image_mime')
    ) {
        return $file;
    }

    $mime = wp_get_image_mime($file['tmp_name']);
    $supported_mimes = array('image/jpeg', 'image/png', 'image/webp');

    if (!in_array($mime, $supported_mimes, true)) {
        // GIF is deliberately skipped to preserve animation. SVG is never
        // decoded here because it requires a different security model.
        return $file;
    }

    $image_size = @getimagesize($file['tmp_name']);
    if (!$image_size || empty($image_size[0]) || empty($image_size[1])) {
        return $file;
    }

    // Avoid exhausting PHP memory on exceptionally large/deceptive images.
    $pixel_count = (int) $image_size[0] * (int) $image_size[1];
    $max_pixels = (int) apply_filters('wonom_upload_image_max_pixels', 40000000);
    if ($pixel_count > $max_pixels) {
        return $file;
    }

    $loaders = array(
        'image/jpeg' => 'imagecreatefromjpeg',
        'image/png' => 'imagecreatefrompng',
        'image/webp' => 'imagecreatefromwebp',
    );
    $loader = $loaders[$mime];

    if (!function_exists($loader)) {
        return $file;
    }

    wp_raise_memory_limit('image');
    $image = @$loader($file['tmp_name']);
    if (!$image) {
        return $file;
    }

    // GD strips EXIF data while saving. Physically rotate common camera-phone
    // orientations first so portrait photos do not appear sideways afterward.
    if ('image/jpeg' === $mime && function_exists('exif_read_data')) {
        $exif = @exif_read_data($file['tmp_name']);
        $orientation = isset($exif['Orientation']) ? (int) $exif['Orientation'] : 1;
        $rotation = 0;

        if (3 === $orientation) {
            $rotation = 180;
        } elseif (6 === $orientation) {
            $rotation = -90;
        } elseif (8 === $orientation) {
            $rotation = 90;
        }

        if ($rotation) {
            $rotated_image = imagerotate($image, $rotation, 0);
            if ($rotated_image) {
                imagedestroy($image);
                $image = $rotated_image;
            }
        }
    }

    if ('image/png' === $mime || 'image/webp' === $mime) {
        imagealphablending($image, false);
        imagesavealpha($image, true);
    }

    $temporary_file = function_exists('wp_tempnam')
        ? wp_tempnam(isset($file['name']) ? $file['name'] : 'wonom-upload')
        : tempnam(dirname($file['tmp_name']), 'wonom-');
    if (!$temporary_file) {
        imagedestroy($image);
        return $file;
    }

    $quality = (int) apply_filters('wonom_upload_webp_quality', 90, $mime, $file);
    $quality = max(1, min(100, $quality));
    $written = function_exists('imagewebp')
        ? imagewebp($image, $temporary_file, $quality)
        : false;

    imagedestroy($image);

    $original_size = filesize($file['tmp_name']);
    $optimized_size = $written && is_readable($temporary_file)
        ? filesize($temporary_file)
        : 0;

    // Never replace the customer's image if compression failed or increased
    // its size. copy() is used because upload temp paths can cross volumes.
    if ($optimized_size > 0 && $optimized_size < $original_size) {
        if (copy($temporary_file, $file['tmp_name'])) {
            clearstatcache(true, $file['tmp_name']);
            $file['size'] = filesize($file['tmp_name']);
            $original_name = isset($file['name']) ? $file['name'] : 'upload';
            $name_without_extension = pathinfo($original_name, PATHINFO_FILENAME);
            $file['name'] = sanitize_file_name($name_without_extension . '.webp');
            $file['type'] = 'image/webp';
        }
    }

    @unlink($temporary_file);
    return $file;
}

add_filter('wp_handle_upload_prefilter', 'wonom_compress_uploaded_image', 20);
add_filter('wp_handle_sideload_prefilter', 'wonom_compress_uploaded_image', 20);
