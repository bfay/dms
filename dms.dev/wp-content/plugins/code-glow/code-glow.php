<?php
/*
	Plugin Name: Code Glow
	Version: 1.2.6
	Description: Syntax highlighting & control.  Enhances & customizes WordPress post/page html editor, as well as PageLines Framework core editors.
	Author: Evan Mattson
	Author URI: http://pagelines.aaemnnost.tv/
	Plugin URI: http://pagelines.aaemnnost.tv/plugins/code-glow
	External: http://pagelines.aaemnnost.tv/plugins/code-glow
	Demo: http://pagelines.aaemnnost.tv/plugins/code-glow/demo
	Tags: syntax highlighting, extension
	PageLines: true
	V3: true
*/

class CodeGlowPlugin
{

	var $cm;
	var $user_id;
	var $config = array(
		'mode'           => 'htmlmixed',
		'theme'          => "codeglow",
		'lineWrapping'   => true,
		'lineNumbers'    => true,
		'matchBrackets'  => true,
		'indentUnit'     => 2,
		'indentWithTabs' => false,
		'tabSize'        => 4,
	);

	private $editors;
	private $default_hooks;
	private $extended_hooks;

	private static $instance;

	const version = '1.2.6';
	const slug = 'code-glow';

	public static function instance()
	{
		if ( is_null( self::$instance ) )
			self::$instance = new self();

		return self::$instance;
	}

	private function __construct()
	{
		$this->name = 'Code Glow';
		$this->editors = array();
		$this->activated = array();
		$this->default_hooks = array();
		$this->extended_hooks = array();

		$this->path = sprintf('%s/%s', WP_PLUGIN_DIR, self::slug);
		$this->uri  = plugins_url( self::slug );

		// codemirror
		$this->cm_path = path_join( $this->path, 'codemirror' );
		$this->cm_uri  = $this->uri('codemirror');
		$this->cm      = $this->load_cm();

		$this->modes = $this->cm->get_modes();

		// fire it up!
		add_action( 'pagelines_hook_pre', array(&$this, 'init') );
	}

	function init()
	{
		add_action( 'set_current_user',						array(&$this, 'setup_user_data'			)		);
		add_action( 'pagelines_setup',						array(&$this, 'setup'					)		);
		add_action( 'init',									array(&$this, 'register_scripts'		)		);
		add_action( 'admin_menu',							array(&$this, 'add_admin_menu'			)		);
		add_action( 'admin_menu',							array(&$this, 'register_defaults'		), 12	);
		add_action( 'admin_init',							array(&$this, 'register_settings'		), 1	);
		add_action( 'load-options.php',						array(&$this, 'save_user_meta'			)		);
		add_action( 'admin_enqueue_scripts',				array(&$this, 'admin_enqueue'			), 100	);
		add_action( 'codeglow_activated_codeglow_modal',	array(&$this, 'codeglow_modal_actions') );

		// pageless
		add_filter( 'pageless_editor_config',				array(&$this, 'pageless_integration')		);
		// core
		add_filter( 'pagelines_customcss_cm_options',		array(&$this, 'core_editors_config_filter')	);
		add_filter( 'pagelines_headerscripts_cm_options',	array(&$this, 'core_editors_config_filter')	);
		add_filter( 'pagelines_cm_config',					array(&$this, 'dms_base_cm_config')			);
	}

	function setup()
	{
		$this->integration = version_compare( PL_CORE_VERSION, '2.3', '>=' ); // 2.3+
		$this->legacy      = version_compare( PL_CORE_VERSION, '2.2', '<'  ); // below 2.2
		$this->pageless    = class_exists( 'PageLESSPlugin' );
		$this->is_dms      = class_exists( 'PageLinesTemplateHandler' );
		$this->editor_on   = false;

		// must come before config!
		$this->add_pl_option_filters();

		if ( $this->is_dms ) {
			$this->editor_on = pl_draft_mode();
			add_action( 'wp_enqueue_scripts',	array(&$this, 'front_enqueue'), 100	);
		}
		if ( function_exists('register_lessdev_dir') )
			register_lessdev_dir( 'aaemnnosttv', self::slug, $this->name, $this->path.'/css', array('themes' => 'themes') );
	}

