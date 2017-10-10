<?php

namespace Craft;

/**
 * FacetedNavigationPlugin class.
 *
 * The primary plugin class tells Craft same basic information about the plugin,
 * such as its name and version number. It also handles a few oddball things like
 * installation/uninstallation and hooks.
 */
class FacetedNavigationPlugin extends BasePlugin
{
	/**
	 * Get plugin name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return Craft::t( 'Faceted Navigation' );
	}

	/**
	 * Get plugin version.
	 *
	 * @return string
	 */
	public function getVersion()
	{
		return '1.1.0';
	}

	/**
	 * Get plugin developer.
	 *
	 * @return string
	 */
	public function getDeveloper()
	{
		return 'Chilli';
	}

	/**
	 * Get plugin developer URL.
	 *
	 * @return string
	 */
	public function getDeveloperUrl()
	{
		return 'https://github.com/wearechilli';
	}

	/**
	 * Get plugin URL.
	 *
	 * @return string
	 */
	public function getPluginUrl()
	{
		return 'https://github.com/wearechilli/facetednavigation';
	}

	/**
	 * Get documentation URL.
	 *
	 * @return string
	 */
	public function getDocumentationUrl()
	{
		return $this->getPluginUrl() . '/blob/master/README.md';
	}

}
