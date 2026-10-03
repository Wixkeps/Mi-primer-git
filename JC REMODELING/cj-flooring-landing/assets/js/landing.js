/*!
 * CJ Flooring Landing - front-end behavior (no dependencies).
 *  - Contact buttons: smooth scroll + focus the right form
 *  - Lead form: validation, fresh nonce, AJAX submit, success / error states
 *  - Tracking: dataLayer / gtag events and UTM capture
 */
(function () {
	'use strict';

	var doc = document;
	var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	var TRACK_KEYS = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'gclid', 'fbclid'];

	/* ------------------------------------------------------------------ */
	/* Analytics (works with GTM dataLayer and/or gtag.js; silently skipped otherwise) */
	/* ------------------------------------------------------------------ */
	function track(name, params) {
		params = params || {};
		try {
			window.dataLayer = window.dataLayer || [];
			var payload = { event: name };
			for (var key in params) {
				if (Object.prototype.hasOwnProperty.call(params, key)) {
					payload[key] = params[key];
				}
			}
			window.dataLayer.push(payload);
		} catch (e) { /* ignore */ }
	}

	function trackLead(variant, flooring) {
		track('cjfl_lead', { form: variant, flooring: flooring || '' });
		try {
			if (typeof window.gtag === 'function') {
				window.gtag('event', 'generate_lead', { form_id: 'cjfl_' + variant, flooring_type: flooring || '' });
			}
			if (typeof window.fbq === 'function') {
				window.fbq('track', 'Lead');
			}
		} catch (e) { /* ignore */ }
	}

	doc.addEventListener('click', function (event) {
		var el = event.target.closest ? event.target.closest('[data-cjfl-track]') : null;
		if (el) {
			track('cjfl_click', { cta: el.getAttribute('data-cjfl-track') });
		}
	});

	/* ------------------------------------------------------------------ */
	/* UTM / click-id capture (first touch of the session)                 */
	/* ------------------------------------------------------------------ */
	function readTracking() {
		var found = {};
		try {
			var params = new URLSearchParams(window.location.search);
			TRACK_KEYS.forEach(function (key) {
				var value = params.get(key);
				if (value) { found[key] = value.slice(0, 120); }
			});
		} catch (e) { /* old browser */ }

		try {
			if (Object.keys(found).length) {
				window.sessionStorage.setItem('cjfl_tracking', JSON.stringify(found));
				return found;
			}
			var saved = window.sessionStorage.getItem('cjfl_tracking');
			return saved ? JSON.parse(saved) : {};
		} catch (e) {
			return found;
		}
	}

	var tracking = readTracking();

	/* ------------------------------------------------------------------ */
	/* Contact buttons: go to the form that makes sense                    */
	/* ------------------------------------------------------------------ */
	// True while the visitor has not scrolled past the quick form at the top of the page.
	function quickFormStillAhead(el) {
		return !!el && el.getBoundingClientRect().bottom > 120;
	}

	function focusFirstField(form) {
		var field = form && form.querySelector('input:not([type="hidden"]):not([tabindex="-1"]), select, textarea');
		if (field) {
			try { field.focus({ preventScroll: true }); } catch (e) { field.focus(); }
		}
	}

	function goToForm(event) {
		var heroForm = doc.getElementById('cjfl-hero-form');
		var target;

		// Near the top of the page the quick form is the closest one; further down, go to the full contact section.
		if (quickFormStillAhead(heroForm)) {
			target = doc.getElementById('estimate');
		} else {
			target = doc.getElementById('quote') || doc.getElementById('estimate');
		}
		if (!target) { return; }

		event.preventDefault();
		var form = target.querySelector('form') || (target.id === 'quote' ? doc.getElementById('cjfl-main-form') : null);
		var header = doc.querySelector('.cjfl-header');
		var offset = (header ? header.offsetHeight : 84) + 16; // the sticky header must not cover the form
		var top = target.getBoundingClientRect().top + window.pageYOffset - offset;
		window.scrollTo({ top: Math.max(0, top), behavior: reduceMotion ? 'auto' : 'smooth' });
		window.setTimeout(function () { focusFirstField(form); }, reduceMotion ? 0 : 650);
	}

	Array.prototype.forEach.call(doc.querySelectorAll('[data-cjfl-scroll]'), function (link) {
		link.addEventListener('click', goToForm);
	});

	/* ------------------------------------------------------------------ */
	/* Lead form                                                           */
	/* ------------------------------------------------------------------ */
	function digits(value) {
		return (value || '').replace(/\D+/g, '');
	}

	function fieldWrap(input) {
		return input.closest ? input.closest('.cjfl-field') : input.parentNode;
	}

	function setError(input, message) {
		var wrap = fieldWrap(input);
		if (!wrap) { return; }
		wrap.classList.add('has-error');
		input.setAttribute('aria-invalid', 'true');
		var id = input.id + '-error';
		var err = doc.getElementById(id);
		if (!err) {
			err = doc.createElement('p');
			err.id = id;
			err.className = 'cjfl-field__error';
			wrap.appendChild(err);
		}
		err.textContent = message;
		input.setAttribute('aria-describedby', id);
	}

	function clearError(input) {
		var wrap = fieldWrap(input);
		if (wrap) { wrap.classList.remove('has-error'); }
		input.removeAttribute('aria-invalid');
		input.removeAttribute('aria-describedby');
		var err = doc.getElementById(input.id + '-error');
		if (err && err.parentNode) { err.parentNode.removeChild(err); }
	}

	function validate(form) {
		var problems = [];
		var name = form.elements.name;
		var phone = form.elements.phone;
		var email = form.elements.email;

		if (!name || name.value.replace(/\s+/g, ' ').trim().length < 2) {
			problems.push([name, 'Please enter your name.']);
		}
		var count = digits(phone && phone.value).length;
		if (!phone || count < 10 || count > 15) {
			problems.push([phone, 'Please enter a valid phone number.']);
		}
		if (email && email.value.trim() !== '' && !/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(email.value.trim())) {
			problems.push([email, 'Please enter a valid email address.']);
		}
		return problems;
	}

	// " at 954-671-8595" taken from the page itself, so it always matches the phone set in the plugin settings.
	function phoneSuffix() {
		var link = doc.querySelector('a[href^="tel:"]:not(.cjfl-btn):not(.cjfl-sticky__btn)') || doc.querySelector('a[href^="tel:"]');
		var number = link ? (link.textContent || '').replace(/^\s*(Call|Phone:)?\s*/i, '').trim() : '';
		return number ? ' at ' + number : '';
	}

	function showStatus(form, message, isError) {
		var box = form.querySelector('.cjfl-form__status');
		if (!box) { return; }
		box.textContent = message;
		box.classList.toggle('is-error', !!isError);
		if (message) { box.focus(); }
	}

	function getNonce(endpoint) {
		return fetch(endpoint + (endpoint.indexOf('?') === -1 ? '?' : '&') + 'action=cjfl_nonce&_=' + Date.now(), {
			method: 'GET',
			credentials: 'same-origin',
			cache: 'no-store'
		}).then(function (response) {
			return response.json();
		}).then(function (json) {
			if (!json || !json.success || !json.data || !json.data.nonce) {
				throw new Error('nonce');
			}
			return json.data.nonce;
		});
	}

	function addHidden(form, name, value) {
		var input = form.querySelector('input[type="hidden"][name="' + name + '"]');
		if (!input) {
			input = doc.createElement('input');
			input.type = 'hidden';
			input.name = name;
			form.appendChild(input);
		}
		input.value = value;
	}

	function initForm(form) {
		var endpoint = form.getAttribute('action');
		var variant = form.getAttribute('data-cjfl-form') || 'main';
		var button = form.querySelector('button[type="submit"]');
		var label = button ? button.querySelector('.cjfl-btn__label') : null;
		var idleText = label ? label.textContent : '';
		var busy = false;

		function setBusy(state) {
			busy = state;
			if (!button) { return; }
			button.disabled = state;
			button.setAttribute('aria-busy', state ? 'true' : 'false');
			if (label) { label.textContent = state ? 'Sending...' : idleText; }
		}

		// Clear a field's error as soon as the visitor edits it.
		Array.prototype.forEach.call(form.querySelectorAll('input, select, textarea'), function (input) {
			input.addEventListener('input', function () { clearError(input); });
		});

		form.addEventListener('submit', function (event) {
			event.preventDefault();
			if (busy) { return; }

			Array.prototype.forEach.call(form.querySelectorAll('[aria-invalid]'), clearError);
			showStatus(form, '', false);

			var problems = validate(form);
			if (problems.length) {
				problems.forEach(function (p) { if (p[0]) { setError(p[0], p[1]); } });
				if (problems[0][0]) { problems[0][0].focus(); }
				return;
			}

			if (!window.fetch || !window.FormData || !window.Promise) {
				showStatus(form, 'Your browser cannot send this form. Please call us' + phoneSuffix() + '.', true);
				return;
			}

			setBusy(true);
			addHidden(form, 'source_url', window.location.href.split('#')[0]);
			addHidden(form, 'referrer', doc.referrer || '');
			Object.keys(tracking).forEach(function (key) { addHidden(form, key, tracking[key]); });

			getNonce(endpoint).then(function (nonce) {
				var data = new FormData(form);
				data.set('_cjfl_nonce', nonce);
				return fetch(endpoint, { method: 'POST', body: data, credentials: 'same-origin' });
			}).then(function (response) {
				return response.json().then(function (json) {
					return { ok: response.ok, json: json };
				});
			}).then(function (result) {
				var json = result.json || {};
				if (result.ok && json.success) {
					var flooring = form.elements.flooring ? form.elements.flooring.value : '';
					var done = form.querySelector('.cjfl-form__done');
					var msg = done ? done.querySelector('[data-cjfl-message]') : null;
					if (msg) { msg.textContent = (json.data && json.data.message) || ''; }
					form.classList.add('is-sent');
					if (done) {
						done.hidden = false;
						done.setAttribute('tabindex', '-1');
						done.setAttribute('role', 'status');
						done.focus();
					}
					trackLead(variant, flooring);
					return;
				}
				var data = json.data || {};
				if (data.fields) {
					var first = null;
					Object.keys(data.fields).forEach(function (key) {
						var input = form.elements[key];
						if (input) {
							setError(input, data.fields[key]);
							if (!first) { first = input; }
						}
					});
					if (first) { first.focus(); }
					return;
				}
				showStatus(form, data.message || 'Something went wrong. Please try again or call us.', true);
			}).catch(function () {
				showStatus(form, 'We could not send your request. Please try again or call us' + phoneSuffix() + '.', true);
			}).then(function () {
				setBusy(false);
			});
		});
	}

	Array.prototype.forEach.call(doc.querySelectorAll('form[data-cjfl-form]'), initForm);
})();
