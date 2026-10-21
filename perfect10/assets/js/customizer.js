/**
 * Perfect 10 — Customizer live preview bindings.
 *
 * Only values that already exist as CSS custom properties in
 * inc/design-system.php are previewed here. Anything that changes markup or a
 * whole palette asks the preview to refresh instead.
 */
(function ($) {
  'use strict';

  var root = document.documentElement;

  function setVar(name, value) {
    if (value === undefined || value === null || value === '') {
      root.style.removeProperty(name);
      return;
    }
    root.style.setProperty(name, value);
  }

  function hexToRgb(hex) {
    hex = String(hex || '').replace('#', '');
    if (hex.length === 3) {
      hex = hex.split('').map(function (c) { return c + c; }).join('');
    }
    if (!/^[0-9a-f]{6}$/i.test(hex)) return '';
    return [0, 2, 4].map(function (i) { return parseInt(hex.substr(i, 2), 16); }).join(' ');
  }

  function bindColor(setting, cssVar, rgbVar) {
    wp.customize(setting, function (value) {
      value.bind(function (to) {
        setVar(cssVar, to || '');
        if (rgbVar) setVar(rgbVar, hexToRgb(to));
      });
    });
  }

  function bindText(setting, selector, attr) {
    wp.customize(setting, function (value) {
      value.bind(function (to) {
        var $el = $(selector);
        if (!$el.length) return;
        if (attr) {
          $el.attr(attr, to);
        } else {
          $el.text(to);
        }
      });
    });
  }

  function bindNumber(setting, cssVar, transform) {
    wp.customize(setting, function (value) {
      value.bind(function (to) {
        var number = parseFloat(to);
        if (isNaN(number)) return;
        setVar(cssVar, transform ? transform(number) : number);
      });
    });
  }

  $(function () {
    /* ---------------------------------------------------------- palette */
    bindColor('p10_color_gold', '--p10-gold', '--p10-gold-rgb');
    bindColor('p10_color_ink', '--p10-ink', '--p10-ink-rgb');
    bindColor('p10_color_cream', '--p10-cream', '--p10-cream-rgb');
    bindColor('p10_color_panel', '--p10-panel', '--p10-panel-rgb');

    // Semantic roles. Roles that mirror a legacy token are updated together so
    // the preview matches the resolved tokens printed by PHP.
    bindColor('p10_color_gold', '--p10-color-primary', '--p10-gold-rgb');
    bindColor('p10_color_cream', '--p10-color-text', '--p10-cream-rgb');
    bindColor('p10_color_ink', '--p10-color-background', '--p10-ink-rgb');
    bindColor('p10_color_panel', '--p10-color-surface', '--p10-panel-rgb');
    bindColor('p10_color_secondary', '--p10-color-secondary');
    bindColor('p10_color_secondary', '--p10-ink-3');
    bindColor('p10_color_accent', '--p10-color-accent');
    bindColor('p10_color_accent', '--p10-gold-soft');
    bindColor('p10_color_muted', '--p10-color-muted');
    bindColor('p10_color_muted', '--p10-muted');
    bindColor('p10_color_border', '--p10-color-border');
    bindColor('p10_color_link', '--p10-color-link');
    bindColor('p10_color_link_hover', '--p10-color-link-hover');
    bindColor('p10_color_focus', '--p10-color-focus');
    bindColor('p10_color_button_bg', '--p10-color-button-bg');
    bindColor('p10_color_button_text', '--p10-color-button-text');
    bindColor('p10_color_button_secondary_text', '--p10-color-button-secondary-text');
    bindColor('p10_color_button_secondary_border', '--p10-color-button-secondary-border');
    bindColor('p10_color_form_bg', '--p10-color-form-bg');
    bindColor('p10_color_form_text', '--p10-color-form-text');
    bindColor('p10_color_form_border', '--p10-color-form-border');

    /* ------------------------------------------------------------ footer */
    bindColor('p10_footer_bg_color', '--p10-footer-bg');
    bindColor('p10_footer_text_color', '--p10-footer-text');
    bindColor('p10_footer_link_color', '--p10-footer-link');
    bindColor('p10_footer_accent_color', '--p10-footer-accent');

    /* ------------------------------------------------- type and layout */
    wp.customize('p10_font_display', function (value) {
      value.bind(function (to) {
        if (to) setVar('--p10-display', '"' + to + '","Times New Roman",Georgia,serif');
      });
    });
    wp.customize('p10_font_body', function (value) {
      value.bind(function (to) {
        if (to) setVar('--p10-body', '"' + to + '",-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif');
      });
    });
    bindNumber('p10_type_scale', '--p10-type-scale', function (n) { return n / 100; });
    wp.customize('p10_heading_weight', function (value) {
      value.bind(function (to) {
        if (to) setVar('--p10-heading-weight', to);
      });
    });
    bindNumber('p10_heading_tracking', '--p10-heading-tracking', function (n) { return (n / 1000) + 'em'; });
    bindNumber('p10_body_line_height', '--p10-body-line', function (n) { return n / 100; });
    bindNumber('p10_container_width', '--p10-shell', function (n) { return 'min(97vw,' + n + 'px)'; });
    bindNumber('p10_container_width', '--p10-shell-wide', function (n) { return 'min(97vw,' + n + 'px)'; });
    bindNumber('p10_radius', '--p10-radius', function (n) { return n + 'px'; });
    bindNumber('p10_card_radius', '--p10-radius-lg', function (n) { return n + 'px'; });
    bindNumber('p10_section_scale', '--p10-section-scale', function (n) { return n / 100; });
    bindNumber('p10_section_scale', '--p10-section-pad', function (n) { return 'calc(clamp(4.5rem,10vw,9rem) * ' + (n / 100) + ')'; });
    bindNumber('p10_section_scale', '--p10-section-pad-tight', function (n) { return 'calc(clamp(3rem,6vw,5rem) * ' + (n / 100) + ')'; });

    /* -------------------------------------------------------------- hero */
    bindText('p10_brand_name', '.p10-brand__name');
    bindText('p10_brand_suffix', '.p10-brand__sub');
    bindText('p10_hero_title_before', '[data-p10-hero-before]');
    bindText('p10_hero_title_accent', '[data-p10-hero-accent]');
    bindText('p10_hero_tagline', '[data-p10-hero-tagline]');
    bindText('p10_hero_eyebrow', '[data-p10-hero-eyebrow]');

    bindNumber('p10_hero_overlay', '--p10-hero-overlay', function (n) { return n / 100; });
    bindColor('p10_hero_overlay_color', '--p10-hero-overlay-color', '--p10-hero-overlay-rgb');
    bindNumber('p10_hero_min_height', '--p10-hero-min-height', function (n) { return n + 'svh'; });

    /* ----------------------------------------------------------- widgets */
    bindNumber('p10_marquee_speed', '--p10-marquee-duration', function (n) { return (n || 38) + 's'; });
    bindNumber('p10_directory_columns', '--p10-grid-columns');

    /* ------------------------------------- settings that change markup/CSS */
    [
      'p10_color_preset',
      'p10_hero_background_type',
      'p10_hero_video_url',
      'p10_hero_video_id',
      'p10_hero_poster',
      'p10_hero_mobile_image',
      'p10_hero_bg_position',
      'p10_hero_text_align',
      'p10_hero_image',
      'p10_font_display',
      'p10_font_body',
      'p10_marquee_enabled',
      'p10_search_enabled',
      'p10_muses_enabled',
      'p10_premium_enabled',
      'p10_types_enabled',
      'p10_cities_enabled',
      'p10_call_enabled',
      'p10_about_enabled',
      'p10_reviews_enabled',
      'p10_ads_enabled',
      'p10_age_gate_enabled',
      'p10_concierge_enabled',
      'p10_ads_image',
      'p10_muses_count',
      'p10_premium_count',
      'p10_types_count',
      'p10_cities_count',
      'p10_reviews_count'
    ].forEach(function (id) {
      wp.customize(id, function (value) {
        value.bind(function () {
          wp.customize.preview.send('refresh');
        });
      });
    });
  });
})(jQuery);
