<?php
namespace WPML\Nav\Application;

use WPML\Nav\Application\Repository\CacheRepositoryInterface as CacheRepository;
use WPML\Nav\Application\Repository\SettingsRepositoryInterface as SettingsRepository;
use WPML\Nav\Presentation\Controller\RequestInterface;

class NavigationCache {

	const TYPE_PAGE = 'nav_page';

	/**
	 * @var CacheRepository
	 */
	private $cacheRepository;

	/**
	 * @var SettingsRepository
	 */
	private $settingsRepository;

	/**
	 * @param CacheRepository $cacheRepository
	 * @param SettingsRepository $settingsRepository
	 */
	public function __construct( CacheRepository $cacheRepository, SettingsRepository $settingsRepository ) {
		$this->cacheRepository = $cacheRepository;
		$this->settingsRepository = $settingsRepository;
	}

	/**
	 * @param RequestInterface $request // todo: This depends on Presentation. is ok?
	 * @return string|null
	 */
	public function getPageCache( $request ) {
		if ( ! $this->isEnabled() ) {
			return null;
		}
		$cacheKey = $this->getChacheKey( $request );
		return $this->cacheRepository->getCacheValue( self::TYPE_PAGE, $cacheKey );
	}

	/**
	 * @param RequestInterface $request // todo: This depends on Presentation. is ok?
	 * @param string $cacheValue
	 * @return void
	 */
	public function setPageCache( $request, $cacheValue ) {
		if ( ! $this->isEnabled() ) {
			return;
		}
		$cacheKey = $this->getChacheKey( $request );
		$this->cacheRepository->setCacheValue( self::TYPE_PAGE, $cacheKey, $cacheValue );
	}

	/**
	 * @return bool
	 */
	private function isEnabled() {
		return $this->settingsRepository->getSettings()->getUseCache();
	}

	/**
	 * @param RequestInterface $request
	 * @return string
	 */
	public function getChacheKey(RequestInterface $request)
	{
		$cacheKey = sprintf(
			'%s-%s',
			$request->getRequestURI(),
			$request->getCurrentLanguage()
		);
		return $cacheKey;
	}
}