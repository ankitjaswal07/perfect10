# Perfect 10 theme — upgrade report

Branch: `arena/08600b92-perfect10` · Theme version: 2.0.0 · Target: WordPress 6.x, PHP 7.4–8.3, Elementor Free (Pro optional)

This branch upgrades the **existing** Perfect 10 theme that ships in `lasperfect10.zip`. The archive is unchanged; the working theme now lives in `perfect10/` so it can be reviewed, diffed and installed directly. No theme was rewritten from scratch, no PHP template was replaced by an Elementor dependency, no taxonomy slug or URL changed, and no database schema was altered.

---

## 1. Internal audit — 10 points

| # | Area | Finding at the start | What this branch does |
|---|------|----------------------|-----------------------|
| 1 | Working features | Classic PHP theme with CPTs (`p10_profile`, `p10_review`, `p10_package`, `p10_enquiry`), taxonomies (`p10_county`, `p10_city`, `p10_category`, `p10_nationality`), nested `/{county}/{city}/{profile}/` URLs, 20 Elementor widgets, a PHP concierge and two importers. All of it worked and was kept. | Untouched behaviour is preserved; changes are additive or fix concrete defects. |
| 2 | Incomplete features | Term “cover image” was stored but had no admin picker, no alt text, no SEO fields and no placeholder path. Hero had no video support. Colours were partly hardcoded. Chatbot had no admin appearance settings, typing state or mobile layout. | Term media + alt + SEO fields with a `wp.media` picker and placeholders; hero image/video; a token layer; chatbot settings and states. |
| 3 | Elementor limitations | Widgets existed but the section API was thin, archive/city/type archives had no dedicated Theme Builder locations, and the hero could not use a background video. | Shared control helpers, five additional Theme Builder locations (11 theme locations + the 4 Elementor core locations), full hero background controls, card-layout control on city/type widgets. Free vs Pro is documented per widget. |
| 4 | Hardcoded styles | Header/hero critical CSS, the hero scrim, footer colours and several gold `rgba()` values bypassed the tokens. | Critical CSS now consumes the CSS custom properties; literals converted to tokens or `rgb(var(--…-rgb) / a)`. Deliberate danger/status/print colours are kept as explicit tokens. |
| 5 | Chatbot UI gaps | No typing indicator styling, no error/disconnected state beyond a line of text, no minimise affordance, panel not full-screen on phones, launcher and header not configurable. | Typing bubble, offline/error states, minimise + close, suggestion chips, mobile full-screen, configurable title/subtitle/icon/position/size/palette/radius/shadow/visibility. Security, rate limiting and the knowledge source are unchanged. |
| 6 | Hero image/video limits | Single `<img>`, no poster, no mobile fallback, no overlay control, no alignment control, LCP image not preloaded when a video poster was used. | Background type (image/video), media-library or external video, poster, mobile image, focal position, overlay colour/opacity, height, alignment, responsive, no-JS and reduced-motion fallbacks. |
| 7 | Missing location/profile-type images | Most terms had no image; there was no safe fallback and no documented way to add media without hotlinking. | Per-term media with verified attachment association, responsive `srcset`, explicit placeholder SVG, alt override, and importer support for staged media only (never invented or hotlinked). |
| 8 | Demo importer limits | The source importer trusted the manifest, did not report missing binaries, and could not summarise outcomes. | Manifest + media validation before any write, counts/notices, per-step validation, outcome summary, JSON report export, safe resume. The generic demo importer stays separate and clearly labelled. |
| 9 | SEO/performance | Archives had no reliable canonical, no term-level titles, thin terms were indexable, `noimageindex` was printed for every visitor, and hero images were missing from preload. | Term titles, archive canonicals, noindex for empty terms and filtered views, schema kept in one place, corrected robots output, hero preload for image or poster. |
| 10 | Security/compat/testing | Nonce present on term saves but no capability check; importer had no pre-flight validation; no automated smoke checks. | Capability checks, validated importer input, escaping on all new output, unchanged REST permissions. PHP lint and JS syntax checks added to the review loop; results below. |

