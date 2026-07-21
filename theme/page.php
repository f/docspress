<?php
/**
 * Documentation page template.
 *
 * @package DocsPress
 */

get_header();

while ( have_posts() ) :
	the_post();
	$prepared = docspress_prepare_content( apply_filters( 'the_content', get_the_content() ) );
	$adjacent = docspress_get_adjacent_pages( get_the_ID() );
	$summary  = docspress_page_summary();
	$wordpress_edit_url = get_edit_post_link( get_the_ID(), '' );
	if ( ! $wordpress_edit_url ) {
		$wordpress_edit_url = wp_login_url( admin_url( 'post.php?post=' . get_the_ID() . '&action=edit' ) );
	}
	$github_edit_url = docspress_get_github_edit_url( get_the_ID() );
	?>
	<div class="docs-shell">
		<?php get_sidebar( 'docs' ); ?>

		<main class="docs-main" id="main-content">
			<article <?php post_class( 'docs-article' ); ?>>
				<?php if ( get_theme_mod( 'docspress_show_breadcrumbs', true ) ) : ?>
					<?php docspress_breadcrumbs(); ?>
				<?php endif; ?>
				<header class="entry-header">
					<?php if ( get_theme_mod( 'docspress_show_kicker', true ) ) : ?>
						<span class="entry-kicker" data-customize-kicker><?php echo esc_html( get_theme_mod( 'docspress_kicker_label', __( 'Guide', 'docspress' ) ) ); ?></span>
					<?php endif; ?>
					<h1 class="entry-title"><?php the_title(); ?></h1>
					<?php if ( $summary && get_theme_mod( 'docspress_show_summary', true ) ) : ?>
						<p class="entry-summary"><?php echo esc_html( $summary ); ?></p>
					<?php endif; ?>
				</header>

				<div class="entry-content">
					<?php echo $prepared['content']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Filtered WordPress post content. ?>
				</div>

				<?php if ( get_theme_mod( 'docspress_show_edit_link', true ) || ( $github_edit_url && get_theme_mod( 'docspress_show_github_edit_link', true ) ) ) : ?>
					<nav class="page-actions" aria-label="<?php esc_attr_e( 'Page actions', 'docspress' ); ?>">
						<?php if ( get_theme_mod( 'docspress_show_edit_link', true ) ) : ?>
							<a class="page-action page-action-wordpress" href="<?php echo esc_url( $wordpress_edit_url ); ?>">
								<?php echo docspress_icon( 'pencil' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								<span data-customize-wordpress-edit-label><?php echo esc_html( get_theme_mod( 'docspress_wordpress_edit_label', __( 'Edit this page in WordPress', 'docspress' ) ) ); ?></span>
							</a>
						<?php endif; ?>
						<?php if ( $github_edit_url && get_theme_mod( 'docspress_show_github_edit_link', true ) ) : ?>
							<a class="page-action page-action-github" href="<?php echo esc_url( $github_edit_url ); ?>" target="_blank" rel="noopener noreferrer">
								<?php echo docspress_icon( 'github' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								<span data-customize-github-edit-label><?php echo esc_html( get_theme_mod( 'docspress_github_edit_label', __( 'Propose changes to this page in GitHub', 'docspress' ) ) ); ?></span>
							</a>
						<?php endif; ?>
					</nav>
				<?php endif; ?>

				<?php if ( ( $adjacent['previous'] || $adjacent['next'] ) && get_theme_mod( 'docspress_show_pagination', true ) ) : ?>
					<nav class="docs-pagination" aria-label="<?php esc_attr_e( 'Documentation pages', 'docspress' ); ?>">
						<?php if ( $adjacent['previous'] ) : ?>
							<a class="pagination-link pagination-previous" href="<?php echo esc_url( get_permalink( $adjacent['previous'] ) ); ?>">
								<span class="pagination-direction"><?php esc_html_e( '← Previous', 'docspress' ); ?></span>
								<span class="pagination-title"><?php echo esc_html( get_the_title( $adjacent['previous'] ) ); ?></span>
							</a>
						<?php else : ?>
							<span aria-hidden="true"></span>
						<?php endif; ?>
						<?php if ( $adjacent['next'] ) : ?>
							<a class="pagination-link pagination-next" href="<?php echo esc_url( get_permalink( $adjacent['next'] ) ); ?>">
								<span class="pagination-direction"><?php esc_html_e( 'Next →', 'docspress' ); ?></span>
								<span class="pagination-title"><?php echo esc_html( get_the_title( $adjacent['next'] ) ); ?></span>
							</a>
						<?php endif; ?>
					</nav>
				<?php endif; ?>
			</article>
		</main>

		<?php if ( $prepared['toc'] && get_theme_mod( 'docspress_show_toc', true ) ) : ?>
			<aside class="docs-toc" aria-label="<?php esc_attr_e( 'On this page', 'docspress' ); ?>">
				<p class="toc-title" data-customize-toc-title><?php echo esc_html( get_theme_mod( 'docspress_toc_title', __( 'On this page', 'docspress' ) ) ); ?></p>
				<ul class="toc-list">
					<?php foreach ( $prepared['toc'] as $item ) : ?>
						<li class="toc-level-<?php echo esc_attr( $item['level'] ); ?>"><a href="#<?php echo esc_attr( $item['id'] ); ?>" data-toc-link><?php echo esc_html( $item['title'] ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</aside>
		<?php else : ?>
			<aside class="docs-toc" aria-hidden="true"></aside>
		<?php endif; ?>
	</div>
	<?php
endwhile;

get_footer();
