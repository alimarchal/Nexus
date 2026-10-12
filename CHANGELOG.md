# Changelog

All notable changes to Nexus. Issue and pull request numbers refer to https://github.com/alimarchal/Nexus.

## [1.2.0] - 2026-10-12

### Added
- **Account Opening maker-checker** (#109). `submit`, `approve` and `unlock account openings` permissions; the maker cannot approve their own request; reject with a reason; approval locks the request for every user; only a super admin can unlock one request, with a reason. A per-request **history** records every step save with old → new values, and every action with who and when.
- **Step-wise wizard** (#110). A step opens only when the steps before it are complete; padlocks in the stepper; the request page shows the steps as complete / next / locked. Switch with `AOF_STRICT_STEPS`.
- **Module-wise permissions** (#90, #98). Every permission belongs to a product module; the user and role pickers have module tabs and the permission list filters by module.
- **User page, role page and bulk user actions** (#111, #112): activate, suspend or delete several users at once (never yourself or the last super admin).
- Password show / hide eye on every password field (#86, #97).
- Typed **Economic Sector Code** for the Government / organisation form (#77, #106). Run `php artisan migrate`.
- `HeadOfficeSeeder`, and `migrate:fresh --seed` now also seeds the Account Opening reference data (#88, #92, #99).

### Changed
- Account Opening list and create screens rebuilt like the Users list (#107, #108).
- Users, Roles and Permissions lists rebuilt with KPI cards, tabs, one search box, filter chips, sortable dense tables, density toggle, rows per page and a print layout (#111, #112, #113).
- Initial deposit is a typed amount with a minimum (`AOF_MINIMUM_INITIAL_DEPOSIT`, default 500) instead of a number spinner (#80, #104).
- Mobile numbers: digits only, and exactly 10 digits for `+92` (#78, #101).
- Tax residency fields stay greyed out until the other-country box is ticked; Pakistan and the USA are not offered (#79, #102).
- Terms and Conditions must be accepted, and every signatory needs a signature and thumb impression, before the next step (#81, #82, #103, #105).
- A missing contact number shows a plain-language message instead of the technical field name (#83, #100).
- `.env.example` no longer contains an `AKSIC_API_TOKEN` value.

### Fixed
- `RolesAndPermissionsSeeder` removed the archive, box and account opening permissions from the office roles on a fresh seed, which hid "Archive to box" and box creation and the whole Account Opening module for branch users (#87, #89, #94, #95, #109).
- Replacing only the signature (or only the thumb impression) dropped the other image (#105).
- Transfers: an explanation when the chosen office has no custodian, and a field error instead of a bare 422 for a custodian from another office (#84, #93).
- Editing a file record can no longer leave it without a scanned page (#85, #91).

### Upgrade notes
- `php artisan migrate` (adds the maker-checker columns and history table, the economic sector code column and the new permissions).
- New `.env` options, all optional: `AOF_MINIMUM_INITIAL_DEPOSIT`, `AOF_MAKER_CHECKER_ENABLED`, `AOF_ALLOW_SELF_APPROVAL`, `AOF_LOCK_AFTER_APPROVAL`, `AOF_STRICT_STEPS`. See the README.
- Run the seeders listed under "Upgrading an existing server" in the README if office users miss account opening, archive or box actions.
- The AKSIC token that was in `.env.example` should be treated as exposed: rotate it.

## [1.1.0] - 2026-10-12

### Fixed
- Open issues #76 to #90 (file management, account opening form validation, password toggle, module-wise permissions); see 1.2.0 for the details of each.
  - #76 print button: already fixed on `main` (the shared header button was added the day after the report); regression tests added (#96).
