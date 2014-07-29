<?php
/*
    Section: Selettore Tabs
    Author: Enrique Chavez
    Author URI: http://enriquechavez.co
    Description: Selettore Tabs is a must-have section. Built using the latest DMS improvements, you can navigate through the content in an easy way using a beatiful transition effect. No more Custom Post Types; edit the content right in the page thanks to the DMS' live editing.
    Class Name: TmSelettoreTabs
    Demo: http://dms.tmeister.net/selettore-tabs
    Version: 1.4
    Loading: active
    PageLines: true
*/

class TmSelettoreTabs extends PageLinesSection {

    var $section_name      = 'Selettore Tabs';
    var $section_version   = '1.4';
    var $section_key ;
    var $chavezShop;

    function section_persistent()
    {
        $this->section_key = strtolower( str_replace(' ', '_', $this->section_name) );
        //$this->verify_license();
        //add_filter('pl_sorted_settings_array', array(&$this, 'add_global_panel'));
    }

    function verify_license(){
        if( !class_exists( 'chavezShopVerifier' ) ) {
            include( dirname( __FILE__ ) . '/inc/chavezshop_verifier.php' );
        }
        $this->chavezShop = new chavezShopVerifier( $this->section_name, $this->section_version, $this->opt('selettore_tabs_license_key') );
    }

    function add_global_panel($settings){
        $valid = "";
        if( get_option( $this->section_key."_activated" ) ){
            $valid = ( $this->chavezShop->check_license() ) ? ' - Your license is valid' : ' - Your license is invalid';
        }

        if( !isset( $settings['eChavez'] ) ){
            $settings['eChavez'] = array(
                'name' => 'Enrique Chavez Shop',
                'icon' => 'icon-shopping-cart',
                'opts' => array()
            );
        }

        $collapser_opts = array(
            'key'   => 'selettore_tabs_license_key',
            'type'  => 'text',
            'title' => '<i class="icon-shopping-cart"></i> ' . __('Selettore Tabs License Key', 'selettore') . $valid,
            'label' => __('License Key', 'selettore'),
            'help'  => __('The section is fully functional whitout a key license, this license is used only get access to autoupdates within your admin.', 'selettore')

        );

        array_push($settings['eChavez']['opts'], $collapser_opts);
        return $settings;

    }

    function section_scripts(){
        wp_enqueue_script('script-name', $this->base_url.'/selettore-tabs.js', array('jquery'), $this->section_version, true );
    }


    function section_head() {
    ?>
        <script>
            jQuery(document).ready(function($) {

                jQuery('.tab<?php echo $this->meta['clone']?>').selettoreTabs();
            });
        </script>

        <style type="text/css">
            .tab<?php echo $this->meta['clone']?> .tab-label{
                background:  <?php echo pl_hashify($this->opt('tab_bg_color'))?>;
                color: <?php echo pl_hashify($this->opt('tab_text_color')); ?>;
            }

            .tab<?php echo $this->meta['clone']?> .tab-label.current{
                background:  <?php echo pl_hashify($this->opt('tab_bg_color_hover'))?> !important;
                color: <?php echo pl_hashify($this->opt('tab_text_color_hover'))?> !important;
            }

            .tab<?php echo $this->meta['clone']?> .tabs-selector .triangle{
                border-left-color: <?php echo pl_hashify($this->opt('tab_bg_color_hover'))?> !important;
            }

            .tab<?php echo $this->meta['clone']?> .tabs-selector .tab-wrapper{
                border-bottom: 1px solid <?php echo pl_hashify($this->opt('tab_border_color'))?> !important;
            }
            .tab<?php echo $this->meta['clone']?> .tabs-selector .tab-wrapper:last-child{
                border-bottom: 0 !important;
            }


        </style>
    <?php
    }

    function before_section_template( $location = '' ) {

	}

