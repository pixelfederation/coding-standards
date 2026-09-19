# Coding standards

This package provides the shared PHP_CodeSniffer rules and GrumPHP tasks used by Pixel Federation PHP projects.

## Requirements

- PHP 8.4 or 8.5
- PHP_CodeSniffer 4
- Slevomat Coding Standard 8

## Installation

```bash
composer require --dev pixelfederation/coding-standards
```

## PHP_CodeSniffer

The package contains rulesets for PHP 8.4 and PHP 8.5. Each PHP version has a standard ruleset and a less
restrictive ruleset for projects that do not use DDD:

| PHP | Standard ruleset | Non-DDD ruleset |
| --- | --- | --- |
| 8.4 | `phpcs.ruleset.84.xml` | `phpcs.ruleset.84.non-ddd.xml` |
| 8.5 | `phpcs.ruleset.85.xml` | `phpcs.ruleset.85.non-ddd.xml` |

Create `phpcs.ruleset.xml` in the project root:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<ruleset name="PixelFederation">
  <description>Project coding standard.</description>

  <exclude-pattern>tests/</exclude-pattern>

  <rule ref="vendor/pixelfederation/coding-standards/phpcs.ruleset.84.xml">
    <!-- Project-specific exclusions can be added here. -->
    <exclude name="SlevomatCodingStandard.Functions.FunctionLength"/>
  </rule>
</ruleset>
```

Run the checks with:

```bash
vendor/bin/phpcs --standard=phpcs.ruleset.xml src
```

Automatically fix supported violations with:

```bash
vendor/bin/phpcbf --standard=phpcs.ruleset.xml src
```

### Alphabetical array keys

The standard checks multi-line associative arrays with
`PixelFederationCodingStandard.Arrays.AlphabeticallySortedByKeys`. Arrays used as the direct value of a `choices`
key are excluded because their order controls the order shown in the user interface. The containing array is still
checked.

Projects can replace the configured list of ignored parent keys in their ruleset:

```xml
<rule ref="PixelFederationCodingStandard.Arrays.AlphabeticallySortedByKeys">
  <properties>
    <property name="ignoredParentKeys" type="array">
      <element value="choices"/>
      <element value="steps"/>
    </property>
  </properties>
</rule>
```

Without `ignoredParentKeys`, the sniff behaves like Slevomat's
`SlevomatCodingStandard.Arrays.AlphabeticallySortedByKeys` sniff and checks every multi-line associative array.

The complete Slevomat sniff documentation is available in the
[Slevomat Coding Standard repository](https://github.com/slevomat/coding-standard).

## GrumPHP

Custom tasks require the Composer installation of GrumPHP. They do not work with `phpro/grumphp-shim` when
parallel execution is enabled because PHAR task classes cannot be serialized.

Register the extension in the project's `grumphp.yml`:

```yaml
grumphp:
  extensions:
    - PixelFederation\CodingStandards\GrumPHP\ExtensionLoader
```

### Doctrine ORM mapping validation

```yaml
grumphp:
  tasks:
    doctrine_schema_validate:
      console_path: bin/console
      em: default
      skip_mapping: false
      skip_property_types: false
      skip_sync: false
      triggered_by: [php, xml, yml]
```

For multiple entity managers, configure multiple tasks using the shared task implementation:

```yaml
grumphp:
  tasks:
    doctrine_schema_validate_application:
      em: application
      metadata:
        task: doctrine_schema_validate
    doctrine_schema_validate_reporting:
      em: reporting
      metadata:
        task: doctrine_schema_validate
```

Options:

| Option | Default | Description |
| --- | --- | --- |
| `console_path` | `bin/console` | Path to the Symfony console. |
| `em` | `null` | Entity manager name. Requires Doctrine ORM 3 or newer. |
| `skip_mapping` | `false` | Skip mapping validation. |
| `skip_property_types` | `null` | Skip the Doctrine property type check. Requires Doctrine ORM 3 or newer. |
| `skip_sync` | `false` | Skip the database synchronization check. |
| `triggered_by` | `[php, xml, yml]` | File extensions that trigger the task. |

### Composer install check

This task checks whether changes to Composer files require dependencies to be installed again.

```yaml
grumphp:
  tasks:
    composer_install_check:
      script: ./vendor/pixelfederation/coding-standards/bin/composer_install_check.sh
      ignore_patterns: []
      triggered_by: [json, lock, php, xml, yaml, yml]
      whitelist_patterns: []
      metadata:
        priority: 900
```

Options:

| Option | Default | Description |
| --- | --- | --- |
| `script` | `./vendor/pixelfederation/coding-standards/bin/composer_install_check.sh` | Path to the check script. |
| `ignore_patterns` | `[]` | Patterns excluded from the check. |
| `triggered_by` | `[json, lock, php, xml, yaml, yml]` | File extensions that trigger the task. |
| `whitelist_patterns` | `[]` | Patterns limiting which changed files are checked. |

### PHPStan Extended

This task extends the standard PHPStan task and splits files into smaller chunks to avoid operating-system command
length limits.

```yaml
grumphp:
  tasks:
    phpstan_extended:
      autoload_file: ~
      chunk_size: 1000
      configuration: phpstan.neon
      force_patterns: []
      ignore_patterns: []
      level: max
      memory_limit: "-1"
      triggered_by: [php]
      use_grumphp_paths: true
```

`chunk_size` determines the maximum number of files processed by one PHPStan execution.

### XMLLint Extended

This task extends the standard XMLLint task with DTD, XInclude, and XML Schema validation. It requires the `dom`
and `libxml` PHP extensions.

```yaml
grumphp:
  tasks:
    xmllint_extended:
      dtd_validation: false
      ignore_patterns: []
      load_from_net: false
      scheme_validation: false
      triggered_by: [xml]
      x_include: false
```

When schema validation is enabled, the linter supports `xsi:noNamespaceSchemaLocation` and multi-namespace
`xsi:schemaLocation` documents. Imported namespaces are resolved by the root document schema.
