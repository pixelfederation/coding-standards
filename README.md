# Coding standards

This package provides PHPCS rule set for coding standards in Pixel Federation. It should be included into
each project maintained by Pixel Federation that uses PHP Code Sniffer [PHPCS](https://github.com/squizlabs/PHP_CodeSniffer).

## Example usage

### 1) Install composer dependencies

```bash
composer require pixelfederation/coding-standards:^2.0 --dev
```

### 2) Ruleset creation

Create a file named `phpcs.ruleset.xml` to the root folder of your project with the following content:

```xml
<?xml version="1.0"?>
<ruleset name="PixelFederation">

  <description>PixelFederation rule set.</description>
    
  <config name="testVersion" value="7.2-7.4"/><!-- insert your php version -->
  <exclude-pattern>tests/</exclude-pattern>
  <rule ref="vendor/pixelfederation/coding-standards/phpcs.ruleset.xml">
    <!-- old projects may want to exclude these rules: -->
    <!-- <exclude name="SlevomatCodingStandard.Classes.SuperfluousAbstractClassNaming"/>
    <exclude name="SlevomatCodingStandard.Classes.SuperfluousTraitNaming"/>
    <exclude name="SlevomatCodingStandard.Classes.SuperfluousInterfaceNaming"/>
    <exclude name="SlevomatCodingStandard.Classes.SuperfluousExceptionNaming"/> -->
    <!-- <exclude name="SlevomatCodingStandard.Files.FunctionLength"/> -->
  </rule>

  <rule ref="SlevomatCodingStandard.Files.TypeNameMatchesFileName">
    <properties>
      <property name="rootNamespaces" type="array">
        <element key="src" value="Your\Root\Namespace"/><!-- add your namespaces -->
      </property>
    </properties>
  </rule>

  <!-- include for PHP 7.2- -->
  <!--  <rule ref="SlevomatCodingStandard.Functions.DisallowTrailingCommaInCall"/>-->

  <!-- include for PHP 7.3+ -->
  <!--  <rule ref="SlevomatCodingStandard.Functions.RequireTrailingCommaInCall"/>-->
  
  <!-- include for PHP 7.4+ -->
  <!--  <rule ref="SlevomatCodingStandard.TypeHints.PropertyTypeHint"/>-->
  <!--  <rule ref="SlevomatCodingStandard.Functions.ArrowFunctionDeclaration"/>-->

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
