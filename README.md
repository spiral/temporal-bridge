# Temporal integration package for Spiral Framework

[![PHP](https://img.shields.io/packagist/php-v/spiral/temporal-bridge.svg?style=flat-square)](https://packagist.org/packages/spiral/temporal-bridge)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/spiral/temporal-bridge.svg?style=flat-square)](https://packagist.org/packages/spiral/temporal-bridge)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/spiral/temporal-bridge/run-tests?label=tests&style=flat-square)](https://github.com/spiral/temporal-bridge/actions?query=workflow%3Arun-tests+branch%3Amain)
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
      - DYNAMIC_CONFIG_FILE_PATH=temporal/development.yaml
    ports:
      - 7233:7233
    volumes:
      - ./temporal:/etc/temporal/config/dynamicconfig

  temporal-admin-tools:
    container_name: temporal-admin-tools
    image: temporalio/admin-tools:1.14.2
    depends_on:
      - temporal
    environment:
      - TEMPORAL_CLI_ADDRESS=temporal:7233
    stdin_open: true
    tty: true

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

> Please make sure that you have configuration file for temporal server.
> `mkdir temporal && touch temporal/development.yaml`

## Creating workflow

You can create a new workflow via console command:

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
        MySuperWorkflowActivityInterface
        MySuperWorkflowActivity
        MySuperWorkflowHandlerInterface
        MySuperWorkflowHandler
```

> You can redefine default namespace via `app/config/temporal.php` config file.

#### Workflow method name definition

```bash
temporal:make-workflow PingSite -m ping
```

```php
#[WorkflowInterface]
interface PingSiteWorkflowInterface
{
    #[WorkflowMethod]
    public function ping(string $name): \Generator;
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
    public function ping(string $url, string $name): \Generator;
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
    public function ping(...): \Generator;

    #[QueryMethod]
    function getStatusCode(): string;

    #[QueryMethod]
    function getHeaders(): array;
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

#### Workflow with namespace definition

```bash
temporal:make-workflow Domain\\MyPackage\\MoneyTransfer ... -s withdraw -s deposit
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
