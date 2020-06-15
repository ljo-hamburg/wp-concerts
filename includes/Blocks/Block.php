<?php
/**
 * The `Block` class.
 *
 * @package LJO\WPConcerts
 */

declare(strict_types=1);

namespace LJO\WPConcerts\Blocks;

/**
 * The `Block` class builds an object-oriented abstraction around the
 * `register_block_type` helper. An instance of this class represents a block available
 * in the Gutenberg editor. An instance does **not** represent a single instance of a
 * block on a specific post.
 *
 * Creating an instance of this class causes the block to be registered automatically.
 *
 * @package LJO\WPConcerts\Blocks
 */
class Block {
	/**
	 * The name of the block. The name does **not** include the wp-concerts prefix.
	 *
	 * @var string
	 */
	private string $name;

	/**
	 * A boolean value indicating whether the block supports server-side rendering.
	 *
	 * @var bool
	 */
	private bool $render;

	/**
	 * Block constructor. Creates and registers a new block with the given name. The
	 * `$name` should not include the wp-concerts prefix. The editor script for the
	 * block is automatically selected based on its name.
	 *
	 * @param string $name The name of the block, excluding the wp-concerts prefix.
	 * @param bool   $render Whether or not to use server-side rendering.
	 */
	public function __construct( string $name, bool $render = false ) {
		assert( ! is_null( $name ) );
		$this->name   = $name;
		$this->render = $render;
		add_action( 'init', array( $this, 'register' ) );
	}

	/**
	 * Returns the full name of the block.
	 *
	 * @return string
	 */
	public function get_block_name(): string {
		return "wp-concerts/$this->name";
	}

	/**
	 * Returns the script handle that is used to register the editor script for this
	 * block.
	 *
	 * @return string
	 */
	public function get_handle(): string {
		return $this->get_block_name();
	}

	/**
	 * Registers the block using the `register_block_type` function.
	 */
	public function register(): void {
		$args         = array();
		$dependencies = require dirname( dirname( __DIR__ ) ) . "/blocks/$this->name.asset.php";
		wp_register_script(
			$this->get_handle(),
			plugins_url( "blocks/$this->name.js", dirname( __DIR__ ) ),
			$dependencies['dependencies'],
			$dependencies['version'],
			true
		);
		wp_set_script_translations(
			$this->get_handle(),
			'wp-concerts',
			dirname( dirname( __DIR__ ) ) . '/languages'
		);
		$args['editor_script'] = $this->get_handle();
		if ( $this->render ) {
			$args['render_callback'] = array( $this, 'render' );
		}
		$args = array_merge( $args, $this->get_extra_args() );
		register_block_type( $this->get_block_name(), $args );
	}

	/**
	 * This function can be overridden by subclasses to provide additional arguments to
	 * the `register_block_type` function. The default implementation returns an empty
	 * array.
	 *
	 * This method may be used for blocks that make use of server-side rendering to
	 * register the block's attributes.
	 *
	 * @return array
	 */
	public function get_extra_args(): array {
		return array();
	}

	/**
	 * Renders the block on the server side. This function is automatically called if
	 * the block was created with the `$render` argument set to `true`.
	 *
	 * @param array  $attributes The block's attributes.
	 * @param string $content The content of the block.
	 *
	 * @return string
	 */
	public function render( array $attributes, string $content ): string {
		return '';
	}
}
