<?php
/**
 * Init Class.
 **/
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {  
	exit;
}

class createAd{
	/** Class constructor */ 
	public function __construct() {  

	}
	static function upload_photo() {  print_r($_POST); exit;
		if ( ! function_exists( 'wp_handle_upload' ) ) {
        	require_once( ABSPATH . 'wp-admin/includes/file.php' );
    	}
		if( isset( $_FILES ) AND !empty( $_FILES ) AND  isset( $_FILES['photos'] ) ){
			$file   = $_FILES['photos'];
			$upload = wp_handle_upload($file, array('test_form' => false));
			print_r($upload );
		}
		print '<br>'. $hash = csrf_generate('Page_123');
		print '<br>'.csrf_verify('Page_123', $hash); 
		//print 'uploaded here'; 
	}	 
	
	
}
?>