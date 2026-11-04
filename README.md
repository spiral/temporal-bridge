<p align="center">
    <a href="https://spiral.dev"><img alt="Spiral Framework" src="https://user-images.githubusercontent.com/773481/228578125-8e0ad713-905f-4450-a473-5839bd650472.png" style="width: 6in; display: block"></a>
</p>

<p align="center">Temporal integration for Spiral Framework</p>

<div align="center">

[![Documentation](https://img.shields.io/badge/Documentation-blue?style=for-the-badge&logo=gitbook&logoColor=white)](https://spiral.dev/docs/temporal-configuration)
[![Sponsor](https://img.shields.io/static/v1?style=for-the-badge&label=&message=Sponsor&logo=githubsponsors&logoColor=white&color=%23EA4AAA)](https://github.com/sponsors/spiral)

[![Psalm Level](https://shepherd.dev/github/spiral/temporal-bridge/level.svg)](https://shepherd.dev/github/spiral/temporal-bridge)
[![Type Coverage](https://shepherd.dev/github/spiral/temporal-bridge/coverage.svg)](https://shepherd.dev/github/spiral/temporal-bridge)
[![Codecov](https://codecov.io/gh/spiral/temporal-bridge/branch/3.x/graph/badge.svg)](https://codecov.io/gh/spiral/temporal-bridge/tree/3.x)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fspiral%2Ftemporal-bridge%2F3.x)](https://dashboard.stryker-mutator.io/reports/github.com/spiral/temporal-bridge/3.x)

</div>

<br />

This package connects a Spiral application to [Temporal](https://temporal.io/), the open source engine
for durable, fault-tolerant workflows. It configures the Temporal clients, registers your workflows and activities
in RoadRunner workers and adds console commands to inspect and generate them.

## Get Started

### Installation

```bash
composer require spiral/temporal-bridge
```

[![PHP](https://img.shields.io/packagist/php-v/spiral/temporal-bridge.svg?style=flat-square&logo=php)](https://packagist.org/packages/spiral/temporal-bridge)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/spiral/temporal-bridge.svg?style=flat-square&logo=packagist)](https://packagist.org/packages/spiral/temporal-bridge)
[![License](https://img.shields.io/packagist/l/spiral/temporal-bridge.svg?style=flat-square)](LICENSE)
[![Total Downloads](https://img.shields.io/packagist/dt/spiral/temporal-bridge.svg?style=flat-square)](https://packagist.org/packages/spiral/temporal-bridge/stats)

Requirements:

- PHP 8.2+
- Spiral Framework 3
- RoadRunner 3 with the [Temporal plugin](https://docs.roadrunner.dev/docs/workflow-engine/temporal)
- Extension `grpc` for client calls, `protobuf` (recommended)

### Configuration

Add the bootloader to the application's bootloader list:

```php
protected const LOAD = [
    \Spiral\TemporalBridge\Bootloader\TemporalBridgeBootloader::class,
    // ...
];
```

Without an `app/config/temporal.php` file the bridge reads the environment:

| Variable              | Default          | Description                              |
|-----------------------|------------------|------------------------------------------|
| `TEMPORAL_ADDRESS`    | `127.0.0.1:7233` | Address of the Temporal server           |
| `TEMPORAL_CONNECTION` | `default`        | Name of the default client               |
| `TEMPORAL_TASK_QUEUE` | `default`        | Task queue of workflows without a worker |

See the [documentation](https://spiral.dev/docs/temporal-configuration) for the config file, workers,
interceptors and the `temporal:info`, `create:workflow` and `create:activity` commands.

## Useful links

- [Temporal PHP SDK](https://github.com/temporalio/sdk-php/) and its [developer guide](https://docs.temporal.io/develop/php)
- [Intro to Temporal: create your first workflow with the PHP SDK](https://youtu.be/goulj2CRNOY)
- [Fault tolerant workflow orchestration on PHP](https://www.youtube.com/watch?v=pdxHkIqX62A)
- [Fault tolerant workflow orchestration on PHP (rus)](https://www.youtube.com/watch?v=mNsjdTnanA4)
- [Discord](https://discord.gg/spiralphp)
