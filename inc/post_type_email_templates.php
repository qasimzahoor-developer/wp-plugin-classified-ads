<?php
/**
 * Init Class.
 **/
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class post_type_email_templates{
	/** Class constructor */ 
	public function __construct() {  
		add_action( 'init', array($this, 'email_templates_post_type'), 0 ); 
	}	 
	public function email_templates_post_type() {  
		$labels = array(
			'name'                => _x( 'Email Templates', 'Post Type General Name' ),
			'singular_name'       => _x( 'Email Template', 'Post Type Singular Name' ),
			'menu_name'           => __( 'Email Templates' ),
			'parent_item_colon'   => __( 'Parent Email Template' ),
			'all_items'           => __( 'All Email Template' ),
			'view_item'           => __( 'View Email Template' ),
			'email_templatesd_new_item'        => __( 'Email Templated New Email Template' ),
			'email_templatesd_new'             => __( 'Email Templated New' ),
			'edit_item'           => __( 'Edit Email Template' ),
			'update_item'         => __( 'Update Email Template' ),
			'search_items'        => __( 'Search Email Template' ),
			'not_found'           => __( 'Not Found' ),
			'not_found_in_trash'  => __( 'Not found in Trash' ),
		);
		$args = array(
			'label'               => __( 'Email Templates' ),
			'description'         => __( 'Email Template Description' ), 
			'labels'              => $labels, 
			'supports'            => array( 'title', 'editor'), 
			'taxonomies'          => array(),
			'hierarchical'        => false,
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => false, 
			'amin_bar'  		  => false,
			'menu_position'       => 5,
			'menu_icon'           => 'dashicons-email-alt',
			'can_export'          => true,
			'has_archive'         => false,
			'exclude_from_search' => false,
			'publicly_queryable'  => false,
			'capability_type'     =>  'post',  
			'map_meta_cap'		  => true,  
		);
		register_post_type( 'email_templates', $args );
		
	 
	}
}
?>