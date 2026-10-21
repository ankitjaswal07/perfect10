=== Perfect 10 ===
Contributors: perfect10
Tags: directory, custom-post-types, custom-taxonomies, one-column, two-columns, custom-colors, custom-menu, featured-images, threaded-comments, translation-ready, editor-style
Requires at least: 6.1
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.0.0
License: GNU General Public License v2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A fully dynamic WordPress theme for a premium companion directory, built around custom post types, taxonomies, AJAX filtering and a dark/gold editorial design system.

== Description ==

Perfect 10 is a classic (non-block) WordPress theme that reproduces a luxury
"Los Angeles · Orange County" companion directory as a genuinely dynamic site:
every card, city, type, review and price on the page is driven by WordPress
data, and every section is editable from the Customizer.

= What makes it dynamic =

* `p10_profile` custom post type with 40+ editable meta fields (age, height,
  rates, physical stats, services, availability, gallery, video, quote).
* `p10_review` and `p10_package` custom post types.
* Four taxonomies: `p10_county`, `p10_city` (hierarchical), `p10_category`
  (profile type) and `p10_nationality`.
* Nested rewrite rules so URLs read `/{county}/{city}/{profile}/`, matching the
  original site's hash routes 1:1.
* A cascading county → city search with all 211 real Los Angeles and Orange
  County locations bundled in `inc/locations.php`.
* Filter and sort the directory by county, city, type, nationality, premium,
  verified, availability, rate ceiling and age range — via query args, a
  sidebar form, or the `perfect10/v1` REST endpoints.
* 134 Customizer controls covering hero, marquee, search, the five numbered
  homepage sections, the call band, about blocks, advertisers, footer, age
  gate and brand colours (which map onto CSS custom properties live).
* Four front-end forms (booking, register, concierge, per-profile enquiry) that
  write to a non-public `p10_enquiry` post type with a status workflow, an
  admin list table and email notifications.
* Five widgets, eleven shortcodes, JSON-LD structured data, Open Graph/Twitter
  tags and an 18+ age gate.

= First run =

1. Activate the theme.
2. Go to **Perfect 10 → Setup & Import** in the admin menu and click **Run the
   importer**. This creates the 2 counties, all 211 cities and districts, the 6
   profile types, 10 nationalities, 6 utility pages, 7 sample profiles, 3 guest
   reviews, 3 premium packages, the primary menu and sideloads stock imagery.
3. Everything already present is left untouched, so the importer is safe to
   re-run.

The theme is usable before the importer runs: `inc/locations.php` is read
directly so the county/city dropdowns and the JavaScript city map are already
populated on activation.

== Frequently Asked Questions ==

= Where do I edit the homepage copy? =

Appearance → Customize → Perfect 10. Each numbered homepage section (§01–§05)
has its own panel with a number, kicker, title, italic accent, action link and
an "enabled" checkbox.

= How do I change the gold/cream colours? =

Appearance → Customize → Perfect 10 → Brand. The four colour controls write
straight to CSS custom properties and preview live in the Customizer.

= How do profiles get ordered on the homepage? =

Set the **Featured order** field on a profile (lower numbers appear first) and
tick **Premium**. The homepage "Muses" grid uses featured order; the "Premium
Collection" grid uses the premium flag.

= How do the nested URLs work? =

`inc/post-types.php` registers `p10_add_nested_rules()` on `init` priority 20,
producing `/{county}/{city}/{profile}/`, `/{county}/{profile}/` and
`/profiles/{slug}/`. Rules are flushed automatically once per theme version.
If permalinks look wrong, visit Settings → Permalinks or use the "Flush
permalinks" button on the setup screen.

= Can I disable the age gate? =

Yes — Appearance → Customize → Perfect 10 → Age Gate. It sets a `p10_age_ok`
cookie and also works without JavaScript via a `?p10_age=accept` query arg.

== Shortcodes ==

* `[p10_profiles limit="6" premium="1" verified="1" county="" city="" type="" columns="3" numbered="0"]`
* `[p10_cities limit="9" county="los-angeles-county"]`
* `[p10_types limit="6"]`
* `[p10_reviews limit="3"]`
* `[p10_packages]`
* `[p10_search]`
* `[p10_form type="booking" submit="Send reservation request"]`
* `[p10_phone label="Call us" class="p10-btn p10-btn--gold"]`
* `[p10_stats]`
* `[p10_section name="callband|about|advertisers|marquee|concierge"]`
* `[p10_button url="/booking/" label="Reserve" style="gold" size="lg"]`

== Page templates ==

* **P10 — Directory** (`page-templates/directory.php`): filterable listing.
* **P10 — Reservation Form** (`page-templates/booking.php`).
* **P10 — Create a Listing** (`page-templates/register.php`).
* **P10 — Premium Packages** (`page-templates/packages.php`).
* **P10 — All Cities** (`page-templates/cities.php`): A–Z index by county.
* **P10 — Blank Landing** (`page-templates/blank.php`).

== Image sizes ==

`p10-hero` 2400×1400, `p10-card` 900×1200, `p10-card-wide` 1400×800,
`p10-gallery` 1600×1200, `p10-thumb` 480×640, `p10-avatar` 160×160.
Regenerate thumbnails after activating if you have existing media.

== Accessibility ==

Skip link, focus-visible rings, `aria-expanded`/`aria-hidden`/`aria-current`
state on every disclosure, labelled form fields with required indicators, a
prefers-reduced-motion path that disables all animation, and an age gate that
traps focus and restores it on close.

== Copyright ==

Perfect 10 WordPress Theme, (C) 2026
License: GNU GPL v2 or later.

Bundled placeholder artwork (`assets/images/placeholder-*.svg`, `favicon.svg`)
is original to this theme and licensed under the same terms.

The demo importer sideloads stock photography from the Pexels CDN for hero,
city and profile-type imagery. Those images are provided under the Pexels
license and are for demonstration only — replace them with your own media
before going live. Set `add_filter( 'p10_demo_fetch_remote', '__return_false' )`
to skip remote downloads entirely and use the bundled placeholders.

== Changelog ==

= 1.0.0 =
* Initial release.

== 1.1.0 integration notes ==
See demo/README.md and the repository docs/UPGRADE-GUIDE.md for the authorized-source batch importer, Elementor Free dynamic widgets/full-width template and website assistant setup. Do not confuse the new Appearance > Authorized Source Import with the original sample-site importer. Source photo binaries are not included. Local assistant retrieval is default; follow-up is opt-in and disabled until privacy setup. Elementor editor UI and real Pro conditions still require staging acceptance testing.
