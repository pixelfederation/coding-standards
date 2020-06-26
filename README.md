# Coding standards

This package provides PHPCS rule set for coding standards in Pixelfederation. It should be included into
each project that uses PHPCS.

## Example usage

1) `composer requre pixelfederation/coding-standards:^1.0 --dev`
1) Create a file named `phpcs.ruleset.xml` to the root folder of your project with the following content:

```xml
<?xml version="1.0"?>
<ruleset name="PixelFederation">

  <description>PixelFederation rule set.</description>
  <exclude-pattern>tests/</exclude-pattern>
  <rule ref="vendor/pixelfederation/coding-standards/phpcs.ruleset.xml"/>
  
  <!-- include for PHP 7.3+ -->
  <!--  <rule ref="SlevomatCodingStandard.Functions.TrailingCommaInCall"/>&lt;!&ndash; for php 7.2 &ndash;&gt;-->
  
  <!-- include for PHP 7.4+ -->
  <!--  <rule ref="SlevomatCodingStandard.TypeHints.PropertyTypeHint"/>-->
</ruleset>
```
