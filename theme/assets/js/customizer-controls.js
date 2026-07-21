(function (api, data) {
	'use strict';

	if (!api || !data || !data.presets) return;

	const presets = data.presets;
	const controlledSettings = data.controlledSettings || [];
	let applyingPreset = false;

	function applyPreset(presetName) {
		const recipe = presets[presetName];
		if (!recipe) return;

		applyingPreset = true;
		Object.keys(recipe).forEach(function (settingId) {
			const setting = api(settingId);
			if (setting) setting.set(recipe[settingId]);
		});

		window.setTimeout(function () {
			applyingPreset = false;
		}, 0);
	}

	api('docspress_design_preset', function (presetSetting) {
		presetSetting.bind(function (next) {
			if (!applyingPreset && next !== 'custom') applyPreset(next);
		});
	});

	controlledSettings.forEach(function (settingId) {
		api(settingId, function (setting) {
			setting.bind(function () {
				if (applyingPreset) return;
				const presetSetting = api('docspress_design_preset');
				if (presetSetting && presetSetting.get() !== 'custom') presetSetting.set('custom');
			});
		});
	});

	api('docspress_show_color_toggle', function (setting) {
		function syncDefaultModeControl(showToggle) {
			const control = api.control('docspress_default_color_mode');
			if (control) control.active.set(!showToggle);
		}

		syncDefaultModeControl(setting.get());
		setting.bind(syncDefaultModeControl);
	});
})(window.wp && window.wp.customize, window.docspressPresetData || {});
