<?php

/**
 * CodeMirror loading helper class
 * @author Evan Mattson (@aaemnnosttv)
 */

class CodeGlowCM extends WP_CodeMirrorLoader {
	var $version = '3.21';
	var $aliases = array(
		'htmlmixed'  => array('html'),
		'javascript' => array('js'),
	);
};