	function uri ( $rel = '' )
	{
		$base = $this->uri;
		return rtrim( $base, '/' ) . '/' . ltrim( $rel, '/' );
	}

	/**
	 * Fire up CodeMirror Loader class
	 */
	function load_cm()
	{
		// parent class
		if ( ! class_exists('WP_CodeMirrorLoader') )
			require_once( "{$this->cm_path}/codemirror-loader.php" );

		// extended class
		require_once( "{$this->cm_path}/codemirror.php" );

		//return new CodeGlowCM( $this->cm_uri, $this->cm_path, false );
		return new CodeGlowCM( $this->cm_uri );
	}

	function register_scripts()
	{
		// scripts
		wp_register_script( self::slug,			$this->uri('js/codeglow.js'),		array('jquery','codemirror'),						self::version );
		wp_register_script( 'bootstrap',		$this->uri('js/bootstrap.min.js'),	array('jquery')	);
		wp_register_script( 'codeglow_editor',	$this->uri('js/post_editor.js'),	array('jquery','quicktags','bootstrap', self::slug), self::version,		true );
		// styles
		wp_register_style(  'codeglow_modal',	$this->uri('css/post_editor.css') );
	}

	/**
	 * Adds top level menu for our option page
	 */
	function add_admin_menu()
	{
		$this->hook = add_menu_page( $this->name, $this->name, 'edit_posts', self::slug, array(&$this, 'build_interface'), "{$this->uri}/img/icon.png" );
		add_action( "load-{$this->hook}", 	array(&$this, 'option_page_actions') );
	}

	/**
	 * Fires actions that will run on our option page only
	 */
	function option_page_actions()
	{
		add_filter( 'pagelines_admin_confirms',			array(&$this, 'filter_confirms') );
		add_filter( 'pagelines_settings_main_title',	array(&$this, 'filter_ui_title') );

		// enqueue the necessary PL admin JS for UI and options
		add_action( 'admin_print_scripts', 'pagelines_theme_settings_scripts' );

		$this->load_dependencies();
	}

	function load_dependencies()
	{
		// CodeGlowOptionsUI
		require_once "{$this->path}/inc/class.options.ui.php";

		if ( !class_exists('OptEngine') )
			require_once "{$this->path}/inc/class.options.engine.php";

		wp_enqueue_script( 'jquery-ui-tabs' );

		if ( $this->is_dms && version_compare(PL_CORE_VERSION, '1.0.9', '>=') )
		{
			require_once "{$this->path}/inc/legacy.functions.php";

			wp_enqueue_script( 'pl-script-common', "{$this->uri}/inc/script.common.js", array('jquery') );
			wp_enqueue_style( 'pl-objects', "{$this->uri}/inc/objects.css" );
			wp_enqueue_style( 'pl-admin-css', "{$this->uri}/inc/admin.css" );
		}
	}

	/**
	 * @todo document
	 */
	function setup_user_data()
	{
		$this->user_id = get_current_user_id();

		$theme = self::op('theme');
		$scheme = $this->get_theme_att('scheme', $theme);

		// default config
		$this->config = array_merge(
			$this->config,
			array(
				'mode'           => 'htmlmixed',
				'theme'          => "codeglow $theme {$scheme}_scheme",
				'lineWrapping'   => (bool) self::op('line_wrap'),
				'lineNumbers'    => (bool) self::op('line_numbers'),
				'matchBrackets'  => (bool) self::op('bracket_matching'),
				'indentUnit'     => (int)  self::op('indent_unit'),
				'indentWithTabs' => (bool) self::op('indent_tabs'),
				'tabSize'        => (int)  self::op('tab_size'),
			)
		);
	}

	/**
	 * Saves submitted form data as user meta
	 */
	function save_user_meta()
	{
		if ( ! isset($_POST['option_page']) || self::slug != $_POST['option_page'] || ! isset($_POST[self::slug]) )
			return;

		$opts = $this->get_ui_option_array();

		foreach ( $opts as $tab => $t )
		{
			foreach ( $t as $oid => $o )
			{
				// oid exists in POST, update saved value
				if ( isset( $_POST[ self::slug ][ $oid ] ) )
					update_user_meta( $this->user_id, $oid, $_POST[ self::slug ][ $oid ] );

				else // oid not in POST
				{
					if ( 'check' == $o['type'] )
						update_user_meta( $this->user_id, $oid, 0 );
					else
						update_user_meta( $this->user_id, $oid, false );
				}
			}
		}
	}

