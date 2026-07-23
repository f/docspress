<?php
/**
 * Customizable site homepage.
 *
 * @package DocsPress
 */

if ( 'documentation' === get_theme_mod( 'docspress_homepage_layout', 'landing' ) && 'page' === get_option( 'show_on_front' ) ) {
	require get_template_directory() . '/page.php';
	return;
}

get_header();

$is_static_front = 'page' === get_option( 'show_on_front' );
$front_page      = null;
if ( $is_static_front && have_posts() ) {
	the_post();
	$front_page = get_post();
}

?>
<main class="homepage-main" id="main-content">
	<?php if ( $front_page ) : ?>
		<?php $front_content = trim( get_the_content( null, false, $front_page ) ); ?>
		<?php if ( $front_content ) : ?>
			<section class="homepage-content entry-content">
				<?php echo apply_filters( 'the_content', $front_content ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Filtered WordPress post content. ?>
				<?php wp_link_pages(); ?>
			</section>
		<?php endif; ?>
	<?php endif; ?>

	<?php if ( get_theme_mod( 'docspress_homepage_show_latest_posts', true ) ) : ?>
		<section class="homepage-updates" id="latest-updates">
			<header class="section-heading">
				<p class="entry-kicker"><?php esc_html_e( 'From the site', 'docspress' ); ?></p>
				<h2 data-customize-homepage-posts-title><?php echo esc_html( get_theme_mod( 'docspress_homepage_posts_title', __( 'Latest updates', 'docspress' ) ) ); ?></h2>
			</header>
			<div class="homepage-card-grid">
				<?php
				if ( $is_static_front ) {
					$latest_posts = new WP_Query(
						array(
							'post_type'           => 'post',
							'post_status'         => 'publish',
							'posts_per_page'      => min( 6, max( 1, absint( get_theme_mod( 'docspress_homepage_posts_count', 3 ) ) ) ),
							'ignore_sticky_posts' => true,
						)
					);
				} else {
					$latest_posts = $wp_query;
				}

				if ( $latest_posts->have_posts() ) :
					while ( $latest_posts->have_posts() ) :
						$latest_posts->the_post();
						get_template_part( 'template-parts/content', 'card' );
					endwhile;
				else :
					?>
					<div class="empty-state"><strong><?php esc_html_e( 'No updates yet.', 'docspress' ); ?></strong><p><?php esc_html_e( 'Publish a post and it will appear here.', 'docspress' ); ?></p></div>
					<?php
				endif;

				if ( $is_static_front ) {
					wp_reset_postdata();
				}
				?>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $front_page && docspress_should_show_comments( $front_page->ID ) ) : ?>
		<div class="homepage-discussion">
			<?php comments_template(); ?>
		</div>
	<?php endif; ?>
</main>
<?php
get_footer();
