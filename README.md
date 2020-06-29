# Coding standards

This package provides PHPCS rule set for coding standards in Pixelfederation. It should be included into
each project that uses PHPCS.

## Example usage

### 1) Install composer dependencies

```bash
composer require phpcompatibility/php-compatibility:^9.3 --dev
composer require slevomat/coding-standard:~6.0 --dev
composer require pixelfederation/coding-standards:^1.0 --dev
```

### 2) Ruleset creation

Create a file named `phpcs.ruleset.xml` to the root folder of your project with the following content:

```xml
<?xml version="1.0"?>
<ruleset name="PixelFederation">

  <description>PixelFederation rule set.</description>
  <exclude-pattern>tests/</exclude-pattern>
  <rule ref="vendor/pixelfederation/coding-standards/phpcs.ruleset.xml"/>

  <rule ref="SlevomatCodingStandard.Files.TypeNameMatchesFileName">
    <properties>
      <property name="rootNamespaces" type="array">
        <element key="src" value="Your\Root\Namespace"/><!-- add your namespaces -->
      </property>
    </properties>
  </rule>
  
  <!-- include for PHP 7.3+ -->
  <!--  <rule ref="SlevomatCodingStandard.Functions.TrailingCommaInCall"/>-->
  
  <!-- include for PHP 7.4+ -->
  <!--  <rule ref="SlevomatCodingStandard.TypeHints.PropertyTypeHint"/>-->
</ruleset>
```

### Running checks

In your project directory run this command:

```bash
vendor/bin/phpcs --standard=phpcs.ruleset.xml --extensions=php --tab-width=4 -sp src
```

### Automatically fixing errors

In your project directory run this command:

```bash
vendor/bin/phpcbf --standard=phpcs.ruleset.xml --extensions=php --tab-width=4 -sp src
```

## Additional links

Sniffs documentation for slevomat coding standards are here: 
https://github.com/slevomat/coding-standard
