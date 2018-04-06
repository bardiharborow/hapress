# hapress
High availability WordPress on Google App Engine's standard environment.

### Known issues

1. Improved separation of secrets and routing logic is blocked pending [an App Engine feature request](https://issuetracker.google.com/issues/62172664).
2. Multisite must be disabled in the `app.yaml` for the first deploy until Wordpress is fully installed and the database tables are populated.
