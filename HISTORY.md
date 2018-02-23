# HISTORY

This history log references the relevant changes like:
- new functionality
- fixes
- changes to methods signatures
- PHP version related changes
- significant optimization

Irrelevant changes that will not be mentioned here:
- refactoring
- documentation changes (readme, PHPDoc, etc.)
- adding/updating/deleting examples

And here goes the history itself...

## 2.1.0 (2018-02-23)

#### New features:

- [composer.json](composer.json) added for registering this library to **Packagist**.
- [GlobalException](src/GlobalException.php)::**getCodeClassMax()** public method
for the biggest class code public access.
- [Parser](src/CustomizableException/Parser.php)::**parse()** new option '_add_errors_' - to suppress validations errors
(like '_ignore_invalid_' option) and also add those errors messages to the output under the '_\_\_errors_' key.

#### Fixes:

- [Parser](src/CustomizableException/Parser.php)::**parse()** crash when trying to filter
[GlobalException](src/GlobalException.php) descendants by class sections.

#### Other:

- [CustomizableException](src/CustomizableException/CustomizableException.php)::**getCodeFormatted()**
is moved to [GlobalException](src/GlobalException.php).
- [CustomizableException](src/CustomizableException/CustomizableException.php)::**getL10N()** parameter **$locale**
default value is changed to **null** (considering locale autodetection inside the method).
- [Parser](src/CustomizableException/Parser.php)::**loadClass()** doesn't check a class existence anymore.
Now it does absolutely nothing.
- [Parser](src/CustomizableException/Parser.php)::**parse()** has now an explicit check
if **$config_class_name** is loaded. This check can not be ignored.
- [Parser](src/CustomizableException/Parser.php)::**BASE_CLASS_NAME** is removed as redundant; **$config_class_name**
must be a subclass of [CustomizableException](src/CustomizableException/CustomizableException.php).

## 2.0 (2018-02-15)

The first open source release, initial public launch. All the code was redesigned and rewritten from scratch on my free
time.

The very first GitHub commit contains all things I wanted to implement for this exceptions management library. Future
updates are possible but not on a regular basis.

## 1.0 (2014-02-26)

The idea of global and customizable exceptions was implemented for the first time. It was used for a @sape-ru project
I continuously develop.
