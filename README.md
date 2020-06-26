# Coding standards

This package provides PHPCS rule set for coding standards in Pixelfederation. It should be included into
each project that uses PHPCS.

## Example usage

### 1) Install composer dependencies

```bash
composer requre phpcompatibility/php-compatibility:^9.3 --dev
composer requre slevomat/coding-standard:~6.0 --dev
composer requre pixelfederation/coding-standards:^1.0 --dev
```

### 2) Ruleset creation

Create a file named `phpcs.ruleset.xml` to the root folder of your project with the following content:

```xml
<?xml version="1.0"?>
<ruleset name="PixelFederation">

  <description>PixelFederation rule set.</description>
  <exclude-pattern>tests/</exclude-pattern>
  <rule ref="vendor/pixelfederation/coding-standards/phpcs.ruleset.xml"/>
  
  <!-- include for PHP 7.3+ -->
  <!--  <rule ref="SlevomatCodingStandard.Functions.TrailingCommaInCall"/>-->
  
  <!-- include for PHP 7.4+ -->
  <!--  <rule ref="SlevomatCodingStandard.TypeHints.PropertyTypeHint"/>-->
</ruleset>
```
