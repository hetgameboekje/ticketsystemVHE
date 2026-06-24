# Database-ontwerp via XML

Eén XML-bestand per tabel. Run na een wijziging:

```
php database/parse.php
```

Dit schrijft `database/.parsed/schema.sql`, dat je vervolgens in HeidiSQL/`mysql` uitvoert.

## Formaat

```xml
<table name="tabelnaam" engine="InnoDB">
  <columns>
    <column name="id" type="INT" primary="true" auto_increment="true"/>
    <column name="naam" type="VARCHAR" length="150" nullable="false"/>
    <column name="status" type="VARCHAR" length="30" nullable="false" default="'open'"/>
    <column name="afdeling_id" type="INT" references="afdelingen.id" on_delete="CASCADE"/>
    <column name="updated_at" type="TIMESTAMP" nullable="false" default="CURRENT_TIMESTAMP" on_update="CURRENT_TIMESTAMP"/>
  </columns>
  <seed>
    <row><value column="naam">ICT</value></row>
  </seed>
</table>
```

Attributen op `<column>`:
- `name`, `type` — verplicht.
- `length` — bv. `150` of `10,2` (voor `NUMERIC(10,2)`).
- `nullable="false"` — voegt `NOT NULL` toe (standaard nullable).
- `primary="true"` — markeert als primary key.
- `auto_increment="true"`
- `unique="true"`
- `default="..."` — letterlijk overgenomen in de SQL, dus zelf quotes toevoegen bij tekst (`default="'open'"`), geen quotes bij expressies (`default="CURRENT_TIMESTAMP"`).
- `on_update="..."` — voor `ON UPDATE CURRENT_TIMESTAMP`.
- `references="tabel.kolom"` — genereert een `FOREIGN KEY`. De parser zet tabellen automatisch in de juiste volgorde op basis hiervan.
- `on_delete="CASCADE"` — optioneel, bij een foreign key.

`<seed>` is optioneel: rijen die met `INSERT IGNORE` worden toegevoegd na het aanmaken van alle tabellen.

Een nieuw veld toevoegen = een `<column>`-regel toevoegen aan het juiste bestand en opnieuw parsen.
