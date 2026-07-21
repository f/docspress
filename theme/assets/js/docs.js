(function () {
	'use strict';

	const root = document.documentElement;
	const body = document.body;
	const drawerToggle = document.querySelector('[data-drawer-toggle]');
	const drawerClose = document.querySelector('[data-drawer-close]');
	const sidebar = document.querySelector('#docs-sidebar');
	const sidebarSearchInput = document.querySelector('[data-docs-filter]');
	const searchTrigger = document.querySelector('[data-docs-search-trigger]');
	const clearButton = document.querySelector('[data-search-clear]');
	const searchDialog = document.querySelector('[data-docs-search-dialog]');
	const commandSearchForm = document.querySelector('[data-command-search-form]');
	const commandSearchInput = document.querySelector('[data-docs-command-input]');
	const commandSearchResults = document.querySelector('[data-command-search-results]');
	const commandSearchStatus = document.querySelector('[data-command-search-status]');
	const commandSearchEmpty = document.querySelector('[data-command-search-empty]');
	const commandSearchClose = document.querySelector('[data-docs-search-close]');
	const shortcutHint = document.querySelector('[data-search-shortcut-hint]');
	const themeToggle = document.querySelector('[data-theme-toggle]');
	const versionSelect = document.querySelector('[data-version-select]');
	const searchData = window.docspressSearchData || {};
	const searchLimit = Number(searchData.limit) || 8;
	const searchIndex = (Array.isArray(searchData.index) ? searchData.index : []).map(function (record) {
		return Object.assign({}, record, {
			_normalizedTitle: normalizeSearchText(record.title),
			_normalizedPath: normalizeSearchText(record.path),
			_normalizedExcerpt: normalizeSearchText(record.excerpt),
			_normalizedContent: normalizeSearchText(record.content)
		});
	});
	let activeSearchResult = -1;
	let visibleSearchResults = [];
	let searchReturnFocus = null;

	function setDrawer(open) {
		body.classList.toggle('drawer-open', open);
		if (drawerToggle) {
			drawerToggle.setAttribute('aria-expanded', String(open));
			drawerToggle.setAttribute('aria-label', open ? 'Close documentation menu' : 'Open documentation menu');
		}
	}

	if (drawerToggle) {
		drawerToggle.addEventListener('click', function () {
			setDrawer(!body.classList.contains('drawer-open'));
		});
	}

	if (drawerClose) {
		drawerClose.addEventListener('click', function () {
			setDrawer(false);
		});
	}

	if (sidebar) {
		sidebar.addEventListener('click', function (event) {
			if (event.target.closest('a') && window.matchMedia('(max-width: 860px)').matches) {
				setDrawer(false);
			}
		});
	}

	function focusSidebarSearch() {
		if (!sidebarSearchInput) return;
		if (window.matchMedia('(max-width: 860px)').matches) {
			setDrawer(true);
		}
		window.setTimeout(function () {
			sidebarSearchInput.focus();
			sidebarSearchInput.select();
		}, 80);
	}

	function normalizeSearchText(value) {
		return String(value || '')
			.toLocaleLowerCase()
			.normalize('NFD')
			.replace(/[\u0300-\u036f]/g, '')
			.replace(/\s+/g, ' ')
			.trim();
	}

	function rankSearchResults(query) {
		const normalizedQuery = normalizeSearchText(query);
		if (!normalizedQuery) return searchIndex.slice(0, searchLimit);

		const tokens = normalizedQuery.split(' ').filter(Boolean);
		return searchIndex.map(function (record, originalIndex) {
			let score = 0;

			if (record._normalizedTitle === normalizedQuery) score += 160;
			else if (record._normalizedTitle.startsWith(normalizedQuery)) score += 100;
			else if (record._normalizedTitle.includes(normalizedQuery)) score += 70;

			if (record._normalizedPath.includes(normalizedQuery)) score += 28;
			if (record._normalizedExcerpt.includes(normalizedQuery)) score += 20;
			if (record._normalizedContent.includes(normalizedQuery)) score += 10;

			const everyTokenMatches = tokens.every(function (token) {
				if (record._normalizedTitle.startsWith(token)) {
					score += 45;
					return true;
				}
				if (record._normalizedTitle.includes(token)) {
					score += 32;
					return true;
				}
				if (record._normalizedPath.includes(token)) {
					score += 18;
					return true;
				}
				if (record._normalizedExcerpt.includes(token)) {
					score += 12;
					return true;
				}
				if (record._normalizedContent.includes(token)) {
					score += 4;
					return true;
				}
				return false;
			});

			return everyTokenMatches ? { record: record, score: score, originalIndex: originalIndex } : null;
		}).filter(Boolean).sort(function (first, second) {
			return second.score - first.score || first.originalIndex - second.originalIndex;
		}).slice(0, searchLimit).map(function (match) {
			return match.record;
		});
	}

	function highlightedText(text, query) {
		const fragment = document.createDocumentFragment();
		const tokens = String(query || '').trim().split(/\s+/).filter(Boolean).sort(function (a, b) { return b.length - a.length; });
		if (!tokens.length) {
			fragment.appendChild(document.createTextNode(text));
			return fragment;
		}

		const escaped = tokens.map(function (token) { return token.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'); });
		const expression = new RegExp('(' + escaped.join('|') + ')', 'gi');
		String(text).split(expression).forEach(function (part) {
			if (!part) return;
			if (tokens.some(function (token) { return normalizeSearchText(part) === normalizeSearchText(token); })) {
				const mark = document.createElement('mark');
				mark.textContent = part;
				fragment.appendChild(mark);
			} else {
				fragment.appendChild(document.createTextNode(part));
			}
		});

		return fragment;
	}

	function searchSnippet(record, query) {
		const source = String(record.excerpt || record.content || '');
		const normalizedSource = normalizeSearchText(source);
		const firstToken = normalizeSearchText(query).split(' ').filter(Boolean).find(function (token) {
			return normalizedSource.includes(token);
		});
		if (!firstToken || source.length <= 190) return source;

		const matchIndex = normalizedSource.indexOf(firstToken);
		const start = Math.max(0, matchIndex - 70);
		const end = Math.min(source.length, start + 190);
		return (start ? '…' : '') + source.slice(start, end).trim() + (end < source.length ? '…' : '');
	}

	function setActiveSearchResult(index, shouldScroll) {
		const options = Array.from(commandSearchResults ? commandSearchResults.querySelectorAll('[role="option"]') : []);
		if (!options.length) {
			activeSearchResult = -1;
			if (commandSearchInput) commandSearchInput.removeAttribute('aria-activedescendant');
			return;
		}

		activeSearchResult = (index + options.length) % options.length;
		options.forEach(function (option, optionIndex) {
			const active = optionIndex === activeSearchResult;
			option.classList.toggle('is-active', active);
			option.setAttribute('aria-selected', String(active));
		});
		commandSearchInput.setAttribute('aria-activedescendant', options[activeSearchResult].id);
		if (shouldScroll) options[activeSearchResult].scrollIntoView({ block: 'nearest' });
	}

	function renderCommandSearch() {
		if (!commandSearchInput || !commandSearchResults) return;
		const query = commandSearchInput.value.trim();
		visibleSearchResults = rankSearchResults(query);
		commandSearchResults.replaceChildren();
		activeSearchResult = -1;

		if (commandSearchStatus) {
			if (!query) {
				commandSearchStatus.textContent = searchData.suggestedLabel || 'Suggested pages';
			} else if (visibleSearchResults.length === 1) {
				commandSearchStatus.textContent = searchData.resultSingular || '1 result';
			} else {
				commandSearchStatus.textContent = String(searchData.resultPlural || '%d results').replace('%d', visibleSearchResults.length);
			}
		}

		if (commandSearchEmpty) commandSearchEmpty.hidden = visibleSearchResults.length > 0;
		visibleSearchResults.forEach(function (record, index) {
			const option = document.createElement('li');
			const link = document.createElement('a');
			const path = document.createElement('span');
			const title = document.createElement('strong');
			const excerpt = document.createElement('span');
			const arrow = document.createElement('span');

			option.className = 'command-search-result';
			option.id = 'docspress-command-result-' + index;
			option.setAttribute('role', 'option');
			option.setAttribute('aria-selected', 'false');
			link.href = record.url;
			link.tabIndex = -1;
			link.setAttribute('data-command-result-link', '');
			path.className = 'command-search-result-path';
			path.textContent = record.path || 'Documentation';
			title.className = 'command-search-result-title';
			title.appendChild(highlightedText(record.title, query));
			excerpt.className = 'command-search-result-excerpt';
			excerpt.appendChild(highlightedText(searchSnippet(record, query), query));
			arrow.className = 'command-search-result-arrow';
			arrow.textContent = '↵';
			arrow.setAttribute('aria-hidden', 'true');
			link.append(path, title, excerpt, arrow);
			option.appendChild(link);
			option.addEventListener('pointermove', function () { setActiveSearchResult(index, false); });
			commandSearchResults.appendChild(option);
		});

		if (visibleSearchResults.length) setActiveSearchResult(0, false);
	}

	function openCommandSearch() {
		if (!searchDialog || !commandSearchInput) {
			focusSidebarSearch();
			return;
		}

		searchReturnFocus = document.activeElement;
		if (!searchDialog.open) {
			if (typeof searchDialog.showModal === 'function') searchDialog.showModal();
			else searchDialog.setAttribute('open', '');
		}
		body.classList.add('command-search-open');
		if (searchTrigger) searchTrigger.setAttribute('aria-expanded', 'true');
		renderCommandSearch();
		window.setTimeout(function () {
			commandSearchInput.focus();
			commandSearchInput.select();
		}, 40);
	}

	function closeCommandSearch(restoreFocus) {
		if (!searchDialog || !searchDialog.open) return;
		if (typeof searchDialog.close === 'function') searchDialog.close();
		else searchDialog.removeAttribute('open');
		body.classList.remove('command-search-open');
		if (searchTrigger) searchTrigger.setAttribute('aria-expanded', 'false');
		if (restoreFocus !== false && searchReturnFocus && typeof searchReturnFocus.focus === 'function') {
			searchReturnFocus.focus();
		}
	}

	if (shortcutHint) {
		const platform = navigator.userAgentData && navigator.userAgentData.platform
			? navigator.userAgentData.platform
			: navigator.platform;
		shortcutHint.textContent = /Mac|iPhone|iPad|iPod/i.test(platform || '') ? '⌘ K' : 'Ctrl K';
	}

	if (searchTrigger) {
		searchTrigger.addEventListener('click', openCommandSearch);
	}

	if (commandSearchClose) {
		commandSearchClose.addEventListener('click', function () { closeCommandSearch(true); });
	}

	if (searchDialog) {
		searchDialog.addEventListener('cancel', function (event) {
			event.preventDefault();
			closeCommandSearch(true);
		});
		searchDialog.addEventListener('click', function (event) {
			if (event.target === searchDialog) closeCommandSearch(true);
		});
		searchDialog.addEventListener('close', function () {
			body.classList.remove('command-search-open');
			if (searchTrigger) searchTrigger.setAttribute('aria-expanded', 'false');
		});
	}

	if (commandSearchInput) {
		commandSearchInput.addEventListener('input', renderCommandSearch);
		commandSearchInput.addEventListener('keydown', function (event) {
			if (event.key === 'ArrowDown') {
				event.preventDefault();
				setActiveSearchResult(activeSearchResult + 1, true);
			}
			if (event.key === 'ArrowUp') {
				event.preventDefault();
				setActiveSearchResult(activeSearchResult - 1, true);
			}
			if (event.key === 'Enter') {
				if (activeSearchResult >= 0 && visibleSearchResults[activeSearchResult]) {
					event.preventDefault();
					window.location.assign(visibleSearchResults[activeSearchResult].url);
				} else if (commandSearchInput.value.trim() && commandSearchForm) {
					event.preventDefault();
					commandSearchForm.submit();
				}
			}
			if (event.key === 'Escape') {
				event.preventDefault();
				event.stopPropagation();
				closeCommandSearch(true);
			}
		});
	}

	if (commandSearchForm) {
		commandSearchForm.addEventListener('submit', function (event) {
			if (activeSearchResult < 0 || !visibleSearchResults[activeSearchResult]) return;
			event.preventDefault();
			window.location.assign(visibleSearchResults[activeSearchResult].url);
		});
	}

	function filterNavigation() {
		const query = sidebarSearchInput.value.trim().toLocaleLowerCase();
		const nav = document.querySelector('[data-docs-nav]');
		const noResults = document.querySelector('[data-no-results]');
		if (!nav) return;

		const items = Array.from(nav.querySelectorAll('li'));
		items.forEach(function (item) {
			item.hidden = false;
		});

		if (query) {
			items.slice().reverse().forEach(function (item) {
				const directLink = Array.from(item.children).find(function (child) { return child.tagName === 'A'; });
				const title = item.dataset.docTitle || (directLink ? directLink.textContent.trim() : '');
				const ownMatch = title.toLocaleLowerCase().includes(query);
				const childMatch = Array.from(item.children).some(function (child) {
					return child.tagName === 'UL' && Array.from(child.children).some(function (li) { return !li.hidden; });
				});
				item.hidden = !ownMatch && !childMatch;
			});
		}

		const hasResults = items.some(function (item) { return !item.hidden; });
		if (noResults) noResults.classList.toggle('is-visible', !hasResults);
		if (clearButton) clearButton.classList.toggle('is-visible', Boolean(query));
	}

	if (sidebarSearchInput) {
		sidebarSearchInput.addEventListener('input', filterNavigation);
	}

	if (clearButton && sidebarSearchInput) {
		clearButton.addEventListener('click', function () {
			sidebarSearchInput.value = '';
			filterNavigation();
			sidebarSearchInput.focus();
		});
	}

	document.addEventListener('keydown', function (event) {
		const activeElement = document.activeElement;
		const isField = /^(INPUT|TEXTAREA|SELECT)$/.test(activeElement.tagName) || activeElement.isContentEditable;
		if ((event.metaKey || event.ctrlKey) && event.key.toLocaleLowerCase() === 'k') {
			event.preventDefault();
			openCommandSearch();
		}
		if (event.key === '/' && !isField) {
			event.preventDefault();
			openCommandSearch();
		}
		if (event.key === 'Escape' && (!searchDialog || !searchDialog.open)) {
			setDrawer(false);
			if (sidebarSearchInput && document.activeElement === sidebarSearchInput) sidebarSearchInput.blur();
		}
	});

	function updateThemeButton() {
		if (!themeToggle) return;
		const isDark = root.dataset.theme === 'dark';
		themeToggle.setAttribute('aria-label', isDark ? 'Use light theme' : 'Use dark theme');
		themeToggle.title = isDark ? 'Use light theme' : 'Use dark theme';
		const lightIcon = themeToggle.querySelector('.theme-icon-light');
		const darkIcon = themeToggle.querySelector('.theme-icon-dark');
		if (lightIcon) lightIcon.style.display = isDark ? 'none' : 'block';
		if (darkIcon) darkIcon.style.display = isDark ? 'block' : 'none';
	}

	if (themeToggle) {
		updateThemeButton();
		themeToggle.addEventListener('click', function () {
			const next = root.dataset.theme === 'dark' ? 'light' : 'dark';
			root.dataset.theme = next;
			try { localStorage.setItem('docspress-color-mode', next); } catch (error) {}
			updateThemeButton();
		});
	}

	if (versionSelect) {
		versionSelect.addEventListener('change', function () {
			if (versionSelect.value) window.location.assign(versionSelect.value);
		});
	}

	document.querySelectorAll('.entry-content pre').forEach(function (pre) {
		const button = document.createElement('button');
		button.className = 'copy-code';
		button.type = 'button';
		button.textContent = 'Copy';
		button.setAttribute('aria-label', 'Copy code to clipboard');
		button.addEventListener('click', async function () {
			const code = pre.querySelector('code');
			try {
				await navigator.clipboard.writeText((code || pre).innerText);
				button.textContent = 'Copied';
				window.setTimeout(function () { button.textContent = 'Copy'; }, 1600);
			} catch (error) {
				button.textContent = 'Select to copy';
			}
		});
		pre.appendChild(button);
	});

	const tocLinks = Array.from(document.querySelectorAll('[data-toc-link]'));
	if ('IntersectionObserver' in window && tocLinks.length) {
		const headingMap = new Map(tocLinks.map(function (link) {
			return [decodeURIComponent(link.hash.slice(1)), link];
		}));
		const headings = Array.from(headingMap.keys()).map(function (id) { return document.getElementById(id); }).filter(Boolean);
		const observer = new IntersectionObserver(function (entries) {
			entries.forEach(function (entry) {
				if (!entry.isIntersecting) return;
				tocLinks.forEach(function (link) { link.classList.remove('is-active'); });
				const link = headingMap.get(entry.target.id);
				if (link) link.classList.add('is-active');
			});
		}, { rootMargin: '-18% 0px -70% 0px' });
		headings.forEach(function (heading) { observer.observe(heading); });
	}
})();
