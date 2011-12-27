<?php



/*
Plugin Name: Themeable Sticky Posts
Plugin URI: http://www.benhuson.co.uk
Description: A widget and template tag to display featured sticky posts. The built-in template displays a simple list of links, or you can create a template file in your theme for more complex layouts.
Author: Ben Huson
Version: 1.0
Author URI: http://www.benhuson.co.uk/
*/



add_action( 'widgets_init', create_function( '', 'return register_widget("Themeable_Sticky_Posts_Widget");' ) );



class Themeable_Sticky_Posts_Widget extends WP_Widget {
	
	
	
	/**
	 * Constructor
	 */
	function Themeable_Sticky_Posts_Widget() {
		
		$widget_ops = array(
			'classname'   => 'widget_themeable_sticky_posts',
			'description' => __( 'Themeable Sticky Posts' )
		);
		$this->WP_Widget( 'smSticky', __( 'Themeable Sticky Posts' ), $widget_ops );
		
	}
	
	
	
	/**
	 * Display the widget
	 */
	function widget( $args, $instance ) {
		
		global $wp_query, $post;
		
		extract( $args );
		
		echo $before_widget;
	
		// Title
		$title = apply_filters( 'widget_title', $instance['title'] );
		if ( !empty( $title ) ) {
			echo $before_title . $title . $after_title;
		}
		
		// Number of posts
		if ( !$number = (int)$instance['number'] ) $number = 5;
		else if ( $number < 1 ) $number = 1;
		else if ( $number > 15 ) $number = 15;
		
		// Create Query
		$posts_query = array(
			'post__in'       => get_option( 'sticky_posts' ),
			'posts_per_page' => $number,
			'orderby'        => 'date',
			'post_status'    => 'publish'
		);
		$sticky_posts = new WP_Query( $posts_query );
		
		// The Loop
		echo '<ul>';
		while ( $sticky_posts->have_posts() ) : $sticky_posts->the_post(); update_post_caches( $posts );
			echo '<li>';
			if ( locate_template( array( 'themeable-sticky-post.php', 'themeable-sticky-post-widget.php' ) ) ) {
				get_template_part( 'themeable-sticky-post', 'widget' );
			} else {
				$plugin_templates_path = WP_PLUGIN_DIR . '/' . str_replace( basename( __FILE__ ), '', plugin_basename( __FILE__ ) ) . 'templates/';
				include( $plugin_templates_path . 'list.php' );
			}
			echo '</li>';
		endwhile;
		$post = $wp_query->post;
		setup_postdata( $post );
		echo '</ul>';
		
		echo $after_widget;
		
	}
	
	
	
	/**
	 * Update the settings
	 */
	function update( $new_instance, $old_instance ) {
		
		$instance = $old_instance;
		
		$instance['title']  = strip_tags( $new_instance['title'] );
		$instance['number'] = (int)$new_instance['number'];
		
		return $instance;
		
	}
	
	
	
	/**
	 * Admin form
	 */
	function form( $instance ) {
		
		$title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		if ( !isset( $instance['number'] ) || !$number = (int)$instance['number'] ) $number = 5;
		
		?>
		<div id="themeable-sticky-posts-admin-panel">
			<p><label for="<?php echo $this->get_field_id( 'title' ); ?>">Title:</label>
			<input type="text" class="widefat" name="<?php echo $this->get_field_name( 'title' ); ?>" id="<?php echo $this->get_field_id( 'title' ); ?>" value="<?php echo $instance['title']; ?>" /></p>
			<p><label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of posts to show:' ); ?></label>
			<input id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="text" value="<?php echo $number; ?>" size="3" /></p>
		</div>
		<?php
		
	}
	
	
	
}



?>