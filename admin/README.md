Telepítés:
-
1. `composer install`
2. .env létrehozás .env.example alapján (swagger url, db connection)
3. queue indítása: `forever queue.js` || `composer start-worker`
5. Admin e-mail: admin@admin.hu jelszó: admin (e-mail felül írható `.env ADMIN_EMAIL` konfigban) 

Dev:
-
- Migráció: `php artisan migrate`
- DB seed: 
    - Működéshez szükséges alapadatok`composer seed-prod`
    - Teszt adatok: `php artisan db:seed`
- Tesztek futtatása: `composer test`
- Asset (js,css) compile: `npm run dev`
- Asset watch: `npm run watch`

API használat:
-
Swagger: `/api/documentation`

API rate limiting: 120 req /min

Minden consumer appnak létre kell hozni egy partnert, amivel használni lehet az API -t.

API kulcs műveletek:
- `apikey:generate <name>`
- `apikey:deactivate <name>`
- `apikey:activate <name>`
- `apikey:delete <name>`
- `apikey:list`

Partner aktiválása:
-
- Új partner létrehozása felületen
- A kapott `external_id` és `api_key` másolása
- Integrálás kf-plugin script config -ba

--------

Fejlesztői jegyzetek:
-

#### Segment user data lekérdezés:

Amikor mentjük a szegmenst elindul egy segment:segmentify command.
Ilyenkor fut le a Segment::buildQuery. Ez a szegment csoportjai alapján összeállít egy user data sql queryt és lekérdezi az ide tartozó elemeket.
Ezután a kapott id -kal elindul két job:
- Unsegmentify: ez szimplán törli az adott szegmens id -ját minden user data sorból (updatekor van jelentősége)
- Segmentify: ez pedig chunkolja az id -kat és minden chunkkal elindít egy SegmentifyChunk jobot. Ez pedig szimplán update -el minden user data sort a kapott id -k alapján.

Tehát:
```
SegmentController::create() 

Commands\Segmentify

Segment::buildQuery()

Jobs\Unsegmentify

Jobs\Segmentify

Jobs\SegmentifyChunk
```

Szegmens megtekintésekor az ide tartozó user data sorokat a Segment::getUserData() kérdezi le. Ez semmi mást nem csinál, mint rászűr a segment id -ra és persze még hozzá teszi a kapott filtereket (ha ezek alapján szükséges, akkor join -olja a page_loads táblát is).

#### Segment életciklus:

**Create**: a már fent említett módon Segmentify job fog futni és beállítja a user data ide tartozó sorait.

**Update**: Itt is pontosan ugyanaz történik, mint create -kor. Itt is a SegmentController::runSegmentify() fogja indítani a commandot és a jobokat. Ezért is fog futni a Segmentify előtt az Unsegmentify. Ennek update -kor van értelme, hiszen kitörli az adott szegmens id -t az összes user data -ból, majd újra szegmentálja azokat.

**Destroy**: Controller fog indítani egy Unsegmentify Jobot.

**Restore**: Create -hez hasonlóan itt is a runSegmentify függvény indít Unsegmentify és Segmentify jobokat.

**Replicate**: Itt nem történik semmi, az új szegmensben kezdetben nem tartozik semmilyen user adat. Update -kor pedig lefut a runSegmentify().

**Ütemezett task**: Ezen kívül van egy ütemezett task, ami minden nap éjfélkor szegmentálja az üresen maradt usr adatokat 'Egyéb' szegmensbe.

#### Normalizerek:

3 helyen vannak használva:
- SegmentGroupCriteria::buildQuery(). Ez szegmens createkor / updater fog futni.
- Segment::getUserData(). Amikor megnézik a szegmen részletek oldalt, és lekérdezzü az ide tartozó user data sorokat.
- Listable::addFilters(). Filterek alkalmazása
