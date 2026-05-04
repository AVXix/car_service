## Car Service Booking (PHP)

Live link: https://webtinkerav.ct.ws/

### Structure
- `index.php` / `admin.php`: entry pages and view switch only.
- `handlers/`: request handling and business rules.
- `views/`: render-only sections.
- `config.php`: shared DB connection bootstrap.

### Security notes
- User and admin passwords are stored as hashes.
- User and admin state-changing forms use CSRF tokens.
- Admin cancellation uses POST (not GET).

### Useful command
Generate a password hash from CLI:

`php -r 'echo password_hash("password", PASSWORD_DEFAULT) . PHP_EOL;'`

Example output:
`$2y$12$0nyRwKEEq92fGi6.KFG.q./RTM8653J4cynG4QnyjSPftDqOtWG4m`
