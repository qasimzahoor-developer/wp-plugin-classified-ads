<?php
/**
 * Init Class.
 **/
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
new mailer();
class mailer{ 
	public function __construct() { 
		global $wp_query;
		add_action( 'phpmailer_init', [$this, 'mailer_config'], 10, 1);
		add_action('wp_mail_failed', [$this, 'log_mailer_errors'], 10, 1);
		add_filter( 'wp_new_user_notification_email',  [$this, 'new_user_notify'], 10, 3);
		add_filter( 'new_user_email_content',  [$this, 'email_change'], 10, 2); 
		$route = isset($_SERVER['REQUEST_URI'])? trim(strtok($_SERVER['REQUEST_URI'], '?'), '/') : ''; 
		if($route=='verify') self::verify(); 
		if($route=='resend') self::resend();
	}
	public function mailer_config(PHPMailer $mailer){
	  $mailer->IsSMTP();
	  $mailer->SMTPAuth = true;
	  $mailer->Host = "sv87.example.com"; // your SMTP server
	  $mailer->Username = "sales@example.com";
	  $mailer->Password = "passs9999";  
	  $mailer->From = 'sales@example.com';
	  $mailer->FromName = 'Ad Now Online';
	  $mailer->Port = 25;
	  $mailer->SMTPDebug = 0; // write 0 if you don't want to see client/server communication in page
	  $mailer->CharSet  = "utf-8";
	  $mailer->IsHTML(true);
	}
	public function log_mailer_errors( $wp_error ){
	  $fn = ABSPATH . '/mail.log'; // say you've got a mail.log file in your server root
	  $fp = fopen($fn, 'a');
	  fputs($fp, "Mailer Error: " . $wp_error->get_error_message() ."\n");
	  fclose($fp);
	}
	public static function verify(){
		if ( !is_user_logged_in() ){ wp_redirect(home_url('/login/')); } 
		global $wpdb; 
		$user = wp_get_current_user();
		//verify user email change
		if(isset($_GET['useremail']) AND !empty($_GET['useremail'])){ 
			$new_email = get_user_meta( $user->ID, '_new_email', true );
			if ( $new_email && hash_equals( $new_email['hash'], $_GET['useremail'] ) ) {
				$email = esc_html( trim( $new_email['newemail'] ) );
				$wpdb->update($wpdb->users, array('user_login' => $email, 'user_email'=>$email, 'user_nicename'=>$email), array('ID' => $user->ID));
				delete_user_meta( $user->ID, '_new_email' ); 
				clean_user_cache($user->ID); 
				$newuser = get_user_by( 'login', $email );  
				wp_set_current_user( $newuser->ID, $newuser->user_login );
				wp_set_auth_cookie( $newuser->ID );
				do_action( 'wp_login', $newuser->user_login, $newuser);    
				setAlert(['type'=>'sucess', 'msg'=>'Email address updated to '.$email ]);  
				wp_redirect( esc_url( site_url('settings') ) );  
			}else{
				setAlert(['type'=>'error', 'msg'=>'Email address not updated, please try again.' ]);
				wp_redirect( esc_url( site_url('settings') ) );
			}
		}
		//verify new user email
		if(isset($_GET['newuser']) AND !empty($_GET['newuser'])){ 
			$validate = check_password_reset_key( $_GET['newuser'], $user->user_login );
			if ( is_wp_error( $validate ) ) {
				setAlert(['type'=>'sucess', 'msg'=>'Your email address '.$email.' is verified now.' ]);
			}else{
				print 'Verified'; 
			}
			var_dump($validate); exit;
		}
	} 
	public static function resend(){
		if ( !is_user_logged_in() ){ wp_redirect(home_url('/login/')); } 
		$__act = (isset($_GET) AND array_diff($_GET, array( 'newuser', 'emailchange', 'passwordchange' )))? $_GET : '';
		if(empty($__act)){ setAlert(['type'=>'error', 'msg'=>'Request type error.' ]); exit; }
		$user = wp_get_current_user();
		$resend_count = get_user_meta($user, '_resend_count', true);
		if((empty($resend_count) OR $resend_count < 5) AND !empty($__act)){  
			wp_new_user_notification($user, null, 'user'); 
			if(empty($resend_count)){
				add_user_meta($user, '_resend_count', 1);
			}else{
				update_user_meta($user, '_resend_count', $resend_count+1);
			} 
			setAlert(['type'=>'sucess', 'msg'=>'An email with new verification code is sent to '.$email ]);  
		}else{
			setAlert(['type'=>'error', 'msg'=>'Faild to verify '.$email.', please change email in settings' ]);
		}
		global $wp;
		wp_redirect( esc_url( home_url( $wp->request ) ) ); exit;
	}
	public function new_user_notify( $wp_new_user_notification_email, $user, $blogname ){   
		global $wpdb;  
		$email_template = $wpdb->get_results( "SELECT $wpdb->posts.post_content FROM $wpdb->posts WHERE 
												$wpdb->posts.post_status = 'publish'  
												AND $wpdb->posts.post_type = 'email_templates'  
												AND $wpdb->posts.post_name = 'new_user_confirm_email'", OBJECT );
		if ( $email_template  ) {   
			$url = esc_url( site_url( 'verify/?newuser=' . get_password_reset_key( $user ) ) );
			//$wpdb->update( $wpdb->users, array( 'user_activation_key' => $user->user_activation_key ), array( 'user_login' => $user->user_login ) );
			$content = $email_template[0]->post_content; 
			$content = str_replace( '###NAME###', $user->first_name.' '.$user->last_name, $content );
			$content = str_replace( '###ACTIVATION_LINK###', '<a href="'.$url.'">'.$url.'</a>', $content );
			$content = str_replace( '###EMAIL###', $user->user_email,  $content);
			$content = str_replace( '###SITENAME###', $blogname,  $content);
			$content = str_replace( '###SITEURL###', home_url(), $content );
			$wp_new_user_notification_email['message'] = $content;
			$wp_new_user_notification_email['subject'] =  __( '[%s] Your account activation required' );
		} 
		//var_dump($wp_new_user_notification_email); exit;
		return $wp_new_user_notification_email; 
	
	}
	public function email_change( $email_text, $new_user_email ){  
		global $wpdb;
		$email_template = $wpdb->get_results( "SELECT $wpdb->posts.post_content FROM $wpdb->posts WHERE 
												$wpdb->posts.post_status = 'publish'  
												AND $wpdb->posts.post_type = 'email_templates' 
												AND $wpdb->posts.post_name = 'email_change'", OBJECT );
		if ( ! $email_template  ) { 
			$error = new WP_Error();
			$error->add('NotUpdated', __('Something went wrong, try again.')); 
			wp_send_json_error( $error ); 
		}else{ 
			$user = wp_get_current_user();  
			$hash = get_user_meta( $user->ID, '_new_email', true); 
			$content = $email_template[0]->post_content;  
			$content = str_replace( '###NAME###', $user->first_name.' '.$user->last_name, $content );
			$content = str_replace( '###RESET_LINK###', esc_url( site_url( 'verify/?useremail=' . $hash['hash'] ) ), $content );
			return $content; 
		}
	}
















}