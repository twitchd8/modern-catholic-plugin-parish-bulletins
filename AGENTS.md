# Parish Bulletins Repository Rules

- Authoritative plugin/version file: `parish-bulletins.php`.
- Preserve `mc_bulletin` and the legacy `parish_bulletin` migration contract.
- Preserve the effective Keep All retention default when no option is saved.
- Run PHP syntax checks only on changed PHP files by default.
- Treat archive/single-page requests, PDF rendering, migrations, retention state, and cron inspection as smoke tests governed by the inherited approval gate.
