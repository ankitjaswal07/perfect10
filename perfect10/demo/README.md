# Supplied-source demo package

`source-manifest.json` is derived deterministically from the repository's two WXR exports by `scripts/build-demo.py`. It is a minimized review/import artifact, not a media license. Verify content and media reuse rights before public import or redistribution.

42 published profiles are eligible, 2 drafts excluded, 240 attachment references. No source-media binaries are bundled and no external images are hotlinked. The new **Appearance → Authorized Source Import** workflow imports the allowlisted source fields in batches; it does not run the legacy sample-site importer or replace the homepage/menus.

Map attachment relative paths to administrator-staged authorized files in `WP_CONTENT_DIR/p10-source-media/`, protected from direct web access. Missing files result in explicit placeholders, not gallery-success claims. The downloadable admin report is the authoritative result for your destination site. All 42 source/destination paths matched in the disposable test, but a production URL/SEO check is still required.

See repository `docs/UPGRADE-GUIDE.md`, `docs/SOURCE-INVENTORY.md` and `docs/TESTING.md` for field mapping, retries, safety, setup and tested/untested scope. Never publish internal source notes; the builder/importer uses an explicit public-field allowlist and omits verification/admin records.
