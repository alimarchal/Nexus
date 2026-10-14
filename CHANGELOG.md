# Changelog

All notable changes to Nexus. Issue and pull request numbers refer to https://github.com/alimarchal/Nexus.

## [2.0.0] - 2026-10-14

A major release: the old BAJK paper Account Opening Form, maker-checker for File Management, AKSIC access control and a round of security and bug fixes. It rolls up 1.3.0, 1.4.0 and 1.5.0 below.

### Highlights
- **Old account opening form**: "+ Old account opening" button, the full 19-section BAJK paper form with view, print and maker-checker (#115-#121).
- **File management maker-checker** with approval lock, per-record super-admin unlock and a full access trail.
- **ORU record categories** (20 official categories with descriptions) replace the old file categories (#122).
- **AKSIC**: configurable API address, separate view / sync / status permissions, 422 validation (#135-#140).
- **Security**: audit and complaint data endpoints now need their view permissions (#142).
- **Create Box** works for the super admin; branch details page; Account Opening option lists load on every `migrate`.

### Upgrading
Run `php artisan migrate --force`, then `php artisan config:clear`. Fill `resources/paper-form/terms.txt` and `indemnity.txt` with the bank's official text, and confirm `AKSIC_API_URL` with the AKSIC team.

## [1.5.0] - 2026-10-14

### Added
- **AKSIC permissions** (#137, #140): `view aksic applications`, `sync aksic applications` and `update aksic application status`. Sync and status update write to the government AKSIC system and were open to every user; now they, and the applications list, details and PDF, are permission-guarded and their buttons are hidden without the permission. Run `php artisan migrate` (existing roles get them from `view aksics` / `approve aksics`).
- **Branch details page** (#141) with the branch's region, district and users, and a View button in the Branches list.
- `AKSIC_API_URL` setting (#135, #138) for the base address of the AKSIC government API.

### Changed
- The AKSIC API now defaults to `https://pmylp.admin.gok.pk/api/bank` (confirm with the AKSIC team) (#135, #138).
- **Create Box** (#143): a super admin chooses the office the box belongs to; other users without an office get a clear message instead of a bare 403. Box numbers are sequenced across all offices so two offices can no longer clash.

### Fixed
- AKSIC status update with missing fields returned a logged HTTP 500; it is now a 422 validation error (#136, #139).
- `/products/audits/{id}/full`, the complaint full data, the complaint analytics data, analytics page, export and attachment download were open to every logged-in user; they now need `view audits` / `view complaints` (#142).
- `/settings/branch/branches/{id}` gave a server error because its view did not exist (#141).

## [1.4.0] - 2026-10-13

### Added
- **File management maker-checker** with permissions `submit`, `approve` and `unlock file management systems`: draft → submitted → approved, reject with a reason, approval locks the record, and a super admin unlocks one record at a time with a reason. Run `php artisan migrate`.
- **Access trail** on every file record: field changes with old and new values, who opened the record, who opened each scanned page, and every workflow action.
- Approval badge and filter on the file list; settings `FMS_MAKER_CHECKER_ENABLED`, `FMS_ALLOW_SELF_APPROVAL`, `FMS_LOCK_AFTER_APPROVAL`.

### Fixed
- Choosing the same nationality twice, or entering the same identification document twice, gave a server error (HTTP 500); it is now a validation message.
- A stray empty template on the Identification step raised a JavaScript error in the browser.
- Old paper form: the Hold Mail and Type of Customer choices of business accounts shared a name with the individual ones, so a browser could not tick them.

## [1.3.0] - 2026-10-13

### Added
- **Old account opening** (#120, #121). A second button, **+ Old account opening**, next to **+ New account opening**; each request records whether it was made with the Old or the New form (list, filter, request page, print). The old BAJK paper form is built in full: 19 sections in paper order, conditional sections (individual / business / joint applicant 2), Other (specify) boxes, mandatory checks before submission, a documents checklist per nature of account, and a view and print in paper layout. It never mixes with the new form. Run `php artisan migrate`.
- Paper-form fields the system lacked (#115, #116, #117): Key / Secret Word (encrypted), preferred language, Hold Mail for individuals, supplementary card, other banks, other BAJK products, introducer, existing BAJK relationships, contact persons, expected average balance.
- **ORU record categories** (#122): the 20 official Categories of Branch Records to Be Stored in the Old Record Unit, with a Description on the create, edit and list pages and under the category picker. Files in the old categories move to the matching new one.
- `php artisan aof:sync-reference-data` and a migration that runs it (#119).
- README: full documentation of the modules, flows, configuration, deployment and troubleshooting.

### Changed
- Account Opening options (#118): JPY, Foreign Currency Term and BMBA accounts, Silver and Gold debit cards, the Illiterate special category, Family Business as source of funds, Hold Mail for individual accounts.
- The mandatory-field star is red and sits after the label on every Account Opening field (#123).

### Fixed
- The Account Opening option lists were empty on a production database that was imported and migrated (#119).

### Needs input
- The official Terms & Conditions (1-52) and Indemnity & Undertaking text for the old form go in `resources/paper-form/terms.txt` and `indemnity.txt`.
- Whether the paper form's Relationship ID is the same as the CIF number (#118).

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
