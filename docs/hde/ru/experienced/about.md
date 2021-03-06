# Углублённое изучение EnterpriseExceptions

(или раздел _для опытных_)

Данный раздел посвящён описанию дополнительных возможностей, которые вы можете использовать или внедрить,
переопределив некоторые константы и методы. Вы уже должны быть знакомы с основным функционалом библиотеки. Если это не
так, [изучите его](../dummies/about.md) в первую очередь!

Если в данном руководстве вы видите упоминание какого-либо метода или константы (вроде
`GlobalException::getCodeClass()`), имейте ввиду, что в таких случаях имя класса приводится лишь для указания, где
данные метод или константа были объявлены впервые. На деле же все обращения ко всем методам и константам
**EnterpriseException** осуществляются с помощью позднего связывания (вроде `static::getCodeClass()`) за исключением
случаев, когда это невозожно или нелогично.

Раздел _для опытных_ содержит соответствующие статьи для каждого класса библиотеки:
- [Углублённое изучение GlobalException](global-exception.md)
- [Углублённое изучение CustomizableException](customizable-exception.md)
- [Углублённое изучение Parser](parser.md)
