<?php
class PageTemplater {  
 
	public function __construct() {
				
	} 
	public static function load( $template ) {
		if ( $overridden_template = locate_template($template) ) {
		   load_template( $overridden_template );
		 } else {
		   load_template( dirname( __FILE__ ) . '/templates/'.$template );
		 }
	}
	
} 