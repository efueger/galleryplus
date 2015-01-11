<?php
/**
 * ownCloud - galleryplus
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Olivier Paroz <owncloud@interfasys.ch>
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 *
 * @copyright Olivier Paroz 2014-2015
 * @copyright Bernhard Posselt 2012-2015
 */

namespace OCA\GalleryPlus\Middleware;

use OCP\IURLGenerator;
use OCP\IRequest;

use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Middleware;

use OCA\GalleryPlus\Utility\SmarterLogger;

/**
 * Checks that we have a valid token linked to a valid resource and that the
 * user is authorised to access it
 *
 * @package OCA\GalleryPlus\Middleware
 */
abstract class CheckMiddleware extends Middleware {

	/**
	 * @type string
	 */
	protected $appName;
	/**
	 * @type IRequest
	 */
	protected $request;
	/**
	 * @type IURLGenerator
	 */
	private $urlGenerator;
	/**
	 * @type SmarterLogger
	 */
	protected $logger;

	/***
	 * Constructor
	 *
	 * @param string $appName
	 * @param IRequest $request
	 * @param IURLGenerator $urlGenerator
	 * @param SmarterLogger $logger
	 */
	public function __construct(
		$appName,
		IRequest $request,
		IURLGenerator $urlGenerator,
		SmarterLogger $logger
	) {
		$this->appName = $appName;
		$this->request = $request;
		$this->urlGenerator = $urlGenerator;
		$this->logger = $logger;
	}

	/**
	 * If a CheckException is being caught, clients who sent an ajax requests
	 * get a JSON error response while the others are redirected to an error
	 * page
	 *
	 * @inheritDoc
	 */
	public function afterException(
		$controller, $methodName, \Exception $exception
	) {
		if ($exception instanceof CheckException) {
			$message = $exception->getMessage();
			$code = $exception->getCode();

			$this->logger->debug(
				"[TokenCheckException] {exception}",
				array(
					'exception' => $exception,
				)
			);

			return $this->computeResponse($message, $code);
		}

		throw $exception;
	}

	/**
	 * Decides which type of response to send
	 *
	 * @param string $message
	 * @param int $code
	 *
	 * @return JSONResponse|RedirectResponse|TemplateResponse
	 */
	private function computeResponse($message, $code) {
		$acceptHtml = stripos($this->request->getHeader('Accept'), 'html');
		if ($acceptHtml === false) {
			$response = $this->sendJsonResponse($acceptHtml, $code);
		} else {
			$response = $this->sendHtmlResponse($message, $code);
		}

		return $response;

	}

	/**
	 * Redirects the client to an error page or shows an authentication form
	 *
	 * @param string $message
	 * @param int $code
	 *
	 * @return RedirectResponse|TemplateResponse
	 */
	private function sendHtmlResponse($message, $code) {
		$this->logger->debug("[CheckException] HTML response");

		/**
		 * We need to render a template for 401 or we'll have an endless loop as
		 * this is called before the controller gets a chance to render anything
		 */
		if ($code === 401) {
			$response = $this->sendHtml401();
		} else {
			$response = $this->redirectToErrorPage($message, $code);
		}

		return $response;
	}

	/**
	 * Shows an authentication form
	 *
	 * @return TemplateResponse
	 */
	private function sendHtml401() {
		$params = $this->request->getParams();

		$this->logger->debug(
			'[CheckException] Unauthorised Request params: {params}',
			array(
				'params' => $params
			)
		);

		return new TemplateResponse(
			$this->appName, 'authenticate', $params,
			'guest'
		);
	}

	/**
	 * Redirects the client to an error page
	 *
	 * @param string $message
	 * @param int $code
	 *
	 * @return RedirectResponse
	 */
	private function redirectToErrorPage($message, $code) {
		$url = $this->urlGenerator->linkToRoute(
			$this->appName . '.page.error_page',
			array(
				'message' => $message,
				'code'    => $code
			)
		);

		return new RedirectResponse($url);
	}

	/**
	 * Returns a JSON response to the client
	 *
	 * @param string $message
	 * @param int $code
	 *
	 * @return JSONResponse
	 */
	private function sendJsonResponse($message, $code) {
		$this->logger->debug("[TokenCheckException] JSON response");

		$jsonData = array(
			'message' => $message,
			'success' => false
		);

		return new JSONResponse($jsonData, $code);
	}

}