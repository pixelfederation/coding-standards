# Coding standards

This package provides PHPCS rule set for coding standards in Pixel Federation. It should be included into
each project maintained by Pixel Federation that uses PHP Code Sniffer [PHPCS](https://github.com/squizlabs/PHP_CodeSniffer).

## Migration from v4 to v4.1.0
Generic ruleset was removed. Now there are only rulesets for specific PHP versions.

Replace (in your ruleset) reference to file
```vendor/pixelfederation/coding-standards/phpcs.ruleset.xml``` 
with 
```vendor/pixelfederation/coding-standards/phpcs.ruleset.84.xml```
for PHP 8.4.

## How to use

### Install composer dependencies

```bash
composer require --dev pixelfederation/coding-standards:^5.0
```

### Supported versions

For each php version there are 2 versions of the ruleset. One for DDD projects and one for Non-DDD projects.

For example for PHP 8.4:
```
vendor/pixelfederation/coding-standards/phpcs.ruleset.84.xml
OR
vendor/pixelfederation/coding-standards/phpcs.ruleset.84.non-ddd.xml
```

### Ruleset creation

Create a file named `phpcs.ruleset.xml` in the root folder of your project with the following content:

```xml
<?xml version="1.0"?>
<ruleset name="PixelFederation">
  <description>PixelFederation rule set.</description>
    
  <exclude-pattern>tests/</exclude-pattern>

  <rule ref="vendor/pixelfederation/coding-standards/phpcs.ruleset.84.xml"> <!-- Insert version for your php version -->
    <!-- You can exclude some rules here -->
    <exclude name="SlevomatCodingStandard.Files.FunctionLength"/> 
  </rule>
</ruleset>
```

### Running checks

In your project directory run this command:

```bash
vendor/bin/phpcs --standard=phpcs.ruleset.xml src
```

### Automatically fixing errors

In your project directory run this command:

```bash
vendor/bin/phpcbf --standard=phpcs.ruleset.xml src
```

## Additional links

Sniffs documentation for slevomat coding standards are here: 
https://github.com/slevomat/coding-standard

# GrumPHP

## Tasks

### Installation

````YAML
# grumphp.yml
grumphp:
    extensions:
        - PixelFederation\CodingStandards\GrumPHP\ExtensionLoader
````

### Doctrine ORM Mapping Validation

````YAML
# grumphp.yml
grumphp:
    tasks:
        doctrine_schema_validate:
            skip_mapping: false
            skip_sync: false
            skip_property_types: false
            em: default
            triggered_by: ['php', 'xml', 'yml']
````

For multiple entity managers you can specify the entity manager to be used:
````YAML
# grumphp.yml
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
````

**console_path**

*Default: 'bin/console'*

With this parameter you can set the path of the console to be used.

**skip_mapping**

*Default: false*

With this parameter you can skip the mapping validation check.

**skip_sync**

*Default: false*

With this parameter you can skip checking if the mapping is in sync with the database.

**triggered_by**

*Default: [php, xml, yml]*

This is a list of extensions that should trigger the Doctrine task.

**em**

*Default: null*

Require `doctrine/orm >= 3.0`.
Specify the entity manager to be used. If not set, the default entity manager will be used.

**skip_property_types**

*Default: null*

Require `doctrine/orm >= 3.0`.
With this parameter you can skip checking if property types match the Doctrine types.

### Composer Install Check

````YAML
# grumphp.yml
grumphp:
    tasks:
        composer_install_check:
            script: './vendor/pixelfederation/coding-standards/bin/composer_install_check.sh',
            ignore_patterns: []
            triggered_by: ['php', 'yml', 'yaml', 'xml']
            whitelist_patterns: []
            metadata:
                priority: 900
````

**script**

*Default: './bin/composer_install_check.sh'*

Path to check script.

**ignore_patterns**

*Default: []*

This is a list of patterns that will be ignored by phpcs. With this option you can skip files like tests. Leave this option blank to run phpcs for every php file.

**triggered_by**

*Default: ['php', 'yml', 'yaml', 'xml']*

This is a list of extensions to be sniffed.

**whitelist_patterns**

*Default: []*

This is a list of regex patterns that will filter files to validate. With this option you can skip files like tests. This option is used in relation with the parameter `triggered_by`.

### PhpMd Extended

Extends the default [PhpMd task](vendor/phpro/grumphp/doc/tasks/phpmd.md) task and splits the files into smaller chunks to prevent the `Argument list too long` error.

***Config***

The task lives under the `phpmd_extended` namespace and has following configurable parameters:

```yaml
# grumphp.yml
grumphp:
    tasks:
        phpmd_extended:
            whitelist_patterns: []
            exclude: []
            report_format: text
            ruleset: ['cleancode', 'codesize', 'naming']
            triggered_by: ['php']
            chunks_size: 1000
```

**chunk_size**

*Default: 1000*

This parameter defines how many files will be checked in one execution of phpmd. This can help with performance on large codebases.

### PHPStan Extended

Extends the default [PHPStan task](vendor/phpro/grumphp/doc/tasks/phpstan.md) and splits the files into smaller chunks to prevent the `Argument list too long` error.

***Config***

The task lives under the `phpstan_extended` namespace and has following configurable parameters:

```yaml
# grumphp.yml
grumphp:
    tasks:
        phpstan_extended:
            autoload_file: ~
            chunk_size: 1000
            configuration: ~
            level: null
            force_patterns: []
            ignore_patterns: []
            triggered_by: ['php']
            memory_limit: "-1"
            use_grumphp_paths: true
```

**chunk_size**

*Default: 1000*

This parameter defines how many files will be checked in one execution of phpstan. This can help with performance on large codebases.

### XmlLint Extended

Extends the default [XmlLint task](vendor/phpro/grumphp/doc/tasks/xmllint.md) with strict schema validation.

***Config***

It lives under the `xmllint_extended` namespace and has following configurable parameters:

```yaml
# grumphp.yml
grumphp:
    tasks:
        xmllint_extended:
            ignore_patterns: []
            load_from_net: false
            x_include: false
            dtd_validation: false
            scheme_validation: false
            triggered_by: ['xml']
```
