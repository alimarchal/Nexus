# Nexus

Nexus is the Bank of Azad Jammu & Kashmir's internal Laravel application: account opening (AOF), AKSIC loan cases and budget, file management and archive boxes, complaints, audits, circulars, stationery, reports, and the user module (users, roles, permissions).

- Laravel 13, PHP 8.4+ (PHP 8.3 is not enough: Symfony 8 needs 8.4), Livewire / Jetstream, Alpine.js, Tailwind CSS
- Spatie packages: permission, activitylog, medialibrary, query-builder
- Tests: Pest

## Contents

1. [Modules](#modules)
2. [Getting started](#getting-started)
3. [Configuration (`.env`)](#configuration-env)
4. [Roles and permissions](#roles-and-permissions)
5. [Account Opening (AOF)](#account-opening-aof): the new form, the old paper form, maker-checker, option lists
6. [File management and ORU categories](#file-management-and-oru-categories)
7. [User module](#user-module)
8. [Deploying and upgrading](#deploying-and-upgrading)
9. [Project layout](#project-layout)
10. [Tests and code style](#tests-and-code-style)
11. [Troubleshooting](#troubleshooting)

## Modules

| Module | What it does | Where |
|---|---|---|
| **Account Opening (AOF)** | Opens customer accounts on the new BAJK form (6-step wizard) or the old BAJK paper form (one 19-section page), with maker-checker approval and a full history | Product → Account openings |
| **File management** | Digital file records with scanned pages, archive boxes, transfers between offices, and the 20 ORU record categories | Product → File management, Settings → File categories |
| **AKSIC** | Loan cases, amortization, claims, budget and rules | Product → AKSIC |
| **Complaints** | Complaints with categories, assignments, escalation, comments, witnesses and metrics | Products → Complaints |
| **Audits** | Audit planning, scope, findings, actions, risks and schedules | Products → Audits |
| **Circulars, stationery, dispatch register, daily positions, employee resources** | Day-to-day registers and records for offices | Product / Products menus |
| **Settings** | Branches, regions, districts, branch targets, managers, categories, users, roles, permissions | Settings |

Access to every module is permission-driven (see [Roles and permissions](#roles-and-permissions)) and every list is limited to the user's own office: branch users see their branch, region users their region's branches, head office and super admin see everything.

## Getting started

```bash
composer install
npm install && npm run build
cp .env.example .env
php artisan key:generate
# set DB_* in .env, then:
php artisan migrate:fresh --seed
php artisan serve
```

`migrate:fresh --seed` creates the roles and permissions, the branches and their branch managers, the head office, all Account Opening option lists and a default super admin:

| Email | Password |
|---|---|
| `superadmin@example.com` | `password` |

Branch managers are `manager<branch code>@bankajk.com` with the password `password@999`. **Change both passwords on any real server.**

## Configuration (`.env`)

Every option below is optional; the value shown is what you get when the line is missing.

### Account Opening (AOF)

| Variable | Default | What it does |
|---|---|---|
| `AOF_MINIMUM_INITIAL_DEPOSIT` | `500` | Smallest initial deposit accepted on the Account and Due Diligence steps. Set the bank's real minimum. |
| `AOF_MAKER_CHECKER_ENABLED` | `true` | Maker-checker: the person who created or submitted a request cannot approve or reject it (a super admin included). |
| `AOF_ALLOW_SELF_APPROVAL` | `false` | Lets one person act as maker and checker (small branches). Ignored when maker-checker is disabled. |
| `AOF_LOCK_AFTER_APPROVAL` | `true` | An approved request is locked for every user. Only a super admin can unlock one particular request, with a reason. |
| `AOF_STRICT_STEPS` | `true` | Step-wise wizard: a step opens only when the steps before it are complete. `false` allows free navigation. |

### File management (`config/file_management.php`)

| Variable | Default | What it does |
|---|---|---|
| `FMS_MAKER_CHECKER_ENABLED` | `true` | Records are approved by a second person before they are final. `false`: no approval, always editable. |
| `FMS_ALLOW_SELF_APPROVAL` | `false` | Lets one person act as maker and checker. |
| `FMS_LOCK_AFTER_APPROVAL` | `true` | An approved record is locked until a super admin unlocks it. |

### AKSIC

| Variable | Default | What it does |
|---|---|---|
| `AKSIC_API_URL` | `https://pmylp.admin.gok.pk/api/bank` | Base address of the government AKSIC API (sync and status updates). Change it here when the API moves; confirm the current address with the AKSIC team. |
| `AKSIC_API_TOKEN` | empty | Token for the AKSIC applications sync. Keep it only in your private `.env`. |
| `AKSIC_EXCEL_IMPORT` | `false` | Shows the Excel template and "Import from Excel". |
| `AKSIC_EXCEL_EXPORT` | `false` | Shows "Export to Excel (CSV)" in the Export / Print menu. |
| `AKSIC_DEMO_DATA` | `false` | Shows "Replace / remove demo cases" (still local and super admin only). |

After changing `.env` on a server run `php artisan config:clear` (or `config:cache`).

## Roles and permissions

Roles seeded by `RolesAndPermissionsSeeder`: `branch`, `region`, `division`, `head-office`, `president-office` and `super-admin`. Permissions are named `<action> <thing>` (for example `create account openings`, `view file categories`) and belong to a product module; the role and user forms group them by module.

Rules of thumb:

- A user sees a menu item and a button only when they hold the permission; direct URLs are guarded too (403).
- Office scope: branch → own branch, region → branches of the region, division / head office / super admin → everything.
- A new permission reaches existing roles only when the seeders run (see [Upgrading](#deploying-and-upgrading)).

## Account Opening (AOF)

Account Opening lists every request with status cards, tabs (Draft, Submitted, Under review, Approved, Rejected), search, filters (form, old / new, branch, dates), sort, density toggle and print. Two buttons start a request:

- **+ New account opening**: the current BAJK AOF, filled in a step-wise wizard.
- **+ Old account opening**: the old BAJK **paper** form, filled on one page in the order of the paper form.

Both buttons need the `create account openings` permission. Each request remembers which form it was made with (**Old** or **New**, shown in the list, on the request page and in the print), and the two forms never share fields or screens.

### New form: the wizard

1. **Start**: choose the printed form (Individual / Joint / Sole proprietor, or Government / Partnership / Company / NGO), the customer category, branch and date. A CIF number is generated.
2. **Customer information (CIF)**, **Identification, address & contact**, **FATCA / CRS & tax residency**, **Account, products & services**, **Applicants & signatures**, **Due diligence & documents**.
3. With `AOF_STRICT_STEPS=true` a step opens only when the previous ones are complete; **Save & exit** always keeps the draft.
4. **Submit for approval** → checker approves or rejects (see [Maker-checker](#maker-checker-both-forms)).

Mandatory fields show a **red \*** after their label; terms and conditions must be accepted and every signatory needs a signature and thumb impression before moving on.

### Old form: the BAJK paper form

Starting an old request needs only the **branch and date**; it has no CIF or account of its own and opens the paper form. The form is drawn from one description (`app/Support/PaperForm`), so the entry screen, the saved view, the print and the validation always agree.

| # | Section | Notes |
|---|---|---|
| 1 | Account header | Branch code and name, account no, Relationship ID, date (branch and date are prefilled) |
| 2 | Currency and type of account | Pak Rupee: Current, PLS Saving, SD, BMBA, Other · Foreign Currency: Current, Saving, Term, BMBA, Other · Currency: USD, GBP, JPY, Euro, Other |
| 3 | Nature of account | Individual (Single / Joint) or Business (Partnership registered / unregistered, Joint Stock public / private, Govt. federal / provincial, Sole proprietorship, Foreign mission, Association / Club / Trust, Other) |
| 4 | Particulars (individual) | Title, S/O, **Key / Secret Word** (min 6, stored encrypted), mailing and permanent address, e-mail, mobile, fax, Hold Mail |
| 5 | Personal information | Applicant 1, and Applicant 2 for Joint: name, S/O D/O W/O, gender, marital status, date of birth, preferred language, nationality, residence, identification, NTN, occupation, employer |
| 6 | Next of kin | Individual accounts |
| 7 | Particulars (business) | Title, company, nature of business, commencement, office and registered address, e-mail, fax, NTN, CNIC (sole proprietorship), Hold Mail, up to 4 contact persons |
| 8 | Existing relationships with BAJK | Business, up to 3 rows |
| 9 | Relationship with other banks | Up to 2 banks: Deposit / Credit Cards / Loans / Other |
| 10 | BAJK Wallet | Silver / Gold card and name on card (individual and sole proprietorship only), supplementary card |
| 11 | Zakat deduction | Yes (only on PLS) / No (affidavit) |
| 12 | Other BAJK products of interest | Credit Cards, Personal Loan, Car Financing, Mortgage, Other |
| 13 | Operating instructions | Singly, Either or Survivor, Jointly, Mandate, Other |
| 14 | Special instructions | Free text |
| 15 | Introducer | Name, CNIC, telephone, address, bank / branch, account no, signature, years and months known, verified by |
| 16 | Terms & Conditions and Indemnity & Undertaking | Official text plus the agreement checkbox |
| 17 | Authorized signature | Up to 4 applicants: name, signature, company stamp |
| 18 | For bank use only | Special category, KYC (individual or business), recommendation, authorized by, branch supervisor / data entry checker |
| 19 | Documents to be obtained | Checklist that follows the nature of account |

How it behaves:

- Sections and fields appear only when they apply: individual sections for Individual accounts, business sections for Business accounts, Applicant 2 for Joint accounts, the card only for individual and sole proprietorship, the documents checklist for the chosen nature. An answer left in a hidden field is dropped on save.
- **Other (specify)** shows a text box only while it is selected.
- **Save** keeps whatever is filled in (formats are always checked: e-mail, dates, row limits). The request page lists the **mandatory (\*) fields still empty**, and **Submit for approval is refused until they are filled**.
- The request page and the print show every option with a ticked or empty box, like the paper form. The Key / Secret Word is shown masked and never printed or written to the history.
- **Terms & Conditions text**: the official points 1-52 and the Indemnity & Undertaking text are read from `resources/paper-form/terms.txt` (one clause per line) and `resources/paper-form/indemnity.txt`. While those files are empty the form shows a notice instead; paste the bank's text in and it appears on the form and in the print.
- Approved old requests are locked like new ones; only a super admin can unlock one.

### Maker-checker (both forms)

The Account Opening form moves through `draft → submitted → approved`. Access is driven by permissions, so the same rules work for any role:

| Permission | Who gets it by default | Can do |
|---|---|---|
| `view / create / edit account openings` | branch, region, division, head office | Start and fill the form (the maker) |
| `submit account openings` | branch, region, division, head office | Submit a complete request for approval |
| `approve account openings` | head office, super admin | Approve or reject a submitted request (the checker) |
| `unlock account openings` | super admin only | Unlock an approved, locked request |
| `delete account openings` | head office, super admin | Delete a request that is not locked |

#### Flow

1. **Maker** fills the six steps and presses **Submit for Approval**. While it waits, nobody can edit it.
2. **Checker** (a different user) presses **Approve & Activate Account**, or **Reject** with a reason. A rejected request goes back to the maker, who corrects it and resubmits.
3. **Approval locks the request for every user**: no step edit, no save, no delete.
4. To change an approved request a **super admin unlocks that one request** and gives a reason. It returns to Draft and has to be submitted and approved again, which locks it once more. Other requests stay locked.

#### Rules

- The creator and the submitter cannot approve or reject their own request, not even a super admin. Switch this off per installation with `AOF_MAKER_CHECKER_ENABLED=false`, or allow one person to do both with `AOF_ALLOW_SELF_APPROVAL=true`.
- `AOF_LOCK_AFTER_APPROVAL=false` turns the lock off.

#### History

Every step save and every action (started, submitted, approved, rejected, unlocked) is written to the request's **History** on its page: who, when, the step, the note or reason, and for step saves the exact **old → new** value of each changed field. Saving without changes adds nothing.

#### Step-wise wizard

With `AOF_STRICT_STEPS=true` the steps are Customer information → Identification → FATCA/CRS → Account → Applicants & signatures → Due diligence. A step opens only after the previous ones are complete: opening or saving a later step sends you to the first incomplete one, and **Save & Continue** moves on only when the step you saved is complete (**Save & exit** always keeps the draft). The request page shows each step as complete, next or locked.

#### Validation worth knowing

- Government / organisation form: the **Economic Sector Code** is typed (letters, numbers, dots, dashes, up to 10 characters) and required; Individual accounts do not need it.
- Mobile numbers are digits only; for `+92` exactly 10 digits without the leading 0 (for example `3008169924`).
- Tax residency fields stay greyed out until "Tax resident of a country other than Pakistan & USA" is ticked.
- The Terms and Conditions must be accepted before the Account step can continue; every signatory needs a specimen signature and a thumb impression before the Applicants step can continue.

### Option lists (account types, currencies, cards ...)

The form's dropdowns and tick boxes come from reference tables (account products, currencies, card types, special categories, sources of income, document requirements and more). They are loaded by the seeders, and also by `php artisan migrate` through the migration `load_account_opening_reference_data`, so a production database that was only imported and migrated is never left with empty options.

```bash
php artisan aof:sync-reference-data
```

The command is safe at any time: it adds missing options and refreshes existing ones by code, and never deletes anything. To ship new options, edit the seeder under `database/seeders` and add a migration that calls this command. Options include JPY, FCY Term and BMBA accounts, Silver and Gold debit cards, the Illiterate special category, Family Business as a source of funds and Hold Mail for individual accounts.

## File management and ORU categories

File management keeps digital records (with scanned pages) for branches, regions and divisions, archives them into boxes, and transfers them between offices with a custodian and a decision.

**File categories** (Settings → File categories) are the 20 official *Categories of Branch Records to Be Stored in the Old Record Unit (ORU)*, `FC-001` to `FC-020`: Account Opening Records, Cash Transaction Records, Cheque and Payment Records, Clearing Records, Remittance and Funds Transfer Records, Deposit Account Records, General Ledger and Accounting Records, Reconciliation and Suspense Accounts, Advances and Credit Records, Collateral and Securities Records, AML/CFT and Compliance Records, Audit and Inspection Records, Fraud, Investigation and Legal Records, Cheque Book and Security Stationery Records, Cash Management Records, Regulatory and Tax Records, Branch Administrative Records, Customer Complaints and Claims, Dormant and Unclaimed Account Records, and Fixed Assets and Other Registers.

### Maker-checker, locking and the access trail

File records follow the same maker-checker idea as Account Opening, driven by permissions:

| Permission | Default holders | Can do |
|---|---|---|
| `create / edit file management systems` | office roles | Make and change a record (the maker) |
| `submit file management systems` | branch, region, division, head office | Submit a complete record (it needs at least one scanned page) |
| `approve file management systems` | branch, region, division, head office, super admin | Approve or reject a submitted record (the checker) |
| `unlock file management systems` | super admin only | Unlock one approved record |

Flow: `draft → submitted → approved (locked)`; a checker can **reject with a reason**, which returns it to the maker to correct and resubmit.

- The maker (creator or submitter) cannot approve their own record, not even a super admin (`FMS_ALLOW_SELF_APPROVAL=true` allows it). Any other user of the same office, with the permission, can be the checker.
- **Approval locks the record for everyone**: no edit, no page added or removed, no delete. A **super admin unlocks that one record**, giving a reason; it goes back to Draft and must be approved again. Other records stay locked. (`FMS_LOCK_AFTER_APPROVAL=false` turns the lock off; `FMS_MAKER_CHECKER_ENABLED=false` turns the whole workflow off.)
- Transfers and archiving into a box are separate, already-audited actions and stay available on an approved record.
- Records that existed before this feature are treated as approved but not locked.
- **Who sees what**: everyone posted to an office sees that office's records (all users of the same branch see the same files); other offices' records are hidden. Take a permission away from a role or a user and the matching button disappears.
- **Access trail** (History on every record): who created it, every field change with its old and new value, pages uploaded or removed, who **opened the record** (once per user every 5 minutes), who **opened each scanned page**, submitted, approved, rejected, unlocked (with the reason), transferred and archived. Scanned pages open through a logged link; the thumbnail previews are loaded straight from storage and are not logged.
- The file list shows an **Approval** badge (Draft, Submitted, Approved · locked, Rejected) and can be filtered by it.

- Every category has a **Description** (the records that belong in it), shown in the category list, on the create and edit pages, and under the category picker when a file is created or edited.
- Upgrading from the old ten default categories: files keep their category. They are moved to the matching ORU category (for example Audit Reports → Audit and Inspection Records, Legal & Litigation → Fraud, Investigation and Legal Records), and categories you created yourself are kept.

## User module

Under **Settings → User module**:

- **Users**: KPI cards, search, filters, bulk activate / suspend / delete (never your own account or the last super admin) and a read-only user page showing access by module.
- **Roles**: what each role grants, by module, and who has it.
- **Permissions**: filter by product module, see which roles give a permission and which permissions nobody has.
- The permission pickers on the user and role forms have **module tabs**, so choosing "File management" shows only File management permissions.

## Deploying and upgrading

```bash
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force      # also loads the Account Opening option lists
npm ci && npm run build
php artisan config:clear
```

Things to check after an upgrade:

- **Option lists empty?** Run `php artisan aof:sync-reference-data`.
- **Terms & Conditions**: fill `resources/paper-form/terms.txt` and `indemnity.txt` for the old form, then `php artisan config:clear`.
- **Permissions**: permissions that a release introduces or fixes only reach existing roles when the seeders run. If office users cannot see account opening, archive or box actions, run once:

```bash
php artisan db:seed --class=HeadOfficeSeeder
php artisan db:seed --class=BajkAccountOpeningSeeder
php artisan db:seed --class=RolesAndPermissionsSeeder
```

`RolesAndPermissionsSeeder` re-syncs each role's permission list to the one in the seeder, so only run it if you have not customised the roles by hand.

**Releases** are tagged `vMAJOR.MINOR.PATCH` on `main` and described in the [changelog](CHANGELOG.md). Every change goes through its own branch and pull request.

## Project layout

| Path | What lives there |
|---|---|
| `app/Http/Controllers` | One controller per module screen (`AccountOpeningController`, `PaperAccountOpeningController`, `FileCategoryController` ...) |
| `app/Http/Requests` | Form requests: validation and authorization |
| `app/Services/AccountOpeningWorkflow.php` | Maker-checker rules and the request history |
| `app/Support/PaperForm` | The old paper form as data: `PaperFormSections` (the 19 sections) and `PaperFormSchema` (visibility, validation, cleaning) |
| `app/Models` | Eloquent models (UUID keys, soft deletes, user tracking) |
| `database/migrations`, `database/seeders` | Schema, roles and permissions, option lists |
| `resources/views/account-openings` | List, create, request page, print, the wizard `steps/` and the old form `paper/` |
| `resources/paper-form` | Terms & Conditions and Indemnity text of the old form |
| `config/account_opening.php`, `config/paper_form.php` | AOF switches and the old form's legal text |
| `tests/Feature` | Pest feature tests per module |

## Tests and code style

```bash
php artisan test --compact                                  # everything
php artisan test --compact tests/Feature/OldAccountOpeningFormTest.php   # one file
vendor/bin/pint --dirty                                     # format changed PHP files
```

Account Opening is covered by `AccountOpeningWizardTest`, `AccountOpeningStrictStepsTest`, `AccountOpeningMakerCheckerTest`, `AccountOpeningIndexTest`, `AccountOpeningEditionTest`, `AccountOpeningOptionsTest`, `AccountOpeningReferenceDataTest` and `OldAccountOpeningFormTest`; file categories by `FileCategoryCrudTest`.

The Audit and Complaint feature tests had failures before the 1.1 / 1.2 work; they are unrelated to the changes listed in the [changelog](CHANGELOG.md).

## Troubleshooting

| Problem | Fix |
|---|---|
| Account Opening dropdowns are empty | `php artisan aof:sync-reference-data` |
| A new button or style is missing | `npm run build` (or `npm run dev` while developing) |
| `Unable to locate file in Vite manifest` | `npm run build` |
| Old form shows "text has not been loaded yet" | Fill `resources/paper-form/terms.txt` and `indemnity.txt`, then `php artisan config:clear` |
| A request cannot be edited | It is submitted (waiting for the checker) or approved and locked; see [Maker-checker](#maker-checker-both-forms) |
| `.env` change has no effect | `php artisan config:clear` |
