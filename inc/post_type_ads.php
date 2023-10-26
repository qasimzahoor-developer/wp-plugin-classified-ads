<?php
/**
 * Init Class.
 **/
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class post_type_ad{
	/** Class constructor */ 
	public function __construct() {  
		add_action( 'init', array($this, 'ad_post_type'), 0 ); 	
		add_action('add_meta_boxes', function() { add_meta_box('ads-expiry-date', 'Expired By', [$this, 'expiry_date_meta_box'], 'ads', 'normal', 'high');});
		add_action('add_meta_boxes', function() { add_meta_box('ads-photos', 'Ad. Photo', [$this, 'photo_meta_box'], 'ads', 'normal', 'high');});
		add_action('add_meta_boxes', function() { add_meta_box('ads-price', 'Ad. Price', [$this, 'price_meta_box'], 'ads', 'normal', 'high');});
		add_action('add_meta_boxes', function() { add_meta_box('ads-seller', 'Seller Deails', [$this, 'seller_meta_box'], 'ads', 'normal', 'high');});
		add_action('save_post', [$this, 'save_all_meta']);
		//add_action( 'pre_get_posts', [$this, 'citywise_post_filter'] );
	}	 
	public function citywise_post_filter($query) {
		//unset($query);// = false;
        remove_all_actions ( '__after_loop');
		return $query;
		}
	public function ad_post_type() {  
		$labels = array(
			'name'                => _x( 'Ads', 'Post Type General Name' ),
			'singular_name'       => _x( 'Ad', 'Post Type Singular Name' ),
			'menu_name'           => __( 'Ads' ),
			'parent_item_colon'   => __( 'Parent Ad' ),
			'all_items'           => __( 'All Ad' ),
			'view_item'           => __( 'View Ad' ),
			'add_new_item'        => __( 'Add New Ad' ),
			'add_new'             => __( 'Add New' ),
			'edit_item'           => __( 'Edit Ad' ),
			'update_item'         => __( 'Update Ad' ),
			'search_items'        => __( 'Search Ad' ),
			'not_found'           => __( 'Not Found' ),
			'not_found_in_trash'  => __( 'Not found in Trash' ),
		);
		$args = array(
			'label'               => __( 'Ads' ),
			'description'         => __( 'Ad Description' ), 
			'labels'              => $labels, 
			'supports'            => array( 'title', 'editor', 'author'), 
			'taxonomies'          => array( 'listings'),
			'hierarchical'        => true,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => true, 
			'show_in_admin_bar'   => true,
			'menu_position'       => 5,
			'menu_icon'           => 'dashicons-megaphone',
			'can_export'          => true,
			'has_archive'         => true,
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'capability_type'     =>  'post', 
			'map_meta_cap'		  => true,  
			'rewrite' => array( 'slug' => '/ad', 'with_front' => false )
		);
		register_post_type( 'ads', $args );
		
	 
	}
	public function photo_meta_box($post) { 
		$photo = get_post_meta( $post->ID, 'photo', true ); 
		?>
        <p>
           <div class="photo-row-content">
                <div id="fileuploader">Upload</div>  
                <div id="extrabutton" class="ajax-file-upload-green">Start Upload</div> 
            </div>
        </p>
        <script>
			jQuery(document).ready(function()  
 				{
					var extraObj = $("#fileuploader").uploadFile({
						url:"http://localhost/adnowonline/upload-photo", 
						fileName:"photos",
						acceptFiles:"image/*",
						maxFileCount:3,
						showPreview:true,
						previewWidth: "100px", 
						extraHTML:function(){ return '<label>Main Image:<select name="default"><option value="1">Yes</option><option value="0" selected="selected">No</option></select></label><input type="hidden" name="post_id" value="<?php echo $post->ID; ?>">'; }, 
						autoSubmit:false, 
					});
				$("#extrabutton").click(function(){ extraObj.startUpload(); });		 
			});
			
			
		</script>
        <?php
	}  
	public function expiry_date_meta_box($post) { 
		$ad_expiry = get_post_meta( $post->ID, 'ad_expiry', true );
		echo '<input type="hidden" name="meta_box_nonce" value="'.wp_create_nonce(basename(__FILE__)).'" />';
    ?> 
    <p>
        <div class="ad_expiry-row-content">
            <label for="ad_expiry"> 
                <?php _e( 'Last Date', 'textdomain' )?>
                <input type="datetime" name="ad_expiry" id="ad_expiry" class="datepicker" value="<?php echo $ad_expiry; ?>" /> 
            </label>   
        </div>
    </p> 
    <script>
		jQuery(function() {
			jQuery( ".datepicker" ).datepicker({ 
				dateFormat : "yy-mm-dd"
			});
		});
    </script> 
    <?php
	} 
	public function price_meta_box($post) { 
		$ad_price = get_post_meta( $post->ID, 'ad_price', true ); 
		?>
        <p>
           <div class="ad_price-row-content">
                <label for="ad_price">  
                    <?php _e( 'Price', 'textdomain' );?>
                    <input type="datetime" name="ad_price" id="ad_price" value="<?php echo $ad_price; ?>" /> 
                </label>   
            </div>
        </p>
        <?php
	}
	public function seller_meta_box($post) { 
		$sname = get_post_meta( $post->ID, 'sname', true ); 
		$semail = get_post_meta( $post->ID, 'semail', true ); 
		$sphone = get_post_meta( $post->ID, 'sphone', true ); 
		?>
        <p>
           <div class="sname-row-content">
                <label for="sname">  
                    <?php _e( 'Seller Name', 'textdomain' );?>
                    <input type="text" name="sname" id="sname" value="<?php echo $sname; ?>" /> 
                </label>   
            </div>
        </p>
        <p>
           <div class="semail-row-content">
                <label for="semail">  
                    <?php _e( 'Seller Email', 'textdomain' );?>
                    <input type="text" name="semail" id="semail" value="<?php echo $semail; ?>" /> 
                </label>   
            </div>
        </p>
        <p>
           <div class="sphone-row-content">
                <label for="sphone">  
                    <?php _e( 'Seller Phone', 'textdomain' );?>
                    <input type="text" name="sphone" id="sphone" value="<?php echo $sphone; ?>" /> 
                </label>   
            </div>
        </p>
        <?php
	}
	public function save_all_meta($post_id) {   
		//ad expiry by  
		if ( isset($_POST['ad_expiry']) AND !empty($_POST['ad_expiry']) ) { 
			update_post_meta($post_id, 'ad_expiry', sanitize_text_field(esc_sql($_POST['ad_expiry'])));
		}
		//ad price  
		if ( isset($_POST['ad_price']) AND !empty($_POST['ad_price']) ) { 
			update_post_meta($post_id, 'ad_price', sanitize_text_field(esc_sql($_POST['ad_price'])));
		}
	}
	
}
?>