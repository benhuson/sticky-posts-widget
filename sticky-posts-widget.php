<?php

/*
Plugin Name: Sticky Posts Widget
Plugin URI: http://www.benhuson.co.uk/wordpress-plugins/sticky-posts-widget/
Description: A simple widget that will display a list of your sticky posts.
Author: Ben Huson
Version: 2.0
Author URI: http://www.benhuson.co.uk
*/

if ( ! class_exists( 'WP_Widget_Sticky_Posts' ) ) {

	class WP_Widget_Sticky_Posts extends WP_Widget {

		/**
		 * Constructor
		 */
		function __construct() {

			$widget_ops = array( 'classname' => 'widget_sticky_posts', 'description' => __( 'Sticky posts on your site' ) );
			parent::__construct( 'sticky-posts', __( 'Sticky Posts' ), $widget_ops );
			$this->alt_option_name = 'widget_sticky_posts';

			add_action( 'save_post', array( $this, 'flush_widget_cache' ) );
			add_action( 'deleted_post', array( $this, 'flush_widget_cache' ) );
			add_action( 'switch_theme', array( $this, 'flush_widget_cache' ) );

		}

		/**
		 * Widget
		 *
		 * @param  array  $args      Widget args.
		 * @param  array  $instance  Widget instance.
		 */
		function widget( $args, $instance ) {

			$cache = wp_cache_get( 'widget_sticky_posts', 'widget' );

			if ( ! is_array( $cache ) ) {
				$cache = array();
			}

			if ( ! isset( $args['widget_id'] ) ) {
				$args['widget_id'] = $this->id;
			}

			if ( isset( $cache[ $args['widget_id'] ] ) ) {
				echo $cache[ $args['widget_id'] ];
				return;
			}

			ob_start();
			extract( $args );

			$title = ( ! empty( $instance['title'] ) ) ? $instance['title'] : __( 'Sticky Posts' );
			$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );
			$number = ( ! empty( $instance['number'] ) ) ? absint( $instance['number'] ) : 10;
			if ( ! $number ) {
	 			$number = 10;
	 		}
			$show_date = isset( $instance['show_date'] ) ? $instance['show_date'] : false;

			$sticky_posts = get_option( 'sticky_posts' );

			if ( ! empty( $sticky_posts ) ) {

				$r = new WP_Query( apply_filters( 'widget_sticky_posts_args', array(
					'posts_per_page' => $number,
					'no_found_rows'  => true,
					'post_status'    => 'publish',
					'post__in'       => $sticky_posts
				) ) );

				if ( $r->have_posts() ) :
					echo $before_widget;
					if ( $title ) {
						echo $before_title . $title . $after_title;
					}
					echo '<ul>';
					while ( $r->have_posts() ) : $r->the_post();
						?>
						<li>
							<a href="<?php the_permalink(); ?>"><?php get_the_title() ? the_title() : the_ID(); ?></a>
							<?php if ( $show_date ) : ?>
								<span class="post-date"><?php echo get_the_date(); ?></span>
							<?php endif; ?>
						</li>
						<?php
					endwhile;
					echo '</ul>';
					echo $after_widget;
					wp_reset_postdata();
				endif;

			}

			$cache[$args['widget_id']] = ob_get_flush();
			wp_cache_set( 'widget_sticky_posts', $cache, 'widget' );

		}

		/**
		 * Update
		 *
		 * @param   array  $new_instance  Widget instance.
		 * @param   array  $old_instance  Old widget instance.
		 * @return  array                 Updates widget instance.
		 */
		function update( $new_instance, $old_instance ) {

			$instance = $old_instance;
			$instance['title'] = strip_tags( $new_instance['title'] );
			$instance['number'] = (int) $new_instance['number'];
			$instance['show_date'] = isset( $new_instance['show_date'] ) ? (bool) $new_instance['show_date'] : false;
			$this->flush_widget_cache();

			$alloptions = wp_cache_get( 'alloptions', 'options' );
			if ( isset( $alloptions['widget_sticky_posts'] ) ) {
				delete_option( 'widget_sticky_posts' );
			}

			return $instance;

		}

		/**
		 * Flush Widget Cache
		 */
		function flush_widget_cache() {

			wp_cache_delete( 'widget_sticky_posts', 'widget' );

		}

		/**
		 * Widget Form
		 *
		 * @param  array  $instance  Widget instance.
		 */
		function form( $instance ) {

			$title     = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
			$number    = isset( $instance['number'] ) ? absint( $instance['number'] ) : 5;
			$show_date = isset( $instance['show_date'] ) ? (bool) $instance['show_date'] : false;

			?>
			<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" /></p>

			<p><label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of posts to show:' ); ?></label>
			<input id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="text" value="<?php echo $number; ?>" size="3" /></p>

			<p><input class="checkbox" type="checkbox" <?php checked( $show_date ); ?> id="<?php echo $this->get_field_id( 'show_date' ); ?>" name="<?php echo $this->get_field_name( 'show_date' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'show_date' ); ?>"><?php _e( 'Display post date?' ); ?></label></p>
			<?php

		}

	}

	/**
	 * Init WP_Widget_Sticky_Posts Widgets
	 */
	function init_WP_Widget_Sticky_Posts() {

		register_widget( 'WP_Widget_Sticky_Posts' );

	}

	add_action( 'widgets_init', 'init_WP_Widget_Sticky_Posts' );

}
