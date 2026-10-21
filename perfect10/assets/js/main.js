/**
 * Perfect 10 — front-end behaviour.
 *
 * Vanilla JS, no dependencies. Every module is defensive so a partial
 * template never throws and blocks the rest of the page.
 */
(function () {
  'use strict';

  var cfg = window.p10Config || {};
  var root = document.documentElement;

  /* ---------------------------------------------------------------- utils */

  function on(el, ev, fn, opts) {
    if (!el) return;
    el.addEventListener(ev, fn, opts || false);
  }

  function all(selector, ctx) {
    return Array.prototype.slice.call((ctx || document).querySelectorAll(selector));
  }

  function setCookie(name, value, days) {
    var d = new Date();
    d.setTime(d.getTime() + days * 864e5);
    document.cookie = name + '=' + encodeURIComponent(value) +
      ';expires=' + d.toUTCString() + ';path=/;SameSite=Lax';
  }

  function prefersReducedMotion() {
    return !!cfg.reducedMotion ||
      (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches);
  }

  /* ------------------------------------------------------------ 1. header */

  function initHeader() {
    var header = document.querySelector('[data-p10-header]');
    if (!header) return;

    var lastY = window.pageYOffset;
    var ticking = false;

    function update() {
      var y = window.pageYOffset;
      header.classList.toggle('is-stuck', y > 40);

      // Hide on scroll down past the hero, reveal on scroll up.
      if (y > 400 && y > lastY + 6) {
        header.classList.add('is-hidden');
      } else if (y < lastY - 6 || y < 200) {
        header.classList.remove('is-hidden');
      }
      lastY = y;
      ticking = false;
    }

    on(window, 'scroll', function () {
      if (!ticking) {
        ticking = true;
        window.requestAnimationFrame(update);
      }
    }, { passive: true });

    update();
  }

  /* ------------------------------------------------------------ 2. drawer */

  function initDrawer() {
    var burger = document.querySelector('[data-p10-burger]');
    var drawer = document.querySelector('[data-p10-drawer]');
    if (!burger || !drawer) return;

    function setOpen(open) {
      burger.setAttribute('aria-expanded', open ? 'true' : 'false');
      drawer.classList.toggle('is-open', open);
      drawer.setAttribute('aria-hidden', open ? 'false' : 'true');
      document.body.classList.toggle('p10-drawer-open', open);
      root.style.overflow = open ? 'hidden' : '';
    }

    on(burger, 'click', function () {
      setOpen(burger.getAttribute('aria-expanded') !== 'true');
    });

    on(document, 'keydown', function (e) {
      if (e.key === 'Escape' && drawer.classList.contains('is-open')) {
        setOpen(false);
        burger.focus();
      }
    });

    // Keep real menu labels inside the fluid header. Never remove links or
    // shrink their type to make them fit; reuse the existing drawer instead.
    var header = burger.closest('.p10-header');
    var inner = header && header.querySelector('.p10-header__inner');
    var nav = header && header.querySelector('.p10-nav');
    var list = nav && nav.querySelector('.p10-nav__list');
    var brand = header && header.querySelector('.p10-brand');
    var actions = header && header.querySelector('.p10-header__actions');
    function updateLayout() {
      if (!inner || !nav || !list || !brand || !actions) return;
      document.body.classList.remove('p10-compact-nav');
      if (window.getComputedStyle(nav).display === 'none') return; // CSS mobile breakpoint.
      var style = window.getComputedStyle(inner);
      var required = brand.getBoundingClientRect().width + list.scrollWidth +
        actions.getBoundingClientRect().width + (parseFloat(style.columnGap) || 0) * 2 +
        (parseFloat(style.paddingLeft) || 0) + (parseFloat(style.paddingRight) || 0);
      var compact = required > inner.clientWidth;
      document.body.classList.toggle('p10-compact-nav', compact);
      // Resizing from the drawer to a fitting desktop menu must unlock page scrolling.
      if (!compact) setOpen(false);
    }
    var layoutFrame;
    on(window, 'resize', function () {
      window.cancelAnimationFrame(layoutFrame);
      layoutFrame = window.requestAnimationFrame(updateLayout);
    });
    if (document.fonts && document.fonts.ready) document.fonts.ready.then(updateLayout);
    updateLayout();

    // Close after navigating.
    all('a', drawer).forEach(function (link) {
      on(link, 'click', function () { setOpen(false); });
    });
  }

  /* ------------------------------------------------------------ 3. marquee */

  function initMarquee() {
    all('[data-p10-marquee]').forEach(function (marquee) {
      var track = marquee.querySelector('.p10-marquee__track');
      if (!track || track.dataset.cloned) return;

      // Duplicate the group once so translate(-50%) loops seamlessly.
      var group = track.firstElementChild;
      if (group) {
        track.appendChild(group.cloneNode(true));
        track.dataset.cloned = '1';
      }
      if (prefersReducedMotion()) {
        track.style.animation = 'none';
      }
    });
  }

  /* ------------------------------------------------------------- 4. reveal */

  function initReveal() {
    var items = all('.p10-reveal');
    if (!items.length) return;

    if (prefersReducedMotion() || !('IntersectionObserver' in window)) {
      items.forEach(function (el) { el.classList.add('is-visible'); });
      return;
    }

    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          observer.unobserve(entry.target);
        }
      });
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 });

    items.forEach(function (el) { observer.observe(el); });
  }

  /* -------------------------------------------- 5. county → city cascade */

  function cityOptions(countySlug, selected) {
    var label = (cfg.i18n && cfg.i18n.allCities) || 'All Cities';
    var html = '<option value="">' + label + '</option>';
    var map = cfg.citiesByCounty || {};
    var groups = countySlug ? [map[countySlug]] : Object.keys(map).map(function (k) { return map[k]; });

    groups.forEach(function (group) {
      if (!group || !group.cities) return;
      if (!countySlug && Object.keys(map).length > 1) {
        html += '<optgroup label="' + group.label + '">';
      }
      group.cities.forEach(function (city) {
        var isSel = selected && city.slug === selected;
        html += '<option value="' + city.slug + '"' + (isSel ? ' selected' : '') + '>' + city.name + '</option>';
      });
      if (!countySlug && Object.keys(map).length > 1) {
        html += '</optgroup>';
      }
    });

    return html;
  }

  function initCityCascade() {
    var counties = all('[data-p10-county]');
    if (!counties.length) return;

    counties.forEach(function (countySelect) {
      var scope = countySelect.closest('form') || document;
      var citySelect = scope.querySelector('[data-p10-city]');

      on(countySelect, 'change', function () {
        if (!citySelect) return;
        var previous = citySelect.value;
        citySelect.innerHTML = cityOptions(countySelect.value, '');
        // Keep the selection if it still exists in the narrowed list.
        if (previous && citySelect.querySelector('option[value="' + previous + '"]')) {
          citySelect.value = previous;
        }
        citySelect.dispatchEvent(new Event('p10:citychange', { bubbles: true }));
      });
    });

    // Populate any city select that ships with a preset county.
    all('[data-p10-city]').forEach(function (citySelect) {
      var preset = citySelect.getAttribute('data-county');
      if (preset && citySelect.options.length <= 1) {
        citySelect.innerHTML = cityOptions(preset, citySelect.value);
      }
    });
  }

  /* -------------------------------------------------------- 6. search form */

  function initSearchForm() {
    var form = document.querySelector('[data-p10-search-form]');
    if (!form) return;

    on(form, 'submit', function (e) {
      var county = form.querySelector('[data-p10-county]');
      var city = form.querySelector('[data-p10-city]');
      var countySlug = county ? county.value : '';
      var citySlug = city ? city.value : '';

      // Nothing chosen, or a city without a county: let the plain GET form run.
      if (!countySlug) return;

      var tpl = form.getAttribute('data-url-template') || '';
      if (tpl.indexOf('__county__') === -1) return;

      e.preventDefault();

      var base = tpl.split('__county__')[0];
      var url = base + encodeURIComponent(countySlug) + '/';
      if (citySlug) url += encodeURIComponent(citySlug) + '/';

      window.location.href = url;
    });
  }

  /* ---------------------------------------------------- 7. filter sidebar */

  function initFilters() {
    var form = document.querySelector('[data-p10-filters]');
    if (!form) return;

    // Changing any control re-runs the listing immediately.
    on(form, 'change', function (e) {
      var target = e.target;
      if (target && target.type === 'range') return; // handled on release
      form.submit();
    });

    all('input[type="range"]', form).forEach(function (range) {
      var out = range.closest('.p10-range') && range.closest('.p10-range').querySelector('[data-p10-range-out]');
      function sync() {
        if (out) out.textContent = range.value;
      }
      on(range, 'input', sync);
      on(range, 'change', function () { sync(); form.submit(); });
      sync();
    });

    var reset = form.querySelector('[data-p10-filters-reset]');
    on(reset, 'click', function (e) {
      e.preventDefault();
      window.location.href = reset.getAttribute('href') || window.location.pathname;
    });
  }

  /* -------------------------------------------------------- 8. sort select */

  function initSort() {
    all('[data-p10-sort]').forEach(function (select) {
      on(select, 'change', function () {
        var url = new URL(window.location.href);
        if (select.value) {
          url.searchParams.set('sort', select.value);
        } else {
          url.searchParams.delete('sort');
        }
        url.searchParams.delete('paged');
        window.location.href = url.toString();
      });
    });
  }

  /* ---------------------------------------------------------- 9. gallery */

  function initGallery() {
    var gallery = document.querySelector('[data-p10-gallery]');
    if (!gallery) return;

    var main = gallery.querySelector('[data-p10-gallery-main] img');
    var thumbs = all('[data-p10-gallery-thumb]', gallery);
    var counter = gallery.querySelector('[data-p10-gallery-count]');
    var index = 0;

    if (!main || !thumbs.length) return;

    function show(i) {
      index = (i + thumbs.length) % thumbs.length;
      var thumb = thumbs[index];
      var full = thumb.getAttribute('data-full') || (thumb.querySelector('img') || {}).src;

      if (full) {
        main.style.opacity = '0';
        window.setTimeout(function () {
          main.src = full;
          main.alt = thumb.getAttribute('data-alt') || main.alt;
          main.style.opacity = '';
        }, prefersReducedMotion() ? 0 : 140);
      }
      thumbs.forEach(function (t, n) { t.setAttribute('aria-current', n === index ? 'true' : 'false'); });
      if (counter) counter.textContent = (index + 1) + ' / ' + thumbs.length;
    }

    thumbs.forEach(function (thumb, i) {
      on(thumb, 'click', function () { show(i); });
    });

    on(gallery.querySelector('[data-p10-gallery-prev]'), 'click', function () { show(index - 1); });
    on(gallery.querySelector('[data-p10-gallery-next]'), 'click', function () { show(index + 1); });

    on(gallery, 'keydown', function (e) {
      if (e.key === 'ArrowLeft') { e.preventDefault(); show(index - 1); }
      if (e.key === 'ArrowRight') { e.preventDefault(); show(index + 1); }
    });

    show(0);
  }

  /* ------------------------------------------------------- 10. concierge */

  function initConcierge() {
    var widget = document.querySelector('[data-p10-concierge]');
    if (!widget) return;

    var btn = widget.querySelector('[data-p10-concierge-open]');
    var close = widget.querySelector('[data-p10-concierge-close]');

    function setOpen(open) {
      widget.classList.toggle('is-open', open);
      if (btn) btn.setAttribute('aria-expanded', open ? 'true' : 'false');
      var panel = widget.querySelector('.p10-concierge__panel');
      if (panel) panel.setAttribute('aria-hidden', open ? 'false' : 'true');
      if (open) {
        var first = widget.querySelector('.p10-concierge__panel input, .p10-concierge__panel textarea');
        if (first) window.setTimeout(function () { first.focus(); }, 220);
      }
    }

    on(btn, 'click', function () { setOpen(!widget.classList.contains('is-open')); });
    on(close, 'click', function () { setOpen(false); });
    on(document, 'keydown', function (e) {
      if (e.key === 'Escape' && widget.classList.contains('is-open')) setOpen(false);
    });
    on(document, 'click', function (e) {
      if (widget.classList.contains('is-open') && !widget.contains(e.target)) setOpen(false);
    });
  }

  /* --------------------------------------------------------- 11. age gate */

  function initAgeGate() {
    var gate = document.querySelector('[data-p10-agegate]');
    if (!gate) return;

    var accept = gate.querySelector('[data-p10-agegate-accept]');

    function unlock() {
      setCookie('p10_age_ok', '1', 30);
      gate.style.transition = 'opacity 420ms ease';
      gate.style.opacity = '0';
      document.body.classList.remove('p10-agegate-open');
      root.style.overflow = '';
      window.setTimeout(function () {
        if (gate.parentNode) gate.parentNode.removeChild(gate);
      }, prefersReducedMotion() ? 0 : 420);
      document.body.classList.remove('p10-blur');
      document.dispatchEvent(new Event('p10:ageaccepted'));
    }

    on(accept, 'click', unlock);
    on(document, 'keydown', function (e) {
      if (e.key === 'Enter' && document.activeElement === document.body) unlock();
    });

    // Move focus into the dialog for screen readers.
    if (accept) window.setTimeout(function () { accept.focus(); }, 60);
  }

  /* ------------------------------------------------- 12. offset anchors */

  function initAnchors() {
    on(document, 'click', function (e) {
      var link = e.target.closest ? e.target.closest('a[href^="#"]') : null;
      if (!link) return;

      var id = link.getAttribute('href');
      if (!id || id === '#') return;

      var target = document.querySelector(id);
      if (!target) return;

      e.preventDefault();
      var headerH = (document.querySelector('[data-p10-header]') || {}).offsetHeight || 76;
      var top = target.getBoundingClientRect().top + window.pageYOffset - headerH - 16;

      window.scrollTo({ top: Math.max(top, 0), behavior: prefersReducedMotion() ? 'auto' : 'smooth' });

      if (history.replaceState) history.replaceState(null, '', id);
    });
  }

  /* ------------------------------------------- 13. live count-up for stats */

  function initCountUp() {
    if (prefersReducedMotion()) return;

    var stats = all('[data-p10-count]');
    if (!stats.length || !('IntersectionObserver' in window)) return;

    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;
        observer.unobserve(entry.target);

        var el = entry.target;
        var target = parseInt(el.getAttribute('data-p10-count'), 10);
        if (isNaN(target)) return;

        var suffix = el.getAttribute('data-p10-suffix') || '';
        var duration = 1100;
        var start = null;

        function step(ts) {
          if (!start) start = ts;
          var p = Math.min((ts - start) / duration, 1);
          var eased = 1 - Math.pow(1 - p, 3);
          el.textContent = Math.round(target * eased) + suffix;
          if (p < 1) window.requestAnimationFrame(step);
        }
        window.requestAnimationFrame(step);
      });
    }, { threshold: 0.4 });

    stats.forEach(function (el) { observer.observe(el); });
  }

  /* ------------------------------------------------------- 14. copy phone */

  function initCopyPhone() {
    all('[data-p10-copy]').forEach(function (btn) {
      on(btn, 'click', function (e) {
        e.preventDefault();
        var text = btn.getAttribute('data-p10-copy');
        if (!text || !navigator.clipboard) return;
        navigator.clipboard.writeText(text).then(function () {
          var original = btn.textContent;
          btn.textContent = (cfg.i18n && cfg.i18n.copied) || 'Copied';
          window.setTimeout(function () { btn.textContent = original; }, 1600);
        });
      });
    });
  }

  /* -------------------------------------------------------- 15b. hero video */

  /**
   * Progressively enhance the hero background.
   *
   * Nothing is requested until the hero is close to the viewport, the screen
   * is wider than a phone and the visitor has not asked for reduced motion.
   * A <video> gets its lazy <source> attached; YouTube/Vimeo get a no-cookie
   * iframe. Both pause while off screen.
   */
  function initHeroVideo() {
    var media = document.querySelectorAll('[data-p10-hero-media]');
    if (!media.length) return;

    var canPlay = window.innerWidth > 767 && !prefersReducedMotion();
    var canObserve = 'IntersectionObserver' in window;

    media.forEach(function (wrap) {
      var type = wrap.getAttribute('data-p10-video-type');
      var src = wrap.getAttribute('data-p10-video-src');
      var id = wrap.getAttribute('data-p10-video-id');
      if (!type || !(src || id)) return;

      var video = wrap.querySelector('[data-p10-hero-video]');
      var embed = wrap.querySelector('[data-p10-hero-embed]');
      if (!video && !embed) return;

      var started = false;
      var playing = false;

      function start() {
        if (started) return;
        started = true;

        if (video) {
          var source = video.querySelector('source[data-src]');
          if (source) {
            source.setAttribute('src', source.getAttribute('data-src'));
            video.load();
          }
          var attempt = video.play();
          if (attempt && attempt.catch) attempt.catch(function () {});
          return;
        }

        if (embed && id) {
          var frame = document.createElement('iframe');
          frame.setAttribute('title', '');
          frame.setAttribute('tabindex', '-1');
          frame.setAttribute('aria-hidden', 'true');
          frame.setAttribute('allow', 'autoplay; encrypted-media; picture-in-picture');
          frame.setAttribute('loading', 'lazy');
          frame.setAttribute('referrerpolicy', 'strict-origin-when-cross-origin');
          if (type === 'youtube') {
            frame.src = 'https://www.youtube-nocookie.com/embed/' + encodeURIComponent(id) +
              '?autoplay=1&mute=1&controls=0&loop=1&playlist=' + encodeURIComponent(id) +
              '&playsinline=1&modestbranding=1&rel=0&iv_load_policy=3&disablekb=1';
          } else if (type === 'vimeo') {
            frame.src = 'https://player.vimeo.com/video/' + encodeURIComponent(id) +
              '?autoplay=1&muted=1&loop=1&background=1&dnt=1';
          } else {
            return;
          }
          embed.appendChild(frame);
        }

        wrap.classList.add('is-video-ready');
      }

      if (video) {
        on(video, 'playing', function () {
          playing = true;
          wrap.classList.add('is-video-ready');
          video.style.opacity = '1';
        });
        on(video, 'error', function () {
          wrap.classList.remove('is-video-ready');
          started = false;
        });
      }

      if (!canPlay || !canObserve) return;

      var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            start();
            if (video && playing) {
              var p = video.play();
              if (p && p.catch) p.catch(function () {});
            }
          } else if (video) {
            video.pause();
          }
        });
      }, { rootMargin: '200px 0px', threshold: 0.01 });

      observer.observe(wrap);
    });
  }

  /* ------------------------------------------------------------- 15. init */

  function boot() {
    if (prefersReducedMotion()) root.classList.add('p10-no-motion');

    initHeader();
    initDrawer();
    initMarquee();
    initReveal();
    initCityCascade();
    initSearchForm();
    initFilters();
    initSort();
    initGallery();
    initConcierge();
    initAgeGate();
    initAnchors();
    initCountUp();
    initCopyPhone();
    initHeroVideo();

    document.dispatchEvent(new Event('p10:ready'));
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