   	function section_template() {

        if( PL_CORE_VERSION <= '1.0.4' &&  !$this->opt('stabs_count')  ){
            echo setup_section_notify($this, __('Please start adding some content.', 'selettore-tabs'));
            return;
        }

        if( PL_CORE_VERSION > '1.0.4' ){
            // MAP OLD SHIT
            $upgrade_mapping = array(
                'icon'        => 'stab_icon%s',
                'icon_label'  => 'stab_icon_label%s',
                'custom_page' => 'stab_custom_page%s',
                'head'        => 'stab_c_head%s',
                'subhead'     => 'stab_c_subhead%s',
                'media'       => 'stab_c_media%s',
                'text'        => 'stab_c_text%s',
            );
            $boxes     = $this->opt('stabs_count');
            $tab_array = $this->opt('tab_array');
            $tab_array = $this->upgrade_to_array_format_from_zero('tab_array', $tab_array, $upgrade_mapping, $boxes);
            $i         = 0;
            if( !is_array( $tab_array) ){
                echo setup_section_notify($this, __('Please start adding some content.', 'selettore-tabs'));
                return;
            }
        }
    ?>

        <div class="row selettore tab<?php echo $this->meta['clone'];?> ">
            <div class="span3">
                <div class="tabs-selector">
                    <?php
                        if( PL_CORE_VERSION > '1.0.4' ){
                            foreach ($tab_array as $tab) {
                                 $this->draw_tab($tab, $i++);
                            }
                        }else{
                            for ($i=0; $i < $this->opt('stabs_count'); $i++){
                                $tab = array(
                                    'icon'        => $this->opt('stab_icon'.$i),
                                    'icon_label'  => $this->opt('stab_icon_label'.$i),
                                    'custom_page' => $this->opt('stab_custom_page'.$i ),
                                    'head'        => $this->opt('stab_c_head'.$i),
                                    'subhead'     => $this->opt('stab_c_subhead'.$i),
                                    'media'       => $this->opt('stab_c_media'.$i),
                                    'text'        => $this->opt('stab_c_text'.$i),
                                );
                                $this->draw_tab($tab, $i);
                            }
                        }
                    ?>
                </div>
            </div>
            <div class="span9">
                <div class="tabs-container"></div>
            </div>
            <div class="clear"></div>
        </div>

    <?php
   	}

    function draw_tab($tab, $i){
        $old = (PL_CORE_VERSION > '1.0.4') ? false : true;
        $tab['media'] = (PL_CORE_VERSION > '1.0.4') ? pl_array_get('media', $tab) : $tab['media'];
        $tab['icon'] = (isset( $tab['icon'] ) && $tab['icon']) ? $tab['icon'] : false;
        $tab['icon_label'] = (isset( $tab['icon_label'] ) && $tab['icon_label']) ? $tab['icon_label'] : false;
        $tab['custom_page'] = (isset( $tab['custom_page'] ) && $tab['custom_page']) ? $tab['custom_page'] : false;
        $tab['head'] = (isset( $tab['head'] ) && $tab['head']) ? $tab['head'] : false;
        $tab['subhead'] = (isset( $tab['subhead'] ) && $tab['subhead']) ? $tab['subhead'] : false;
        $tab['text'] = (isset( $tab['text'] ) && $tab['text']) ? $tab['text'] : false;

        ob_start();
    ?>
        <div class="tab-wrapper" data-index="<?php echo $i ?>">
            <div class="tab-label <?php echo $i == 0 ? 'current' : '' ?>">
                <div class="tab-icon"><i class="icon icon-<?php echo $tab['icon'] ? $tab['icon'] : 'move' ?> my-tab-icon"></i></div>
                <span class="tab-title" data-sync="<?php echo $old ? 'stab_icon_label'.$i : 'tab_array_item'.($i+1).'_icon_label' ?>">
                    <?php echo $tab['icon_label'] ? $tab['icon_label'] : 'Insert your label' ?>
                </span>
                <div class="tab-pointer"><div class="triangle"></div></div>
            </div>
            <div class="tab-contents hentry <?php echo (! $tab['custom_page'] ) ? 'preformats' : '' ?>">
                <?php if ( $tab['custom_page'] ): ?>
                    <?php
                        $page_data = get_page( $tab['custom_page'] );
                        echo apply_filters('the_content', $page_data->post_content);
                    ?>
                <?php else: ?>
                    <h1 data-sync="<?php echo $old ? 'stab_c_head'.$i : 'tab_array_item'.($i+1).'_head' ?>">
                        <?php echo $tab['head'] ? $tab['head'] : 'Insert the head title' ?>
                    </h1>
                    <h5 data-sync="<?php echo $old ? 'stab_c_subhead'.$i : 'tab_array_item'.($i+1).'_subhead' ?>">
                       <?php echo $tab['subhead'] ? $tab['subhead'] : 'Insert subhead' ?>
                    </h5>
                    <div class="media">
                        <?php $media = $tab['media'] ? $tab['media'] : 'http://dms.tmeister.net/selettore-tabs/wp-content/uploads/sites/3/2013/08/sample.png' ?>
                        <img src="<?php echo $media ?>" alt="" data-sync="<?php echo $old ? 'stab_c_media'.$i : 'tab_array_item'.($i+1).'_media' ?>">
                    </div>
                    <div class="stab-details" data-sync="<?php echo $old ? 'stab_c_text'.$i : 'tab_array_item'.($i+1).'_text' ?>">
                        <?php echo $tab['text'] ? $tab['text'] : '<p>Please add some content, this field accepts HTML, this is a sample link <a href="http://pagelines.com">PageLines</a></p>'?>
                    </div>
                <?php endif ?>

            </div>
        </div>
    <?php

        ob_end_flush();
    }

