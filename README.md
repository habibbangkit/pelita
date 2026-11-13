# PELITA

PELITA is a Laravel 12 application for mapping AI literacy and readiness. It supports participant assessments without participant accounts and an authenticated admin area for managing the instrument, scoring configuration, and results.

## Requirements

- PHP 8.2 or newer and Composer
- MySQL/MariaDB for production; SQLite is supported for local development and tests
- Node.js/npm for compiling frontend assets
- The `pdo_sqlite` PHP extension when running tests with SQLite

## Local setup

1. Copy `.env.example` to `.env` and configure the database.
2. Set `PELITA_ADMIN_NAME`, `PELITA_ADMIN_EMAIL`, and `PELITA_ADMIN_PASSWORD` to provision the initial administrator. Keep these values out of source control.
3. Install dependencies, generate the application key, migrate and seed the database, and build frontend assets:

   ```sh
   composer install
   php artisan key:generate
   php artisan migrate --seed
   npm install
   npm run build
   php artisan serve
   ```

4. Open `/` for the participant landing page and `/admin/login` for administrator sign-in.

The foundation seeder creates the four default dimensions, three maturity levels, and a **draft** assessment. It does not contain approved assessment items: the PRD does not provide validated questions. Re-running the seeder creates missing foundation records without overwriting scoring values already configured by an administrator.

## Admin workflow

1. Review and manage dimensions at `/admin/dimensions`. New dimensions start inactive with zero weight.
2. Configure active dimensions and their weights, and set maturity score ranges at `/admin/scoring-settings`. Active dimension weights must total 100%; maturity ranges must be contiguous from 0 through 100.
3. Create and manage questions and options at `/admin/questions`. Multiple-choice questions require exactly one correct option. Likert options each need a rubric score from 0 to 100. Questions can be reordered, deactivated, or archived; records are retained rather than deleted.
4. Publish the draft assessment from the question-management page. Publication is rejected unless every active dimension has an active question and each active question has valid options, type, and weight. Only a published, complete assessment is offered on the participant landing page.
5. To revise assessment questions, first return the assessment to draft. Instrument and scoring changes are blocked while participants are in progress. Dimensions that are used by a published assessment cannot be activated or deactivated until the published assessment is brought back to draft and its questions are adjusted.

Administrators can view the dashboard at `/admin/dashboard`, search and filter completed participants at `/admin/participants`, inspect individual result snapshots, and export the filtered results as CSV.

## Scoring and historical results

- Multiple-choice answers score 100 when correct and 0 when incorrect. Likert answers use the selected option's `score_value`.
- Question scores are averaged by question weight within each dimension. The final score is the weighted sum of active dimension scores.
- Maturity ranges and dimension weights are managed in the database; the foundation seeder supplies defaults: Aware (0–39.99), Adopter (40–74.99), and Transformer (75–100).
- Completed attempts store score, maturity level, dimension weights, answer/question text, selected option text, and recommendation snapshots. Later edits to the instrument or scoring configuration do not recalculate historical results.
- If no configured recommendation matches the lowest-scoring dimension, PELITA displays a neutral fallback recommendation. Recommendations can be stored in the `recommendations` table.

## Production deployment

Use HTTPS and a supported MySQL/MariaDB server. Before deployment, provision the database and application environment, including a strong admin password and a persistent `APP_KEY`. Set at least:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.example
SESSION_SECURE_COOKIE=true
```

Set the database credentials and `PELITA_ADMIN_*` values through the hosting platform's secret/environment configuration, not in source control. Configure the web server to serve Laravel's `public` directory, enable HTTPS, and ensure the PHP process can write to `storage` and `bootstrap/cache`. If TLS terminates at a reverse proxy, configure Laravel's trusted proxies so secure-request detection and HSTS behave correctly.

For a release, build assets and install production dependencies, then run migrations and refresh cached configuration while the application is in maintenance mode:

```sh
npm install
npm run build
composer install --no-dev --optimize-autoloader
php artisan down
php artisan migrate --force
php artisan optimize
php artisan up
```

On the initial deployment, generate `APP_KEY` once before serving traffic and run the seeder with the production environment values to create the initial admin. Do not regenerate the key during routine releases. Verify `/up`, administrator sign-in, and a complete test assessment after deployment.

## Database backup and restore

Take a database backup before each schema migration and on a schedule determined by the data owner. Store backups encrypted and access-restricted, separate from the application host. For MySQL/MariaDB, use the database client tools and enter the password at the prompt:

```sh
mysqldump --single-transaction \
  --host="$DB_HOST" --port="$DB_PORT" --user="$DB_USERNAME" --password \
  "$DB_DATABASE" > pelita-backup.sql
```

Periodically restore a backup into an isolated database and verify the restored data and schema before relying on the backup:

```sh
mysql --host="$DB_HOST" --port="$DB_PORT" --user="$DB_USERNAME" --password \
  pelita_restore < pelita-backup.sql
```

Point a test/staging copy of the application at the restored database and verify with `php artisan migrate:status`, then sign in and inspect representative assessment results. Do not test a restore against the production database.

Production deployment, scheduled backups, and a restore drill have not been run from this workspace; they require an explicitly provisioned target server and database.

## Access and data handling

- Participants do not need accounts. Each attempt is identified by a random UUID URL; treat that URL as a bearer link and do not share it.
- Admin routes require an admin account. The public participant flow shows a short data-use notice.
- Data retention and deletion periods must be set by the data owner. Establish an operational retention and backup policy before collecting production participant data.

## Tests

```sh
php artisan test
```

Tests use an in-memory SQLite database and require the `pdo_sqlite` extension.
