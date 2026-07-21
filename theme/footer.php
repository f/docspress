<?php
/**
 * Site footer.
 *
 * @package DocsPress
 */
?>
<button class="drawer-scrim" type="button" data-drawer-close aria-label="<?php esc_attr_e( 'Close documentation menu', 'docspress' ); ?>"></button>
<?php if ( get_theme_mod( 'docspress_show_footer', true ) ) : ?>
	<footer class="site-footer">
		<p>
			<?php
			$footer_text = get_theme_mod( 'docspress_footer_text', __( 'Documentation powered by WordPress · {year}', 'docspress' ) );
			$footer_text = strtr(
				$footer_text,
				array(
					'{year}'       => gmdate( 'Y' ),
					'{site_title}' => get_bloginfo( 'name' ),
				)
			);
			?>
			<span data-customize-footer-text><?php echo esc_html( $footer_text ); ?></span>
			<?php $footer_link_url = get_theme_mod( 'docspress_footer_link_url', '' ); ?>
			<?php $footer_link_label = get_theme_mod( 'docspress_footer_link_label', '' ); ?>
			<?php if ( $footer_link_url && $footer_link_label ) : ?>
				<span aria-hidden="true"> · </span><a href="<?php echo esc_url( $footer_link_url ); ?>"><?php echo esc_html( $footer_link_label ); ?></a>
			<?php endif; ?>
		</p>
	</footer>
<?php endif; ?>
<?php wp_footer(); ?>
</body>
</html>
