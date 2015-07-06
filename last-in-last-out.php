<?php
/**
 * Plugin Name: Last in Last out
 * Plugin URI: http://kcdipesh.com.np
 * Description: When a new post type is added it displayed first in the admin. This plugin maintain the menu order automatically to display the last post added at last.
 * Version: 0.1
 * Author: Dipesh KC
 * Author URI: http://kcdipesh.com.np
 * License: GPL2
 * 
 * Copyright 2013  Dipesh KC  (email : caseydipesh@gmail.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as 
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 **/

if(!is_admin()) return;

class KC_Last_In_Last_Out {
	

	public function __construct()
    {

        register_activation_hook( __FILE__, array($this,'activate' ) );
        add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array($this,'plugin_action_links' ) );

        add_action( 'admin_menu', array($this,'add_menu' ) );


        add_action( 'admin_init', array($this,'settings_api' ) );
		add_action('load-post-new.php', array($this,'make_some_action_at_post_insert') );


	}

    function add_menu()
    {
        add_submenu_page( 'options-general.php','LILO', 'LILO', 'manage_options', 'lilo', array($this,'lilo_options_page' ) );
        //add_submenu_page( 'tools.php', 'LILO', 'LILO', 'manage_options', 'lilo', 'lilo_options_page' );
    }

    function plugin_action_links( $links ) {
        $links[] = '<a href="'. esc_url( get_admin_url(null, 'options-writing.php') ) .'#lilo">Settings</a>';
        return $links;
    }

    function lilo_options_page(  ) {

        ?>
        <form action='options.php' method='post'>

            <h2>LILO</h2>

            <?php
            settings_fields( 'options-general.php?page=lilo' );
            do_settings_sections( 'options-general.php?page=lilo' );
            submit_button();
            ?>

        </form>
        <?php

    }

    public function activate()
    {
        //during plugin activation mark page as the elegible post type for lilo
        update_option( 'lilo_post_type_page',1 );

    }

    function settings_api()
    {
        register_setting( 'options-general.php?page=lilo', 'lilo_settings' );

        add_settings_section(
            'lilo_post_type_selection_setting_section',
            'Last In Last Out Post Types',
            array($this,'post_type_setting_callaback'),
            'options-general.php?page=lilo'

        );

        $post_types = get_post_types(array('public'=>true));

        foreach($post_types as $post_type) {
            register_setting( 'options-general.php?page=lilo', 'lilo_post_type_'.$post_type.'' );
        }

    }


    function post_type_setting_callaback()
    {
        echo '<a name="lilo"></a>';
        echo '<p>Select post types for which you want to display the post based on default menu order so that oldest post is displayed first</p>';

        $post_types = get_post_types(array('public'=>true));

        foreach($post_types as $post_type) {

            echo '<input name="lilo_post_type_'.$post_type.'" type="checkbox" value="1" class="code" ' . checked( 1, get_option( 'lilo_post_type_'.$post_type.'' ), false ) . ' /> '.$post_type.'<br>';
        }
    }


	public function make_some_action_at_post_insert()
    {
		add_filter('wp_insert_post_data',array($this,'modify_default_menu_order'),10,2);
	}

	/**
	 * @param $data
	 * @param $postarr
	 */
	public function modify_default_menu_order($data,$postarr)
    {

        //make a list of user selected post types
        $enabled_post_types = array();
        $post_types = get_post_types(array('public'=>true));

        foreach($post_types as $post_type) {
            if( 1 == get_option( 'lilo_post_type_'.$post_type ) ) {
                $enabled_post_types[] = $post_type;
            }
        }

        //give developers the power to control
        $enabled_post_types = (array) apply_filters('lilo_post_types',$enabled_post_types);
        //trigger_error(print_r($enabled_post_types,true));

        $post_type = $data['post_type'];
		if( !in_array($post_type, $enabled_post_types ))
			return $data;

		global $wpdb;
		$data['menu_order'] = $wpdb->get_var("SELECT MAX(menu_order)+1 AS menu_order FROM {$wpdb->posts} WHERE post_status='publish' AND post_type='{$post_type}'");
		return $data;

	}
}

new KC_Last_In_Last_Out();