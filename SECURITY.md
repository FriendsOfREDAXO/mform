# Security Policy

## Unterstützte Versionen

Sicherheitsupdates erhalten ausschließlich die folgenden Versionsstränge:

| Version  | Unterstützt        |
| -------- | ------------------ |
| 9.0.x    | :white_check_mark: |
| 8.x      | :white_check_mark: (nur kritische Fixes) |
| < 8.0    | :x:                |

## Sicherheitslücke melden

Sicherheitsrelevante Funde bitte **nicht** über den öffentlichen Issue-Tracker
melden, sondern vertraulich über einen der folgenden Wege:

- Privater Security Advisory:
  https://github.com/FriendsOfREDAXO/mform/security/advisories/new
- Alternativ per E-Mail an die REDAXO-Community:
  security@redaxo.org (PGP optional, siehe https://redaxo.org)

Bitte folgende Informationen mitliefern:

1. Betroffene MForm-Version (`package.yml`) und REDAXO-Version
2. Schritt-für-Schritt-Reproduktion (möglichst minimaler PoC)
3. Erwartetes vs. tatsächliches Verhalten
4. Mögliche Auswirkungen (z. B. XSS, Privilege Escalation, RCE, Path Traversal)
5. Optional: Vorschlag für einen Fix oder Patch

## Bearbeitungsablauf

- **Eingangsbestätigung:** innerhalb von 5 Werktagen
- **Erstbewertung & Schweregrad-Einschätzung (CVSS):** innerhalb von 10 Werktagen
- **Fix-Bereitstellung:** je nach Schweregrad
  - Kritisch (CVSS ≥ 9.0): bevorzugt innerhalb von 14 Tagen
  - Hoch (7.0–8.9): innerhalb von 30 Tagen
  - Mittel/Niedrig: nach Roadmap
- **Veröffentlichung:** koordiniert per GitHub Security Advisory + Release-Notes
  nach Bereitstellung der gefixten Version

Wir bitten um Verständnis, dass MForm ein Community-Projekt ist und
Reaktionszeiten von der Verfügbarkeit der Maintainer abhängen.

## Scope

In Scope:
- MForm-Addon-Code (`lib/`, `pages/`, `assets/`, `fragments/`, `install.php`,
  `boot.php`)
- Vom Addon ausgelieferte Default-Module und Form-Builder-Ausgaben
- Dokumentation, soweit sie sicherheitsrelevante Empfehlungen enthält

Out of Scope:
- Sicherheitslücken im REDAXO-Core → bitte direkt an
  https://github.com/redaxo/redaxo/security
- Sicherheitslücken in Drittanbieter-Addons, die MForm verwenden
- Konfigurationsfehler in produktiven REDAXO-Instanzen

## Verantwortungsvolle Offenlegung

Bitte gib uns ausreichend Zeit, einen Fix bereitzustellen, bevor Details
öffentlich gemacht werden. Reporter werden – sofern gewünscht – im Advisory
und in den Release-Notes namentlich genannt.

Vielen Dank, dass du dazu beiträgst, MForm und das REDAXO-Ökosystem sicherer
zu machen!