	/**
	 * Registers settings and sets other class variables
	 */
	function register_settings()
	{
		add_filter( "option_page_capability_{self::slug}", create_function('', "return 'edit_posts';") );

		// whitelist option
		register_setting( self::slug, self::slug );
	}

	/**
	 * Setup default editors
	 */
	function register_defaults()
	{
		global $_pagelines_options_page_hook;
		global $_pagelines_account_hook;

		$dms_hooks = array( $_pagelines_account_hook );
		$v2_hooks = array( $_pagelines_options_page_hook );

		$this->default_hooks = $this->is_dms ? $dms_hooks : $v2_hooks;

		// legacy-only
		$pre_core_cm = array(
			'pl_custom_css'      => array(
				'id'     => PAGELINES_SETTINGS.'_customcss',
				'config' => $this->legacy ? 'mode=css' : 'mode=less',
				'hooks'  => $this->default_hooks
			),
			'pl_header_scripts'  => array(
				'id'     => PAGELINES_SETTINGS.'_headerscripts',
				'hooks'  => $this->default_hooks
			)
		);
		// 2.3+
		$v2_editors = array(
			'pl_footer_scripts'  => array(
				'id'     => PAGELINES_SETTINGS.'_footerscripts',
				'hooks'  => $this->default_hooks
			),
			'pl_async_analytics' => array(
				'id'     => PAGELINES_SETTINGS.'_asynch_analytics',
				'hooks'  => $this->default_hooks
			),
			'codeglow_modal' => array(
				'id'          => 'codeglow_content',
				'hooks'       => array('post.php', 'post-new.php'),
				'object_name' => 'codeglow_modal_editor'
			)
		);
		// DMS
		$dms_editors = array();

		$default_editors = $this->is_dms ? $dms_editors : $v2_editors;

		// only include these for PLF pre 2.3
		if ( ! $this->integration )
			$default_editors = array_merge( $pre_core_cm, $v2_editors );

		foreach ( $default_editors as $handle => $args )
			register_codeglow_editor( $handle, $args );
	}

	/**
	 * Extra actions to fire when the CODEGLOW Post Editor Modal is activated
	 */
	function codeglow_modal_actions()
	{
		wp_enqueue_script( 'bootstrap'		 );
		wp_enqueue_script( 'codeglow_editor' );
		wp_enqueue_style(  'codeglow_modal'	 );

		$modal_config['keyboard'] = (bool) ploption("{self::slug}_modal_keyboard");
		$modal_config['backdrop'] = ( (bool) ploption("{self::slug}_modal_backdrop") ) ? true : 'static';

		wp_localize_script( 'codeglow_editor', 'codeglowModalConfig', $modal_config );

		add_action( 'admin_footer', 		array(&$this, 'print_codeglow_post_modal') );
	}

	/**
	 * Registers a new editor to be rendered
	 *
	 * @param string 	$handle 	unique identifier
	 * @param array  	$args   	setup array
	 */
	public function register_codeglow_editor( $handle, $args )
	{
		$d = array(
			'domain'      => 'admin',	// admin|front|both?
			'id'          => '',		// html ID for textarea
			'selector'    => '',		// selector for textarea
			'mode'        => '',		// shortcut for config setting
			'config'      => array(),	// codemirror config
			'hooks'       => array(),	// admin only
			'object_name' => ''			// js var name for cm editor instance
		);

		$settings = wp_parse_args( $args, $d );

		// we need one of these
		if ( ! $settings['id'] && ! $settings['selector'] )
			return false;

		// parse query-string format
		$config = wp_parse_args( $settings['config'] );

		// mode
		if ( $settings['mode'] )
			$config = wp_parse_args( array( 'mode' => $settings['mode'] ), $config );

		// config
		$settings['config'] = $config;

		// allow single hooks to be passed as a string
		if ( is_string( $settings['hooks'] ) )
			$settings['hooks'] = array( $settings['hooks'] );

		$this->editors[ $handle ] = $settings;

		return true;
	}

