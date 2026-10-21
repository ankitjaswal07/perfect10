/**
 * Perfect 10 admin interface.
 *
 * Drives the batched demo importer, the Elementor homepage builder and the
 * guarded demo reset. No business logic is duplicated here; every action calls
 * a capability- and nonce-checked WordPress AJAX handler.
 */
(function () {
	'use strict';

	var config = window.P10Admin || {};
	var phrase = config.resetPhrase || 'RESET DEMO CONTENT';
	var running = false;

	function $(selector, scope) {
		return (scope || document).querySelector(selector);
	}

	function $all(selector, scope) {
		return Array.prototype.slice.call((scope || document).querySelectorAll(selector));
	}

	function post(action, data) {
		var body = new URLSearchParams();
		body.append('action', action);
		body.append('nonce', config.nonce || '');
		Object.keys(data || {}).forEach(function (key) {
			body.append(key, data[key]);
		});

		return fetch(config.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
			body: body.toString()
		}).then(function (response) {
			return response.json().then(function (payload) {
				if (!response.ok || !payload.success) {
					var message = payload && payload.data && payload.data.message ? payload.data.message : config.i18n.error;
					throw new Error(message);
				}
				return payload.data;
			});
		});
	}

	function setFeedback(node, message, type) {
		if (!node) { return; }
		node.textContent = message || '';
		node.className = 'p10-admin-feedback' + (type ? ' is-' + type : '');
	}

	function spinner(on) {
		$all('[data-p10-spinner]').forEach(function (node) {
			node.classList.toggle('is-active', !!on);
		});
	}

	function setBusy(on) {
		running = !!on;
		$all('[data-p10-import-start]').forEach(function (button) {
			button.disabled = !!on;
		});
		$all('[data-p10-build-home]').forEach(function (button) {
			button.disabled = !!on || !config.elementor;
		});
	}

	function stepList() {
		var list = $('[data-p10-steps]');
		if (!list) { return []; }
		return $all('[data-step]', list).map(function (node) {
			return { slug: node.getAttribute('data-step'), node: node };
		});
	}

	function markStep(slug, state) {
		var node = $('[data-step="' + slug + '"]');
		if (!node) { return; }
		node.classList.remove('is-active', 'is-done', 'is-error');
		if (state) { node.classList.add('is-' + state); }
		var mark = $('.p10-import-steps__mark', node);
		if (mark) {
			mark.textContent = state === 'done' ? '✓' : (state === 'error' ? '!' : '○');
		}
	}

	function updateProgress(percent) {
		var bar = $('[data-p10-progress] span');
		var label = $('[data-p10-progress-label]');
		percent = Math.max(0, Math.min(100, parseInt(percent, 10) || 0));
		if (bar) { bar.style.width = percent + '%'; }
		if (label) { label.textContent = percent + '% complete'; }
	}

	function renderLog(lines) {
		var log = $('[data-p10-log]');
		if (!log || !Array.isArray(lines)) { return; }
		log.innerHTML = '';
		lines.slice(-24).forEach(function (line) {
			var p = document.createElement('p');
			p.className = 'is-' + (line.level || 'info');
			var time = document.createElement('time');
			time.textContent = line.time || '';
			p.appendChild(time);
			p.appendChild(document.createTextNode(' ' + (line.text || '')));
			log.appendChild(p);
		});
		log.scrollTop = log.scrollHeight;
	}

	function newRunId() {
		return 'p10-' + Date.now().toString(36) + '-' + Math.random().toString(36).slice(2, 8);
	}

	function runImport() {
		if (running) { return; }

		var steps = stepList();
		if (!steps.length) { return; }

		var startAt = 0;
		var state = config.state || {};
		if (state.status === 'running' && Array.isArray(state.completed) && state.completed.length) {
			for (var i = 0; i < steps.length; i++) {
				if (state.completed.indexOf(steps[i].slug) === -1) {
					startAt = i;
					break;
				}
			}
		}

		var runId = newRunId();
		setBusy(true);
		spinner(true);
		setFeedback($('[data-p10-import-feedback]'), config.i18n.working, 'working');
		updateProgress(0);
		steps.forEach(function (item) { markStep(item.slug, ''); });

		function next(index) {
			if (index >= steps.length) {
				spinner(false);
				setBusy(false);
				updateProgress(100);
				setFeedback($('[data-p10-import-feedback]'), config.i18n.done, 'success');
				return;
			}

			var item = steps[index];
			markStep(item.slug, 'active');
			setFeedback($('[data-p10-import-feedback]'), config.i18n.working + ' ' + item.slug, 'working');

			post('p10_demo_step', { step: item.slug, run_id: runId, force: 1 }).then(function (data) {
				markStep(item.slug, 'done');
				if (data.result) {
					updateProgress(data.result.progress);
				}
				if (data.state) {
					renderLog(data.state.log);
					config.state = data.state;
				}
				next(index + 1);
			}).catch(function (error) {
				markStep(item.slug, 'error');
				spinner(false);
				setBusy(false);
				setFeedback($('[data-p10-import-feedback]'), config.i18n.stepFailed + ' ' + error.message, 'error');
			});
		}

		next(startAt);
	}

	function bindImport() {
		$all('[data-p10-import-start]').forEach(function (button) {
			button.addEventListener('click', runImport);
		});
	}

	function bindBuildHome() {
		$all('[data-p10-build-home]').forEach(function (button) {
			button.addEventListener('click', function () {
				if (running) { return; }
				setBusy(true);
				spinner(true);
				$all('[data-p10-home-feedback]').forEach(function (node) {
					setFeedback(node, config.i18n.homeWorking, 'working');
				});

				post('p10_build_home', { force: 1 }).then(function (data) {
					spinner(false);
					setBusy(false);
					$all('[data-p10-home-feedback]').forEach(function (node) {
						var message = data.message || config.i18n.homeBuilt;
						node.textContent = '';
						node.className = 'p10-admin-feedback is-success';
						node.appendChild(document.createTextNode(message));
						if (data.status && data.status.home_edit_url) {
							node.appendChild(document.createTextNode(' '));
							var link = document.createElement('a');
							link.href = data.status.home_edit_url;
							link.target = '_blank';
							link.rel = 'noopener';
							link.textContent = 'Open Elementor';
							node.appendChild(link);
						}
					});
				}).catch(function (error) {
					spinner(false);
					setBusy(false);
					$all('[data-p10-home-feedback]').forEach(function (node) {
						setFeedback(node, config.i18n.homeFailed + ' ' + error.message, 'error');
					});
				});
			});
		});
	}

	function bindReset() {
		var input = $('[data-p10-reset-input]');
		var button = $('[data-p10-reset]');
		if (!input || !button) { return; }

		input.addEventListener('input', function () {
			var matches = input.value.trim().toUpperCase() === phrase;
			button.disabled = !matches;
			if (!matches) {
				setFeedback($('[data-p10-reset-feedback]'), config.i18n.resetPrompt, '');
			} else {
				setFeedback($('[data-p10-reset-feedback]'), '', '');
			}
		});

		button.addEventListener('click', function () {
			if (input.value.trim().toUpperCase() !== phrase) { return; }
			if (!window.confirm(config.i18n.confirm)) { return; }

			button.disabled = true;
			setFeedback($('[data-p10-reset-feedback]'), config.i18n.working, 'working');

			post('p10_demo_reset', { confirm: input.value.trim() }).then(function (data) {
				setFeedback($('[data-p10-reset-feedback]'), (data.message || '') + ' ' + config.i18n.resetDone, 'success');
				spinner(false);
				window.setTimeout(function () { window.location.reload(); }, 2200);
			}).catch(function (error) {
				button.disabled = false;
				setFeedback($('[data-p10-reset-feedback]'), error.message, 'error');
			});
		});
	}

	document.addEventListener('DOMContentLoaded', function () {
		bindImport();
		bindBuildHome();
		bindReset();

		var state = config.state || {};
		if (state.status === 'running' && Array.isArray(state.completed)) {
			state.completed.forEach(function (slug) { markStep(slug, 'done'); });
			updateProgress(Math.round((state.completed.length / Math.max(1, Object.keys(config.steps || {}).length)) * 100));
		}
	});
})();
