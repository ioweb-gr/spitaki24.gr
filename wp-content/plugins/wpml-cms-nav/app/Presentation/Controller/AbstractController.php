<?php
namespace WPML\Nav\Presentation\Controller;

abstract class AbstractController implements ControllerInterface {

	/**
	 * @var RequestInterface
	 */
	protected $request;

	/**
	 * @param RequestInterface $request
	 */
	public function __construct( RequestInterface $request ) {
		$this->request = $request;
	}

	/**
	 * @param string $path
	 * @param mixed $viewObject
	 * @param array $variables
	 * @return string
	 */
	public function render( $path, $viewObject, $variables = [] ) {
		ob_start();
		extract( $variables );
		include __DIR__ . '/../View/' . $path;
		return ob_get_clean();
	}

	abstract public function register();

}