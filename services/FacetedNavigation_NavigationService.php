<?php

namespace Craft;

/**
 * FacetedNavigation_NavigationService class.
 *
 * This service’s public methods are available globally throughout the system,
 * for both this plugin and other plugins to access. They can be accessed via
 * craft()->serviceHandle->methodName().
 */
class FacetedNavigation_NavigationService extends BaseApplicationComponent
{
	/** @var array Category handles */
	public $categoryHandles  = [];

	/** @var array Active filters */
	public $activeFilters    = [];

	/** @var array Category groups */
	public $categoryGroups   = [];

	/** @var array Categories */
	public $categories       = [];

	/** @var array Active categories */
	public $activeCategories = [];

	/**
	 * Get navigation.
	 *
	 * @param  array $categoryHandles An array of category handles.
	 * @return array
	 */
	public function getNavigation( $categoryHandles )
	{
		$this->categoryHandles = is_array( $categoryHandles ) ? $categoryHandles : [ $categoryHandles ];

		$this->_setActiveFilters();
		$this->_setCategoryGroups();
		$this->_setCategories();

		return [
			'activeFilters' 	=> $this->activeFilters,
			'categoryGroups' 	=> $this->categoryGroups,
			'categories' 		=> $this->categories,
			'activeCategories' 	=> $this->activeCategories,
		];
	}

	/**
	 * Build URI.
	 *
	 * Return two URI strings for a category: URI with category removed if
	 * current category status is active (`remove`), and vice versa (`add`).
	 *
	 * @access private
	 * @param  string $slug  Category slug.
	 * @param  string $group Category group.
	 * @return array         An array with the `add` and `remove` URIs.
	 */
	private function _buildUri( $slug, $group )
	{
		# init active groups array
		$activeGroups = [];

		# init add and remove variables
		$add = $remove = '';

		# iterate over category handles
		foreach( $this->categoryHandles as $key )
		{
			# check if category handle is in active filters array
			if ( isset( $this->activeFilters[ $key ] ) )
			{
				# get active filters for category handle
				$filters = $this->activeFilters[ $key ];

				# add category handle to active groups array
				$activeGroups[] = $key;

				# add active group and terms to URI
				$add .= sprintf( '/%s/%s', $key, implode( '|', $filters ) );

				# check if passed slug belongs to group, and needs to be added to URI
				if ( ! in_array( $slug, $filters, true ) && $key === $group )
				{
					$add .= sprintf( '|%s', $slug );
				}

				# iterate over active filters array
				foreach( $filters as $k => $filter )
				{
					# category slug dealt with, remove from active filters array
					if ( $slug === $filter )
					{
						unset( $filters[ $k ] );
					}
				}

				# build the `remove` URI
				if ( ! empty( $filters ) )
				{
					$remove .= sprintf( '/%s/%s', $key, implode( '|', $filters ) );
				}
			}
		}

		# check for rogue category group + slug pair to append to the `add` URI
		if ( ! in_array( $group, $activeGroups, true ) )
		{
			$add .= sprintf( '/%s/%s', $group, $slug );
		}

		# return the URIs
		return compact( 'add', 'remove' );
	}

	/**
	 * Set categories.
	 *
	 * @access private
	 * @return void
	 */
	private function _setCategories()
	{
		$all_categories = $this->_getActiveCategories();

		foreach( $this->categoryGroups as $group )
		{
			$criteria        = craft()->elements->getCriteria( ElementType::Category );
			$criteria->group = $group['handle'];
			$categories      = craft()->elements->findElements( $criteria );

			foreach( $categories as $category )
			{
				$active = isset( $this->activeFilters[ $group['handle'] ] )
					   && in_array( $category->attributes['slug'], $this->activeFilters[ $group['handle'] ], true );

				$category_count = $this->_getCategoryCountRelatedTo( $category, $categories, $all_categories );

				$data = [
					'attributes' => $category->attributes,
					'title'      => $category->title,
					'active'     => $active,
					'url'        => $this->_buildUri( $category->attributes['slug'], $group['handle'] ),
					'model'      => $category,
					'count'      => $category_count,
				];

				$this->categories[ $group['handle'] ][] = $data;

				if ( $active )
				{
					$this->activeCategories[ $category->attributes['slug'] ] = $data;
				}
			}
		}
	}

	/**
	 * Set category groups.
	 *
	 * @access private
	 * @return void
	 */
	private function _setCategoryGroups()
	{
		foreach( $this->categoryHandles as $handle )
		{
			$this->categoryGroups[ $handle ] = craft()->categories->getGroupByHandle( $handle )->attributes;
		}
	}

	/**
	 * Set active filters.
	 *
	 * @access private
	 * @return void
	 */
	private function _setActiveFilters()
	{
		# get segments
		$segments = craft()->request->getSegments();

		# cleanup?
		// $segments = array_map( function( $segment ) { return trim( $segment, '|' ); }, $segments );
		// $segments = array_unique( $segments );

		# iterate over segments
		foreach( $segments as $key => $segment )
		{
			if ( in_array( $segment, $this->categoryHandles, true ) && isset( $segments[ $key + 1 ] ) )
			{
				$active_filters = explode( '|', $segments[ $key + 1 ] );
				$active_filters = array_diff( $active_filters, $this->categoryHandles );

				if ( ! empty( $active_filters ) ) {
					$this->activeFilters[ $segment ] = $active_filters;
				}
			}
		}

		asort( $this->activeFilters );
	}

	/**
	 * Get relative category count.
	 *
	 * @access private
	 * @param  object $category
	 * @param  array  $categories
	 * @param  array  $all_categories
	 * @return int
	 */
	private function _getCategoryCountRelatedTo( $category, $categories, $all_categories )
	{
		$key = 'element';

		$related_to   = [];
		$related_to[] = 'and';
		$related_to[] = [ $key => $category ];

		$categories_diffed = array_diff( [ $category ], $categories );

		foreach ( $all_categories as $cat )
		{
			$category_diffed = array_diff( [ $cat ], $categories_diffed );

			if ( empty( $category_diffed ) )
				continue;

			foreach ( $category_diffed as $diff )
			{
				$related_to[] = [ $key => $diff ];
			}
		}

		$category_object            = craft()->elements->getCriteria( ElementType::Entry );
		$category_object->limit     = null;
		$category_object->relatedTo = $related_to;

		return count( $category_object->find() );
	}

	/**
	 * Get all categories.
	 *
	 * @access private
	 * @return array
	 */
	private function _getActiveCategories()
	{
		$all_categories = [];

		foreach( $this->categoryGroups as $group )
		{
			$criteria        = craft()->elements->getCriteria( ElementType::Category );
			$criteria->group = $group['handle'];
			$categories      = craft()->elements->findElements( $criteria );

			foreach( $categories as $category )
			{
				$active = isset( $this->activeFilters[ $group['handle'] ] )
					   && in_array( $category->attributes['slug'], $this->activeFilters[ $group['handle'] ], true );

				if ( $active ) {
					$all_categories[] = $category;
				}
			}
		}

		return $all_categories;
	}

}
