# Mastering enterprise exceptions

(or the _experienced_ section)

This section describes additional features you can use or implement by overriding certain constants and methods.
You should be already familiar with the library functionality. Otherwise [go read about it](../dummies/about.md) first!

When you see any method mentioned in this guide (like `GlobalException::getCodeClass()`) you should consider that a
class name mentioned before a method name is just a reference for a method origins. In fact all **EnterpriseException**
methods are called with late static bindings (like `static::getCodeClass()`) unless it's impossible or illogical.

## Further reading

- [Mastering GlobalException](global-exception.md)
- [Mastering CustomizableException]()
- [Mastering Parser]()
