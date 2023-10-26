<?php
class Recomended_ads_Widget extends WP_Widget {
 
    function __construct() {
        parent::__construct(
            'recomended-ads',  // Base ID
            'Recomended ads'   // Name
        );
 
        add_action( 'widgets_init', function() {
            register_widget( 'Recomended_ads_Widget' );
        });
		add_action( 'widgets_init', [$this, 'recomended_ads_widgets_init'] );
    }
 
    public $args = array(  
        'before_title'  => '<h4 class="widgettitle">',
        'after_title'   => '</h4>',
        'before_widget' => '<div class="widget-wrap">',
        'after_widget'  => '</div></div>'
    );
 
   public function recomended_ads_widgets_init() {
			register_sidebar( array(
				'name' => __( 'Recomended ads', 'recomended_ads' ),
				'id' => 'recomended-ads', 
				'before_widget' => '<div  class="recomended-ads">', 
				'after_widget' => '</div>',
				'before_title' => '<h2  class="title">', 
				'after_title' => '</h2>', 
			) );
	}
		   
    public function widget( $args, $instance ) {
		$count = ! empty( $instance['count'] ) ? $instance['count'] : esc_html__( '', 'text_domain' );
		print $count;
    }
 
    public function form( $instance ) { 
        $count = ! empty( $instance['count'] ) ? $instance['count'] : esc_html__( '', 'text_domain' );
			print '<p><label for="'.esc_attr( $this->get_field_id( 'recomended_ads_rows' ) ).'">'.esc_attr_e( 'No. of posts', 'text_domain' ).'</label>';
			print '<input class="widefat" id="'.esc_attr( $this->get_field_id( 'count' ) ).'" name="'.esc_attr( $this->get_field_name( 'count' ) ).'" type="text" value="'.esc_attr( $count ).'"/>'; 
 
    }
 
    public function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        //$instance['recomended_ads'] = ( !empty( $new_instance['recomended_ads'] ) ) ? ($new_instance['recomended_ads']) : array();
		$instance['count'] = ( !empty( $new_instance['count'] ) ) ? (int)$new_instance['count'] : 8;
        return $instance;
    }
	private function get_terms_list($taxonomy){ 
		global $wpdb;
		$result = $wpdb->get_results("SELECT tr.term_id, tr.name, tt.taxonomy
		FROM $wpdb->terms tr  
		LEFT JOIN $wpdb->term_taxonomy tt ON tt.term_id=tr.term_id
					WHERE tt.taxonomy IN ('".implode('\',\'', $taxonomy)."') AND tt.count > 0 ORDER BY tt.count DESC;");
		if($wpdb->last_error !== ''){ var_dump($wpdb->last_error); print '<br>'.$wpdb->last_query; exit; }
		return $result;
	}
 
}
$Recomended_ads_Widget = new Recomended_ads_Widget();