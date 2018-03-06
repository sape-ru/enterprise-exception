## Contributing guide

Your wish to contribute is appreciated. :smile_cat: And I will look through all contributings I get ASAP!

There are two ways how you can contribute:
- :memo: [Create an issue](#create-an-issue) to ask me for changes.
- :arrow_upper_left: [Make a pull request](#make-a-pull-request) to suggest your changes.

One main rule for all contributings and communications: :gb: speak english only :us:. This repository is public
so are all changes and discussions. And I want everyone is able to read and understand everything is discussed
and documented here.

### Create an issue

You can freely create issues if you have some great ideas for improvement or new features or if you encounter any bugs.

- Try to add an unambigous and certain title. Here are some examples:
    - Good :smile: :
        - _Add an option %option_name_or_2-3-words_description% to Parser_
        - _Improve Parser performance by %your_suggestion_in_2-3_words%_
        - _Objects construction bug_
        - _Grammar fix for %document_name_or_section%_
    - Bad :rage: :
        - _Please add a new option to Parser_
        - _An idea how to improve Parser performance_
        - _WTF dude?!_
        - _I have a question_
- Write as much detailed description as possible:
    - Is it a bug? How to reproduce it?
    - A new feature? For what purposes (use cases)? How it should be controlled (configured / triggered / etc.)?

### Make a pull request

You must follow the rules if you don't want your pull request to be rewritten (in that case
your commits will obviously get my name and email) or deleted.

- Follow the same code style. The code for this library is written strictly followind
[PSR-1](https://www.php-fig.org/psr/psr-1/), [PSR-2](https://www.php-fig.org/psr/psr-2/) and
[PSR-4](https://www.php-fig.org/psr/psr-4/) with a note for _PSR-2_:
    - Property names MUST BE prefixed with a single underscore to indicate protected or private visibility.
- Follow the same documenting style. Add PHPDoc blocks for every class, method, constant or property you add.
    - Each doc block must have its short single line summary. If you want to add a detailed description a blank line
    must be between it and a summary.
    - Write descriptions as detailed as possible so nobody needs to inspect code itself for better understanding.

There are no other strict rules for PHPDoc style. I tried to follow the abandoned
[PSR-5](https://github.com/phpDocumentor/fig-standards/blob/master/proposed/phpdoc.md) when possible but also made
some differences according to my IDE hinting - because the suggested recomendation didn't work.
For instance you can encounter an array elements description enclosed into _\<pre\>_ tags. Alse some functions doc
blocks have _\@noinspection_ tags to suppress local IDE inspections.
