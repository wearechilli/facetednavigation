<?php

namespace Craft;

use \PHPUnit_Framework_MockObject_MockObject as mock;

/**
 * @coversDefaultClass Craft\FacetedNavigation_NavigationService
 * @covers ::<!public>
 */
class FacetedNavigation_NavigationServiceTest extends BaseTest
{
	public $service = null;

	/**
	 * {@inheritdoc}
	 */
	public static function setUpBeforeClass()
	{
		parent::setUpBeforeClass();

		require_once __DIR__ . '/../../services/FacetedNavigation_NavigationService.php';

		$this->service = new FacetedNavigation_NavigationService();
	}

	/**
	 * @covers ::getNavigation
	 */
	public function testGetNavigationShouldReturnArray()
	{
		$this->assertArrayHasKey( 'activeFilters', $this->service->getNavigation( array( 'color', 'size' ) ) );
	}

}
