<?php
/**
 * MslsMain
 * @author Dennis Ploetner <re@lloc.de>
 * @since 0.9.8
 */

/**
 * Abstraction for the hook classes
 * @package Msls
 */
abstract class MslsMain {

	/**
	 * Options
	 * @var MslsOptions
	 */
	protected $options;

	/**
	 * Blogs
	 * @var MslsBlogCollection
	 */
	protected $blogs;

	/**
	 * Every child of MslsMain has to define a init-method
	 */
	abstract public static function init();

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->options = MslsOptions::instance();
		$this->blogs   = MslsBlogCollection::instance();
	}

	/**
	 * Save
	 * @param int $object_id
	 * @param string $class
	 * @param array $input
	 */
	protected function save( $object_id, $class ) {
		$input = array(
			$this->blogs->get_current_blog()->get_language() => (int) $object_id,
		);
		foreach ( $_POST as $key => $value ) {
			if ( false !== strpos( $key, 'msls_input_' ) && !empty( $value ) ) {
				$input[substr( $key, 11 )] = (int) $value;
			}
		}
		$msla      = new MslsLanguageArray( $input );
		$options   = new $class( $object_id );
		$language  = $this->blogs->get_current_blog()->get_language();
		$temp      = $options->get_arr();
		$object_id = $msla->get_val( $language );
		if ( 0 != $object_id ) 
			$options->save( $msla->get_arr( $language ) );
		else
			$options->delete();
		$blogs = $this->blogs->get();
		foreach ( $blogs as $blog ) {
			switch_to_blog( $blog->userblog_id );
			$language  = $blog->get_language();
			$object_id = $msla->get_val( $language );
			if ( 0 != $object_id ) { 
				$options = new $class( $object_id );
				$options->save( $msla->get_arr( $language ) );
			}
			else {
				if ( isset( $temp[$language] ) ) {
					$options = new $class( $temp[$language] );
					$options->delete();
				}
			}
			restore_current_blog();
		}
	}

	/**
	 * Delete
	 * @param int $post_id
	 */
	public function delete( $post_id ) {
		$options = new MslsOptionsPost( $post_id );
		$this->save( $post_id, 'MslsOptionsPost', $options->get_arr() );
	}

}
