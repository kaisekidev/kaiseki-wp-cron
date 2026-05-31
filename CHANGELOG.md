# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 1.0.0 - 2026-05-31

First tagged release.

### Added

- Declarative WordPress cron registration wired through `ConfigProvider` under the `cron` config key:
  - `CronJobInterface` — describe a job by its hook, recurrence and `run()` callback;
    `getRecurrence()` returns a `ScheduleInterface`.
  - `ScheduleInterface` — common type for a recurrence, identified by its WordPress schedule name.
  - `Recurrence` — a backed `ScheduleInterface` enum of the built-in WordPress schedules (`Hourly`,
    `TwiceDaily`, `Daily`, `Weekly`).
  - `CustomScheduleInterface` / `IntervalSchedule` — a custom interval returned straight from a job;
    the scheduler registers it on the `cron_schedules` filter automatically, so there is no separate
    registration step or recurrence-name string to keep in sync.
  - `CronScheduler` — the single `kaiseki/wp-hook` provider that wires each job's action, registers
    the custom schedules its jobs use, schedules the event, reschedules it when the recurrence
    changes, and clears events for jobs removed from configuration. `ConfigProvider` adds it to
    `hook.provider` automatically.

### Changed

- PHP requirement is `^8.2` (PHP 8.4 is the primary target); replaced the legacy `^7.4` scaffold.
- Adopted the org baseline: `kaiseki/config: ^2.0` and `kaiseki/wp-hook: ^2.0`,
  `kaiseki/php-coding-standard: ^1.0` with the shared PHPStan config (`level: max`, over `src` and
  `tests`), PHPStan 2, PHPUnit 11, and `composer-require-checker: ^4.0` (added a `check-deps`
  script); replaced the eventjet coding standard / PHPCS setup with `php-cs-fixer`. CI runs via the
  reusable workflow in `kaisekidev/.github`.
- Full PHPUnit suite (Brain Monkey + Mockery) covering the scheduler, factory, config provider and
  value objects — 100% line coverage of `src`; CI runs it on the 8.2/8.3/8.4 matrix with the 100%
  coverage gate.
- License set to `MIT`.
