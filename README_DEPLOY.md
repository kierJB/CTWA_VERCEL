# CampusHub - Vercel deployment copy

This folder is a SEPARATE copy of the project. Your XAMPP project and its
database are not touched. Vercel cannot reach `localhost`, so this copy uses an
ONLINE MySQL database that you configure with environment variables.

## 1. Create an online MySQL database
Use any MySQL-compatible host that allows public connections (for example TiDB
Cloud Serverless, Aiven, Railway, PlanetScale-style services - check their
current free-tier terms). Note the host, port, user, password and database name.

## 2. Create the tables
Open the provider's SQL editor, select your database, and run
`database/campushub_cloud.sql`.

(Optional) To bring your existing data: in phpMyAdmin choose
Export > Custom > "Data" only, then run that file on the cloud database.
Exporting only reads from XAMPP; it changes nothing.

## 3. Push to GitHub
Upload the contents of this folder to a new GitHub repository.

## 4. Import to Vercel
- New Project > select the repo
- Framework Preset: Other (leave build/output settings empty)
- Add Environment Variables:

| Name     | Example                        |
|----------|--------------------------------|
| DB_HOST  | your-cloud-db-host             |
| DB_PORT  | 3306 (TiDB Cloud uses 4000)    |
| DB_USER  | your user                      |
| DB_PASS  | your password                  |
| DB_NAME  | student_org_system             |
| DB_SSL   | 1 (if the provider needs TLS)  |

- Deploy. Visit your `.vercel.app` URL.

## 5. First login
If the users table is empty, the app creates `admin` / `Admin123!`.
Change that password immediately - the site is public.

## Folder map
- `api/index.php`  router (sends every URL to the right file in `app/`)
- `app/`           your PHP pages (admin, student, includes, config)
- `public/assets/` CSS and JS
- `vercel.json`    uses the community `vercel-php` runtime (PHP 8.3)
- `database/`      SQL for the cloud DB (not uploaded to Vercel)
