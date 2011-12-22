<?php

/*
Plugin Name: Sticky Posts Widget
Plugin URI: http://www.benhuson.co.uk/wordpress-plugins/sticky-posts-widget/
Description: A simple widget that will display a list of your sticky posts.
Author: Ben Huson
Version: 1.0
Author URI: http://www.benhuson.co.uk
*/

if ( !class_exists( 'WP_Widget_Sticky_Posts' ) ) {
	
	class WP_Widget_Sticky_Posts extends WP_Widget {
	
		function WP_Widget_Sticky_Posts() {
			
			$widget_ops = array( 'classname' => 'widget_sticky_posts', 'description' => __( 'Sticky posts on your site' ) );
			$this->WP_Widget( 'sticky-posts', __( 'Sticky Posts' ), $widget_ops );
			$this->alt_option_name = 'widget_sticky_posts';
	
			add_action( 'save_post', array( &$this, 'flush_widget_cache' ) );
			add_action( 'deleted_post', array( &$this, 'flush_widget_cache' ) );
			add_action( 'switch_theme', array( &$this, 'flush_widget_cache' ) );
			
		}
	
		function widget( $args, $instance ) {
			
			$cache = wp_cache_get( 'widget_sticky_posts', 'widget' );
	
			if ( !is_array( $cache ) )
				$cache = array();
	
			if ( isset( $cache[$args['widget_id']] ) ) {
				echo $cache[$args['widget_id']];
				return;
			}
	
			ob_start();
			extract( $args );
	
			$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Sticky Posts' ) : $instance['title'], $instance, $this->id_base );
			if ( !$number = (int) $instance['number'] )
				$number = 10;
			else if ( $number < 1 )
				$number = 1;
			else if ( $number > 15 )
				$number = 15;
	
			$r = new WP_Query( array( 'showposts' => $number, 'nopaging' => 0, 'post_status' => 'publish', 'caller_get_posts' => 1, 'post__in' => get_option( 'sticky_posts' ) ) );
			
			if ( $r->have_posts() ) :
				
				echo $before_widget;
				
				if ( $title ) echo $before_title . $title . $after_title;
				echo '<ul>';
				while ( $r->have_posts() ) : $r->the_post();
					?>
					<li><a href="<?php the_permalink(); ?>" title="<?php echo esc_attr( get_the_title() ? get_the_title() : get_the_ID() ); ?>"><?php if ( get_the_title() ) the_title(); else the_ID(); ?></a></li>
					<?php
				endwhile;
				echo '</ul>';
				echo $after_widget;
				
				// Reset the global $the_post as this query will have stomped on it
				wp_reset_postdata();
	
			endif;
	
			$cache[$args['widget_id']] = ob_get_flush();
			wp_cache_set( 'widget_sticky_posts', $cache, 'widget' );
		}
	
		function update( $new_instance, $old_instance ) {
			
			$instance = $old_instance;
			$instance['title'] = strip_tags( $new_instance['title'] );
			$instance['number'] = (int) $new_instance['number'];
			$this->flush_widget_cache();
	
			$alloptions = wp_cache_get( 'alloptions', 'options' );
			if ( isset( $alloptions['widget_sticky_posts'] ) )
				delete_option( 'widget_sticky_posts' );
	
			return $instance;
			
		}
	
		function flush_widget_cache() {
			
			wp_cache_delete( 'widget_sticky_posts', 'widget' );
			
		}
	
		function form( $instance ) {
			
			$title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
			if ( !isset($instance['number']) || !$number = (int) $instance['number'] )
				$number = 5;
			?>
			<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" /></p>
	
			<p><label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of posts to show:' ); ?></label>
			<input id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="text" value="<?php echo $number; ?>" size="3" /></p>
			<?php
			
		}
		
	}
	
	function init_WP_Widget_Sticky_Posts() {
	
		register_widget( 'WP_Widget_Sticky_Posts' );
		
	}
	
	add_action( 'widgets_init', 'init_WP_Widget_Sticky_Posts' );

}

?>