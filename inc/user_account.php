<?php
/**
 * UserAccount Class.
 **/
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
new UserAccount();
class UserAccount {
	public function __construct() { 
		global $wp_query;
		$route = isset($wp_query->query_vars['route'])? $wp_query->query_vars['route'] :  $wp_query->query_vars['route-ajax'];
		if($route=='login') self::login();
		if($route=='logout') self::logout();
		if($route=='forgot-password') self::forgot_password();
		if($route=='new-ad') self::register();
		if($route=='register') self::register();
		if($route=='my-ads') self::register();
		if($route=='settings') self::settings();
		if($route == 'update-account') self::update();  
	}
	public static function login(){   
		if ( is_user_logged_in() ){ wp_redirect(get_home_url()); }
		if(isset($_POST) AND !empty($_POST)){
			$error = new WP_Error();
			if(!csrf_verify('ad_login', $_POST['_csrfToken'])){ $error->add('invalid_requirest', __('<strong>ERROR</strong>: Request not valid.')); }
			if(empty($_POST['email'])){ $error->add('empty_username', __('<strong>ERROR</strong>: Email field is empty.')); }
			if(isset($_POST['email']) AND !empty($_POST['email']) AND !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)){ $error->add('invalid_username', __('<strong>ERROR</strong>: Email is invalid.')); }
			if(empty($_POST['password'])){ $error->add('empty_password', __('<strong>ERROR</strong>: Password field is empty.')); }
			if(empty($error->errors)){
				$creds = array(
					'user_login'    => sanitize_text_field(esc_sql($_POST['email'])),
					'user_password' => sanitize_text_field(esc_sql($_POST['password'])),
					'remember'      => (isset($_POST['remember']) AND $_POST['remember']==1)? true : false
				);
				$user = wp_signon( $creds ); 
				if ( is_wp_error( $user ) ) { 
					$error->add('invalid_credentials', __('<strong>ERROR</strong>: Email or Password is wrong.'));
				}else{
					wp_redirect(get_home_url());
				}
			} 
		}
		add_filter( 'wp_title', function(){ return 'Login'; }, 10, 2 ); 
		require_once(locate_template('login.php')); 
	}
	public static function logout(){
		wp_logout();
		wp_redirect(get_home_url()); 
		exit();
	}
	public static function forgot_password(){
		if ( is_user_logged_in() ){ wp_redirect(get_home_url()); }  
		exit;
	}
	public static function register(){ 
		if ( is_user_logged_in() ){ wp_redirect(get_home_url()); }   
		if(isset($_POST) AND !empty($_POST)){
			$error = new WP_Error();
			if(!csrf_verify('ad_signup', $_POST['_csrfToken'])){ $error->add('invalid_requirest', __('<strong>ERROR</strong>: Request not valid.')); }
			if(!isset($_POST['first_name']) || empty($_POST['first_name'])){ $error->add('empty_first_name', __('<strong>ERROR</strong>: First name required.')); }
			if(!isset($_POST['last_name']) || empty($_POST['last_name'])){ $error->add('empty_last_name', __('<strong>ERROR</strong>: Last name required.')); }
			if(empty($_POST['email'])){ $error->add('empty_username', __('<strong>ERROR</strong>: Email field is empty.')); }
			if(isset($_POST['email']) AND !empty($_POST['email']) AND !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)){ $error->add('invalid_username', __('<strong>ERROR</strong>: Email is not valid.')); }
			if(empty($error->get_error_messages('invalid_username')) AND email_exists($_POST['email'])){  $error->add('exists_email', __('<strong>ERROR</strong>: Email already exists.'));  }
			if(empty($_POST['password'])){ $error->add('empty_password', __('<strong>ERROR</strong>: Password field is empty.')); }
			if(!empty($_POST['password']) AND strlen($_POST['password']) < 8){ $error->add('short_password', __('<strong>ERROR</strong>: Password should be atleast 8 chracter long.')); }
			if(empty($_POST['confirm_password'])){ $error->add('empty_confirm_password', __('<strong>ERROR</strong>: Confirm password field is empty.')); }
			if(!empty($_POST['password']) AND !empty($_POST['confirm_password']) AND ($_POST['password' ]!= $_POST['confirm_password'])){ $error->add('empty_password_nomatch', __('<strong>ERROR</strong>: Password and Confirm password does not match.')); }
			if(empty($error->errors)){
				$registration_data = array( 
					'user_login'    => sanitize_text_field(esc_sql($_POST['email'])),
					'user_email' => sanitize_text_field(esc_sql($_POST['email'])),
					'user_pass' => sanitize_text_field(esc_sql($_POST['password'])),
					'display_name' => sanitize_text_field(esc_sql($_POST['first_name'].' '.$_POST['last_name'])),
					'nickname' => sanitize_text_field(esc_sql($_POST['first_name'].' '.$_POST['last_name'])), 
					'first_name' => sanitize_text_field(esc_sql($_POST['first_name'])),
					'last_name' => sanitize_text_field(esc_sql($_POST['last_name'])), 
					'show_admin_bar_front' => 'false',   
				); 
				$user = wp_insert_user( $registration_data );   
				if ( is_wp_error( $user ) ) { 
					$error->add('invalid_error', __('<strong>ERROR</strong>: Something went wrong, please try again.'));
				}else{
					wp_new_user_notification($user, null, 'both'); 
					add_user_meta($user, '_not_active', true);  
					$creds = array(
						'user_login'    => sanitize_text_field(esc_sql($_POST['email'])), 
						'user_password' => sanitize_text_field(esc_sql($_POST['password'])), 
						'remember'      => false
					);
					$user = wp_signon( $creds );  
					if ( is_wp_error( $user ) ) { wp_redirect(get_home_url()); } else{ wp_redirect(get_home_url().'/login/'); }
				}
			}
		}
		add_filter( 'wp_title', function(){ return 'Signup Now'; }, 10, 2 );
		require_once(locate_template('register.php'));
		exit;
	}
	public static function settings(){ 
		if ( !is_user_logged_in() ){ wp_redirect(get_home_url()); }
		$user = wp_get_current_user();
		add_filter( 'wp_title', function(){ return 'Account Settings'; }, 10, 2 );
		require_once(locate_template('settings.php'));
		exit;
	}
	public static function update(){ 
		 $error = new WP_Error();
		 if($_SERVER['REQUEST_METHOD']!== 'POST'){ $error->add('InvalidMethod', __('Request method not valid.')); wp_send_json_error( $error ); }
		 if(isset($_POST['_csrfToken']) AND !csrf_verify('update_account', $_POST['_csrfToken'])){ $error->add('InvalidRequest', __('Request not valid.')); wp_send_json_error( $error ); }
		 //update name
		 if(isset($_POST['_act']) AND csrf_verify('update_name', $_POST['_act'])){
			if(!isset($_POST['first_name']) || empty($_POST['first_name'])){ $error->add('first_name', __('First name required.')); }
			if(!isset($_POST['last_name']) || empty($_POST['last_name'])){ $error->add('last_name', __('Last name required.')); }
			$userID = get_current_user_id(); 
			$first_name = sanitize_text_field(esc_sql($_POST['first_name']));
			$last_name = sanitize_text_field(esc_sql($_POST['last_name']));
			$nickname = $first_name .' '. $last_name;
			if(empty($error->errors)){
				$user_id = wp_update_user( array( 'ID' => $userID, 'first_name'=>$first_name, 'last_name'=>$last_name, 'nickname'=>$nickname, 'display_name'=>$nickname) ); 
				if ( is_wp_error( $user_id ) ) {
					$error->add('NotUpdated', __('Something went wrong, try again.')); 
					wp_send_json_error( $error );
				} else {
					wp_send_json(['success'=>true,'message'=>'Name has been updated']);
				}
			}else{
				wp_send_json_error( $error );
			}
			 
		 }
		 //change email
		 if(isset($_POST['_act']) AND csrf_verify('change_email', $_POST['_act'])){ 
			 if(empty($_POST['email'])){ $error->add('email', __('Email field is empty.')); }
			 if(isset($_POST['email']) AND !empty($_POST['email']) AND !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)){ $error->add('email', __('Email is not valid.')); }
			 if(empty($error->get_error_messages('email')) AND email_exists($_POST['email'])){  $error->add('email', __('Email already exists.'));  }
			$userID = get_current_user_id(); 
			$email = sanitize_text_field(esc_sql($_POST['email']));
			if(empty($error->errors)){
				global $wpdb;   
				//$user_id = wp_update_user( array( 'ID' => $userID, 'user_email'=>$email, 'user_nicename'=>$email ) );   
				//$user_login = $wpdb->update($wpdb->users, array('user_login' => $email), array('ID' => $user_id));
				$_POST['user_id'] = $userID;
				$change_req = send_confirmation_on_profile_email();
				if ( $change_req == false AND !is_null($change_req) ) {
					$error->add('NotUpdated', __('Something went wrong, try again.')); 
					wp_send_json_error( $error );
				} else { 
					wp_send_json(['success'=>true,'message'=>'A confirmation email is sent to '.$email.'.']);
				}
			}else{
				wp_send_json_error( $error );
			} 
			 
		 }
		 //change password
		 if(isset($_POST['_act']) AND csrf_verify('change_password', $_POST['_act'])){
			 if(empty($_POST['old_password'])){ $error->add('old_password', __('Old password is required.')); }
			 if(empty($_POST['new_password'])){ $error->add('new_password', __('New Password field is empty.')); }
			 if(empty($_POST['confirm_n_password'])){ $error->add('confirm_n_password', __('Confirm password field is empty.')); }
			 if($_POST['new_password'] !== $_POST['confirm_n_password']){ $error->add('new_password', __('New password not match with confirm password.')); }
			 $userID = get_current_user_id(); 
			 if(empty($error->errors)){
				global $wpdb;   
				//$user_id = wp_update_user( array( 'ID' => $userID, 'user_email'=>$email, 'user_nicename'=>$email ) );   
				//$user_login = $wpdb->update($wpdb->users, array('user_login' => $email), array('ID' => $user_id));
				$_POST['user_id'] = $userID;
				$change_req = send_confirmation_on_profile_email();
				if ( $change_req == false AND !is_null($change_req) ) {
					$error->add('NotUpdated', __('Something went wrong, try again.')); 
					wp_send_json_error( $error );
				} else { 
					wp_send_json(['success'=>true,'message'=>'Passwords updated.']);
				}
			}else{
				wp_send_json_error( $error );
			}
		 }
		 // next....
		var_dump($error);
		exit;
	}
}

