<?php
/**
 * ownCloud - galleryplus
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Olivier Paroz <owncloud@interfasys.ch>
 *
 * @copyright Olivier Paroz 2015
 */

namespace OCA\GalleryPlus\Service;

/**
 * Base64 encoding utility method
 *
 * @package OCA\GalleryPlus\Service
 */
trait Base64Encode {

	/**
	 * Returns base64 encoded data of a preview
	 *
	 * Using base64_encode for files which are downloaded
	 * (cached Thumbnails, SVG, GIFs) and using __toStrings
	 * for the previews which are instances of \OC_Image
	 *
	 * @param \OC_Image|string $previewData
	 *
	 * @return string
	 */
	protected function encode($previewData) {
		if ($previewData instanceof \OC_Image) {
			$previewData = (string)$previewData;
		} else {
			$previewData = base64_encode($previewData);
		}

		return $previewData;
	}
}
