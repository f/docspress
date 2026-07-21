<?php
/**
 * Not found template.
 *
 * @package DocsPress
 */

get_header();
?>
<div class="docs-shell">
	<?php get_sidebar( 'docs' ); ?>
	<main class="docs-main" id="main-content">
		<div class="docs-article">
			<header class="entry-header">
				<span class="entry-kicker">404</span>
				<h1 class="entry-title"><?php esc_html_e( 'Page not found', 'docspress' ); ?></h1>
				<p class="entry-summary"><?php esc_html_e( 'This documentation page may have moved. Pick a page from the sidebar or return to the start.', 'docspress' ); ?></p>
			</header>
			<p><a href="<?php echo esc_url( home_url( '/' ) ); ?>">← <?php esc_html_e( 'Back to documentation', 'docspress' ); ?></a></p>
		</div>
	</main>
	<aside class="docs-toc" aria-hidden="true"></aside>
</div>
<?php
get_footer();
