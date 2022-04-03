**Ajánlórendszer**

Előre meghatározott látogatói információk alapján szegmens alapú termék vagy szoftver ajánlás készítése a látogató számára. Az ajánló a jellemző szegmenshez kapcsolódó (látogatás és vásárlás) interakciók alapján adja vissza a látogató számára hasznos a szegmenshez kapcsolódó termékeket vagy szolgáltatásokat, így ösztönözve további interakciókra.

**A komponensek áttekintése:**

_**Partner oldalába épített script**_
(Ezt a csomagot nem tartalmazza a rendszer)

Feladata:
 - Látogatókról adatokat gyűjt, beküldi az admin felületnek majd az admin rendszerbe eltárolásra kerül
 - Ajánlásáokat kér az admint rendszertől és megjeleníti

_**Admin**_

Feladata:
 - CMS rendszerként funkciónál, a partnerek, proposerek, termékek, szegmensek szerkesztésére.
 - Közvetítő szerep a partner oldali scrip és az ajánló rendszer között TODO leírni részletesen
 
 Szegmensek:
 
 A rendszer lelkét a szegmensek képezik. Ezek tulajdonképpen felhasználói csoportokat jelentenek, tehát a látogatókat fogjuk különböző kritériumok alapján csoportokba sorolni. 
 A szegmensekben csoportok kapnak helyet, amik között logikai kapcsolatokat tudunk megfogalmazni, a csoportokban pedig kritériumok vannak, amik közt szintén logikai kapcsolatot tudunk megadni.
 
 Proposerek:
 
 Egy proposer lényegében egy olyan elem, ami megjelenik a partner oldalán, és benne található X darab ajánlás. Ez tehát felfogható egy tároló elemnek, amiben az ajánlott termékek jellennek meg.
 Proposernek 3 típusa lehet:
	iFrame
	Oldalba ágyazott
	Popup

 Termék:
 
 Termékek lesznek, amiket megjelenítünk majd ajánlásokban. Létrehozáskor az alap adatokon kívül lehet megadni ‘Attribútumokat’. Ezek olyan jellemzők, amiket a felhasználó definiálhat. 
 Először létre kell hozni termék attribútumot, amihez tartozik egy típus (pl szöveges, szám, dátum, stb), majd fel kell vinni a termékhez, megadva az értéket.
 Ezek az attribútumok majd később az ajánlásoknál lesznek fontosabb. Ezek ugyanis segítenek abban, hogy hasonló termékeket találjunk. 

_**Recommender**_

Ez az ajánló rendszer, angolul recommender engine. Itt kap helyet a neurális hálózat, ami a felhasználók szegmentálását végzi, és itt találhatók egyéb gépi tanulás algoritmusok, 
amik pedig a termék ajánlásokat fogják kiszolgálni.


Feladata:
 - Új látogatókat segmentálja besorolja
 - Termék ajánlások a segmensekhez






