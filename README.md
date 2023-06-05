# FieldtypeButton
ProcessWire Module Fieldtype to easily create and edit buttons for the frontend.

The returned value is an instance of **Button**. Direct output provided.

## Button properties
With the button object the following properties are provided:

**Defined in field settings**

|Property|Description|
|:-|:-|
| `html` | Markup for the output. Placeholders can be used. Define placeholders by surrounding property names with curled brackets. If a property is an object use dot syntax to get subproperties. |

**Defined in inputfield**

|Property|Description|
|:-|:-|
| `label` | Page object if detected as internal page, default: NULL |
| `target` | Relative paths will be translated to page (if exists). Placeholders (e.g. language home segments) can be used **@see** `html`  |
| `class` | CSS class |

**Generic properties**

|Property|Description|
|:-|:-|
| `targetPage` | Page object if detected as internal page, default: NULL |

**Language specific properties**

|Property|Description|
|:-|:-|
| `language` | Language object (current user language) |
| `lang` | Language home segment |
| `langNonDefault` | Language home segment appended by a slash or empty string if default language |
| `langFor<homeSegment>` | E.g. `langForEn`. Language home segment appended by a slash. Provided **only** if user language matches, otherwise an empty string will be returned |
|`label<languageID>` | E.g. `label1234`. Language specific button label. |



