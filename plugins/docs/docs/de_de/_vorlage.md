# Seitenüberschrift

-   [Kopfbereich](#kopfbereich)
-   [Überschriften](#ueberschriften)
-   [Links](#links)
-   [Listen](#listen)
-   [Tabellen](#tabellen)
-   [Code](#code)
-   [Hinweise](#hinweise)
-   [Anker 3](#anker-3)
    - [Anker 3a](#anker-3a)
    - [Anker 3b](#anker-3b)
    - [Anker 3c](#anker-3c)
-   [Anker 4](#anker-4)

---

<a name="kopfbereich"></a>
## Kopfbereich

1. Seitenüberschrift als h1 auszeichnen
2. TOC Liste mit Anker erstellen, Die erste Ebene wird im Text mit `h2`, die zweite Ebene mit `h3` ausgezeichnet.

**Beispiel Kopfbereich**

    # Seitenüberschrift

    - [Überschrift](#anker-zur-ueberschrift)
    - [Anker 2](#anker-2)
        - [Anker 2a](#anker2a)
    - [Anker 3](#anker-3)
        - [Anker 3a](#anker-3a)
        - [Anker 3b](#anker-3b)
        - [Anker 3c](#anker-3c)
    - [Anker 4](#anker-4)


<a name="ueberschriften"></a>
## Überschriften mit Anker setzen

Die Sprunganker müssen an der betreffenden Stelle gesetzt werden.

**Beispiel Sprunganker**

    <a name="anker-zur-ueberschrift"></a>
    ## Überschrift

---

<a name="links"></a>
## Links

Der verlinkte Text wird in eckige Klammern gesetzt, der Link dahinter in runden Klammern.

**Beispiel Link**

    [Linktitel](markdown-datei.md)

**Ausgabe Link**

[Linktitel](markdown-datei.md)

---

<a name="listen"></a>
## Listen

**Beispiel Liste**

    - Listenpunkt 1
    - Listenpunkt 2
    - Listenpunkt 3
    - Listenpunkt 4

**Ausgabe Liste**

- Listenpunkt 1
- Listenpunkt 2
- Listenpunkt 3
- Listenpunkt 4

---

<a name="tabellen"></a>
## Tabellen

**Beispiel Tabelle**

```
Alt | Neu
------ | ------
`$REX['SERVERNAME']` | `rex::getServername()`
```

**Ausgabe Tabelle**

| Alt                  | Neu                    |
| -------------------- | ---------------------- |
| `$REX['SERVERNAME']` | `rex::getServername()` |

---

<a name="code"></a>
## Code

**Beispiel Code Block**

```php
    <?php
    // Code wird einfach nur mit Tabs eingerückt.
    // Nicht (wie hier auf Github möglich ) die ``` verwenden.
    $article = rex_article::get();
```


**Beispiel Code Inline**

	Code innerhalb eines Text wird `ganz normal` mit Backticks ausgezeichnet.

**Ausgabe Code Inline**

Code innerhalb eines Text wird `ganz normal` mit Backticks ausgezeichnet.

---

<a name="hinweise"></a>
## Hinweise

Für Hinweise könnten wir die Blockquote-Formatierung verwenden.

**Beispiel Hinweis**

    > **Hinweis:** Aliquam arcu lectus, imperdiet sollicitudin vehicula ultricies, pellentesque at nunc. Pellentesque ut consectetur nisl. In finibus efficitur turpis, posuere facilisis dui tristique ac.

**Ausgabe Hinweis**

> **Hinweis:** Aliquam arcu lectus, imperdiet sollicitudin vehicula ultricies, pellentesque at nunc. Pellentesque ut consectetur nisl. In finibus efficitur turpis, posuere facilisis dui tristique ac.

---

## Markdown-Referenz

[https://daringfireball.net/projects/markdown/syntax](https://daringfireball.net/projects/markdown/syntax)  
[http://markdown.de/](http://markdown.de/)
