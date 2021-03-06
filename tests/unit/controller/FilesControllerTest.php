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

use OCA\GalleryPlus\Service\ServiceException;
use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\ILogger;

use OCP\AppFramework\IAppContainer;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\AppFramework\Http\JSONResponse;

use OCA\GalleryPlus\AppInfo\Application;
use OCA\GalleryPlus\Http\ImageResponse;
use OCA\GalleryPlus\Service\SearchFolderService;
use OCA\GalleryPlus\Service\ConfigService;
use OCA\GalleryPlus\Service\SearchMediaService;
use OCA\GalleryPlus\Service\DownloadService;

/**
 * Class FilesControllerTest
 *
 * @package OCA\GalleryPlus\Controller
 */
class FilesControllerTest extends \Test\TestCase {

	use PathManipulation;

	/** @var IAppContainer */
	protected $container;
	/** @var string */
	protected $appName = 'galleryplus';
	/** @var IRequest */
	protected $request;
	/** @var FilesController */
	protected $controller;
	/** @var IURLGenerator */
	protected $urlGenerator;
	/** @var SearchFolderService */
	protected $searchFolderService;
	/** @var ConfigService */
	protected $configService;
	/** @var SearchMediaService */
	protected $searchMediaService;
	/** @var DownloadService */
	protected $downloadService;
	/** @var ILogger */
	protected $logger;

	/**
	 * Test set up
	 */
	public function setUp() {
		parent::setUp();

		$app = new Application;
		$this->container = $app->getContainer();
		$this->container['UserFolder'] = $this->getMockBuilder('OCP\Files\Folder')
											  ->disableOriginalConstructor()
											  ->getMock();
		$this->request = $this->getMockBuilder('\OCP\IRequest')
							  ->disableOriginalConstructor()
							  ->getMock();
		$this->urlGenerator = $this->getMockBuilder('\OCP\IURLGenerator')
								   ->disableOriginalConstructor()
								   ->getMock();
		$this->searchFolderService =
			$this->getMockBuilder('\OCA\GalleryPlus\Service\SearchFolderService')
				 ->disableOriginalConstructor()
				 ->getMock();
		$this->configService = $this->getMockBuilder('\OCA\GalleryPlus\Service\ConfigService')
									->disableOriginalConstructor()
									->getMock();
		$this->searchMediaService = $this->getMockBuilder('\OCA\GalleryPlus\Service\SearchMediaService')
										 ->disableOriginalConstructor()
										 ->getMock();
		$this->downloadService = $this->getMockBuilder('\OCA\GalleryPlus\Service\DownloadService')
									  ->disableOriginalConstructor()
									  ->getMock();
		$this->logger = $this->getMockBuilder('\OCP\ILogger')
							 ->disableOriginalConstructor()
							 ->getMock();
		$this->controller = new FilesController(
			$this->appName,
			$this->request,
			$this->urlGenerator,
			$this->searchFolderService,
			$this->configService,
			$this->searchMediaService,
			$this->downloadService,
			$this->logger
		);
	}

	public function testDownload() {
		$fileId = 1234;
		$filename = null;

		$download = $this->mockGetDownload($fileId, $filename);

		/** @type ImageResponse $response */
		$response = $this->controller->download($fileId, $filename);

		$this->assertEquals(Http::STATUS_OK, $response->getStatus());
		$this->assertEquals(
			$download['mimetype'] . '; charset=utf-8', $response->getHeaders()['Content-type']
		);
		$this->assertEquals($download['preview'], $response->render());
	}

	public function testDownloadWithWrongId() {
		$fileId = 99999;
		$filename = null;

		$this->mockGetResourceFromId($fileId, false);

		$redirect = new RedirectResponse(
			$this->urlGenerator->linkToRoute($this->appName . '.page.error_page')
		);

		$response = $this->controller->download($fileId, $filename);

		$this->assertEquals($redirect->getRedirectURL(), $response->getRedirectURL());
	}

	public function testGetFilesWithWorkingSetup() {
		$location = 'folder1';
		$folderPathFromRoot = 'user/files/' . $location;
		$features = '';
		$etag = 1111222233334444;
		$mediatypes = 'image/png';
		$folderId = 9876;
		$folderPermissions = 31;
		$folderEtag = 9999888877776666;
		$files = [
			['path' => $folderPathFromRoot . '/deep/path.png'],
			['path' => $folderPathFromRoot . '/testimage.png']
		];
		$albumInfo = [
			'path'        => $folderPathFromRoot,
			'fileid'      => $folderId,
			'permissions' => $folderPermissions,
			'etag'        => $folderEtag
		];
		$locationHasChanged = false;
		$result = [
			'files'              => $files,
			'albuminfo'          => $albumInfo,
			'locationhaschanged' => $locationHasChanged
		];
		$folder = $this->mockGetFolder($folderId, $files, $folderPermissions, $folderEtag);

		$this->mockGetCurrentFolder(
			$location, $folderPathFromRoot, [$features], $locationHasChanged, $folder
		);
		$this->mockGetAlbumInfo($folder, $folderPathFromRoot, [$features], $albumInfo);
		$this->mockGetMediaFiles($folder, [$mediatypes], [$features], $files);

		$response = $this->controller->getList($location, $features, $etag, $mediatypes);

		$this->assertEquals($result, $response);
	}

	public function testGetFilesWithBrokenSetup() {
		$location = '';
		$features = '';
		$etag = 1111222233334444;
		$mediatypes = 'image/png';
		$exceptionMessage = 'Aïe!';
		$this->searchFolderService->expects($this->once())
								  ->method('getCurrentFolder')
								  ->with(
									  $location,
									  [$features]
								  )
								  ->willThrowException(new ServiceException($exceptionMessage));

		$errorMessage = [
			'message' => $exceptionMessage,
			'success' => false
		];
		/** @type JSONResponse $response */
		$response = $this->controller->getList($location, $features, $etag, $mediatypes);

		$this->assertEquals($errorMessage, $response->getData());
	}

