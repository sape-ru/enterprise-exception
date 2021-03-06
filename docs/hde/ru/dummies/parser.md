# Parser

`Parser::parse()` применяется для... не поверите... _парсинга_ наследников [GlobalException](global-exception.md) и
[CustomizableException](customizable-exception.md).
<br/>![You don't say!](../../../assets/images/you-dont-say.jpg)

Содержание:
- [Зачем это нужно](#зачем-это-нужно)
- [Как это работает](#как-это-работает)
- [Требования](#требования)
- [Фильтрация классов](#фильтрация-классов)
    - [... по разделам](#фильтрация-классов-по-разделам)
- [Валидация классов](#валидация-классов)
- [Фильтрация исключений](#фильтрация-исключений)
- [Валидация исключений](#валидация-исключений)
- [Возвращаемые данные](#возвращаемые-данные)
    - [Демонстрационный скрипт](#демонстрационный-скрипт)

## Зачем это нужно

По несколкьим причинам. Вот вам пример из жизни.

Крупное веб-приложение, которое моя команда постоянно разрабатывает и развивает, насчитывает уже свыше _140_ классов с
более чем _1300_ исключений суммарно (объём и тех, и других продолжает расти). Когда у вас так много исключений и все
они используют функционал [GlobalException](global-exception.md#настройка), вам определённо захочется удостоверятся
в отсутствии дубликатов _глобальных кодов_.

Кроме того, если мы сообщаем пользователям лишь код исключения (ибо настоящее сообщение мы скрываем), здорово иметь
возможность, когда наши модераторы могут ввести этот код в специальное поле, нажать на кнопку и получить настоящее
сообщение, описывающее исключение. После чего модераторы могут решить, что делать дальше - иногда настоящее сообщение
описывает внутреннюю политику, понятную модераторам, но скрытую от пользователей.

Другая причина - API для наших пользователей. Мы хотим выводить на странице его описания список возможных исключений с
их кодами и сообщениями. Однако такой список должен обновляться автоматически и содержать только FE-доступные
исключения.

Нам нужно было как-то фильтровать и валидировать исключения. Так и появился **Parser**!

## Как это работает

**Parser** считывает `CLASS_CODE_LIST` для получения списка разбираемых классов. Сначала разбирается непосредственно
сам класс из этого списка, а затем разбираются его исключения (и их свойства). Сначала фильтры `$filters` сокращают
объём обрабатываемых данных, затем валидируются коды класса или исключения. Под конец формируется ответ из собранных
данных на основе настроек `$options`, которые вы задаёте.

## Требования

Если вы хотите использовать **Parser**, у вас должен быть базовый класс исключений, наследующий
[CustomizableException](customizable-exception.md), и в нём должен быть задан `CLASS_CODE_LIST` со всеми классами
исключений, которые вы хотите разбирать (даже если вы хотите разобрать только наследников
[GlobalException](global-exception.md)). Если вы не хотите использовать функционал
[GlobalException](global-exception.md#настройка), вы можете отключить его для конкретного класса в `CLASS_CODE_LIST`,
задав соответствующему классу **0** в качестве _кода класса_ - такие классы рассматриваются как не глобальные (опущены
валидация кодов и вычисление _глобальных кодов_).

```php
use MagicPush\EnterpriseException\CustomizableException\CustomizableException;

abstract class AppException extends CustomizableException
{
    const CLASS_CODE_LIST = [
        UserException::class    => 1,
        ProfileException::class => 2,
        BillingException::class => 0, // глобализация отключена
        // ...
    ];
}

// где-то ещё...
$options = [
    // ...
];
$filters = [
    // ...
];
$exceptions_parsed = Parser::parse(AppException::class, $options, $filters);
```

Прежде чем вы запустите разбор ваших классов исключений, сначала убедитесь, что все эти классы загружены. Это является
обязательным условием для работы **Parser**, чтобы тот мог сделать хоть что-нибудь (классы, которые не были загружены,
игнорируются в процессе разбора). Загрузите все классы вручную, либо установите автозагрузчик и настройте его должным
образом. С другой стороны, вам не нужно загружать разом все классы перед обращением к **Parser**. За подробностями
обратитесь к [разделу _для опытных_](../experienced/parser.md#загрузка-одного-класса-за-раз).

## Фильтрация классов

Все фильтры классов имеют в своих именах суффиксы _ex_ и _in_, обозначающие соответственно исключение и включение
классов.

Если вы хотите обработать лишь классы с конкретными кодами, используйте фильтр '_class_code_list_in_'. К примеру,
`$filters['class_code_list_in'] = [2, 3]` заставит **Parser** обрабатывать лишь классы с кодами **2** и **3**. Если же
вы хотите наоборот исключить классы с кодами **2** и **3** из обработки, используйте аналогичным образом фильтр
'_class_code_list\_**ex**_'.

Фильтры '_class_name_part_list_ex_' и '_class_name_part_list_in_' позволяют вам исключить или включить только классы,
в чьих полных именах содержатся искомые подстроки (чувствительно к регистру). К примеру,
`$filters['class_name_part_list_in'] = ['Billing']` заставит **Parser** обрабатывать только классы, в чьих полных
именах есть подстрока '_Billing_'.

### Фильтрация классов по разделам

Вы также можете фильтровать ваши классы по _разделам_ (чувствительно к регистру). _Раздел_ - это просто строка. К
примеру, у вас есть классы `OddAppException`, `FriendlyAppException` и `PartnerAppException`. Все эти классы
представляют исключения у ваших партнёров. Исключения всех этих классов могут быть выброшены приложениями ваших
партнёров. Так что если вы хотите отобрать лишь _партнёрские_ иключения, задайте всем этим классам одно имя _раздела_
('_партнёр_', '_ВНЕШНИЙ_' или какое-либо другое имя, которое захотите). После этого вы можете легко отобрать все эти
классы одним фильтром: `$filters['class_name_part_list_in'] = ['партнёр', 'ВНЕШНИЙ']`. Либо же можете наоборот
исключить эти классы с помощью обратного фильтра '_class_name_part_list\_**ex**_'.

Вы можете задавать разделы двумя способами:
1. Определите массив `CLASS_SECTION_LIST` таким же образом, как определяли до этого `CLASS_CODE_LIST`, только вместо
кодов (в качестве значений элементов) задайте любые строки (или что-либо другое, что можно привести к строке), какие
захотите:

    ```php
      // ...
  
      abstract class AppException extends CustomizableException
      {
          // ...
          
          const CLASS_SECTION_LIST = [
              FriendlyAppException::class => 'партнёр',
              PartnerAppException::class  => 'партнёр',
              OddAppException::class      => 'ВНЕШНИЙ',
              // ...
          ];
      }
    ```
    
1. Определите константу `CLASS_SECTION_DEFAULT`, задав ей строку (или что-либо другое, что можно привести к строке).
Любой класс с этой константой (как и все его наследники, которые не переопределяли `CLASS_SECTION_DEFAULT`) будет
относиться к соответствующему разделу.

## Валидация классов

Валидация выполняется только для классов, для которых включён функционал
[GlobalException](global-exception.md#валидация-кодов) (значение элемента `CLASS_CODE_LIST` не равно **0**).

Сначала _код класса_ проверяется с помощью `GlobalException::validateCodeClass()`. Если код считается некорректным,
будет выброшено исключение.

Затем вычисляется потенциальный _глобальный код_ с помощью `GlobalException::getCodeGlobal()`, которому передаётся
**1** в качестве _базового кода_. Если для какого-либо другого класса будет вычислен такой же потенциальный _глобальный
код_ (т. е. могут возникнуть дубликаты _глобальных кодов_), будет выброшено исключение.

Если вы среди настроек зададите '_add_errors_' или '_ignore_invalid_', тогда выброшенные в процессе валидации
ошибки будут подавлены, а соответствующие классы (и все их исключения) будут просто пропущены.

## Фильтрация исключений

Возможна фильтрация только исключений из классов, наследующих [CustomizableException](customizable-exception.md).

Почти все фильтры исключений имеют в своих именах суффиксы _ex_ и _in_, обозначающие соответственно исключение и
включение... эээ... исключений (в английском языке эта же фраза звучит явно лучше).

Если вы хотите обработать лишь исключения с конкретными кодами (_базовыми кодами_, если соответствующие классы
исключений используют функционал [GlobalException](global-exception.md#как-это-работает)), используйте фильтр
'_base_code_list_in_'. К примеру, `$filters['base_code_list_in'] = [11, 12, 14]` заставит **Parser** обрабатывать лишь
исключения с кодами **11**, **12** и **14**. Если же вы хотите наоборот убрать из обработки исключения с (_базовыми_)
кодами **11**, **12** и **14**, используйте аналогичным образом фильтр '_base_code_list\_**ex**_'.

Также вы можете фильтровать исключения по их (_базовым_) кодам в заданных диапазонах (или просто границах, если задать
только "левую" или "правую"):
- '_base_code_from_in_' - для задания начала диапазона **в**ключения
- '_base_code_from_ex_' - для задания начала диапазона **ис**ключения
- '_base_code_to_in_' - для задания конца диапазона **в**ключения
- '_base_code_to_ex_' - для задания конца диапазона **ис**ключения

Существует ещё и фильтр, относящийся к одному из свойств исключений - '_show_fe_'. Если вы хотите получить список
исключений, сообщения которых можно показывать пользователям, задайте фильтр '_show_fe_' со значением **true**. Если же
вы наоборот хотите список исключений, сообщения которым пользователям показывать нельзя, задайте фильтр '_show_fe_' со
значением **false**. Задайте значение **null** для отключения этого фильтра (поведение по умолчанию).

Если вы хотите добавить свои собственные фильтры, вам следует обратиться за инструкциями к
[разделу _для опытных_](../experienced/parser.md#ваши-фильтры-исключений).

## Валидация исключений

Валидация выполняется только для исключений, чьи классы наследуют [CustomizableException](customizable-exception.md)
и используют функционал [GlobalException](global-exception.md#валидация-кодов) (значение элемента `CLASS_CODE_LIST`
не равно **0**).

_Базовый код_ каждого исключения проверяется с помощью `GlobalException::validateCodeBase()`. Если какой-либо _базовый
код_ считается некорректным, выбрасывается исключение.

Если вы среди настроек зададите '_add_errors_' или '_ignore_invalid_', тогда выброшенные в процессе валидации
ошибки будут подавлены, а соответствующие исключения будут просто пропущены.

## Возвращаемые данные

Возвращаемые данные относятся лишь к классам и исключениям, которые "пережили" фильтрацию и валидацию. Ключи элементов
массива первого уровня зависят от классов.

Если разобранные классы используют функционал [GlobalException](global-exception.md#настройка), все данные по ним будут
расположены под ключом '_\_\_global_'. Элементы данного подмассива представляют _глобальные исключения_ и содержат их
_глобальные коды_ в качестве ключей:

```php
array (
    '__global' => 
    array (
        100001 => // ...
        100002 => // ...
        // ...
        // затем идут глобальные коды из следующего класса:
        200001 => // ...
        200002 => // ...
        // ...
    ),
    // ...
```

Если разобранные классы не используют функционал [GlobalException](global-exception.md#настройка), все данные по ним
будут расположены под ключами, равными полным именам соответствующих классов. Элементы данных подмассивов представляют
исключения соответствующих классов и содержат их коды в качестве ключей:

```php
array (
    'MyCoolException' => 
    array (
        1  => // ...
        2  => // ...
        // ...
    ),
    'AnotherCoolException' =>
    array ( // ...
        100 => // ...
        101 => // ...
        200 => // ...
    ),
    // ...
```

Ни один из вышеупомянутых ключей массива первого уровня не будет присутствовать в возвращаемом массиве, если задать
настройку '_no_data_'. Это бывает полезно, если вы хотите лишь провалидировать ваши исключения.

Если вы включите настройку '_add_errors_', то ошибки валидации [классов](#валидация-классов) и
[исключений](#валидация-исключений) будут подавлены (как и в случаче с настройкой '_ignore_invalid_'), но все сообщения
этих ошибок будут присутствовать в виде подмассива в возвращаемом массиве под ключом первого уровня '_\_\_errors_':

```php
array (
  // ...
  '__errors' => 
  array (
    0 => 'The base code -5 for "MyCoolException" must range from 1 to 99999.',
    1 => 'The base code 250000 for "AnotherCoolException" must range from 1 to 99999.',
    2 => 'Same potential global code 500001 generated for "CrazyException" and "MadException".',
    // ...
  ),
)
```

По умолчанию в качестве возвращаемых данных присутствуют _полные сообщения_ исключений, собранные из свойств
'_context_' и '_message_' (подробности читайте в
[настройке CustomizableException](customizable-exception.md#настройка)). Вы можете заменить подстроки '_message_' на
'_message_fe_' (если таковые заданы для тех или иных исключений), включив настройку '_use_message_fe_':

```php
array (
  '__global' => 
  array (
    100001 => 'Случилось что-то ужасное',
    100002 => 'Обновление профиля: недостаточно средств', // задано свойство 'context'
    // строка ниже возвращается, если настройка 'use_message_fe' выключена
    100003 => 'Операция недоступна для ненадёжных пользователей',
    // но если вы включите настройку 'use_message_fe':
 // 100003 => 'Пожалуйста, сначала подтвердите ваш электронный адрес',
    // ...
  ),
  // ...
)
```

Если вам недостаточно только сообщений, и вы хотите получить все возможные данные по исключениям, включите настройку
'_is_extended_':

```php
array (
  '__global' => 
  array (
    100001 => 
    array (
      'base_code' => 1,
      'class_code' => 1,
      'class_name' => 'MyCoolException',
      'class_section' => '',
      'context' => '',
      'message' => 'Случилось что-то ужасное',
      'message_fe' => '',
      'show_fe' => false,
    ),
    100002 => 
    array (
      'base_code' => 2,
      'class_code' => 1,
      'class_name' => 'MyCoolException',
      'class_section' => '',
      'context' => 'Обновление профиля',
      'message' => 'недостаточно средств',
      'message_fe' => '',
      'show_fe' => true,
    ),
    100003 => 
    array (
      'base_code' => 3,
      'class_code' => 1,
      'class_name' => 'MyCoolException',
      'class_section' => '',
      'context' => '',
      'message' => 'Операция недоступна для ненадёжных пользователей',
      'message_fe' => 'Пожалуйста, сначала подтвердите ваш электронный адрес',
      'show_fe' => true,
    ),
    // ...
  ),
  // ...
)
```

Среди настроек есть также '_locale_', которая передаётся методу `CustomizableException::getL10N()`
([обёртке для переводов](../experienced/customizable-exception.md#обёртка-для-переводов)) для перевода всех частей
сообщения для каждого исключения.

Также вы можете полностью изменить формат возвращаемых данных или добавить новый (возможно, добавить дополнительные
настройки для управения форматами).
Чтобы узнать больше, посетите [раздел _для опытных_](../experienced/parser.md#настройка-вывода).

### Демонстрационный скрипт

В репозитории есть демонстрационный скрипт с несколькими уже настроенными классами и свойствами их исключений для
быстрого ознакомления с результатами разбора исключений. Просто запустите его в командной строке:

```php
php examples/parser.php
```

## Читайте также

- [GlobalException](global-exception.md)
- [CustomizableException](customizable-exception.md)
- [Углублённое изучение Parser](../experienced/parser.md)

