<?php
/**
 * Fallback index template.
 *
 * @package DocsPress
 */

get_header();
?>
<div class="docs-shell">
	<?php get_sidebar( 'docs' ); ?>
	<main class="docs-main" id="main-content">
		<div class="docs-article">
			<header class="entry-header archive-heading">
				<span class="entry-kicker"><?php esc_html_e( 'Knowledge base', 'docspress' ); ?></span>
				<h1 class="entry-title"><?php bloginfo( 'name' ); ?></h1>
				<?php if ( get_bloginfo( 'description' ) ) : ?>
					<p class="entry-summary"><?php bloginfo( 'description' ); ?></p>
				<?php endif; ?>
			</header>

			<?php if ( have_posts() ) : ?>
				<div class="result-list">
					<?php while ( have_posts() ) : ?>
						<?php the_post(); ?>
						<article <?php post_class( 'result-card' ); ?>>
							<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
							<?php the_excerpt(); ?>
						</article>
					<?php endwhile; ?>
				</div>
				<?php the_posts_pagination(); ?>
			<?php else : ?>
				<div class="empty-state"><strong><?php esc_html_e( 'No documentation yet.', 'docspress' ); ?></strong><p><?php esc_html_e( 'Publish your first Page to get started.', 'docspress' ); ?></p></div>
			<?php endif; ?>
		</div>
	</main>
	<aside class="docs-toc" aria-hidden="true"></aside>
</div>
<?php
get_footer();
