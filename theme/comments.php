<?php
/**
 * Comments template. Documentation pages keep comments intentionally quiet.
 *
 * @package DocsPress
 */

if ( post_password_required() ) {
	return;
}

if ( have_comments() ) :
	?>
	<section class="comments-area">
		<h2><?php esc_html_e( 'Discussion', 'docspress' ); ?></h2>
		<ol class="comment-list"><?php wp_list_comments(); ?></ol>
		<?php the_comments_navigation(); ?>
	</section>
	<?php
endif;

if ( comments_open() ) {
	comment_form();
}
