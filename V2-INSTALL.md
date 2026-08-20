# Bookwise V2 overlay installation

This ZIP is an overlay for the existing Laravel application from the `feature/v1-platform` branch at commit `29f0d124b33252b85c0bbfcca685a9e97147ad90`.

1. Back up the application files and database.
2. Extract the ZIP, then copy its contents into the Laravel project root. Keep the included paths and replace files when prompted.
3. From the Laravel project root, run:

```bash
php artisan migrate --force
php artisan platform:admin:create
npm ci
npm run build
php artisan optimize:clear
```

On a production server, optionally finish with `php artisan optimize` after checking the deployment.

Open `/platform/login` and sign in with the platform administrator created by the command. Existing business administrators continue to use `/admin/login`.

Do not run `migrate:fresh`: it deletes existing tables and data. The V2 migration preserves existing businesses, accounts, and appointment history.