	function after_section_template($clone = null){}

	function section_foot(){}

	function welcome(){

		ob_start();

		?><div style="font-size:12px;line-height:14px;color:#444;"><p><?php _e('You can have some custom text here.','nb-section');?></p></div><?php

		return ob_get_clean();
	}

	function section_opts(){
        $options = array(
            array(
                'key' => 'stab_multi',
                'type' => 'multi',
                'title' => 'Selettore Tabs Configuration',
                'opts' => array(
                    array(
                        'key' => 'stabs_count',
                        'type' => 'count_select',
                        'count_start' => 1,
                        'count_number' => 20,
                        'label' => __( 'How many tabs do you want to show?', 'selettore-tabs')
                    ),
                    array(
                        'key' => 'tab_bg_color',
                        'type' => 'color',
                        'label' => __('Tabs background color', 'selettore-tabs'),
                        'default' => '#ffffff'
                    ),
                    array(
                        'key' => 'tab_text_color',
                        'type' => 'color',
                        'label' => __('Tabs text color', 'selettore-tabs'),
                        'default' => '#5c5c5c'
                    ),
                    array(
                        'key' => 'tab_bg_color_hover',
                        'type' => 'color',
                        'label' => __('Selected tab background color', 'selettore-tabs'),
                        'default' => '#10b9b9'
                    ),
                    array(
                        'key' => 'tab_text_color_hover',
                        'type' => 'color',
                        'label' => __('Selected tab text color', 'selettore-tabs'),
                        'default' => '#ffffff'
                    ),
                    array(
                        'key' => 'tab_border_color',
                        'type' => 'color',
                        'label' => __('Selected tab border color', 'selettore-tabs'),
                        'default' => '#eeeeee'
                    )
                )
            ),
        );

        if( PL_CORE_VERSION > '1.0.4' ){
            unset( $options[0]['opts'][0] );
            $options = $this->create_accordion($options);
        }else{
            $options = $this->create_tabs_settings($options);
        }
		return $options;
	}

    function create_accordion($options){

        $tabHelp = "<h6 style='padding-bottom:5px; margin-bottom:5px; border-bottom:1px solid #ccc'>". __('Tab settings' ,'selettore-tabs') . "</h6>";

        $contentHelp = "<h6 style='padding-bottom:5px; margin-bottom:5px; border-bottom:1px solid #ccc'>". __('Content settings' ,'selettore-tabs') . "</h6>";

        $available_pages = $this->get_pages_to_show();

        $options[] = array(
            'key'       => 'tab_array',
            'type'      => 'accordion',
            'col'       => 2,
            'title'     => __('Selettore Tab Contents', 'selettore-tabs'),
            'post_type' => 'Selettore Tab',
            'opts'      => array(
                array(
                    'key'      => 'help',
                    'type'     => 'template',
                    'template' => $tabHelp
                ),
                array(
                    'key'     => 'icon',
                    'type'    => 'select_icon',
                    'label'   => 'Tab icon',
                    'default' => 'move'
                ),
                array(
                    'key' => 'icon_label',
                    'type' => 'text',
                    'label' => 'Tab Label'
                ),
                array(
                    'key' => 'stab_h2',
                    'type' => 'template',
                    'template' => $contentHelp
                ),
                array(
                    'key' => 'head',
                    'type' => 'text',
                    'label' => 'Content head',
                ),
                array(
                    'key' => 'subhead',
                    'type' => 'text',
                    'label' => 'Content subhead',
                ),
                 array(
                    'key' => 'media',
                    'type' => 'image_upload',
                    'label' => 'Content media',
                ),
                 array(
                    'key' => 'text',
                    'type' => 'textarea',
                    'label' => 'Content text',
                ),
                 array(
                    'key' => 'custom_page',
                    'type' => 'select',
                    'label' => __('Select a page for content', 'selettore-tabs'),
                    'opts' => $available_pages
                )
            )
        );

        return $options;

    }

