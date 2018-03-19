# EnterpriseException: HOWTO for dummies and experienced

This guide contains instructions, usage examples and some unordinary cases solutions for all **EnterpriseException**
library classes.

The _dummies_ section describes all functionality accessible _out-of-the-box_, how all classes work and their usage
minimal setup instructions.

The _experienced_ section describes additional functionality you can configure or add. Some unordinary cases and their
solutions are discussed in that section as well.

A note for both the library and this guide: "_FE_" stands for "Front End" (or "frontend") - the interface which is
accessible by an application users.

Contents:
- [Basic usage (or the _dummies_ section)](dummies/about.md)
    - [GlobalException](dummies/global-exception.md)
    - [CustomizableException](dummies/customizable-exception.md)
    - [Parser](dummies/parser.md)
- [Mastering EnterpriseExceptions (or the _exprerienced_ section)](experienced/about.md)
    - [... GlobalException](experienced/global-exception.md)
    - [... CustomizableException](experienced/customizable-exception.md)
    - [... Parser](experienced/parser.md)

You can install the library by [cloning the repository]({{ site.github.repository_url }}) or via **composer**:

```
composer require magic-push/enterprise-exception
```

If this guide needs any fixes, polishing or additions, if you still don't understand something about
**EnterpriseException** then feel free to [create an issue]({{ site.github.repository_url }}/issues) with
"_documentation_" label! I'll do my best to update this guide so it could become more useful and the library usage
could become more transparent.
