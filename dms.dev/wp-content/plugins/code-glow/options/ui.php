<?php
/*
	CodeGlow UI Options
*/

$tabs['highlighting'] = array(
	self::oid( 'theme' ) => array(
		'title'        => self::__( 'Syntax Highlighting' ),
		'shortexp'     => self::__( 'Choose a color theme for syntax highlighting' ),
		'exp'          => self::__( 'Note: not all colors are shown on icons.<br>(Default: <code>Bootstrap</code>)' ),
		'inputlabel'   => self::__( 'Choose a color scheme' ),
		'type'         => 'graphic_selector',
		'layout'       => 'interface',
		'default'      => 'bootstrap',
		'showname'     => true,
		'sprite'       => "{$this->uri}/img/theme-sprite.png",
		'height'       => '88px', 
		'width'        => '130px',
		'selectvalues' => array(
			// light
			'default'     => array('name' => 'Default',			'offset' => '0 0'),
			'solarized'   => array('name' => 'Solarize',		'offset' => '0 -88px' ),
			'bootstrap'   => array('name' => 'Bootstrap',		'offset' => '0 -176px' ),
			// dark
			'ambiance'    => array('name' => 'Ambiance',		'offset' => '0 -264px' ),
			'monokai'     => array('name' => 'Monokai Bright',	'offset' => '0 -352px' ),
			'lesser-dark' => array('name' => 'Lesser Dark',		'offset' => '0 -440px' ),					
		)
	)
);
$tabs['post_editor'] = array(
	self::oid( 'modal_keyboard' ) => array(
		'title' 	   => self::__('Escape Key'),
		'shortexp'	   => self::__('Pressing Esc closes enhanced editor overlay?'),
		'exp'		   => 'Whether or not the enhanced editor for posts/pages should close should close when the escape key is pressed.<br />Enabled closes on escape key press.<br />(Default: <code>enabled</code>)',
		'type'		   => 'check',
		'inputlabel'   => self::__('Enable Esc key?'),
		'default'	   => true,
	),
	self::oid( 'modal_backdrop' ) => array(
		'title' 	   => self::__('Backdrop Click'),
		'shortexp'	   => self::__('Clicking overlay backdrop closes enhanced editor?'),
		'exp'		   => 'Whether or not the enhanced editor for posts/pages should close when the backdrop behind it is clicked.<br />Enabled closes on backdrop click.<br />(Default: <code>enabled</code>)',
		'type'		   => 'check',
		'inputlabel'   => 'Close on backdrop click?',
		'default'	   => true,
	),	
);
$tabs['editor_options'] = array(
	self::oid( 'line_wrap' ) => array(
		'title' 	   => self::__('Line Wrapping'),
		'shortexp'	   => self::__('Enable line wrapping in editors'),
		'exp'		   => 'Whether or not the editor should wrap long lines or scroll horizontally.<br />(Default: <code>enabled</code>)',
		'type'		   => 'check',
		'inputlabel'   => self::__('Enable line wrapping?'),
		'default'	   => true,
	),
	self::oid( 'line_numbers' ) => array(
		'title'        => self::__('Line Numbers'),
		'shortexp'     => self::__('Enable line numbers on editors?'),
		'exp'	  	   => '(Default: <code>enabled</code>)',
		'type'         => 'check',
		'default'	   => true,
		'inputlabel'   => self::__('Enable line numbers?')
	),
	self::oid( 'bracket_matching' ) => array(
		'title'        => self::__('Bracket Matching'),
		'shortexp'     => self::__('Enable bracket matching on editors?'),
		'type'         => 'check',
		'default'	   => true,
		'inputlabel'   => self::__('Enable bracket matching?'),
		'exp'		   => 'Bracket matching highlights both opening <strong>{</strong> and closing brackets <strong>}</strong> in a block of code when the cursor is next to one of them.<br />(Default: <code>enabled</code>)'
	),
	self::oid( 'indent_unit' ) => array(
		'title' 	   => self::__('Indent Size'),
		'shortexp'	   => self::__('The number of spaces used when indenting.'),
		'exp'		   => 'Defines the number of spaces used when indenting.<br />(Default: <code>2</code>)',
		'type'		   => 'select',
		'inputlabel'   => self::__('Number of spaces to use when indenting'),
		'default'	   => 2,
		'selectvalues' => array(
			2 => array('name' => '2'),
			4 => array('name' => '4'),
		),
	),
	self::oid( 'indent_tabs' ) => array(
		'title' 	   => self::__('Indent With Tabs?'),
		'shortexp'	   => self::__('Whether to use tabs or spaces when indenting'),
		'exp'		   => 'Whether when indenting, tabs should replace spaces. Number of spaces replaced depends on Tab Size.<br />Enabled replaces spaces with tabs.<br />(Default: <code>disabled</code>)',
		'inputlabel'   => self::__('Use tabs when indenting?'),
		'type'         => 'check',
		'default'	   => false,
	),
	self::oid( 'tab_size' )	   => array(
		'title' 	   => self::__('Tab Size'),
		'shortexp'	   => self::__('Tab size in spaces'),
		'exp'		   => 'Width of a tab character in spaces.<br />(Default: <code>4</code>)',
		'type'		   => 'select',
		'inputlabel'   => self::__('Select Tab Width'),
		'default'	   => 4,
		'selectvalues' => array(
			2 => array('name' => '2'),
			4 => array('name' => '4'),
		),
	),
);

return $tabs;