	/**
	 * Register a page hook or array of hooks to load codemirror styling on
	 * Needed for pages with editors that are not instantiated by CG, but are styled by it
	 *
	 * @param  string/array 	$hooks 		page hook
	 */
	public function extend_hooks( $hooks )
	{
		if ( is_array( $hooks ) )
		{
			foreach ( $hooks as $hook )
				if ( ! in_array( $hook, $this->extended_hooks ) )
					$this->extended_hooks[] = $hook;
		}
		elseif ( is_string( $hooks ) )
		{
			$hook = $hooks;
			if ( ! in_array( $hook, $this->extended_hooks ) )
				$this->extended_hooks[] = $hook;
		}
	}

	function get_active_handles()
	{
		global $hook_suffix;

		$domain = is_admin() ? 'admin' : 'front';
		$active = array();

		foreach ( $this->editors as $handle => $s )
		{
			if ( is_admin() && in_array( $s['domain'], array('admin','both') ) )
			{
				if ( in_array( $hook_suffix, $s['hooks'] ) )
					$active[] = $handle;
			}
			// will load on all front-end pages
			// no other qualification at this point
			elseif ( !is_admin() && in_array( $s['domain'], array('front','both') ) )
				$active[] = $handle;
		}

		return $active;
	}

	/**
	 * Admin activation
	 *
	 * V2 / V3
	 */
	function admin_enqueue()
	{
		global $hook_suffix;

		$active = $this->get_active_handles();
		$hooks = array_merge( $this->default_hooks, $this->extended_hooks );

		// qualify current page for activation
		if ( ! empty( $active ) || in_array( $hook_suffix, $hooks ) )
		{
			$this->activate_editors( $active );
			$this->active_actions();
		}
	}

	/**
	 * Front end activation
	 * DMS only
	 */
	function front_enqueue()
	{
		if ( !$this->editor_on )
			return;

		$active = $this->get_active_handles();

		// qualify current page for activation
		if ( ! empty( $active ) )
		{
			$this->activate_editors( $active );
			$this->active_actions();
		}
		else
			add_action( 'pagelines_head_last',	array(&$this, 'print_inline_theme_css') );
	}

	function active_actions()
	{
		$print_hook = is_admin() ? 'admin_print_styles' : 'pagelines_head_last';

		// codemirror core styles
		wp_enqueue_style( 'codemirror' );

		// modes
		foreach ( $this->modes as $mode )
			wp_enqueue_script( "codemirror-$mode" );

		// codeglow
		wp_enqueue_script ( self::slug );
		wp_localize_script( self::slug, 'codeglowConfig', $this->config );
		wp_localize_script( self::slug, 'codeglow_editors', $this->activated );

		// print editor theme styles
		add_action( $print_hook,	array(&$this, 'print_inline_theme_css') );
	}

	/**
	 * Activates editors for the current page
	 *
	 * @param  array 	$active 	active editor handles
	 */
	private function activate_editors( $active )
	{
		foreach ( $active as $handle )
		{
			$editor = $this->editors[ $handle ];

			// setup config
			$editor['config'] = wp_parse_args( $editor['config'], $this->config );

			// editor config var
			$var_name = $editor['object_name'] ? $editor['object_name'] : $handle;

			// saniztize js object name /[a-zA-Z0-9_]+/
			$var_name = str_replace( '-', '_', $var_name );
			$var_name = preg_replace( '/[^a-zA-Z0-9_]+/', '', $var_name );

			// deprecated
			// codemirror footer js
			/*$this->active_editors[ $handle ] = sprintf('var %s = CodeMirror.fromTextArea(document.getElementById("%s"), %s );',
				"{$var_name}_editor",
				$editor['id'],
				json_encode( (object) $editor['config'] )
			);*/
			// new method
			$this->activated[ $handle ] = array(
				'selector' => $editor['selector'] ? $editor['selector'] : "#{$editor['id']}",
				'var_name' => $var_name,
				'config'   => $editor['config']
			);

			// perhaps do something extra when this editor is activated
			do_action( "codeglow_activated_$handle" );
			do_action( 'codeglow_editor_activated', $handle );
		}
	}


	/**
	 * Filter callback for core framework editor configuration
	 * @since PLF v2.3.1
	 * @param  array 	$config 	Core CM config array
	 * @return array
	 */
	function core_editors_config_filter( $config )
	{
		$filter = current_filter();
		$config = $this->config;
		$config['mode'] = ( 'pagelines_customcss_cm_options' == $filter ) ? 'less' : 'htmlmixed';

		return $config;
	}

