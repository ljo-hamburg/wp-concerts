<?php
/**
 * Activates the WP Concerts plugin.
 *
 * @package LJO\WPConcerts
 */

declare(strict_types=1);

namespace LJO\WPConcerts;

/**
 * Class PluginActivator. Activates the wp concerts plugin.
 *
 * @package LJO\WPConcerts
 */
class PluginActivator {

	/**
	 * This function is called when the WP Concerts plugin is activates. It makes sure
	 * that the rewrite rules are flushed so that the custom post types can be accessed.
	 */
	public static function activate(): void {
		ConcertPostType::register_post_type();
		flush_rewrite_rules();
	}
}