---

## 2. What changed

### 2.1 Design system (`inc/design-system.php`, new)

* Existing tokens are kept: `--p10-ink`, `--p10-panel`, `--p10-gold`, `--p10-cream`, `--p10-muted`, `--p10-line`.
* A **semantic layer** is derived from them: `--p10-color-primary`, `-secondary`, `-accent`, `-background`, `-surface`, `-text`, `-muted`, `-border`, `-link`, `-link-hover`, `-focus`, `-button-bg`, `-button-text`, `-button-secondary-text`, `-button-secondary-border`, `-form-bg`, `-form-text`, `-form-border`.
* Role settings can be blank; blank means “inherit from the palette”, so one change to Primary updates header, nav, footer, buttons, links, cards, territory templates, forms, pagination, Elementor widgets and the chatbot.
* Presets: dark editorial (default), ivory, midnight. Presets only change colours/typography — never content or URLs.
* `p10_design_tokens_css()` prints a whitelisted custom-property block once (`wp_head` 20).
* `p10_sync_elementor_kit()` writes the same values into the Elementor kit (4 system colours, up to 10 custom colours, 4 typography entries) and stores a payload hash so an administrator-edited kit is **not** overwritten on every Customizer save (`$force` to override).

### 2.2 Hero (`inc/hero.php` new, `template-parts/hero.php`, `assets/js/main.js`, `assets/css/main.css`)

* `p10_hero_media_html()` renders one media layer used by the PHP hero *and* the Elementor widget.
* Background types: image, video (media library or external MP4/WebM/OGV), YouTube/Vimeo (iframe injected only when appropriate).
* Poster + optional mobile image via `<picture>`; only the matching source is downloaded.
* Inline `--p10-hero-pos`, `--p10-hero-overlay`, `--p10-hero-overlay-color`, `--p10-hero-overlay-rgb`, `--p10-hero-min-height`.
* Video is `muted`, `loop`, `playsinline`, `preload="none"`; the source is attached by `main.js` only on wide viewports, without reduced-motion, and when the hero is in view. Poster remains for no-JS users.
* Hero image (or video poster) is `fetchpriority="high"` + `loading="eager"` and preloaded by `inc/optimize.php`; it is never lazy-loaded.

### 2.3 Location and profile-type media (`inc/meta.php`, `inc/template-tags.php`, `template-parts/card-city.php`, `template-parts/card-type.php`)

* Term fields: `image` (attachment ID, `wp.media` picker), `image_alt`, `seo_title`, `seo_description`, plus existing blurb/county/kind/featured/sort.
* `p10_term_image_data()` / `p10_term_image_id()` / `p10_term_image_tag()` return URL, `srcset`, `sizes`, width/height, alt and a placeholder flag.
* Missing media falls back to `placeholder-city.svg` / `placeholder-profile.svg`; nothing is hotlinked and no unrelated stock image is assigned.
* Card arguments (both partials): `layout` (`overlay` | `stacked`), `show_blurb`, `show_count`, `overlay` 0–90, `ratio`, `focal`, `image` override, `fetchpriority`, `reveal`, `index`.
* Save handler now checks the taxonomy `edit_terms` capability in addition to the nonce.

### 2.4 Elementor (`inc/elementor.php`, `inc/elementor/*`)

* Still **20 existing widgets**, no duplicates. Shared helpers: grid, spacing, query, heading, card, button, term media.
* Theme Builder locations registered (Pro): core header/footer/single/archive plus `p10_search`, `p10_404`, `p10_location_archive`, `p10_type_archive`, `p10_profile_single`, `p10_gallery`, `p10_homepage`, `p10_page`, `p10_county_archive`, `p10_city_archive`, `p10_nationality_archive`.
* Resolution order is dedicated → combined → native (`archive`/`single`), then the original PHP template.
* PHP templates remain the fallback for every route when Elementor or Pro is absent.
* Hero widget keeps its existing content controls and adds background/style/responsive controls; the overlay value is now written as a 0–1 custom property (previously `62%`, which did not match the CSS).

