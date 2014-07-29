<?php

/**
 * CodeMirror loading helper class
 * Can be loaded directly
 * Use a child class containing source files
 * 
 * /lib - core js & css
 * /mode - contains all modes to be loaded
 * /addon - any/all addons
 * 
 * @author Evan Mattson (@aaemnnosttv)
 * 
 */

class WP_CodeMirrorLoader {

	protected $version = '';	// override this with bundled cm version in child
	protected $mode_path;		// absolute path to mode directory
	protected $addon_path;		// absolute path to addon directory
	protected $modes;			// array of available modes
	protected $addons;			// array of available addons
	protected $aliases;			// array of asset slug aliases
	protected $registered;		// array of registered assets 

	function __construct( $base_uri, $base_path = '', $autoload = true ) {

		$this->path       = $base_path ? $base_path : dirname( __FILE__ );
		$this->uri        = $base_uri;
		$this->addon_path = path_join( $this->path, 'addon' );
		$this->mode_path  = path_join( $this->path, 'mode' );
		
		$this->addons     = $this->get_addons();
		$this->modes      = $this->get_modes();

		if ( $autoload )
			$this->init();
	}

	/**
	 * Register assets as soon as possible
	 * If autoload is disabled, this method can be called directly after instantiation
	 */
	function init() {
		if ( !did_action( 'init' ) )
			add_action( 'init', array(&$this, 'register_assets') );
		else
			$this->register_assets();
	}

	public function get_modes() {
		return is_array( $this->modes ) ? $this->modes : $this->get_dirnames( $this->mode_path );
	}

	public function get_addons() {
		return is_array( $this->addons ) ? $this->addons : $this->get_dirnames( $this->addon_path );
	}

	public function get_registered() {
		return $this->registered;
	}

	/**
	 * Asset Discovery
	 * Populates class arrays for modes & addons
	 * @param  [type] $path [description]
	 * @return [type]       [description]
	 */
	protected function get_dirnames( $path ) {

		$all = scandir( $path );
		foreach ( $all as &$ent ) {
			if ( in_array( $ent, array('.','..') ) )
				$ent = false;
			elseif ( !is_dir( "$path/$ent" ) )
				$ent = false;
		}

		return array_values( array_filter( $all ) );
	}

	function register_assets() {

		// style
		$this->register('codemirror','lib/codemirror.css');
		// scripts
		$this->register('codemirror','lib/codemirror.js');

		$assets = array(
			'mode'  => $this->modes,
			'addon' => $this->addons
		);

		foreach ( $assets as $type => $resources ) :
			foreach ( $resources as $slug ) :
				$basepath = "$type/$slug/$slug";
				foreach ( array('js','css') as $ext ) :
					if ( !is_file( path_join( $this->path, "$basepath.$ext" ) ) )
						continue;

					$this->register_module( $type, $slug, $ext, $basepath );

					if ( isset( $this->aliases[ $slug ] ) && is_array( $this->aliases[ $slug ] ) ) {
						foreach ( $this->aliases[ $slug ] as $aslug )
							$this->register_module( $type, $aslug, $ext, $basepath );
					}
				endforeach;	
			endforeach;
		endforeach;
	}

	function register_module( $type, $slug, $ext, $basepath ) {
		$this->register(
			"codemirror-$slug",
			"$basepath.$ext",
			$this->get_dependencies( $slug, $ext, $type ),
			$type
		);
	}

	// override if you wish to use them
	// codemirror is added automatically
	function get_dependencies( $slug, $ext, $type ) {
		return array();
	}

	function register( $handle, $relative_src, $deps = array(), $type = 'core' ) {

		$file = pathinfo( path_join( $this->path, $relative_src ) );
		$src = $this->uri( $relative_src );

		// auto-add core as first dependency
		if ( 'codemirror' != $handle && !in_array('codemirror', $deps) )
			array_unshift( $deps, 'codemirror' );

		if ( 'js' == $file['extension'] )
			wp_register_script( $handle, $src, $deps, $this->version, true );
		else
			wp_register_style ( $handle, $src, $deps, $this->version );

		$this->registered[ $type ][ $handle ][ $file['extension'] ] = array(
			'deps'         => $deps,
			'relative_src' => $relative_src,
			'src'          => $src,
			'pathinfo'     => $file
		);
	}

	function uri( $rel ) {
		return rtrim( $this->uri, '/') . '/' . ltrim( $rel, '/');
	}

}