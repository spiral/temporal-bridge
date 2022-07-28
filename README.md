# Temporal integration package for Spiral Framework

[![PHP](https://img.shields.io/packagist/php-v/spiral/temporal-bridge.svg?style=flat-square)](https://packagist.org/packages/spiral/temporal-bridge)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/spiral/temporal-bridge.svg?style=flat-square)](https://packagist.org/packages/spiral/temporal-bridge)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/spiral/temporal-bridge/run-tests?label=tests&style=flat-square)](https://github.com/spiral/temporal-bridge/actions?query=workflow%3Arun-tests)
[![Total Downloads](https://img.shields.io/packagist/dt/spiral/temporal-bridge.svg?style=flat-square)](https://packagist.org/packages/spiral/temporal-bridge)

[Temporal](https://temporal.io/) is the simple, scalable open source way to write and run reliable cloud applications.

## Requirements

Make sure that your server is configured with following PHP version and extensions:

- PHP 8.0+
- Spiral framework 2.9+

## Installation

You can install the package via composer:

```bash
composer require spiral/temporal-bridge
```

After package install you need to register bootloader from the package.

```php
protected const LOAD = [
    // ...
    \Spiral\TemporalBridge\Bootloader\TemporalBridgeBootloader::class,
];
```

> Note: if you are using [`spiral-packages/discoverer`](https://github.com/spiral-packages/discoverer),
> you don't need to register bootloader by yourself.

#### Configuration
The package is already configured by default, use these features only if you need to change the default configuration.
The package provides the ability to configure `address`, `namespace`, `defaultWorker`, `workers` parameters.
Create file `app/config/temporal.php` and configure options. For example:

```php
declare(strict_types=1);

use Temporal\Worker\WorkerFactoryInterface;
use Temporal\Worker\WorkerOptions;

return [
    'address' => 'localhost:7233', 
    'namespace' => 'App\\Workflow',
    'defaultWorker' => WorkerFactoryInterface::DEFAULT_TASK_QUEUE,
    'workers' => [
        'workerName' => WorkerOptions::new()
    ],
];
```

#### RoadRunner configuration

Add `temporal` plugin section in your RoadRunner `rr.yaml` config:

```yaml
temporal:
  address: localhost:7233
  activities:
    num_workers: 10
```

#### Temporal

You can run temporal server via docker by using the example below:

> You can find official docker compose files here https://github.com/temporalio/docker-compose

```yaml
version: '3.5'

services:
  postgresql:
    container_name: temporal-postgresql
    image: postgres:13
    environment:
      POSTGRES_PASSWORD: temporal
      POSTGRES_USER: temporal
    ports:
      - 5432:5432

  temporal:
    container_name: temporal
    image: temporalio/auto-setup:1.14.2
    depends_on:
      - postgresql
    environment:
      - DB=postgresql
      - DB_PORT=5432
      - POSTGRES_USER=temporal
      - POSTGRES_PWD=temporal
      - POSTGRES_SEEDS=postgresql
      - DYNAMIC_CONFIG_FILE_PATH=config/dynamicconfig/development.yaml
    ports:
      - 7233:7233
    volumes:
      - ./temporal:/etc/temporal/config/dynamicconfig

  temporal-web:
    container_name: temporal-web
    image: temporalio/web:1.13.0
    depends_on:
      - temporal
    environment:
      - TEMPORAL_GRPC_ENDPOINT=temporal:7233
      - TEMPORAL_PERMIT_WRITE_API=true
    ports:
      - 8088:8088
```

> Please make sure that a configuration file for temporal server exists.
> `mkdir temporal && touch temporal/development.yaml`

## Creating workflow

You are able to create a new workflow via console command:

```bash
php app.php temporal:make-workflow MySuperWorkflow
```

The command will generate the following files with default namespace `App\Workflow`:

```
project/
  src/
    Workflow/
      MySuperWorkflow/
        MySuperWorkflowInterface
        MySuperWorkflow
```

> You can redefine default namespace via `app/config/temporal.php` config file.

#### Workflow with activity classes

```bash
php app.php temporal:make-workflow MySuperWorkflow --with-activity
```

```
project/
  src/
    Workflow/
      MySuperWorkflow/
        ...
        MySuperWorkflowHandlerInterface
        MySuperWorkflowHandler
```

#### Workflow with handler classes

```bash
php app.php temporal:make-workflow MySuperWorkflow --with-handler
```

```
project/
  src/
    Workflow/
      MySuperWorkflow/
        ...
        MySuperWorkflowActivityInterface
        MySuperWorkflowActivity
```

> You can mixin options `--with-activity --with-handler`

#### Workflow method name definition

```bash
temporal:make-workflow PingSite -m ping
```

```php
#[WorkflowInterface]
interface PingSiteWorkflowInterface
{
    #[WorkflowMethod]
    public function ping(string $name): mixed;
}
```

#### Workflow method parameters definition

```bash
temporal:make-workflow PingSite ... -p url:string -p name:string
```

```php
#[WorkflowInterface]
interface PingSiteWorkflowInterface
{
    #[WorkflowMethod]
    public function ping(string $url, string $name): mixed;
}
```

#### Workflow query methods definition

```bash
temporal:make-workflow PingSite ... -r getStatusCode -r getHeaders:array
```

```php
#[WorkflowInterface]
interface PingSiteWorkflowInterface
{
    #[WorkflowMethod]
    public function ping(...): mixed;

    #[QueryMethod]
    function getStatusCode(): string;

    #[QueryMethod]
    function getHeaders(): array;
}
```

#### Workflow with specific task queue

```bash
temporal:make-workflow Domain\\MyPackage\\MoneyTransfer ... --queue foo
```

```php
#[AssignWorker(name: 'foo')]
class PingSiteWorkflow implements PingSiteWorkflowInterface
{
    // ...
}
```

#### Workflow with namespace definition

```bash
temporal:make-workflow Domain\\MyPackage\\MoneyTransfer ... -s withdraw -s deposit
```

## Creating activity

You are able to create a new workflow activity via console command:

```bash
php app.php temporal:make-activity MySuperActivity
```

The command will generate the following files with default namespace `App\Workflow`:

```
project/
  src/
    Workflow/
      MySuperWorkflow/
        MySuperActivityInterface
        MySuperActivity
```

> You can redefine default namespace via `app/config/temporal.php` config file.

#### Activity method name definition

```bash
temporal:make-activity PingSite -m ping
```

```php
#[ActivityInterface]
interface PingSiteActivityInterface
{
    #[ActivityMethod]
    public function ping(string $name): mixed;
}
```

#### Activity method parameters definition

```bash
temporal:make-activity PingSite ... -p url:string -p name:string
```

```php
#[ActivityInterface]
interface PingSiteActivityInterface
{
    #[ActivityMethod]
    public function ping(string $url, string $name): mixed;
}
```

#### Activity with specific task queue

```bash
temporal:make-activity Domain\\MyPackage\\MoneyTransfer ... --queue foo
```
```php
#[AssignWorker(name: 'foo')]
class PingSiteActivity implements PingSiteActivityInterface
{
    // ...
}
```

#### Activity with namespace definition

```bash
temporal:make-activity Domain\\MyPackage\\MoneyTransfer ... -s withdraw -s deposit
```

## Creating workflow from presets

The package provides the ability to create predefined Workflows. Presets for the package can be provided via third-party
packages.

**Example of usage**

```bash
php app.php temporal:make-preset subscribtion-trial CustomerTrialSubscription 
```

A preset will create all necessary classes.

> You can show list of available presets using the console command `php app.php temporal:presets`

#### Creating a preset

A preset class should implement `Spiral\TemporalBridge\Preset\PresetInterface` and should have an
attribute `Spiral\TemporalBridge\Preset\WorkflowPreset`

```php
use Spiral\TemporalBridge\Generator\WorkflowInterfaceGenerator;
use Spiral\TemporalBridge\Generator\SignalWorkflowGenerator;
use Spiral\TemporalBridge\Generator\ActivityInterfaceGenerator;
use Spiral\TemporalBridge\Generator\ActivityGenerator;
use Spiral\TemporalBridge\Generator\HandlerInterfaceGenerator;
use Spiral\TemporalBridge\Generator\HandlerGenerator;
use Spiral\TemporalBridge\Preset\PresetInterface;
use Spiral\TemporalBridge\Preset\WorkflowPreset;

#[WorkflowPreset('signal')]
final class SignalWorkflow implements PresetInterface
{
    public function getDescription(): ?string
    {
        return 'Workflow with signals';
    }
    
    public function generators(Context $context): array
    {
        $generators = [
            'WorkflowInterface' => new WorkflowInterfaceGenerator(),
            'Workflow' => new SignalWorkflowGenerator(),
        ];

        if ($context->hasActivity()) {
            $generators = \array_merge($generators, [
                'ActivityInterface' => new ActivityInterfaceGenerator(),
                'Activity' => new ActivityGenerator(),
            ]);
        }

        if ($context->hasHandler()) {
            $generators = \array_merge($generators, [
                'HandlerInterface' => new HandlerInterfaceGenerator(),
                'Handler' => new HandlerGenerator(),
            ]);
        }

        return $generators;
    }
}
```

**Please note: If you are using `WorkflowPreset` you have to add a directory with presets to tokenizer.**

```php
use Spiral\Tokenizer\Bootloader\TokenizerBootloader;

class MyBootloader extends \Spiral\Boot\Bootloader\Bootloader
{
    protected const DEPENDENCIES = [
        TokenizerBootloader::class
    ];

    public function start(TokenizerBootloader $tokenizer)
    {
        $tokenizer->addDirectory(__DIR__..'/presets');
    }
}
```

You can omit `WorkflowPreset` attribute and register your preset via Bootloader

```php
use Spiral\TemporalBridge\Preset\PresetRegistryInterface;

class MyBootloader extends \Spiral\Boot\Bootloader\Bootloader
{
    public function start(PresetRegistryInterface $registry)
    {
        $registry->register('signal', new SignalWorkflow());
    }
}
```

#### Workflow signal methods definition

```bash
temporal:make-workflow MoneyTransfer ... -s withdraw -s deposit
```

```php
#[WorkflowInterface]
interface MoneyTransferWorkflowInterface
{
    #[WorkflowMethod]
    public function ping(...): \Generator;

    #[SignalMethod]
    function withdraw(): void;

    #[SignalMethod]
    function deposit(): void;
}
```

> You may discover available workflow samples [here](https://github.com/temporalio/samples-php)

## Usage

Configure temporal address via env variables `.env`

```
TEMPORAL_ADDRESS=127.0.0.1:7233
```

### Running workflow

```php
class PingController 
{
    public function ping(StoreRequest $request, PingSiteHandler $handler): void
    {
        $this->hanlder->handle(
            $request->url, 
            $request->name
        );
    }
}
```

## Running workers with different task queue

Add a `Spiral\TemporalBridge\Attribute\AssignWorker` attribute to your Workflow or Activity with the `name` of the worker. 
This Workflow or Activity will be processed by the specified worker.
Example:

```php
<?php

declare(strict_types=1);

namespace App\Workflow;

use Spiral\TemporalBridge\Attribute\AssignWorker;
use Temporal\Workflow\WorkflowInterface;

#[AssignWorker(name: 'worker1')]
#[WorkflowInterface]
interface MoneyTransferWorkflowInterface
{
    #[WorkflowMethod]
    public function ping(...): \Generator;

    #[SignalMethod]
    function withdraw(): void;

    #[SignalMethod]
    function deposit(): void;
}
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [butschster](https://github.com/spiral)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
