<?php
/**
 * Plugin Name: Flexi Pages Widget
 * Plugin URI: http://srinig.com/wordpress/plugins/flexi-pages/
 * Description: A highly configurable WordPress sidebar widget to list pages and sub-pages. User friendly widget control comes with various options. 
 * Version: 1.8 alpha
 * Author: Srini G
 * Author URI: http://srinig.com/wordpress
 * Text Domain: flexipages
 * Domain Path: /languages/
 * License: GPL2
 */

/*  Copyright 2007-2013 Srini G (email : srinig.com@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


include_once( 'inc/class-flexi-pages.php' );
include_once( 'inc/class-flexi-pages-widget.php' );


function flexipages_init()
{
	if( $old_widget_options = get_option( 'flexipages_widget') ) {
		if( !( $new_widget_options = get_option( 'widget_flexipages') ) ) {
			add_option( 'widget_flexipages', $old_widget_options );
		}
		delete_option( 'flexipages_widget' );
	}

	if(function_exists('load_plugin_textdomain'))
		load_plugin_textdomain('flexipages', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );



	/**
	 * The flexipages() template function
	 */
	function flexipages( $args = array() ) {

		$options = array();
		if( is_string( $args ) ) {
			$key_value = explode('&', $args);
			foreach($key_value as $value) {
				$x = explode('=', $value);
				$options[$x[0]] = $x[1]; // $options['key'] = 'value';
			}
		}
		else if( is_array( $args) ) {
			$options = $args;
		}


		$flexipages = new Flexi_Pages( $options );
	
		if( isset( $options['dropdown'] ) && $options['dropdown'] ) {
			$display = $flexipages->get_dropdown();
		}
		else {
			$display = $flexipages->get_list();
		}
		
		if( isset( $options['echo'] ) && !$options['echo'] ) {
			return $display;
		}
		else {
			echo $display;
		}

	}

	/** Alias of flexipages() function */
	function flexi_pages( $args = array() ) {
		return flexipages( $args );
	}
	
}

function flexipages_custom_link_text( $post ) {
	wp_nonce_field( 'flexipages_custom_link_text', 'flexipages_custom_link_text_nonce' );
	$value = get_post_meta( $post->ID, 'flexipages_custom_link_text', true);
	echo '<input type="text" name="flexipages_custom_link_text" value="'.esc_attr($value).'" style="width: 100%" />';
}

function flexipages_custom_link_text_save( $post_id ) {
	/*
	 * We need to verify this came from our screen and with proper authorization,
	 * because the save_post action can be triggered at other times.
	 */

	// Check if our nonce is set.
	if ( ! isset( $_POST['flexipages_custom_link_text_nonce'] ) ) {
		return;
	}

	// Verify that the nonce is valid.
	if ( ! wp_verify_nonce( $_POST['flexipages_custom_link_text_nonce'], 'flexipages_custom_link_text' ) ) {
		return;
	}

	// If this is an autosave, our form has not been submitted, so we don't want to do anything.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// Check the user's permissions.
	if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {

		if ( ! current_user_can( 'edit_page', $post_id ) ) {
			return;
		}

	} else {
		return;
	}

	/* OK, it's safe for us to save the data now. */
	
	// Make sure that it is set.
	if ( ! isset( $_POST['flexipages_custom_link_text'] ) ) {
		return;
	}

	// Sanitize user input.
	$value = sanitize_text_field( $_POST['flexipages_custom_link_text'] );

	// Update the meta field in the database.
	update_post_meta( $post_id, 'flexipages_custom_link_text', $value );
}

function flexipages_add_meta_boxes() {
	add_meta_box(
		'flexipages_custom_link_text',
		__( 'Flexi Pages Custom Link Text', 'flexipages' ),
		'flexipages_custom_link_text',
		'page',
		'side'
		);
}

add_action( 'plugins_loaded', 'flexipages_init' );
add_action( 'widgets_init', array('Flexi_Pages_Widget', 'register') );
add_action( 'add_meta_boxes', 'flexipages_add_meta_boxes' );
add_action( 'save_post', 'flexipages_custom_link_text_save' );

?>