	public function providesFilesData() {
		$location = 'folder1';
		$folderPathFromRoot = 'user/files/' . $location;

		return [
			[
				['path' => $folderPathFromRoot . '/deep/folder/to/test/path/reduction.png'],
				$folderPathFromRoot . '/deep/reduction.png',
				$folderPathFromRoot
			],
			[
				['path' => $folderPathFromRoot . '/folder/image.png'],
				$folderPathFromRoot . '/folder/image.png',
				$folderPathFromRoot
			],
			[
				['path' => $folderPathFromRoot . '/testimage.png'],
				$folderPathFromRoot . '/testimage.png',
				$folderPathFromRoot
			]
		];
	}

	/**
	 * @dataProvider providesFilesData
	 *
	 * @param array $file
	 * @param string $fixedPath
	 * @param string $folderPathFromRoot
	 */
	public function testGetReducedPath($file, $fixedPath, $folderPathFromRoot) {
		$response = $this->getReducedPath($file['path'], $folderPathFromRoot);

		$this->assertEquals($fixedPath, $response);
	}

	/**
	 * Mocks Files->getDownload
	 *
	 * @param int $fileId the ID of the file of which we need a large preview of
	 * @param string|null $filename
	 *
	 * @return array
	 */
	private function mockGetDownload($fileId, $filename) {
		$file = $this->mockFile($fileId);

		$this->mockGetResourceFromId($fileId, $file);

		$download = $this->mockDownloadData($file, $filename);

		$this->mockDownloadFile($file, $download);

		return $download;
	}

	/**
	 * Mocks OCP\Files\File
	 *
	 * Contains a JPG
	 *
	 * @param int $fileId
	 *
	 * @return object|\PHPUnit_Framework_MockObject_MockObject
	 */
	private function mockFile($fileId) {
		$file = $this->getMockBuilder('OCP\Files\File')
					 ->disableOriginalConstructor()
					 ->getMock();
		$file->method('getId')
			 ->willReturn($fileId);
		$file->method('getContent')
			 ->willReturn(file_get_contents(\OC::$SERVERROOT . '/tests/data/testimage.jpg'));
		$file->method('getName')
			 ->willReturn('testimage.jpg');
		$file->method('getMimeType')
			 ->willReturn('image/jpeg');

		return $file;
	}

	/**
	 * Mocks DownloadService->getResourceFromId
	 *
	 * @param int $fileId
	 * @param object|\PHPUnit_Framework_MockObject_MockObject|bool $answer
	 */
	private function mockGetResourceFromId($fileId, $answer) {
		$this->downloadService->expects($this->once())
							  ->method('getResourceFromId')
							  ->with($this->equalTo($fileId))
							  ->willReturn($answer);
	}

	/**
	 * @param object|\PHPUnit_Framework_MockObject_MockObject $file
	 * @param $filename
	 *
	 * @return array
	 */
	private function mockDownloadData($file, $filename) {
		$download = [
			'preview'  => $file->getContent(),
			'mimetype' => $file->getMimeType(),
		];

		if ($download) {
			if (is_null($filename)) {
				$filename = $file->getName();
			}
			$download['name'] = $filename;
		}

		return $download;
	}

	/**
	 * Mocks DownloadService->downloadFile
	 *
	 * @param object|\PHPUnit_Framework_MockObject_MockObject $file
	 * @param array $download
	 */
	private function mockDownloadFile($file, $download) {
		$this->downloadService->expects($this->once())
							  ->method('downloadFile')
							  ->with($this->equalTo($file))
							  ->willReturn($download);
	}

	/**
	 * Mocks SearchFolderService->getCurrentFolder
	 *
	 * @param $location
	 * @param $folderPathFromRoot
	 * @param $features
	 * @param $locationHasChanged
	 * @param $folder
	 */
	private function mockGetCurrentFolder(
		$location, $folderPathFromRoot, $features, $locationHasChanged, $folder
	) {
		$answer = [
			$folderPathFromRoot,
			$folder,
			$locationHasChanged
		];
		$this->searchFolderService->expects($this->once())
								  ->method('getCurrentFolder')
								  ->with(
									  $location,
									  $features
								  )
								  ->willReturn($answer);
	}

	private function mockGetFolder($nodeId, $files, $permissions, $etag) {
		$folder = $this->getMockBuilder('OCP\Files\Folder')
					   ->disableOriginalConstructor()
					   ->getMock();
		$folder->method('getType')
			   ->willReturn('folder');
		$folder->method('getId')
			   ->willReturn($nodeId);
		$folder->method('getDirectoryListing')
			   ->willReturn($files);
		$folder->method('getPermissions')
			   ->willReturn($permissions);
		$folder->method('getEtag')
			   ->willReturn($etag);

		return $folder;
	}

	private function mockGetAlbumInfo($folderNode, $folderPathFromRoot, $features, $answer) {
		$this->configService->expects($this->once())
							->method('getAlbumInfo')
							->with(
								$folderNode,
								$folderPathFromRoot,
								$features
							)
							->willReturn($answer);
	}

	private function mockGetMediaFiles($folderNode, $mediatypes, $features, $answer) {
		$this->searchMediaService->expects($this->once())
								 ->method('getMediaFiles')
								 ->with(
									 $folderNode,
									 $mediatypes,
									 $features
								 )
								 ->willReturn($answer);
	}
}
