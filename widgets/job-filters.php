<?php
class ads_Filter_Widget extends WP_Widget {
 
    function __construct() {
 
        parent::__construct(
            'ads-filter',  // Base ID
            'ads Filter'   // Name
        );
 
        add_action( 'widgets_init', function() {
            register_widget( 'ads_Filter_Widget' );
        });
		add_action( 'widgets_init', [$this, 'ads_filter_widgets_init'] );
 
    }
 
    public $args = array(  
        'before_title'  => '<h4 class="widgettitle">',
        'after_title'   => '</h4>',
        'before_widget' => '<div class="widget-wrap">',
        'after_widget'  => '</div></div>'
    );
 
   public function ads_filter_widgets_init() {
			register_sidebar( array(
				'name' => __( 'ads Filter', 'ads_filter' ),
				'id' => 'ads-filter', 
				'before_widget' => '<div  class="ads-filter">', 
				'after_widget' => '</div>',
				'before_title' => '<h2  class="title">', 
				'after_title' => '</h2>', 
			) );
	}
		   
    public function widget( $args, $instance ) {
		$texonomies = (isset($instance['ad_filters']) AND !empty($instance['ad_filters']))?  $instance['ad_filters'] : array();
		$count = ! empty( $instance['count'] ) ? $instance['count'] : esc_html__( '', 'text_domain' );
		$terms = $this->get_terms_list($texonomies);
		//print_r($terms); exit;
		/*$terms = get_terms( array(
			'taxonomy' => $texonomies,
			'orderby' => 'count',
			'order' => 'DESC',
		) ); */
		print '<div id="adFilter" class="accordion"> ';
		foreach($texonomies as $texonomy): 
		$texonomy = ucfirst($texonomy);
		print '<div class="card">
                <div class="card-header p-0" id="ad'.$texonomy.'H">
                  <h5 class="mb-0">
                    <button class="btn btn-link" data-toggle="collapse" data-target="#ad'.$texonomy.'" aria-expanded="false" aria-controls="collapsead'.$texonomy.'" type="button">
                      ads by '.$texonomy.'
                    </button>
                  </h5>
                </div>
            
                <div id="ad'.$texonomy.'" class="collapse show togglediv" aria-labelledby="ad'.$texonomy.'"  data-parent="#ad'.$texonomy.'">
                  <div class="card-body p-1 toggleme">
                  
                  <div class="form-check">';
                  foreach($terms as $term): if($texonomy != ucfirst($term->taxonomy)) continue;
                          print '<label class="form-check-label checkRadio" for="'.$texonomy.$term->term_id.'">
                            '.$term->name.'
                            <input class="form-check-input"  name="'.$texonomy.'[]" type="checkbox" value="'.$term->term_id.'>" id="'.$texonomy.$term->term_id.'">
                            <span class="checkmark"></span>
                            <span class="float-right">2</span>
                          </label>';
                endforeach; 
           print '             </div>
                  </div>
                  <div class="col-12"><a class="toggleParent" data-toggle-text="Show more" data-toggled-text="Show less" href="javascript:void(0);">Show more</a></div>
                </div>
              </div><!--card-->';
			  
			endforeach;
			print '<div class="filterBottom">
              	<div class="btn-group" role="group" aria-label="Third group">
                <button type="submit" class="btn btn-primary">Filter Results</button>
              </div>
              <div class="btn-group" role="group" aria-label="Third group">
                <button type="button" class="btn btn-secondary">Reset</button>
              </div>
              </div><!--filterBottom-->
              <span id="filterViewPort"></span>';
			  print '</div><!--#adFilter-->';
    }
 
    public function form( $instance ) { 
        $count = ! empty( $instance['count'] ) ? $instance['count'] : esc_html__( '', 'text_domain' );
		$taxonomies = get_object_taxonomies('ads');
		$selected = (isset($instance['ad_filters']) AND !empty($instance['ad_filters']))?  $instance['ad_filters'] : array();
		print '<p><label for="'.esc_attr( $this->get_field_id( 'ad_filters' ) ).'">'.esc_attr_e( 'Filterable By:', 'text_domain' ).'</label>';
		printf (
                '<select multiple="multiple" name="%s[]" id="%s" class="widefat" size="15" style="margin-bottom:10px">',
                esc_attr( $this->get_field_name( 'ad_filters' ) ),
                esc_attr( $this->get_field_name( 'ad_filters' ) )
            );

            // Each individual option
            foreach( $taxonomies as $taxonomy )
            {  
                printf(
                    '<option value="%s" %s style="margin-bottom:3px;">%s</option>',
                    $taxonomy,
                    (is_array($selected) AND in_array( $taxonomy, $selected)) ? 'selected="selected"' : '',
                    ucfirst($taxonomy)
                );
            }

            echo '</select></p>';
			print '<p><label for="'.esc_attr( $this->get_field_id( 'ad_filters_rows' ) ).'">'.esc_attr_e( 'Max. Filter rows per category', 'text_domain' ).'</label>';
			print '<input class="widefat" id="'.esc_attr( $this->get_field_id( 'count' ) ).'" name="'.esc_attr( $this->get_field_name( 'count' ) ).'" type="text" value="'.esc_attr( $count ).'"/>'; 
 
    }
 
    public function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $instance['ad_filters'] = ( !empty( $new_instance['ad_filters'] ) ) ? ($new_instance['ad_filters']) : array();
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
$ads_Filter_Widget = new ads_Filter_Widget();









