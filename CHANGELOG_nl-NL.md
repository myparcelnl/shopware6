# Version 2.7.0

#### Nieuwe functies

- feat: klein pakket toevoegen
- feat: registreer afhaaladres in bestelling wanneer gekozen

# Versie 2.6.0

- Alleen voor shopware 6.4.

# Versie 2.5.0

#### Nieuwe functies

- feature: sta het instellen van het maximaal brievenbusgewicht toe

#### Verbeteringen

- fix: voorkom type error in DeliveryCalculatorDecorator
- fix(afrekenen): voorkom dat het winkelwagentje blijft hangen op de brievenbusprijs
- fix(afrekenen): verander het pakkettype goed
- fix(labels): print a6 wanneer daarom wordt gevraagd

# Versie 2.4.0

- Alleen voor shopware 6.4.

# Version 2.3.0

#### New features
- feat(checkout): pakkettype keuze toestaan

#### Improvements
- fix(settings): verwijder overbodige instellingen en corrigeer labels
- fix(checkout): toon de juiste verzendprijzen
- fix(checkout): voorkom adres aanpassingen die niet doorgegeven worden

# Versie 2.2.0

### New features
- Support voor Shopware 6.5 toevoegen

# Versie 2.1.0

#### New features
- Etiket omschrijving toevoegen

#### Improvements
- MyParcel order grid verbeteren
- Internationale verzendingen toestaan wanneer brievenbuspakket standaard is
- Vertalingen van leveringsopties toevoegen in config
- Postcodes met volgspaties toestaan

# Versie 2.0.0

#### Brekende veranderingen
- Om een betere klantervaring te bieden in de checkout, hebben we het aantal MyParcel verzendmethoden in Shopware teruggebracht naar één. Dit betekent dat de oude verzendmethoden zijn uitgeschakeld, en niet opnieuw moeten worden ingeschakeld. Orders die met deze verzendmethoden zijn gemaakt, worden ook niet meer weergegeven in de MyParcel Orders sectie in de administratie.
- Ter voorbereiding op nieuwe functies en verbeteringen hebben we de ondersteuning voor oudere Shopware-versies moeten stopzetten. De minimaal vereiste versie van Shopware is nu 6.4.1.

#### Nieuwe functies
- Verbeterde klantervaring in de checkout: De beschikbare verzendopties veranderen dynamisch op basis van het verzendadres en de geselecteerde instellingen uit uw MyParcel backoffice.
- Wereldwijde verzendingen: Verzending naar landen buiten Europa is nu beschikbaar.

# Version 1.3.3
- Probleem opgelost waardoor de administratie module niet beschikbaar was

# Version 1.3.2
- Optie cut-off tijd toegevoegd
- Verbeterde vertalingen
- Verzekerde zendingen toegevoegd

# Version 1.3.1
- Optie toegevoegd om de datumkiezer uit te schakelen bij het afrekenen
- Een bug opgelost die uitzonderingen veroorzaakte wanneer eerst een ander type verzendmethode werd geselecteerd en vervolgens werd overgeschakeld op de MyParcel-optie
- Enkele andere kleine bugs opgelost

# Version 1.3.0
- Toevoegen van de mogelijkheid om in te stellen welke velden er voor het adres gebruikt dienen te worden

# Versie 1.2.1
- Bug opgelost in Javascript waardoor op alle pagina's geprobeerd werd de verzendopties op te halen

# Version 1.1.0
- Shopware 6.4 compatibiliteit toegevoegd

# Version 1.0.1
- Api test knop in plugin configuratie toegevoegd.
- Opgelost dat het verwijderen van de plugin data achter liet.

# Version 1.0.0
- Initiële release
