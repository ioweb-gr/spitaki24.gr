<?php

namespace WPML\Nav\Presentation;

use WPML\Container\Container;
use WPML\Nav\Application\Filter\SidebarSectionsFilterInterface;
use WPML\Nav\Application\Repository\CacheRepositoryInterface;
use WPML\Nav\Application\Repository\PostRepositoryInterface;
use WPML\Nav\Application\Repository\NavigationRepositoryInterface;
use WPML\Nav\Application\Repository\SettingsRepositoryInterface;
use WPML\Nav\Application\Repository\TranslationRepositoryInterface;
use WPML\Nav\Infrastructure\Adapter\Request;
use WPML\Nav\Infrastructure\Filter\SidebarSectionsFilter;
use WPML\Nav\Infrastructure\Repository\CacheRepository;
use WPML\Nav\Infrastructure\Repository\NavigationRepository;
use WPML\Nav\Infrastructure\Repository\PostRepository;
use WPML\Nav\Infrastructure\Repository\SettingsRepository;
use WPML\Nav\Infrastructure\Repository\TranslationRepository;
use WPML\Nav\Presentation\Controller\PageNavigationController;
use WPML\Nav\Presentation\Controller\RequestInterface;
use function WPML\Container\make;

final class App {
	const CONTROLLERS = [
		PageNavigationController::class,
	];

	const INFRASTRUCTURE_CLASSES = [
		//Presentation Layer
		RequestInterface::class => Request::class,

		//Application Repositories
		PostRepositoryInterface::class => PostRepository::class,
		TranslationRepositoryInterface::class => TranslationRepository::class,
		SettingsRepositoryInterface::class => SettingsRepository::class,
		NavigationRepositoryInterface::class => NavigationRepository::class,
		CacheRepositoryInterface::class => CacheRepository::class,

		//Application Filters
		SidebarSectionsFilterInterface::class => SidebarSectionsFilter::class,
	];

	public function run() {
		$this->connectInterfacesToInfrastructure();

		$this->registerControllers();
	}

	private function connectInterfacesToInfrastructure() {
		Container::alias( self::INFRASTRUCTURE_CLASSES );
	}

	private function registerControllers() {
		foreach( self::CONTROLLERS as $controllerClass ) {
			$controller = make( $controllerClass );
			$controller->register();
		}
	}
}
