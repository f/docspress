(function () {
	'use strict';

	const customizerStyle = document.querySelector('#docspress-customizer-css');
	const fontStacks = {
		rounded: 'ui-rounded, "Arial Rounded MT Bold", "Avenir Next", "Trebuchet MS", sans-serif',
		avenir: '"Avenir Next", Avenir, "Century Gothic", Futura, sans-serif',
		inter: 'Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif',
		humanist: 'Optima, Candara, "Noto Sans", sans-serif',
		geometric: 'Futura, "Trebuchet MS", "Century Gothic", sans-serif',
		editorial: '"Gill Sans", "Gill Sans MT", Calibri, sans-serif',
		eb_garamond: '"EB Garamond", "Iowan Old Style", Palatino, "Palatino Linotype", serif',
		recoleta: 'Recoleta, "EB Garamond", Georgia, serif',
		charter: 'Charter, "Bitstream Charter", "Sitka Text", Georgia, serif',
		iowan: '"Iowan Old Style", Palatino, "Palatino Linotype", serif',
		georgia: 'Georgia, "Times New Roman", serif'
	};

	function bindText(setting, selector, attribute) {
		wp.customize(setting, function (value) {
			value.bind(function (next) {
				const element = document.querySelector(selector);
				if (!element) return;
				if (attribute) element.setAttribute(attribute, next);
				else element.textContent = next;
			});
		});
	}

	function getRule(index) {
		return customizerStyle && customizerStyle.sheet && customizerStyle.sheet.cssRules[index]
			? customizerStyle.sheet.cssRules[index].style
			: null;
	}

	function setRuleVariable(index, variable, value) {
		const rule = getRule(index);
		if (rule) rule.setProperty(variable, value);
	}

	function bindVariable(setting, variable, suffix) {
		wp.customize(setting, function (value) {
			value.bind(function (next) {
				setRuleVariable(0, variable, String(next) + (suffix || ''));
			});
		});
	}

	function bindInverseClass(setting, className) {
		wp.customize(setting, function (value) {
			value.bind(function (next) {
				document.body.classList.toggle(className, !next);
			});
		});
	}

	bindText('docspress_sidebar_title', '[data-customize-sidebar-title]');
	bindText('docspress_search_placeholder', '[data-docs-filter]', 'placeholder');
	bindText('docspress_brand_suffix', '[data-customize-brand-suffix]');
	bindText('docspress_header_search_label', '[data-customize-search-label]');
	bindText('docspress_search_dialog_placeholder', '[data-docs-command-input]', 'placeholder');
	bindText('docspress_search_no_results_label', '[data-customize-search-no-results]');
	bindText('docspress_kicker_label', '[data-customize-kicker]');
	bindText('docspress_toc_title', '[data-customize-toc-title]');
	bindText('docspress_wordpress_edit_label', '[data-customize-wordpress-edit-label]');
	bindText('docspress_github_edit_label', '[data-customize-github-edit-label]');

	wp.customize('docspress_footer_text', function (value) {
		value.bind(function (next) {
			const element = document.querySelector('[data-customize-footer-text]');
			const siteTitle = document.querySelector('[data-customize-site-title]');
			if (!element) return;
			element.textContent = String(next)
				.split('{year}').join(String(new Date().getFullYear()))
				.split('{site_title}').join(siteTitle ? siteTitle.textContent.trim() : '');
		});
	});

	bindVariable('docspress_article_width', '--dp-content-width', 'px');
	bindVariable('docspress_sidebar_width', '--dp-sidebar-width', 'px');
	bindVariable('docspress_toc_width', '--dp-toc-width', 'px');
	bindVariable('docspress_content_font_size', '--dp-content-font-size', 'px');
	bindVariable('docspress_heading_weight', '--dp-heading-weight');
	bindVariable('docspress_border_radius', '--dp-radius', 'px');
	bindVariable('docspress_search_width', '--dp-search-width', 'px');
	bindVariable('docspress_search_height', '--dp-search-height', 'px');
	bindVariable('docspress_search_overlay_opacity', '--dp-search-overlay-opacity', '%');
	bindVariable('docspress_search_overlay_blur', '--dp-search-overlay-blur', 'px');

	function updateSearchRadius() {
		const mode = wp.customize('docspress_search_radius_mode').get();
		const radius = wp.customize('docspress_search_radius').get();
		setRuleVariable(0, '--dp-search-radius', mode === 'custom' ? String(radius) + 'px' : 'var(--dp-radius)');
	}

	wp.customize('docspress_search_radius_mode', function (value) {
		value.bind(updateSearchRadius);
	});

	wp.customize('docspress_search_radius', function (value) {
		value.bind(updateSearchRadius);
	});

	bindInverseClass('docspress_search_show_paths', 'docspress-search-hide-paths');
	bindInverseClass('docspress_search_show_excerpts', 'docspress-search-hide-excerpts');
	bindInverseClass('docspress_search_show_hints', 'docspress-search-hide-hints');

	const lightColors = {
		docspress_accent_color: '--dp-blue',
		docspress_accent_strong: '--dp-blue-dark',
		docspress_accent_soft: '--dp-blue-soft',
		docspress_paper_color: '--dp-paper',
		docspress_canvas_color: '--dp-canvas',
		docspress_ink_color: '--dp-ink',
		docspress_copy_color: '--dp-copy',
		docspress_muted_color: '--dp-muted',
		docspress_line_color: '--dp-line',
		docspress_line_strong_color: '--dp-line-strong'
	};
	Object.keys(lightColors).forEach(function (setting) {
		wp.customize(setting, function (value) {
			value.bind(function (next) {
				setRuleVariable(0, lightColors[setting], next);
			});
		});
	});

	const darkColors = {
		docspress_dark_accent: '--dp-blue',
		docspress_dark_strong: '--dp-blue-dark',
		docspress_dark_soft: '--dp-blue-soft',
		docspress_dark_paper: '--dp-paper',
		docspress_dark_canvas: '--dp-canvas',
		docspress_dark_ink: '--dp-ink',
		docspress_dark_copy: '--dp-copy',
		docspress_dark_muted: '--dp-muted',
		docspress_dark_line: '--dp-line',
		docspress_dark_line_strong: '--dp-line-strong'
	};
	Object.keys(darkColors).forEach(function (setting) {
		wp.customize(setting, function (value) {
			value.bind(function (next) {
				setRuleVariable(1, darkColors[setting], next);
			});
		});
	});

	wp.customize('docspress_ui_font', function (value) {
		value.bind(function (next) {
			const stack = fontStacks[next] || fontStacks.avenir;
			setRuleVariable(0, '--dp-font-ui', stack);
			if (wp.customize('docspress_content_font').get() === 'sans') {
				setRuleVariable(0, '--dp-font-copy', stack);
			}
			if (wp.customize('docspress_heading_font').get() === 'interface') {
				setRuleVariable(0, '--dp-font-heading', stack);
			}
		});
	});

	wp.customize('docspress_content_font', function (value) {
		value.bind(function (next) {
			const currentUi = wp.customize('docspress_ui_font').get();
			const stack = next === 'sans' ? (fontStacks[currentUi] || fontStacks.avenir) : (fontStacks[next] || fontStacks.charter);
			setRuleVariable(0, '--dp-font-copy', stack);
		});
	});

	wp.customize('docspress_heading_font', function (value) {
		value.bind(function (next) {
			const currentUi = wp.customize('docspress_ui_font').get();
			const stack = next === 'interface' ? (fontStacks[currentUi] || fontStacks.avenir) : (fontStacks[next] || fontStacks.avenir);
			setRuleVariable(0, '--dp-font-heading', stack);
		});
	});
})();
