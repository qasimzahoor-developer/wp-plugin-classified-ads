<?php
/**
 * Init Class.
 **/ 
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} 
class classified_init {
	/** Class constructor */
	public function __construct() {
		if(!session_id()) session_start(); 
		add_action('wp_footer', [$this, 'php_loadtime_num_queries_footer'], 100); 
		add_action('init', [$this, 'check_access'], 10, 0);
		add_action('init', [$this, 'rewite'], 10, 0);
		add_action('init', [$this, 'mailer'], 10, 0);
		add_action('parse_query', [$this, 'redirect_rout']); 
		add_filter( 'query_vars', [$this, 'register_query_vars']);  
		add_action('admin_enqueue_scripts',  [$this, 'admin_assets']);  
		$this->register_post_texonomies();
		$this->load_widgets();
	}
	public function check_access(){ 
		if ( !is_user_logged_in() ){ return ; }  
		else{
			$user = wp_get_current_user();
			$_not_active = get_user_meta( $user->ID, '_not_active', true ); 
			$path =  isset($_SERVER['REQUEST_URI'])? trim(strtok($_SERVER['REQUEST_URI'], '?'), '/') : '';
			if(!empty($_not_active)){  
				if(!empty($path) AND in_array($path, array( 'logout', 'verify', 'resend', 'settings','update-account' )) ){ 
					return ;  
				 }
				require_once(locate_template('notice_disabled.php')); 
				exit; 
			}
			return;
		} 
	}  
	protected function register_post_texonomies(){
		//Post
		require_once('inc/templater.php');
		require_once('inc/post_type_ads.php');
		require_once('inc/post_type_email_templates.php');
		require_once('inc/post_type_ads_texonomy.php');
		new post_type_ad();
		new post_type_email_templates();
		new post_type_ad_texonomy();
		new PageTemplater(); 
		
	}
	protected function load_widgets(){   
		//Widgets 
		//require_once('widgets/ad-filters.php');
		//require_once('widgets/recomended-ads.php');
	}
	public function admin_assets(){
		$wp_scripts = wp_scripts(); 
		wp_enqueue_script('jquery-ui-datepicker'); 
		wp_enqueue_script('upload-file-js', '//hayageek.github.io/jQuery-Upload-File/4.0.11/jquery.uploadfile.min.js');
		wp_enqueue_style('upload-file-css', '//hayageek.github.io/jQuery-Upload-File/4.0.11/uploadfile.css');
		wp_enqueue_style('jquery-ui-css', sprintf('//ajax.googleapis.com/ajax/libs/jqueryui/%s/themes/smoothness/jquery-ui.css', $wp_scripts->registered['jquery-ui-core']->ver) );
	}
	protected function assets(){
		
	}
	public function register_query_vars( $vars ) {
		$vars[] = 'route';
		$vars[] = 'route-ajax'; 
		return $vars;
	}
	public function rewite(){
		add_rewrite_rule('^login/?','index.php?route=login','top');
		add_rewrite_rule('^logout/?','index.php?route=logout','top');
		add_rewrite_rule('^register/?','index.php?route=register','top'); 
		add_rewrite_rule('^forgot-password/?','index.php?route=forgot-password','top');
		add_rewrite_rule('^new-ad/?','index.php?route=new-ad','top');
		add_rewrite_rule('^my-ads/?','index.php?route=my-ads','top');
		add_rewrite_rule('^settings/?','index.php?route=settings','top');
		add_rewrite_rule('^verify/?','index.php?route=verify','top');
		add_rewrite_rule('^resend/?','index.php?route=resend','top'); 
		
		add_rewrite_rule('^upload-photo/?','index.php?route-ajax=upload-photo','top');
		add_rewrite_rule('^update-account/?','index.php?route-ajax=update-account','top');
		flush_rewrite_rules();
	}    
	public function redirect_rout($template) {  
		global $wp_query;
		if(isset($wp_query->query_vars['route-ajax'])){ 
			$route = $wp_query->query_vars['route-ajax'];
			if($route == 'upload-photo'){ require_once('inc/create_ad.php'); createAd::upload_photo();   } 
			if($route == 'update-account'){ require_once('inc/user_account.php');   }
			exit;
		}
		if(isset($wp_query->query_vars['route'])){ 
			$route = $wp_query->query_vars['route'] ;
			if(in_array($route, array('login', 'logout', 'register', 'forgot-password', 'settings', 'my-ads'))) require_once('inc/user_account.php');
			if(in_array($route, array('verify', 'resend'))) require_once('inc/emails.php'); 
			//echo memory_get_usage() . "\n"; 
			exit; 
		}
		return $template;
	}
	public function mailer( ) {
		//require_once('inc/emails.php');
		//new mailer(); 
	}
	public function add_texonomy(){
		
		//remove_role( 'employer' );
		/*$result = add_role(
    'employer', 
    __( 'Employer' ),
    array('publish_ads' => true,
        'edit_ads' => true,
        'edit_others_ads' => true,
        'delete_ads' => true,
        'delete_others_ads' => true,
        'read_private_ads' => true,
        'edit_ad' => true,
        'delete_ad' => true,
        'read_ad' => true,)  
);*/  
		
		return;   exit; 
		$wpdb2 = new WPDB( 'root', '', 'adnow', 'localhost');
		$sql = "SELECT * FROM `oc_t_category` ct
		LEFT JOIN `oc_t_category_description` cd  ON cd.fk_i_category_id = ct.pk_i_id WHERE ct.fk_i_parent_id IS NOT NULL;
		";
		$result = $wpdb2->get_results( $sql, 'ARRAY_A' );  
		
		//print_r(($result)); exit; 
		$i = 0; 
		foreach($result as $row){   
				//$sql2 = "SELECT s_slug FROM `oc_t_category_description` WHERE fk_i_category_id=".$row['fk_i_parent_id'].";";
				//$result2 = $wpdb2->get_results( $sql2, 'ARRAY_A' );
				 //$term  = get_term_by('slug', $result2[0]['s_slug'], 'listings');
				 //print_r($term->term_id); exit;   
				 $parent  = (isset($row['fk_i_parent_id']) AND !empty($row['fk_i_parent_id']))? $row['fk_i_parent_id']+1 : NULL;
				if($parent != NULL){
					$term = wp_insert_term($row['s_name'], 'listings', array('parent'=> $parent, 'slug' => $row['s_slug'])); 
					print_r($term);// exit;
					$i++;
				}
		} 
		print_r($i); exit; 
		
	}  
	public function php_loadtime_num_queries_footer() {
	  echo '<p style="background:#D33C44; color:#fff; padding:5px; margin:0px;"><small>', get_num_queries(), __(' queries, ');
		timer_stop(1);
	  echo __(' seconds.'), '</small></p>';
	}
		
}











