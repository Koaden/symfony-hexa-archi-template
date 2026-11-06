# Database backups

Backups use `pg_dump` (custom format) and restore with `pg_restore`.

## Manual

```bash
make db.backup
make db.restore FILE=backups/db-YYYYMMDD-HHMMSS.dump
```

Backups are written to `backups/` (gitignored).

## Automatic (production only)

`compose.prod.yaml` runs a `db-backup` service that executes `pg_dump` daily
and keeps the last 7 days of dumps in the `db_backups` volume.

Inspect or copy backups:

```bash
docker compose -f compose.yaml -f compose.prod.yaml exec db-backup ls -lh /backups
docker compose -f compose.yaml -f compose.prod.yaml cp db-backup:/backups ./backups
```

## Scheduled (cron)

Run on the host where Compose runs:

```cron
0 2 * * * cd /path/to/project && make db.backup.prod
```

Keep/rotate backup files with your storage policy; volumes are not backups.
