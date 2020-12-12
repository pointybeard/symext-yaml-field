# YAML Field for Symphony CMS

A field for [Symphony CMS][ext-Symphony-cms] that validates and stores YAML formatted data.

-   [Installation](#installation)
    -   [With Git and Composer](#with-git-and-composer)
    -   [With Orchestra](#with-orchestra)
-   [Basic Usage](#basic-usage)
-   [About](#about)
    -   [Requirements](#dependencies)
    -   [Dependencies](#dependencies)
-   [Support](#support)
-   [Contributing](#contributing)
-   [License](#license)

## Installation

This is an extension for [Symphony CMS][ext-Symphony-cms]. Add it to the `/extensions` folder of your Symphony CMS installation, then enable it though the interface.

### With Git and Composer

```bash
$ git clone --depth 1 https://github.com/pointybeard/symext-yaml-field.git extensions/yamlfield
$ composer update -vv --profile -d ./extensions/yamlfield
```
After finishing the steps above, enable "Field: YAML" though the administration interface or, if using [Orchestra][ext-Orchestra], with `bin/extension enable yamlfield`.

### With Orchestra

1. Add the following extension defintion to your `.orchestra/build.json` file in the `"extensions"` block:

```json
{
    "name": "yamlfield",
    "repository": {
        "url": "https://github.com/pointybeard/symext-yaml-field.git"
    }
}
```

2. Run the following command to rebuild your Extensions

```bash
$ bin/orchestra build \
    --skip-import-sections \
    --database-skip-import-data \
    --database-skip-import-structure \
    --skip-create-author \
    --skip-seeders \
    --skip-git-reset \
    --skip-composer \
    --skip-postbuild
```

## Basic Usage

This extension adds a new field called "Yaml". It can be added to sections like any other field. It functions in a similar way to a Text Box field, however, upon saving it will validate the contents and throw an error if it is not valid YAML.

## About

### Requirements

- This extension works with PHP 7.4 or above.

### Dependencies

This extension depends on the following Composer libraries:

-   [PHP Helpers][dep-helpers]
-   [Symphony CMS: Extended Base Class Library][dep-symphony-extended]
-   [Dallgoot: YAML library for PHP][dep-dallgoot-yaml]

## Support

If you believe you have found a bug, please report it using the [GitHub issue tracker][ext-issues],
or better yet, fork the library and submit a pull request.

## Contributing

We encourage you to contribute to this project. Please check out the [Contributing to this project][doc-CONTRIBUTING] documentation for guidelines about how to get involved.

## Author
-   Alannah Kearney - <https://github.com/pointybeard>
-   See also the list of [contributors][ext-contributor] who participated in this project

## License
"YAML Field for Symphony CMS" is released under the MIT License. See [LICENCE][doc-LICENCE] for details.

[doc-CONTRIBUTING]: https://github.com/pointybeard/symext-yaml-field/blob/master/CONTRIBUTING.md
[doc-LICENCE]: http://www.opensource.org/licenses/MIT
[dep-helpers]: https://github.com/pointybeard/helpers
[dep-dallgoot-yaml]: https://github.com/dallgoot/yaml
[dep-symphony-extended]: https://github.com/pointybeard/symphony-extended
[ext-issues]: https://github.com/pointybeard/symext-yaml-field/issues
[ext-Symphony-cms]: http://getsymphony.com
[ext-Orchestra]: https://github.com/pointybeard/orchestra
[ext-contributor]: https://github.com/pointybeard/symext-yaml-field/contributors
[ext-docs]: https://github.com/pointybeard/symext-yaml-field/blob/master/.docs/toc.md
