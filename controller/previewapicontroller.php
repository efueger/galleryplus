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

namespace OCA\GalleryPlus\Controller;

use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\ILogger;
use OCP\Files\File;

use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;

use OCA\GalleryPlus\Http\ImageResponse;
use OCA\GalleryPlus\Service\ThumbnailService;
use OCA\GalleryPlus\Service\PreviewService;
use OCA\GalleryPlus\Service\DownloadService;
use OCA\GalleryPlus\Utility\EventSource;

/**
 * Class PreviewApiController
 *
 * @package OCA\GalleryPlus\Controller
 */
class PreviewApiController extends ApiController {

	use Preview;
	use JsonHttpError;

	/**
	 * @var EventSource
	 */
	private $eventSource;

	/**
	 * Constructor
	 *
	 * @param string $appName
	 * @param IRequest $request
	 * @param IURLGenerator $urlGenerator
	 * @param ThumbnailService $thumbnailService
	 * @param PreviewService $previewService
	 * @param DownloadService $downloadService
	 * @param EventSource $eventSource
	 * @param ILogger $logger
	 */
	public function __construct(
		$appName,
		IRequest $request,
		IURLGenerator $urlGenerator,
		ThumbnailService $thumbnailService,
		PreviewService $previewService,
		DownloadService $downloadService,
		EventSource $eventSource,
		ILogger $logger
	) {
		parent::__construct($appName, $request);

		$this->urlGenerator = $urlGenerator;
		$this->thumbnailService = $thumbnailService;
		$this->previewService = $previewService;
		$this->downloadService = $downloadService;
		$this->eventSource = $eventSource;
		$this->logger = $logger;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @CORS
	 *
	 * Generates thumbnails
	 *
	 * @see PreviewController::getThumbnails()
	 *
	 * @param string $ids the ID of the files of which we need thumbnail previews of
	 * @param bool $square
	 * @param double $scale
	 *
	 * @return array<string,array|string|null>
	 */
	public function getThumbnails($ids, $square, $scale) {
		$idsArray = explode(';', $ids);

		foreach ($idsArray as $id) {
			// Casting to integer here instead of using array_map to extract IDs from the URL
			list($thumbnail, $status) = $this->getThumbnail((int)$id, $square, $scale);
			$thumbnail['fileid'] = $id;
			$thumbnail['status'] = $status;

			$this->eventSource->send('preview', $thumbnail);
		}
		$this->eventSource->close();

		$this->exitController();
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @CORS
	 *
	 * Sends either a large preview of the requested file or the original file itself
	 *
	 * @param int $fileId the ID of the file of which we need a large preview of
	 * @param int $width
	 * @param int $height
	 *
	 * @return ImageResponse|Http\JSONResponse
	 */
	public function getPreview($fileId, $width, $height) {
		/** @type File $file */
		list($file, $preview, $status) = $this->getData($fileId, $width, $height);

		if (!$preview) {
			return new JSONResponse(
				[
					'message' => "I'm truly sorry, but we were unable to generate a preview for this file",
					'success' => false
				], $status
			);
		}
		$preview['name'] = $file->getName();

		return new ImageResponse($preview, $status);
	}

}
