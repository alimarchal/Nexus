# Nexus

Nexus is the Bank of Azad Jammu & Kashmir's internal Laravel application: account opening (AOF), AKSIC loan cases and budget, file management and archive boxes, complaints, audits, reports, and the user module (users, roles, permissions).

- Laravel 13, PHP 8.4+, Livewire / Jetstream, Alpine.js, Tailwind CSS
- Spatie packages: permission, activitylog, medialibrary, query-builder
- Tests: Pest

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

`migrate:fresh --seed` creates the roles and permissions, the branches and their branch managers, the head office, all Account Opening reference data and a default super admin:

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

### AKSIC

| Variable | Default | What it does |
|---|---|---|
| `AKSIC_API_TOKEN` | empty | Token for the AKSIC applications sync. Keep it only in your private `.env`. |
| `AKSIC_EXCEL_IMPORT` | `false` | Shows the Excel template and "Import from Excel". |
| `AKSIC_EXCEL_EXPORT` | `false` | Shows "Export to Excel (CSV)" in the Export / Print menu. |
| `AKSIC_DEMO_DATA` | `false` | Shows "Replace / remove demo cases" (still local and super admin only). |

After changing `.env` on a server run `php artisan config:clear` (or `config:cache`).

## Account Opening: maker-checker

The Account Opening form moves through `draft → submitted → approved`. Access is driven by permissions, so the same rules work for any role:

| Permission | Who gets it by default | Can do |
|---|---|---|
| `view / create / edit account openings` | branch, region, division, head office | Start and fill the form (the maker) |
| `submit account openings` | branch, region, division, head office | Submit a complete request for approval |
| `approve account openings` | head office, super admin | Approve or reject a submitted request (the checker) |
| `unlock account openings` | super admin only | Unlock an approved, locked request |
| `delete account openings` | head office, super admin | Delete a request that is not locked |

### Flow

1. **Maker** fills the six steps and presses **Submit for Approval**. While it waits, nobody can edit it.
2. **Checker** (a different user) presses **Approve & Activate Account**, or **Reject** with a reason. A rejected request goes back to the maker, who corrects it and resubmits.
3. **Approval locks the request for every user**: no step edit, no save, no delete.
4. To change an approved request a **super admin unlocks that one request** and gives a reason. It returns to Draft and has to be submitted and approved again, which locks it once more. Other requests stay locked.

### Rules

- The creator and the submitter cannot approve or reject their own request, not even a super admin. Switch this off per installation with `AOF_MAKER_CHECKER_ENABLED=false`, or allow one person to do both with `AOF_ALLOW_SELF_APPROVAL=true`.
- `AOF_LOCK_AFTER_APPROVAL=false` turns the lock off.

### History

Every step save and every action (started, submitted, approved, rejected, unlocked) is written to the request's **History** on its page: who, when, the step, the note or reason, and for step saves the exact **old → new** value of each changed field. Saving without changes adds nothing.

### Step-wise wizard

With `AOF_STRICT_STEPS=true` the steps are Customer information → Identification → FATCA/CRS → Account → Applicants & signatures → Due diligence. A step opens only after the previous ones are complete: opening or saving a later step sends you to the first incomplete one, and **Save & Continue** moves on only when the step you saved is complete (**Save & exit** always keeps the draft). The request page shows each step as complete, next or locked.

### Validation worth knowing

- Government / organisation form: the **Economic Sector Code** is typed (letters, numbers, dots, dashes, up to 10 characters) and required; Individual accounts do not need it.
- Mobile numbers are digits only; for `+92` exactly 10 digits without the leading 0 (for example `3008169924`).
- Tax residency fields stay greyed out until "Tax resident of a country other than Pakistan & USA" is ticked.
- The Terms and Conditions must be accepted before the Account step can continue; every signatory needs a specimen signature and a thumb impression before the Applicants step can continue.

## User module

Under **Settings → User module**:

- **Users**: KPI cards, search, filters, bulk activate / suspend / delete (never your own account or the last super admin) and a read-only user page showing access by module.
- **Roles**: what each role grants, by module, and who has it.
- **Permissions**: filter by product module, see which roles give a permission and which permissions nobody has.
- The permission pickers on the user and role forms have **module tabs**, so choosing "File management" shows only File management permissions.

## Upgrading an existing server

```bash
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force
npm ci && npm run build
php artisan config:clear
```

Permissions that this release introduced or fixed only reach existing roles when the seeders run. If office users cannot see account opening, archive or box actions after upgrading, run once:

```bash
php artisan db:seed --class=HeadOfficeSeeder
php artisan db:seed --class=BajkAccountOpeningSeeder
php artisan db:seed --class=RolesAndPermissionsSeeder
```

`RolesAndPermissionsSeeder` re-syncs each role's permission list to the one in the seeder, so only run it if you have not customised the roles by hand.

## Tests

```bash
php artisan test --compact
vendor/bin/pint --dirty
```

The Audit and Complaint feature tests had failures before the 1.1 / 1.2 work; they are unrelated to the changes listed in the [changelog](CHANGELOG.md).
