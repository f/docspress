<?php
/**
 * Documentation command-search dialog.
 *
 * @package DocsPress
 */
?>
<dialog class="search-dialog" id="docspress-search-dialog" data-docs-search-dialog aria-labelledby="docspress-search-title">
	<div class="search-dialog-panel">
		<form class="command-search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" data-command-search-form>
			<div class="command-search-field">
				<?php echo docspress_icon( 'search' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<label class="screen-reader-text" id="docspress-search-title" for="docspress-command-search"><?php esc_html_e( 'Search documentation', 'docspress' ); ?></label>
				<input
					id="docspress-command-search"
					name="s"
					type="search"
					placeholder="<?php echo esc_attr( get_theme_mod( 'docspress_search_dialog_placeholder', __( 'Search documentation…', 'docspress' ) ) ); ?>"
					autocomplete="off"
					spellcheck="false"
					aria-autocomplete="list"
					aria-controls="docspress-command-results"
					data-docs-command-input
				>
				<input type="hidden" name="post_type" value="page">
				<button class="command-search-close" type="button" data-docs-search-close aria-label="<?php esc_attr_e( 'Close search', 'docspress' ); ?>">
					<span aria-hidden="true">×</span>
				</button>
			</div>

			<div class="command-search-body">
				<div class="command-search-status" aria-live="polite" aria-atomic="true" data-command-search-status></div>
				<ul class="command-search-results" id="docspress-command-results" role="listbox" data-command-search-results></ul>
				<div class="command-search-empty" data-command-search-empty hidden>
					<span class="command-search-empty-icon" aria-hidden="true"><?php echo docspress_icon( 'search' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<strong data-customize-search-no-results><?php echo esc_html( get_theme_mod( 'docspress_search_no_results_label', __( 'No documentation matched that search.', 'docspress' ) ) ); ?></strong>
					<span><?php esc_html_e( 'Try another term or press Enter for the full WordPress search.', 'docspress' ); ?></span>
				</div>
			</div>

			<footer class="command-search-footer" aria-hidden="true">
				<span><kbd>↑</kbd><kbd>↓</kbd> <?php esc_html_e( 'to navigate', 'docspress' ); ?></span>
				<span><kbd>↵</kbd> <?php esc_html_e( 'to open', 'docspress' ); ?></span>
				<span><kbd>Esc</kbd> <?php esc_html_e( 'to close', 'docspress' ); ?></span>
			</footer>
		</form>
	</div>
</dialog>
