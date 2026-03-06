# Work Steps

1. In `config.php` I set up the shared mysqli connection, and at the top of both `index.php` and `admin.php` I include that file so they pull from the same database without repeating the setup logic.
2. In `admin.php` I added `SELECT` queries that count each mechanic’s bookings for today and also list every appointment; the page now prints availability and a simple table so the admin can see who is scheduled.
3. In `index.php` I built the HTML form with fields for name, address, phone, car details, date/time, and a dropdown that loops through the `$mechanics` array loaded from the database.
4. Also in `index.php` I added the POST handler that trims the input, parses the `datetime-local` value, checks for duplicate phone/date bookings, enforces a max of four cars per mechanic per day, writes the row into `appointments`, and displays success or error messages.
5. I peppered both files with short comments so it is clear what each section is doing—fetching mechanics, handling the POST, or rendering the list—making it easier to read for someone getting started with PHP.



php -r 'echo password_hash("password", PASSWORD_DEFAULT) . PHP_EOL;'
$2y$12$0nyRwKEEq92fGi6.KFG.q./RTM8653J4cynG4QnyjSPftDqOtWG4m