### 2.5 Chatbot (`inc/chatbot.php`, `template-parts/concierge.php`, `assets/css/chatbot.css`, `assets/js/chatbot.js`)

* Settings: title, subtitle, launcher icon (HTTPS URL or bundled glyph), position (left/right), width/height, header/bubble/user/input colours, radius, shadow strength, extra font size, welcome text, up to 4 suggested questions, page-type visibility, provider/model/page IDs/follow-up.
* UI: launcher, native `<dialog>` with `aria-labelledby`/`aria-describedby`, scrollable `role="log"`, distinct bubbles, input + send, typing indicator, suggestion chips, status line, offline/error states, link wrapping, keyboard focus, minimise (collapses the panel while keeping the dialog) and close.
* Mobile: full-screen panel under 600px.
* Unchanged: endpoint routes, nonce + origin check, per-IP and global rate limits, knowledge source, no persistence of chat text, provider config, vendor files.

### 2.6 Importers (`inc/source-import.php`, `demo/README.md`)

* `p10_source_validate()` checks schema, required profile keys, duplicate source IDs, media paths and staged-file counts **before** the run locks.
* `p10_source_summary()` reports per-outcome totals; the screen shows profiles/published/drafts/media/staged counts, missing-binary warnings and a dynamic progress denominator.
* Missing media produces an explicit `missing`/`placeholder` outcome; the run never claims all images were imported.
* JSON mapping/outcome export and resume-safe batch processing retained.
* The generic demo importer (`inc/demo-importer.php` + `inc/demo-content.php`) remains separate; it is sample content, not authorised source content. Documentation updated to match the implementation.

### 2.7 SEO (`inc/seo.php`, `inc/post-types.php`, `inc/age-gate.php`)

* Term titles: explicit `seo_title`, otherwise factual name + parent + brand; paginated variants.
* Archive canonicals for taxonomies, post-type archives and the blog; paged archives canonicalise to their own page.
* `noindex,follow` for search, 404, attachments, empty terms and filtered directory views (single output).
* Profile titles: `Name · City · Brand`.
* `noimageindex` is no longer printed for every visitor (opt back in with `p10_age_gate_noimageindex`).
* Schema (Organization, WebSite, Service/Person/Offer, ItemList, BreadcrumbList, reviews) still comes from one provider and is skipped when Yoast/Rank Math/AIOSEO/SEOPress is active.
* County and city rewrite rules were corrected: a city archive no longer resolved to the county archive, and new counties/cities rebuild nested rules before flushing.

### 2.8 Performance, compatibility, security

* Hardcoded colours replaced by tokens in `main.css`, `widgets.css`, `critical.css`; `rgba()` gold values now use `--p10-gold-rgb`.
* Chatbot CSS/JS load only when the assistant is visible for that page type.
* No core or vendor edits; no schema changes; importer writes go through WP APIs.
* All new output escaped; new inputs sanitised (hex colours, clamps, whitelists, ID lists).

---

## 3. Test results (21 checks)

Environment: WordPress 6.8.3 (SQLite), PHP 8.3 via WordPress Playground, theme mounted read-only, Elementor **not installed** (no reachable plugin source in the sandbox).

