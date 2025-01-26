# Coding standards

This package provides PHPCS rule set for coding standards in Pixel Federation. It should be included into
each project maintained by Pixel Federation that uses PHP Code Sniffer [PHPCS](https://github.com/squizlabs/PHP_CodeSniffer).

## Migration from v4 to v5
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
