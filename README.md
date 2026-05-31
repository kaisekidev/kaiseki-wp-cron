# kaiseki/wp-cron

Register and manage WordPress cron jobs declaratively through a single config key.

Describe a cron job as a small class — its hook, its recurrence and the work it does — and register
it in the `cron.jobs` config key. A single `kaiseki/wp-hook` provider (`CronScheduler`) wires the
action, schedules the event, **reschedules it when you change the recurrence**, registers custom
intervals and **clears events for jobs you remove** — so you never hand-write the
`wp_next_scheduled()` / `wp_schedule_event()` dance again.

## Installation

```bash
composer require kaiseki/wp-cron
```

Requires PHP 8.2 or newer.

## Usage

Register `ConfigProvider` with your laminas-style config aggregator. That alone activates the
scheduler (it adds `CronScheduler` to `hook.provider` for you); you only supply the jobs.

### 1. Write a cron job

Implement `CronJobInterface`. Return a `Recurrence` case for the built-in WordPress schedules:

```php
use Kaiseki\WordPress\Cron\CronJobInterface;
use Kaiseki\WordPress\Cron\Recurrence;
use Kaiseki\WordPress\Cron\ScheduleInterface;

final class PublishScheduledPosts implements CronJobInterface
{
    public function getHook(): string
    {
        return 'acme_publish_scheduled_posts';
    }

    public function getRecurrence(): ScheduleInterface
    {
        return Recurrence::Hourly;
    }

    public function run(): void
    {
        // … the work that runs on every tick.
    }
}
```

`Recurrence` is a backed enum of the WordPress defaults — `Hourly`, `TwiceDaily`, `Daily`, `Weekly` —
so you do not hard-code the magic strings.

### 2. Register it

```php
use Kaiseki\WordPress\Cron\ConfigProvider;

return [
    'cron' => [
        'jobs' => [
            PublishScheduledPosts::class,
        ],
    ],
];
```

That is the whole contract. On the next request the scheduler:

- adds `add_action('acme_publish_scheduled_posts', [$job, 'run'])`,
- schedules the recurring event if it is not already scheduled,
- **reschedules** it if you later change `getRecurrence()` (WordPress keeps the old interval
  otherwise), and
- **unschedules** the event if you remove the class from `cron.jobs`.

Jobs are resolved from your PSR-11 container, so constructor dependencies (loggers, repositories,
the environment) are injected as usual — register each job class with the container as you would any
other service.

### Custom intervals

WordPress only ships `hourly`, `twicedaily`, `daily` and `weekly`. For anything else, return a
`CustomScheduleInterface` from the job. It carries its own interval and label, and the scheduler
registers it with WordPress (via the `cron_schedules` filter) before scheduling the event — there is
**no separate registration step and no recurrence name to keep in sync**.

The quickest way is the ready-made `IntervalSchedule` value object:

```php
use Kaiseki\WordPress\Cron\CronJobInterface;
use Kaiseki\WordPress\Cron\IntervalSchedule;
use Kaiseki\WordPress\Cron\ScheduleInterface;

final class HealthCheck implements CronJobInterface
{
    public function getHook(): string
    {
        return 'acme_health_check';
    }

    public function getRecurrence(): ScheduleInterface
    {
        return new IntervalSchedule('acme_every_five_minutes', 5 * 60, 'Every five minutes');
    }

    public function run(): void
    {
        // …
    }
}
```

Registering the job in `cron.jobs` is all that is needed — the schedule comes with it:

```php
return [
    'cron' => [
        'jobs' => [
            HealthCheck::class,
        ],
    ],
];
```

If a schedule is shared across several jobs, implement `CustomScheduleInterface` on a dedicated class
(or expose a shared `IntervalSchedule` instance) instead of repeating the literal — any job that
returns it registers it, and the scheduler de-duplicates by name.

### What this replaces

The hand-written pattern repeated in every theme's cron class:

```php
public function addHooks(): void
{
    add_action(self::HOOK, [$this, 'run'], 99);
    if (wp_next_scheduled(self::HOOK) !== false) {
        return;
    }
    wp_schedule_event(time(), 'hourly', self::HOOK);
}
```

collapses to a `getHook()` / `getRecurrence()` / `run()` triple — and you additionally get
reschedule-on-change and cleanup-on-removal, which the snippet above silently gets wrong (changing
`'hourly'` to `'daily'` there leaves the old hourly event running forever).

## Development

```bash
composer install
composer check   # check-deps, cs-check, phpstan
```

## License

MIT — see [LICENSE](LICENSE).
