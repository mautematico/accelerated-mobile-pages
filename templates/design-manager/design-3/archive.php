<?php global $redux_builder_amp;  ?>
<!doctype html>
<html amp <?php echo AMP_HTML_Utils::build_attributes_string( $this->get( 'html_tag_attributes' ) ); ?>>
<head>
	<meta charset="utf-8">
  <link rel="dns-prefetch" href="https://cdn.ampproject.org">
	<?php
	global $redux_builder_amp;
	if ( is_home() || is_front_page()  || ( is_archive() && $redux_builder_amp['ampforwp-archive-support'] ) ){
		global $wp;
		$current_archive_url = home_url( $wp->request );
		$amp_url 	= trailingslashit($current_archive_url);
		$remove 	= '/'. AMPFORWP_AMP_QUERY_VAR;
		$amp_url 	= str_replace($remove, '', $amp_url) ;
	} 

	if ( is_archive() ) {
		$description 	= get_the_archive_description();
		$sanitizer = new AMPFORWP_Content( $description, array(), 
			apply_filters( 'ampforwp_content_sanitizers',
				array( 
					'AMP_Style_Sanitizer' 		=> array(),
					'AMP_Blacklist_Sanitizer' 	=> array(),
					'AMP_Img_Sanitizer' 		=> array(),
					'AMP_Video_Sanitizer' 		=> array(),
					'AMP_Audio_Sanitizer' 		=> array(),
					'AMP_Iframe_Sanitizer' 		=> array(
						'add_placeholder' 		=> true,
					)
				) ) );
	} ?>
	<link rel="canonical" href="<?php echo $amp_url ?>">
	<meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no">
	<?php do_action( 'amp_post_template_head', $this ); ?>
	<?php
	$amp_component_scripts = $sanitizer->amp_scripts;
	if ( $sanitizer && $amp_component_scripts) {	
		foreach ($amp_component_scripts as $ampforwp_service => $ampforwp_js_file) { ?>
			<script custom-element="<?php echo $ampforwp_service; ?>"  src="<?php echo $ampforwp_js_file; ?>" async></script> <?php
		}
	}?>
	<style amp-custom>
	<?php $this->load_parts( array( 'style' ) ); ?>
	<?php do_action( 'amp_post_template_css', $this ); ?>
	</style>
</head>
<body class="amp_home_body archives_body design_3_wrapper">
<?php $this->load_parts( array( 'header-bar' ) ); ?>

<?php do_action( 'ampforwp_after_header', $this );

if ( get_query_var( 'paged' ) ) {
      $paged = get_query_var('paged');
  } elseif ( get_query_var( 'page' ) ) {
      $paged = get_query_var('page');
  } else {
      $paged = 1;
  }

 ?>

<main>
	<?php do_action('ampforwp_post_before_loop') ?>
	<?php

	    $exclude_ids = get_option('ampforwp_exclude_post');

		$q = new WP_Query( array(
			'post_type'           => 'post',
			'orderby'             => 'date',
			'ignore_sticky_posts' => 1,
			'paged'               => esc_attr($paged),
			'post__not_in' 		  => $exclude_ids,
			'has_password' => false ,
			'post_status'=> 'publish'
		) ); ?>

 	<?php if ( is_archive() ) {
 			the_archive_title( '<h3 class="amp-wp-content page-title">', '</h3>' );
 			
			$arch_desc 		= $sanitizer->get_amp_content();
			if( $arch_desc ) {  ?>
				<div class="amp-wp-content taxonomy-description">
					<?php echo $arch_desc ; ?>
			  </div> <?php
			}
 		} ?>

    <?php if ( have_posts() ) : while ( have_posts() ) : the_post();
  		$ampforwp_amp_post_url = trailingslashit( get_permalink() ) . AMPFORWP_AMP_QUERY_VAR ;
  		$ampforwp_amp_post_url  = trailingslashit( $ampforwp_amp_post_url );

			if( in_array( 'ampforwp-custom-type-amp-endpoint' , $redux_builder_amp ) ) {
	  		if ( $redux_builder_amp['ampforwp-custom-type-amp-endpoint']) {
	  			$ampforwp_amp_post_url = trailingslashit( get_permalink() ) . '?amp';
	  		}
			}?>

		<div class="amp-wp-content amp-loop-list">
			<?php if ( has_post_thumbnail() ) { ?>
				<?php
				$thumb_id = get_post_thumbnail_id();
				$thumb_url_array = wp_get_attachment_image_src($thumb_id, 'medium', true);
				$thumb_url = $thumb_url_array[0];
				?>
				<div class="home-post_image">
					<a href="<?php echo esc_url( $ampforwp_amp_post_url ); ?>">
						<amp-img
						layout="responsive"
						src=<?php echo $thumb_url ?>
						width=450
						height=270
					></amp-img>
				</a>
			</div>
			<?php } ?>

			<div class="amp-wp-post-content">
                <ul class="amp-wp-tags">
					<?php foreach((get_the_category()) as $category) { ?>
					    <li class="amp-cat-<?php echo $category->term_id;?>"><?php echo $category->cat_name ?></li>
					<?php } ?>
                </ul>
				<h2 class="amp-wp-title"> <a href="<?php echo esc_url( $ampforwp_amp_post_url ); ?>"> <?php the_title(); ?></a></h2>


				<?php
					if(has_excerpt()){
						$content = get_the_excerpt();
					}else{
						$content = get_the_content();
					}
				?>
		        <p><?php echo wp_trim_words( strip_shortcodes(  $content ) , '15' ); ?></p>
                <div class="featured_time">
                  <?php
                       printf( _x( '%1$s '. ampforwp_translation( $redux_builder_amp['amp-translator-ago-date-text'],'ago' ), '%2$s = human-readable time difference', 'accelerated-mobile-pages' ),
                             human_time_diff( get_the_time( 'U' ),
                             current_time( 'timestamp' ) ) );
                  ?>
                </div>

		    </div>
            <div class="cb"></div>
	</div>

	<?php endwhile;  ?>

	<div class="amp-wp-content pagination-holder">


		<div id="pagination">
			<div class="next"><?php next_posts_link( ampforwp_translation ($redux_builder_amp['amp-translator-show-more-posts-text'] , 'Show more Posts') ,0 ) ?></div>
					<?php if ( $paged > 1 ) { ?>
						<div class="prev"><?php previous_posts_link( ampforwp_translation($redux_builder_amp['amp-translator-show-previous-posts-text'], 'Show previous Posts') ); ?></div>
					<?php } ?>
			<div class="clearfix"></div>
		</div>
	</div>

	<?php endif; ?>
	<?php wp_reset_postdata(); ?>
	<?php do_action('ampforwp_post_after_loop') ?>
</main>
<?php do_action( 'amp_post_template_above_footer', $this ); ?>
<?php $this->load_parts( array( 'footer' ) ); ?>
<?php do_action( 'amp_post_template_footer', $this ); ?>
</body>
</html>