	/**
	 * Filter callback for DMS editors
	 */
	function dms_base_cm_config( $config )
	{
		return wp_parse_args( $this->config, $config );
	}

	/**
	 * Filters Page{LESS} editor configuration
	 * @param  	array 	$config 	pageless configuration array
	 * @return  array
	 */
	function pageless_integration( $config )
	{
		return wp_parse_args( 'mode=less', $this->config );
	}

	/**
	 * Prints customized theme CSS into admin header
	 */
	function print_inline_theme_css()
	{
		$theme = ploption( self::slug.'_theme' );

		// version specific solution to handling editor theme class with 2.3
		if ( '2.3' == CORE_VERSION && 'default' != $theme )
			add_action('admin_print_footer_scripts', array( $this, 'print_codeglow_class_change_js') );

		$css = self::get_theme_css( $theme );

		echo self::draw_css( $css, 'codeglow-css', 'Code Glow CSS' );
	}

	/**
	 * Builds theme custom CSS
	 * @param  string 	$theme  	theme name
	 * @return string
	 */
	function get_theme_css( $theme )
	{
		$css = array(
			pl_file_get_contents( "{$this->path}/css/themes/{$theme}.css" ),
			self::get_theme_mods( $theme ),
			self::get_css_overrides()
		);

		return join( "\n", $css );
	}

	/**
	 * Dynamically add color modification css for each theme
	 * @param  string 	$theme 		theme name
	 * @return string         		css
	 */
	function get_theme_mods( $theme )
	{
		extract( self::get_theme_atts( $theme ) );
		// $background (hex)
		// $scheme 	(light/dark)

		if ( !class_exists( 'CodeGlowColor' ) )
			require_once "{$this->path}/inc/class.color.php";

		$base = new CodeGlowColor( $background );
		$mods = array();

		// keep default the same just cause.
		if ( 'default' != $theme )
		{
			$mods['.CodeMirror'][] = 'border: 0 !important;';
			//$mods['.CodeMirror'][] = 'background: transparent !important;';
			$mods['.CodeMirror'][] = '-webkit-box-shadow: 0 1px 3px rgba(0,0,0,0.25) !important; -moz-box-shadow: 0 1px 3px rgba(0,0,0,0.25) !important; box-shadow: 0 1px 3px rgba(0,0,0,0.25) !important;';
			$mods['.CodeMirror'][] = '-webkit-border-radius: 4px !important; -moz-border-radius: 4px !important; border-radius: 4px !important;';
			$mods['.CodeMirror .CodeMirror-scroll'][] = '-webkit-border-radius: 4px; -moz-border-radius: 4px; border-radius: 4px;';
			$mods['.CodeMirror .CodeMirror-gutter'][] = '-webkit-border-radius: 4px 0 0 4px; -moz-border-radius: 4px 0 0 4px; border-radius: 4px 0 0 4px;';
			//$mods['.CodeMirror .CodeMirror-scroll'][] = sprintf('border: 1px solid %s;)', $base->c('contrast', '10%') );
			$mods['.CodeMirror .CodeMirror-gutter'][] = sprintf('background-color: %s !important; border-right: 1px solid %s !important;',
				$base->c('contrast', '3%'), // gutter background
				$base->c('contrast', '7%')  // gutter right border
			);
		}

		if ( 'dark' == $scheme )
		{
			$mods['.CodeMirror .CodeMirror-gutter'][] = 'box-shadow: none;';
			$mods['.cm-s-codeglow .cm-comment'][] = sprintf( 'color: %s !important;', $base->c('contrast', '15%') );
		}

		return self::array_to_css( $mods );
	}

	/**
	 * "Always on" css overrides
	 * @return string  	css
	 */
	function get_css_overrides()
	{
		// force scrolling
		$o['#tabs .CodeMirror'] = 'max-width: 709px;'; // options ui

		// override phantom core max-width
		$o['#codeglow_modal .CodeMirror'] = 'max-width: 100%;';

		// better fonts
		$o['.CodeMirror'] = 'text-shadow: none; font-family: Consolas, Monaco, monospace !important;';

		return self::array_to_css( $o );
	}

