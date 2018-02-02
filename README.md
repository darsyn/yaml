# Distributed YAML

> This library is **NOT** intended to be used in production or taken seriously.
> It's just an idea I had to use as an example for working with [custom YAML
tags introduced in Symfony 3.3](https://symfony.com/blog/new-in-symfony-3-3-custom-yaml-tags)
and the associated [`TaggedValue`](https://github.com/symfony/yaml/blob/master/Tag/TaggedValue.php
"Symfony\Component\Yaml\Tag\TaggedValue") class.

### Example Usage

```yaml
# config/doctrine.yaml

doctrine:
    dbal:
        driver: 'pdo_mysql'
        url: '%env(resolve:DATABASE_URL)%'
    orm:
        auto_mapping: true
        # This is the magic...
        mappings: !import "./doctrine/mappings.yaml"
```

```yaml
# config/doctrine/mappings.yaml

App:
    is_bundle: false
    type: annotation
    dir: '%kernel.project_dir%/src/Entity'
    prefix: 'App\Entity'
    alias: App
```

```php
<?php declare(strict_types=1);
use Darsyn\Yaml\DistributedYaml;
require_once __DIR__ . '/vendor/autoload.php';
$doctrineConfig = DistributedYaml::parseFile('config/doctrine.yaml');
echo json_encode($doctrineConfig, JSON_PRETTY_PRINT) . PHP_EOL;
```

The above PHP file will echo out the following JSON:

```json
{
    "doctrine": {
        "dbal": {
            "driver": "pdo_mysql",
            "url": "%env(resolve:DATABASE_URL)%"
        },
        "orm": {
            "auto_mapping": true,
            "mappings": {
                "App": {
                    "is_bundle": false,
                    "type": "annotation",
                    "dir": "%kernel.project_dir%\/src\/Entity",
                    "prefix": "App\\Entity",
                    "alias": "App"
                }
            }
        }
    }
}
```
