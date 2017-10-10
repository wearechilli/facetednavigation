<?php

namespace Craft;

/**
 * FacetedNavigationVariable class.
 *
 * The template-facing version of our service. This primary variable class can
 * be accessed from any template via craft.facetedNavigation, e.g. craft.facetedNavigation.getNavigation.
 */
class FacetedNavigationVariable
{
	/**
	 * Get faceted navigation.
	 *
	 * @param  array $categoryHandles An aray of category handles.
	 * @return array
	 */
	public function getNavigation( $categoryHandles = [] )
	{
		if ( empty( $categoryHandles ) )
		{
			throw new HttpException( '501', 'No category group handles supplied in getNavigation, eg: craft.facetedNavigation.getNavigation(["plants", "sun", "soil"])' );
		}

		return craft()->facetedNavigation_navigation->getNavigation( $categoryHandles );
	}

}