	/**
	 * Prints Modal markup
	 */
	function print_codeglow_post_modal()
	{
		?>
		<div id="codeglow_modal" class="modal hide fade">
			<div class="modal-header">
				<button type="button" class="close" role="dialog" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3 class="modal-title">Code Glow</h3>
			</div>
			<div class="modal-body">
				<textarea id="codeglow_content"></textarea>
			</div>
			<div class="modal-footer">
				<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
			</div>
		</div>
		<?php
	}

	/**
	 * Change theme class of hardcoded editors
	 * PLF 2.3.0 only
	 */
	function print_codeglow_class_change_js()
	{
		?>
		<script type="text/javascript">
			jQuery(document).ready(function($){
				$('.cm-s-default').removeClass('cm-s-default').addClass('cm-s-codeglow');
			});
		</script>
		<?php
	}

	/**
	 * Helper function to assemble CSS from an array
	 * $array[selector] = 'rules';
	 *
	 * @param  array 	$array 	input array
	 * @return string 			assembled css
	 */
	static function array_to_css( $array )
	{
		$css = "\n";
		foreach ( $array as $selector => $rules )
		{
			if ( is_array( $rules ) )
				$rules = implode(' ', $rules);

			$css .= sprintf("%s { %s }\n", $selector, $rules);
		}

		return $css;
	}

	/**
	 * Helper function to draw CSS
	 *
	 * @param  mixed 	$css 		(string/array) raw css (no tags)
	 * @param  string 	$id 		css id for <style> tag
	 * @param  string 	$comment 	html comment text (optional)
	 *
	 * @return string 				complete html/css
	 */
	static function draw_css( $css, $id = null, $comment = null )
	{
		global $render_css;

		if ( is_array( $css ) )
			$css = $this->array_to_css( $css );

		//if ( is_a( $render_css, 'PageLinesRenderCSS' ) && method_exists($render_css, 'minify') )
		//	$css = $render_css->minify( $css );

		$out = $comment ? pl_source_comment( $comment ) : '';
		$out.= inline_css_markup( $id, $css, false );

		return $out."\n";
	}

	/**
	 * Helper function to draw JS
	 * @param  string 	$js 		javascript to output (no tags)
	 * @param  string 	$id 		id for <script> tag
	 * @param  string 	$comment 	html comment text (optional)
	 * @return string 				complete html/js
	 */
	function draw_js( $js, $id = null, $comment = null )
	{
		if ( is_array( $js ) )
			$js = implode( "\n", $js );

		$out = sprintf("%s\n<script type=\"text/javascript\">\n%s\n</script>\n",
			$comment ? pl_source_comment( $comment ) : '',
			$js
		);

		return $out;
	}

	function get_theme_att( $att, $theme = false )
	{
		$d = array(
			'scheme'     => 'light',
			'background' => '#ffffff'
		);

		$theme = $theme ? $theme : self::op( 'theme' );
		$atts  = $this->get_theme_atts( $theme );
		$atts  = wp_parse_args( $atts, $d );

		return isset( $atts[ $att ] ) ? $atts[ $att ] : '';
	}


	/**
	 * Returns an array of theme attributes for customizing dynamic CSS
	 * @param  string  	$t  theme name
	 * @return array 			attributes
	 */
	function get_theme_atts( $t )
	{
		$themes = self::_get_theme_atts_array();

		$default = array(
			'scheme'     => 'light',
			'background' => '#ffffff'
		);

		return isset( $themes[ $t ] ) ? $themes[ $t ] : $default;
	}

	/**
	 * Returns array of theme attributes
	 * @return array   	attributes
	 */
	static function _get_theme_atts_array()
	{
		$themes['default']     = array( 'scheme' => 'light', 'background' => '#ffffff' );
		$themes['solarized']   = array( 'scheme' => 'light', 'background' => '#FDF6E3' );
		$themes['bootstrap']   = array( 'scheme' => 'light', 'background' => '#f0f0f0' );

		$themes['monokai']     = array( 'scheme' => 'dark', 'background' => '#272822' );
		$themes['lesser-dark'] = array( 'scheme' => 'dark', 'background' => '#262626' );
		$themes['ambiance']    = array( 'scheme' => 'dark', 'background' => '#202020' );

		return $themes;
	}

