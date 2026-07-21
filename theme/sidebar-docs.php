<?php
/**
 * Documentation sidebar.
 *
 * @package DocsPress
 */

$pages    = docspress_get_docs_pages();
$root_id  = docspress_get_docs_root_id();
$versions = docspress_get_versions();
$source   = get_theme_mod( 'docspress_sidebar_source', 'page_tree' );
$depth    = absint( get_theme_mod( 'docspress_sidebar_depth', 0 ) );
?>
<aside class="docs-sidebar" id="docs-sidebar" aria-label="<?php esc_attr_e( 'Documentation navigation', 'docspress' ); ?>">
	<p class="sidebar-eyebrow" data-customize-sidebar-title><?php echo esc_html( get_theme_mod( 'docspress_sidebar_title', __( 'Documentation', 'docspress' ) ) ); ?></p>
	<?php if ( get_theme_mod( 'docspress_show_sidebar_search', true ) ) : ?>
		<div class="sidebar-search">
			<?php echo docspress_icon( 'search' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<label class="screen-reader-text" for="docspress-filter"><?php esc_html_e( 'Filter documentation pages', 'docspress' ); ?></label>
			<input id="docspress-filter" type="search" placeholder="<?php echo esc_attr( get_theme_mod( 'docspress_search_placeholder', __( 'Filter pages…', 'docspress' ) ) ); ?>" autocomplete="off" data-docs-filter>
			<button class="sidebar-search-clear" type="button" data-search-clear aria-label="<?php esc_attr_e( 'Clear filter', 'docspress' ); ?>">×</button>
		</div>
	<?php endif; ?>

	<?php if ( $versions['terms'] && get_theme_mod( 'docspress_show_version_selector', true ) ) : ?>
		<label class="screen-reader-text" for="docspress-version"><?php esc_html_e( 'Documentation version', 'docspress' ); ?></label>
		<select class="version-select" id="docspress-version" data-version-select>
			<option value=""><?php esc_html_e( 'Choose a version', 'docspress' ); ?></option>
			<?php foreach ( $versions['terms'] as $version ) : ?>
				<?php $version_url = get_term_link( $version ); ?>
				<?php if ( ! is_wp_error( $version_url ) ) : ?>
					<option value="<?php echo esc_url( $version_url ); ?>" <?php selected( $versions['current'], $version->term_id ); ?>><?php echo esc_html( $version->name ); ?></option>
				<?php endif; ?>
			<?php endforeach; ?>
		</select>
	<?php endif; ?>

	<nav class="docs-nav<?php echo 'custom_menu' === $source ? ' docs-nav-custom' : ''; ?>" data-docs-nav>
		<?php if ( 'custom_menu' === $source ) : ?>
			<?php
			$sidebar_menu = absint( get_theme_mod( 'docspress_sidebar_menu', 0 ) );
			if ( $sidebar_menu || has_nav_menu( 'docs_sidebar' ) ) {
				wp_nav_menu(
					array(
						'theme_location' => 'docs_sidebar',
						'menu'           => $sidebar_menu ? $sidebar_menu : '',
						'container'      => false,
						'items_wrap'     => '<ul>%3$s</ul>',
						'depth'          => $depth,
						'fallback_cb'    => false,
					)
				);
			} else {
				echo '<p class="docs-nav-empty">' . esc_html__( 'Create a menu and assign it to Documentation sidebar.', 'docspress' ) . '</p>';
			}
			?>
		<?php elseif ( $pages ) : ?>
			<?php if ( $root_id && ! get_theme_mod( 'docspress_sidebar_show_root', true ) ) : ?>
				<?php docspress_render_page_tree( $pages, $root_id, 0, 1, $depth ); ?>
			<?php else : ?>
				<?php docspress_render_page_tree( $pages, 0, $root_id, 1, $depth ); ?>
			<?php endif; ?>
		<?php else : ?>
			<p class="docs-nav-empty"><?php esc_html_e( 'Publish Pages to populate this navigation.', 'docspress' ); ?></p>
		<?php endif; ?>
	</nav>
	<?php if ( get_theme_mod( 'docspress_show_sidebar_search', true ) ) : ?>
		<p class="sidebar-no-results" data-no-results><?php esc_html_e( 'No pages match that filter.', 'docspress' ); ?></p>
	<?php endif; ?>
</aside>