    function create_tabs_settings($opts){
        $loopCount = (  $this->opt('stabs_count') ) ? $this->opt('stabs_count') : false;
        $tabHelp = "<h6 style='padding-bottom:5px; margin-bottom:5px; border-bottom:1px solid #ccc'>". __('Left tabs settings' ,'selettore-tabs') . "</h6>";

        $contentHelp = "<h6 style='padding-bottom:5px; margin-bottom:5px; border-bottom:1px solid #ccc'>". __('Tab content settings' ,'selettore-tabs') . "</h6>";

        $newSetup = __('Select how many tabs you want to display in the left panel.' ,'selettore-tabs');

        if(!$loopCount){
            $box = array(
                'key' => 'stab_h3',
                'type' => 'template',
                'template' => $newSetup,
                'title' => __('Demo Content', 'selettore-tabs')
            );
            array_push($opts, $box);
        }

        $available_pages = $this->get_pages_to_show();

        for ($i=0; $i < $loopCount; $i++) {
            $box = array(
                'key' => 'stab_single'.$i,
                'type' =>  'multi',
                'title' => 'Selettore Tab ' . ($i+1) .' settings',
                'label' => 'Settings',
                'opts' => array(
                    array(
                        'key' => 'stab_h1_'.$i,
                        'type' => 'template',
                        'template' => $tabHelp
                    ),
                    array(
                        'key' => 'stab_icon' .$i,
                        'type' => 'select_icon',
                        'label' => 'Tab icon',
                        'default' => 'move'
                    ),
                    array(
                        'key' => 'stab_icon_label' .$i,
                        'type' => 'text',
                        'label' => 'Tab label',
                    ),
                    array(
                        'key' => 'stab_h2_'.$i,
                        'type' => 'template',
                        'template' => $contentHelp
                    ),
                    array(
                        'key' => 'stab_c_head' .$i,
                        'type' => 'text',
                        'label' => 'Content head',
                    ),
                    array(
                        'key' => 'stab_c_subhead' .$i,
                        'type' => 'text',
                        'label' => 'Content subhead',
                    ),
                     array(
                        'key' => 'stab_c_media' .$i,
                        'type' => 'image_upload',
                        'label' => 'Content media',
                    ),
                     array(
                        'key' => 'stab_c_text' .$i,
                        'type' => 'textarea',
                        'label' => 'Content text',
                    ),
                     array(
                        'key' => 'stab_custom_page'.$i,
                        'type' => 'select',
                        'label' => __('Select a page for content', 'selettore-tabs'),
                        'opts' => $available_pages
                    )
                )
            );

            array_push($opts, $box);

        }
        return $opts;
    }

    function get_pages_to_show(){
        $pages = get_pages();
        $out = array();
        foreach ($pages as $page) {
            $out[$page->ID] = array('name' => $page->post_title);
        }
        return $out;
    }

    // Custom Upgrate my count start in 0 not in 1.
    function upgrade_to_array_format_from_zero( $new_key, $array, $mapping, $number ){
        $scopes = array('local', 'type', 'global');
        if( ! $number )
        {
            return $array;
        }

        if( !$array || $array == 'false' || empty( $array ) )
        {
            for($i = 0; $i < $number; $i++)
            {
                // Set up new output for viewing
                foreach( $mapping as $new_index_key => $old_option_key ){
                    $old_settings[ $i ][ $new_index_key ] = $this->opt( sprintf($old_option_key, $i) );
                }

                // Load up old values using cascade
                foreach( $scopes as $scope )
                {
                    foreach( $mapping as $new_index_key => $old_option_key ){
                        $upgrade_array[$scope]['item'.($i+1)][ $new_index_key ] = $this->opt( sprintf($old_option_key, $i), array('scope' => $scope) );
                    }
                }
            }
            // Setup in new format & update
            foreach($scopes as $scope)
            {
                $this->opt_update( $new_key, $upgrade_array[$scope], $scope );
            }
            return $old_settings;


        } else{
            return $array;
        }
    }
}