	/**
	 * Creates the options UI
	 */
	function build_interface()
	{
		// force graphic selector to show a maximum of 3 columns for now
		echo self::draw_css('.graphic_selector_pad {padding: 15px 175px 23px;}');

		$this->ui_args = array(
			'title'       => self::__( 'Your Preferences | Code Glow' ),
			'settings'    => self::slug,
			'callback'    => array(&$this, 'get_ui_option_array'),
			'basic_reset' => false,
			'reset_cb'    => null,
			'show_save'   => true,
			'show_reset'  => false,
			'tabs'        => false // not really enough stuff to need them
		);

		new CodeGlowOptionsUI( $this->ui_args );
	}

	/*function fallback_ui()
	{
		?>
		<div style="width: 600px; margin: auto; text-align: center;">
			<h1>DMS 1.1 Notice</h1>
			<p>This version of Code Glow is not compatible with DMS 1.1</p>
			<p>Code Glow is still active, but these options are not currently changable.</p>
			<p>This options panel has been temporarily removed to allow your site to remain functional.</p>
			<p>Please know that I am working with PageLines to restore this functionality as soon as possible.</p>
			<p>For the latest information, follow my extension update stream on twitter: <a href="https://twitter.com/aaemnnosttv_ext" target="_blank">@aaemnnosttv_ext</a></p>
		</div>
		<?php
	}*/

	/**
	 * Dynamically add ploption filters for all of our options
	 * This is more for the options panel values.
	 */
	function add_pl_option_filters()
	{
		$opts = $this->get_ui_option_array();

		foreach ( $opts as $tab => $t )
			foreach ( $t as $oid => $o )
				add_filter( "ploption_$oid", array(&$this, 'pl_option_megafilter'), 10, 2 );
	}

	/**
	 * Returns saved user meta for queried key or default if no saved data exists yet
	 * @param  string 	$key 	option id
	 * @param  array 	$o   	option settings & values
	 * @return string 			single value
	 */
	function pl_option_megafilter( $key, $o )
	{
		// If the meta value does not exist and $single is true the function will return an empty string.
		// If $single is false an empty array is returned.
		if ( '' !== ($meta = get_user_meta( $this->user_id, $key, true )) )
			return $meta;
		else
			return self::get_option_default( $key );
	}

	/**
	 * Returns default value from option array
	 * @param  string 	$oid 	namespaced option id
	 * @return string 			defined default option value
	 */
	function get_option_default( $oid )
	{
		foreach ( $this->get_ui_option_array() as $tab => $t )
		{
			foreach ( $t as $_oid => $o )
			{
				if ( $_oid == $oid )
				{
					return isset( $o['default'] )
						? $o['default']
						: false;
				}
			}
		}
	
		return false;
	}

	/**
	 * Filters PageLines confirms
	 * @param  	array 	$c 		confirms
	 * @return  array
	 */
	function filter_confirms( $c )
	{
		if ( isset( $_GET['settings-updated'] ) )
			$c[0]['text'] = self::__( 'Your Code Glow preferences have been updated.' );

		return $c;
	}

	/**
	 * Filters options ui title
	 * @param  string 	$ui_title 	title
	 * @return string
	 */
	function filter_ui_title( $ui_title )
	{
		$ui_title = sprintf('%s <span class="btag grdnt">%s</span>',
			$this->ui_args['title'],
			self::version );

		return $ui_title;
	}

	/**
	 * Callback for build_interface()
	 * @return array   	options
	 */
	function get_ui_option_array()
	{
		return require "{$this->path}/options/ui.php";
	}

	static function __( $text )
	{
		return __( $text, self::slug );
	}

	static function oid( $key )
	{
		return self::slug . "_$key";
	}

	static function op( $key, $oset = array() )
	{
		$user_id = self::instance()->user_id;
		return get_user_meta( $user_id, self::oid( $key ), true );
	}

} // CodeGlowPlugin

// Instantiate right away to make API available
###############################################

	$GLOBALS['codeglow'] = codeglow();

###############################################

function codeglow()
{
	return CodeGlowPlugin::instance();
}

function extend_codeglow_hooks( $hooks )
{
	codeglow()->extend_hooks( $hooks );
}

function register_codeglow_editor( $handle, $args )
{
	codeglow()->register_codeglow_editor( $handle, $args );
}