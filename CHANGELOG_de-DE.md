# Version 2.7.0

#### Neue Funktionen

- Funktion: Kleines Paket hinzufügen
- Funktion: Abholadresse bei Auswahl in der Bestellung registrieren

# Version 2.6.0

- Nur für Shopware 6.4.

# Version 2.5.0

#### Neue Funktionen

- Feature: Einstellung des maximalen Postfachgewichts möglich

#### Verbesserungen

- Fix: Vermeidung von Typfehlern im DeliveryCalculatorDecorator
- Fix (Checkout): Vermeidung von Hängenbleiben des Einkaufswagens beim Postfachpreis
- Fix (Checkout): Pakettyp einfach wechseln
- Fix (Labels): A6 auf Anfrage drucken

# Version 2.4.0

- Nur für Shopware 6.4.

# Version 2.3.0

#### Neue Funktionen
- feat(checkout): Auswahl des Pakettyps zulassen

#### Verbesserungen
- fix(settings): Überflüssige Einstellungen entfernen und Beschriftungen korrigieren
- fix(checkout): korrekte Versandpreise anzeigen
- fix(checkout): Verhindert Änderungen an der Adresse, die nicht weitergegeben werden

# Version 2.2.0

### New features
- Support für Shopware 6.5

# Version 2.1.0

#### New features
- Etikett Beschreibung hinzufügen

#### Improvements
- Verbessert die MyParcel -Bestellnetzansicht
- Ermöglicht den Versand internationaler Bestellungen, wenn Mailbox Standard ist
- Fügen Sie Übersetzungen für Lieferoptionen in der Konfiguration hinzu
- Postleitzahlen mit nachgestellten Leerzeichen zulassen

# Version 2.0.0

#### Einschneidende Änderungen
- Um eine bessere Kundenerfahrung im Checkout zu bieten, haben wir die Anzahl der MyParcel-Versandmethoden in Shopware auf eine reduziert. Das bedeutet, dass die alten Versandmethoden deaktiviert wurden und nicht wieder aktiviert werden sollten. Bestellungen, die mit diesen Versandmethoden getätigt wurden, werden auch nicht mehr im Abschnitt MyParcel Orders in der Verwaltung angezeigt.
- In Vorbereitung auf kommende Funktionen und Erweiterungen mussten wir die Unterstützung für ältere Shopware-Versionen einstellen. Daher ist die erforderliche Mindestversion von Shopware jetzt 6.4.1.

#### Neue Funktionen
- Verbesserte Kundenerfahrung im Checkout: Die verfügbaren Versandoptionen ändern sich dynamisch basierend auf der Versandadresse und den ausgewählten Einstellungen aus Ihrem MyParcel-Backoffice.
- Weltweite Sendungen: Der Versand in Länder außerhalb Europas ist jetzt möglich.

# Version 1.3.3
- Behoben: Administrationsmodul ist nicht verfügbar

# Version 1.3.2
- Option "Abschaltzeit" hinzugefügt
- Verbesserte Snippets
- Versicherte Sendungen hinzugefügt

# Version 1.3.1
- Option zur Deaktivierung der Datumsauswahl im Checkout hinzugefügt
- Ein Fehler wurde behoben, der Ausnahmen verursachte, wenn zuerst eine andere Versandart ausgewählt und dann auf die Option MyParcel umgestellt wurde
- Einige andere kleinere Fehler behoben

# Version 1.3.0
- Hinzufügen der Möglichkeit, festzulegen, welche Felder für die Adresse verwendet werden sollen

# Version 1.2.1
- Fehler in Javascript behoben, der versuchte, die Versandoptionen auf allen Seiten abzurufen

# Version 1.2.0
- Unterstützung für Abholpunkte für Spediteure, die diese unterstützen, wurde hinzugefügt

# Version 1.1.0
- Kompatibilität mit Shopware 6.4 hinzugefügt

# Version 1.0.1
- Api-Test in Plugin-Konfiguration hinzugefügt.
- Deinstallation des Plugins, das Daten zurücklässt, behoben

# Version 1.0.0
- Erstes Release
