/**
 * Perfect 10 — website assistant.
 *
 * Presentation only: the REST endpoints, retrieval, rate limiting, privacy
 * guards and follow-up consent flow are unchanged. Nothing is persisted in the
 * browser; transcripts live in memory for the lifetime of the dialog.
 */
(function () {
  'use strict';

  var root = document.querySelector('[data-p10-chat]');
  if (!root || typeof p10Chat === 'undefined') return;

  var cfg = p10Chat;
  var i18n = cfg.i18n || {};
  var dialog = root.querySelector('dialog');
  var find = function (name) { return root.querySelector('[data-chat-' + name + ']'); };
  var input = root.querySelector('#p10-chat-input');
  var log = find('log');
  var status = find('status');
  var suggestions = find('suggestions');
  var minimize = find('minimize');
  var busy = false;
  var controller;
  var muted = true;
  var last = '';
  var recognition;
  var flow = null;
  var generation = 0;
  var typing = null;
  var synth = window.speechSynthesis;
  var follow = find('follow');

  function setStatus(message, isError) {
    status.textContent = message || '';
    status.classList.toggle('is-error', !!isError);
  }

  find('provider').textContent = cfg.provider === 'openai'
    ? 'Optional OpenAI relevance ranking is enabled: redacted text and public extracts may be sent to OpenAI. No audio or full conversation is sent. Local answers are used if it is unavailable.'
    : 'Local search mode: no messages are sent to an AI provider.';

  function bubble(text, speaker) {
    var el = document.createElement('div');
    el.className = 'p10-chat-message p10-chat-message--' + speaker;
    var label = document.createElement('strong');
    label.textContent = speaker === 'user' ? (i18n.you || 'You') : (i18n.assistant || 'Assistant');
    el.appendChild(label);
    el.appendChild(document.createTextNode(text));
    log.appendChild(el);
    scrollLog();
    return el;
  }

  function scrollLog() {
    log.scrollTop = log.scrollHeight;
  }

  function button(parent, label, callback) {
    var el = document.createElement('button');
    el.type = 'button';
    el.textContent = label;
    el.addEventListener('click', callback);
    parent.appendChild(el);
    return el;
  }

  function showTyping() {
    if (typing) return;
    typing = document.createElement('div');
    typing.className = 'p10-chat__typing';
    typing.setAttribute('role', 'status');
    typing.setAttribute('aria-label', i18n.typing || 'Assistant is typing');
    for (var i = 0; i < 3; i++) {
      typing.appendChild(document.createElement('span'));
    }
    log.appendChild(typing);
    log.setAttribute('aria-busy', 'true');
    scrollLog();
  }

  function hideTyping() {
    if (typing && typing.parentNode) typing.parentNode.removeChild(typing);
    typing = null;
    log.removeAttribute('aria-busy');
  }

  function renderSuggestions() {
    if (!suggestions) return;
    suggestions.replaceChildren();
    (cfg.suggestions || []).forEach(function (text) {
      var chip = document.createElement('button');
      chip.type = 'button';
      chip.className = 'p10-chat__suggestion';
      chip.setAttribute('data-chat-suggestion', '');
      chip.textContent = text;
      chip.addEventListener('click', function () {
        if (busy || flow) return;
        input.value = text;
        submitMessage();
      });
      suggestions.appendChild(chip);
    });
  }

  function welcome() {
    var text = cfg.welcome || 'How can I help you find published profiles, locations or website information?';
    bubble(text, 'assistant');
    renderSuggestions();
  }

  function stopVoice() {
    if (synth) synth.cancel();
    if (recognition) {
      try { recognition.abort(); } catch (e) { /* already stopped */ }
    }
    find('mic').textContent = 'Use microphone';
  }

  function speak(text) {
    if (!synth || muted || !text) return;
    synth.cancel();
    var speech = new SpeechSynthesisUtterance(text);
    speech.lang = document.documentElement.lang || 'en';
    speech.onerror = function () { setStatus('Voice output unavailable; please read the text.', true); };
    synth.speak(speech);
  }

  function reset() {
    generation++;
    if (controller) controller.abort();
    stopVoice();
    flow = null;
    busy = false;
    last = '';
    if (input) {
      input.value = '';
      input.type = 'text';
      input.maxLength = 600;
    }
    log.replaceChildren();
    hideTyping();
    setStatus('');
    if (follow) follow.hidden = true;
    welcome();
  }

  function openDialog() {
    if (typeof dialog.showModal !== 'function') {
      setStatus('Your browser does not support this chat dialog.', true);
      return;
    }
    dialog.classList.remove('is-minimized');
    if (minimize) minimize.setAttribute('aria-expanded', 'true');
    if (!dialog.open) dialog.showModal();
    find('open').setAttribute('aria-expanded', 'true');
    input.focus();
    scrollLog();
  }

  find('open').addEventListener('click', openDialog);

  find('close').addEventListener('click', function () { dialog.close(); });

  if (minimize) {
    minimize.addEventListener('click', function () {
      var min = !dialog.classList.contains('is-minimized');
      dialog.classList.toggle('is-minimized', min);
      minimize.setAttribute('aria-expanded', String(!min));
      minimize.setAttribute('aria-label', min ? 'Expand assistant' : 'Minimize assistant');
      if (min) {
        find('open').focus();
      } else {
        input.focus();
      }
    });
  }

  dialog.addEventListener('close', function () {
    generation++;
    if (controller) controller.abort();
    busy = false;
    flow = null;
    hideTyping();
    if (input) input.value = '';
    stopVoice();
    find('open').setAttribute('aria-expanded', 'false');
    dialog.classList.remove('is-minimized');
    if (minimize) minimize.setAttribute('aria-expanded', 'true');
    find('open').focus();
    // Contact details collected for an unfinished follow-up stay in memory only.
    log.replaceChildren();
    welcome();
  });

  // Clicking the backdrop closes the dialog (native dialog behaviour keeps Esc).
  dialog.addEventListener('click', function (event) {
    if (event.target === dialog) dialog.close();
  });

  find('stop').addEventListener('click', stopVoice);
  find('reset').addEventListener('click', function () { reset(); input.focus(); });
  find('repeat').addEventListener('click', function () {
    if (!muted) speak(last);
    else setStatus('Enable voice responses first, or read the last response above.');
  });
  find('mute').addEventListener('click', function () {
    muted = !muted;
    if (muted) stopVoice();
    find('mute').textContent = muted ? 'Voice muted' : 'Voice enabled';
    find('mute').setAttribute('aria-pressed', String(muted));
  });
  if (!synth) {
    find('mute').disabled = true;
    find('repeat').disabled = true;
  }

  var Recognition = window.SpeechRecognition || window.webkitSpeechRecognition;
  if (Recognition) {
    find('mic').hidden = false;
    recognition = new Recognition();
    recognition.interimResults = false;
    recognition.lang = document.documentElement.lang || 'en';
    recognition.onresult = function (event) {
      input.value = event.results[0][0].transcript.slice(0, 600);
      setStatus('Review the recognized text, then press Send.');
      input.focus();
    };
    recognition.onerror = function () {
      setStatus('Microphone unavailable or permission denied. Please type instead.', true);
    };
    recognition.onend = function () { find('mic').textContent = 'Use microphone'; };
    find('mic').addEventListener('click', function () {
      if (busy || flow) {
        setStatus('Please finish the current request using text.');
        return;
      }
      try {
        recognition.start();
        find('mic').textContent = 'Listening…';
        setStatus('Browser speech service listening. Stop voice cancels.');
      } catch (e) {
        setStatus('Microphone already active or unavailable. Use text instead.', true);
      }
    });
  }

  async function request(path, payload) {
    controller = new AbortController();
    var timeout = setTimeout(function () { controller.abort(); }, 22000);
    try {
      var response = await fetch(cfg.root + path, {
        method: 'POST',
        credentials: 'same-origin',
        signal: controller.signal,
        headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': cfg.nonce },
        body: JSON.stringify(payload)
      });
      var data = await response.json();
      if (!response.ok) throw new Error(data.message || (i18n.error || 'Request failed.'));
      return data;
    } finally {
      clearTimeout(timeout);
    }
  }

  function sourceLink(parent, url, title) {
    try {
      var parsed = new URL(url, window.location.href);
      if (!/^https?:$/.test(parsed.protocol) || parsed.origin !== window.location.origin) return;
      var a = document.createElement('a');
      a.href = parsed.href;
      a.textContent = title;
      parent.appendChild(a);
    } catch (e) { /* invalid server URL is never rendered */ }
  }

  function errorBubble(message, retry) {
    var el = bubble(message || i18n.error || 'Something went wrong. Please try again.', 'assistant');
    if (retry) {
      button(el, 'Try again', function () {
        el.remove();
        submitMessage(retry);
      });
    }
  }

  function collect(text) {
    if (flow.step === 'consent' || flow.step === 'confirm') {
      bubble('Choose the consent/confirmation button, or reset to cancel. Nothing has been saved.', 'assistant');
      return;
    }
    if (flow.step === 'name') {
      if (text.length > 80) {
        bubble('Please keep the name to 80 characters.', 'assistant');
        return;
      }
      flow.data.name = text;
      flow.step = 'contact';
      bubble('What email address or phone number should the administrator use? Provide just one.', 'assistant');
    } else if (flow.step === 'contact') {
      if (text.indexOf('@') >= 0 && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(text) && text.length <= 150) flow.data.email = text;
      else if (/^\+?[0-9 ()-]{7,40}$/.test(text)) flow.data.phone = text;
      else {
        bubble('Please enter a valid email address or phone number, or reset to cancel.', 'assistant');
        return;
      }
      flow.step = 'location';
      bubble('Which profile or location is this about? Type “skip” if none. This is context only, not a booking.', 'assistant');
    } else if (flow.step === 'location') {
      if (text.length > 120) {
        bubble('Please use no more than 120 characters.', 'assistant');
        return;
      }
      flow.data.location = text.toLowerCase() === 'skip' ? '' : text;
      flow.step = 'message';
      bubble('Please type a short enquiry (600 characters maximum). Do not include sensitive information.', 'assistant');
    } else if (flow.step === 'message') {
      flow.data.message = text;
      flow.step = 'confirm';
      var generationAtConfirmation = generation;
      var el = bubble('Save this name, contact detail, profile/location context and enquiry for administrator follow-up? No chat transcript will be saved. Reset cancels.', 'assistant');
      button(el, 'Consent and save enquiry', async function () {
        if (!flow || flow.step !== 'confirm' || busy || generation !== generationAtConfirmation) return;
        busy = true;
        setStatus('Saving your consented enquiry…');
        try {
          var result = await request('/followup', Object.assign({}, flow.data, { consent: true, notice: cfg.notice }));
          if (generation !== generationAtConfirmation) return;
          flow = null;
          log.replaceChildren();
          bubble(result.message, 'assistant');
          setStatus('Saved. ' + result.notification.replace(/_/g, ' '));
        } catch (e) {
          if (generation === generationAtConfirmation) {
            setStatus(e.name === 'AbortError' ? 'Request timed out or was cancelled. Saving may have reached the server; check before retrying.' : e.message, true);
          }
        } finally {
          if (generation === generationAtConfirmation) busy = false;
        }
      });
    }
  }

  follow.addEventListener('click', function () {
    if (!cfg.followup || busy || flow) return;
    stopVoice();
    flow = { step: 'consent', data: {} };
    follow.hidden = true;
    var el = bubble('Optional follow-up: with your consent we will collect and save your name, email OR phone, related profile/location and short enquiry in a private administrator area. No chat transcript is saved. See the privacy policy for retention and deletion. Do you want to continue?', 'assistant');
    sourceLink(el, cfg.privacy, 'Privacy policy');
    button(el, 'Yes, continue', function () {
      if (!flow || flow.step !== 'consent') return;
      flow.step = 'name';
      bubble('What name should we use? You can reset or close at any time before saving.', 'assistant');
      input.focus();
    });
    button(el, 'No, cancel', function () { reset(); input.focus(); });
  });

  async function submitMessage(retryText) {
    var text = typeof retryText === 'string' ? retryText : input.value.trim();
    if (!text || busy) return;

    if (!navigator.onLine) {
      setStatus(i18n.offline || 'You appear to be offline. Reconnect and try again.', true);
      return;
    }

    if (!retryText) input.value = '';
    if (flow) {
      collect(text);
      return;
    }

    if (suggestions) suggestions.replaceChildren();
    bubble(text, 'user');
    busy = true;
    setStatus('Searching published website content…');
    showTyping();

    var started = generation;
    try {
      var data = await request('', { message: text });
      if (started !== generation) return;
      hideTyping();
      var el = bubble(data.answer, 'assistant');
      last = data.answer;
      (data.sources || []).forEach(function (source) {
        var row = document.createElement('p');
        sourceLink(row, source.url, source.title);
        if (source.text) row.appendChild(document.createTextNode(' — ' + source.text));
        if (row.childNodes.length) el.appendChild(row);
      });
      sourceLink(el, data.directory, 'Browse directory');
      scrollLog();
      speak(last);
      setStatus((data.provider_status || '').indexOf('fallback') >= 0 ? 'Provider unavailable; local published information shown.' : '');
      follow.hidden = !cfg.followup;
    } catch (e) {
      if (started !== generation) return;
      hideTyping();
      var message = e.name === 'AbortError'
        ? 'Request timed out or was cancelled. Please try again.'
        : (e.message || i18n.error);
      setStatus(message, true);
      errorBubble(message, text);
    } finally {
      if (started === generation) {
        hideTyping();
        busy = false;
      }
    }
  }

  find('form').addEventListener('submit', function (event) {
    event.preventDefault();
    submitMessage();
  });

  window.addEventListener('offline', function () {
    setStatus(i18n.offline || 'You appear to be offline. Reconnect and try again.', true);
  });
  window.addEventListener('online', function () {
    if (status.classList.contains('is-error')) setStatus('');
  });

  reset();
})();
