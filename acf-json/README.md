# ACF Local JSON

This directory is the version-controlled Local JSON location for theme-owned ACF field groups that are maintained through the WordPress ACF admin UI.

ACF automatically saves field-group definitions here when this directory exists and is writable, and automatically discovers JSON definitions here when the theme is active. No custom save/load filters are needed while the standard theme location is used.

## Choose the source of truth

- Use Local JSON for editorial schemas maintained in the ACF UI, such as page-builder layouts, shared clone groups, and template-specific content fields.
- Use PHP registration in a focused file under `inc/` for deliberately code-driven groups, programmatically generated definitions, and dynamic choices or behavior.
- Use only one source for each field group. A group key must never be registered in both PHP and JSON.

The existing header, homepage hero, and footer groups remain PHP-registered. Do not export those same group keys into this directory unless an explicit migration removes their PHP registration in the same change.

## Workflow

1. Create or update the field group in the local ACF admin UI.
2. Confirm that ACF created or updated its `group_*.json` file in this directory.
3. Review the JSON diff, especially field keys, location rules, required states, limits, defaults, and return formats.
4. Test the matching template with populated, empty, and missing-ACF states.
5. Commit the JSON together with the PHP, CSS, JavaScript, and documentation that consume the fields.

Local JSON stores field definitions only. Editorial values remain in the WordPress database. Never include secrets, credentials, personal data, or environment-specific values in these files.
