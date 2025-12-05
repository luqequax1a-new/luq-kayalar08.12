# Queue Setup for Delayed Emails

To enable delayed jobs (e.g., review request emails) use the `database` queue driver:

1. Set environment variable:
   - `QUEUE_CONNECTION=database`
2. Create queue tables and migrate:
   - `php artisan queue:table`
   - `php artisan migrate`
3. Start the worker:
   - `php artisan queue:work`

Notes:
- Delays do not execute with the `sync` driver.
- Use `redis` driver for higher throughput if available.
