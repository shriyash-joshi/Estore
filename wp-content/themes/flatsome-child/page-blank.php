<?php
/*
Template name: Page - Full Width
*/
get_header(); ?>

<?php do_action( 'flatsome_before_page' ); ?>

<div id="content" role="main" class="content-area">
	<div class="row row-main">
		<div class="large-12 col">
			<div class="col-inner">
				<?php while ( have_posts() ) : the_post(); ?>

					<?php the_content(); ?>
				
				<?php endwhile; // end of the loop. ?>
			</div>
		</div>
	</div>
</div>

<?php do_action( 'flatsome_after_page' ); ?>

<?php get_footer(); ?>
