<?php
/**
 * Init Class.
 **/
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class post_type_ad_texonomy{
	/** Class constructor */
	public function __construct() { 
		add_action( 'init', array($this, 'ad_texonomy_catagory'), 0 ); 
	}
	public function ad_texonomy_catagory() {   
		  $labels = array(
			'name' => _x( 'Catagories', 'taxonomy general name' ),
			'singular_name' => _x( 'Catagory', 'taxonomy singular name' ),
			'search_items' =>  __( 'Search Catagory' ),
			'all_items' => __( 'All Catagories' ),
			'parent_item' => __( 'Parent Catagory' ),
			'parent_item_colon' => __( 'Parent Parent Catagory:' ),
			'edit_item' => __( 'Edit Catagory' ), 
			'update_item' => __( 'Update Catagory' ), 
			'add_new_item' => __( 'Add New Catagory' ),
			'new_item_name' => __( 'New Catagory Name' ),
			'menu_name' => __( 'Catagories' ),
		  ); 	
		 
		  register_taxonomy('listings',array('ads'), array(
			'hierarchical' => true,
			'labels' => $labels,
			'show_ui' => true,
			'show_admin_column' => true,
			'query_var' => true,
			'rewrite' => array( 'slug' => 'listing' )
		  ));
	} 

}
?>