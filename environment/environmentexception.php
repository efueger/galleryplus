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

namespace OCA\GalleryPlus\Environment;

use Exception;

/**
 * Thrown when the service cannot reply to a request
 */
class EnvironmentException extends Exception {

	/**
	 * Constructor
	 *
	 * @param string $msg the message contained in the exception
	 */
	public function __construct($msg) {
		parent::__construct($msg);
	}
}
