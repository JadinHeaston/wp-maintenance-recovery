# WordPress Maintenance Recovery

A PHP script that attempts to monitor for failed WP updates and automatically recover.

## How it works

1. The script is deployed to the server.
2. A CRON job is created to run it frequently.
3. The script checks if the `.maintenance` file exists AND if the time elapsed has been met since the files creation.
4. If both are true, the file is deleted and an optional email is sent to administrators.

## CRON

- CRON Line: ``
  - `--seconds-elapsed` is optional. Default: `300`
  - `--site-url` is optional and is only used for the mailing templates. Default: N/A
