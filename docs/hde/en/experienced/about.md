# Mastering enterprise exceptions

(or the _experienced_ section)

This section describes additional features you can use or implement by overriding certain constants and methods.
You should be already familiar with the library functionality. Otherwise [go read about it](../dummies/about.md) first!

When you see any method mentioned in this guide (like `GlobalException::getCodeClass()`) you should consider that a
class name mentioned before a method name is just a reference for a method origins. In fact all **EnterpriseException**
methods and constants are accessed with late static bindings (like `static::getCodeClass()`) unless it's impossible
or illogical.

The _experienced_ section provides you with according articles for every class from the library:
- [Mastering GlobalException](global-exception.md)
- [Mastering CustomizableException](customizable-exception.md)
- [Mastering Parser](parser.md)
