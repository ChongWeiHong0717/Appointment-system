# Bookwise Workers / Capacity Booking — Phase 1 to 3

This overlay upgrades Bookwise from one-appointment-at-a-time availability to worker-aware capacity booking.

## Included

- Workers belong to a business and can be active/inactive.
- Workers can be qualified for selected services.
- Services have `workers_required` in addition to their existing duration.
- Multiple appointments can overlap when enough qualified workers are free.
- Bookwise automatically assigns the least-loaded qualified workers.
- Final booking is rechecked inside the existing business-level database lock to prevent double allocation.
- Full-day worker absences can be recorded from the Workers page.
- Existing appointments are never automatically cancelled because of an absence.
- When a worker becomes absent, Bookwise tries to reassign affected appointments automatically.
- If no replacement exists, the appointment remains booked and appears as a staffing conflict.
- Dashboard and appointment detail screens show staffing health.
- Existing businesses with zero active workers remain in legacy one-appointment-at-a-time mode until staff are configured.

## Install locally

1. Back up your project and database.
2. Extract this ZIP.
3. Copy its contents into the root of your existing `Appointment-system` project and allow Windows to replace matching files.
4. From the project root run:

```powershell
php artisan migrate
php artisan optimize:clear
npm run build
php artisan test
```

Do **not** run `php artisan migrate:fresh`. That would erase businesses, users and appointments.

## First setup after installation

1. Log in to a business admin account.
2. Open **Workers** in the sidebar.
3. Add the business's workers and select which services each worker can perform.
4. Open **Services** and confirm each service's **Workers required** value and qualified workers.
5. Check the dashboard for staffing conflicts on already-booked future appointments.

Until the first active worker is added, the business deliberately keeps the old one-appointment-at-a-time behaviour. This prevents the migration from suddenly making an existing live business unbookable.

## Railway deployment

Your existing Railway pre-deploy command can stay:

```bash
php artisan migrate --force
```

After you have tested locally:

```powershell
git add .
git commit -m "Add worker capacity booking and absence management"
git push
```

Railway should apply the migration during deployment. No production seeding is required.

## Current scope

Phase 1–3 supports full-day absences. The database already includes nullable `starts_at` / `ends_at` fields so partial-day leave can be added later without redesigning the data model.

Individual worker shifts, preferred-worker customer selection, payroll, commissions and employee accounts are intentionally out of scope for this upgrade.