| # | Check | Status | Evidence |
|---|-------|--------|----------|
| 1 | Activation | Passed | `switch_theme('perfect10')`, homepage returned 200 |
| 2 | Homepage | Passed | 200, title “Perfect 10 · Los Angeles Premier Companion Directory”, no PHP notices |
| 3 | Elementor editor loads | Blocked | Elementor cannot be installed in this sandbox |
| 4 | Widgets register/render | Blocked | Same; PHP source linted only |
| 5 | Section edit/reorder/hide | Not tested | Requires the editor |
| 6 | Hero image ↔ video | Partially passed | Server markup verified for image, direct video, YouTube (iframe not printed server-side), poster/mobile `<picture>`, overlay vars; playback not visually tested |
| 7 | Location/type images | Partially passed | Term image tag: `lazy`, `srcset`, width/height, alt override, placeholder fallback, per-card override; admin picker not clicked |
| 8 | Theme Builder templates | Blocked | Locations require Elementor Pro |
| 9 | Search/filter/pagination | Partially passed | `/?s=beverly` 200, directory 200; filters and paged archives not exercised |
| 10 | Chatbot | Partially passed | Markup renders with title/subtitle/suggestions, `node --check` OK; conversation not exercised |
| 11 | Global colours | Passed (unit) | Changing Primary changes `--p10-gold` and `--p10-color-button-bg`; visual propagation not browser-tested |
| 12 | Typography/presets | Passed (unit) | `type_scale=120` → `--p10-type-scale:1.2`; ivory preset switches background/text tokens |
| 13 | Local overrides preserved | Not tested | Needs visual comparison |
| 14 | Importer validation | Passed | Manifest valid; counts 44 profiles / 240 media; invalid manifests abort before locking |
| 15 | Repeat imports | Not tested | Batch resume logic reviewed only |
| 16 | Media/galleries | Not tested | No staged binaries in the sandbox |
| 17 | SEO/canonicals | Passed | County/city/type/profile 200 with canonical + titles; empty term `noindex,follow`; schema present once per type |
| 18 | Responsive | Not tested | No browser |
| 19 | PHP/JS errors | Passed | PHP lint 92 files / 0 failures; `node --check` all 5 JS files; 9 URLs with 0 notices |
| 20 | Performance/a11y | Not tested | No Lighthouse run; preload/lazy rules inspected in markup |
| 21 | Elementor-disabled fallback | Passed | Entire site rendered with Elementor inactive |

“Passed” means executed in this sandbox. Anything requiring a browser, Elementor, or staged media is marked blocked/not tested rather than assumed.

---

## 4. Modified files (31)

New: `inc/design-system.php`, `inc/hero.php`.

Changed: `functions.php`; `inc/age-gate.php`, `inc/chatbot.php`, `inc/customizer.php`, `inc/elementor.php`, `inc/helpers.php`, `inc/meta.php`, `inc/optimize.php`, `inc/post-types.php`, `inc/seo.php`, `inc/source-import.php`, `inc/template-tags.php`; `inc/elementor/class-p10-elementor-cities.php`, `class-p10-elementor-hero.php`, `class-p10-elementor-types.php`, `class-p10-elementor-widget.php`; `template-parts/card-city.php`, `card-type.php`, `concierge.php`, `hero.php`; `assets/css/admin.css`, `chatbot.css`, `critical.css`, `main.css`, `widgets.css`; `assets/js/admin.js`, `chatbot.js`, `customizer.js`, `main.js`.

---

## 5. Remaining limitations

* Elementor **Pro** is required for Theme Builder locations, the search/404/gallery locations and display conditions. Free Elementor users get the widgets and native header/footer/single/archive behaviour only.
* Most location and profile-type terms still have no approved photograph. The theme shows an explicit placeholder; media must be supplied with reuse rights (admin picker or staged `WP_CONTENT_DIR/p10-source-media/`).
* `demo/source-manifest.json` lists binaries that are not present in the repository; those rows import with a `missing` outcome until an administrator stages the files.
* The chatbot answers from the site’s published content/provider configuration; answer quality depends on that configuration and was not evaluated here.
* No visual/regression or Lighthouse testing was possible in this environment. Items 3, 4, 5, 8, 13, 15, 16, 18 and 20 remain open.
* Elementor kit sync intentionally skips writing when the kit hash shows administrator edits; use the admin “sync” action to force it.

---

## 6. Upgrade notes

* No options, post meta or term meta are renamed; existing content renders unchanged.
* No rewrite slug changed. County/city rules were fixed, so flush permalinks once after deploying (the theme also flushes on activation and when a county/city changes).
* `hero_background_type` defaults to `image`; existing sites keep their current hero.
* New colour/typography settings default to blank/“inherit”, so the dark editorial look is preserved until an administrator changes it.
