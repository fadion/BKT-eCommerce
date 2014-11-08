# Paketë PHP për shërbimin eCommerce të bankës BKT

Klasa të thjeshta për të lehtësuar gjenerimin e të dhënave për autorizimin e kërkesës dhe validimin e përgjigjes. Shërbimi eCommerce i BKT, ndryshe nga të tjerë, është i thjeshtë për t'u implementuar. Megjithatë, problemi kryesor me ta është gjenerimi i një hash për të autorizuar porosinë dhe validimi i tij në përgjigje, pjesë që nuk shpjegohen qartë në manual. Me këtë paketë nuk do ju duhet të bëni më shumë se të plotësoni disa parametra.

## Instalimi

Klasat mund të përdoren manualisht ose me `composer`. Kjo e fundit është mënyra e këshilluar dhe çdo zhvillues i zgjuar PHP duhet tashmë ta ketë futur composer në rutinën e tij.

### Composer

Fillimisht përfshini paketën në `composer.json`:

```json
"require" : {
    "fadion/bkt": "dev-master"
}
```

Instalojeni duke egzekutuar në terminal:

    $ composer install

Përfshini autoload të composer:

```php
require 'vendor/autoload.php';
```

### Manualisht

Mjafton ti përfshini klasat aty ku ju duhet. Vendodhja e tyre mund të mos jetë si më poshtë, sepse varet ku i keni vendosur.

```php
require('bkt/src/Authenticate.php');
require('bkt/src/Notify.php');
```

## Autorizimi

Autorizimi është faza ku gjeneroni të dhënat e porosisë dhe ja dërgoni serverit të BKT-së. Këtu blerësi do të drejtohet tek forma e pagesës dhe është e rëndësishme që të dhënat të jenë të sakta. Sidomos, si ç'përmenda më sipër, gjenerimi i hash-it është i pa dokumentuar dhe me siguri do ju sjellë dhembje koke.

Çdo llogari eCommerce nga BKT merr `clientid` dhe `storekey`, të dhëna unike që funksionojnë respektivisht si çelësa publik dhe privat. Në shembujt në vijim, do të përdor të dhëna të sajuara, që normalisht do t'ju duhet ti ndërroni me tuajat.

### Vendosja e të dhënave

```php
use Fadion\BKT\Authenticate;

$auth = new Authenticate([
    'orderid' => '12345',
    'clientid' => 'ABC123',
    'okUrl' => 'http://www.dyqanijuaj.com/sukses',
    'failUrl' => 'http://www.dyqanijuaj.com/problem',
    'storekey' => 'DEF456',
    'amount' => 1500
]);
```

Disa nga të dhënat standarte janë të para-vendosura dhe përgjithësisht nuk do ju duhet ti modifikoni. Ato të shfaqura më sipër i përkasin dyqanit tuaj apo porosisë në fjalë dhe do duhet ti vendosni në çdo autorizim.

Krahas kalimit të të dhënave si listë, mund ti kaloni edhe përmes metodave:

```php
$auth = new Authenticate();

$auth->setOrderId('ABC123');
$auth->setClientId('12345');
$auth->setOkUrl('http://www.dyqanijuaj.com/sukses');
$auth->setFailUrl('http://www.dyqanijuaj.com/problem');
$auth->setStoreKey('DEF456');
$auth->setAmount(1500);
```

Valuta është e vendosur automatikisht si LEK (kodi 008). Për ta ndryshuar keni dy mënyra:

Mund ta kaloni si çelës në listë:
```php
$auth = new Authenticate([
    // të dhënat e tjera
    'currency' => '840'
]);
```

Ose përmes metodës së dedikuar:

```php
$auth = new Authenticate();
$auth->setCurrency('840');
```

Kodet e valutave janë të vështirë të mbahen mend, prandaj mund të përdorni konstante për 3 vlerat tipike:

```php
$auth->setCurrency(Authenticate::CURRENCY_LEK);
$auth->setCurrency(Authenticate::CURRENCY_EUR);
$auth->setCurrency(Authenticate::CURRENCY_USD);
```

### Gjenerimi i Hash

Të gjitha të dhënat e kaluara deri tani tek objekti i autorizimit shërbejnë për një qëllim: gjenerimin e hash-it. Ky kod i gjeneruar i dërgohet serverit të BKT-së, i cili e përpunon dhe validon kërkesën.

```php
$auth->generate();
```

Mjafton metoda më sipër për ta gjeneruar hash dhe për ta futur në listën e të dhënave.

### Krijimi i Kërkesës

Serveri i BKT-së pret që kërkesa të jetë POST dhe mënyra më e lehtë për ta dërguar është përmes një forme. Fushat e formës janë të shpjeguara mirë në manual dhe nuk ka nevojë të zgjatemi këtu. Ajo që ju intereson është si ti përdorni të dhënat tek kjo formë.

Më sipër gjeneruam hash-in, i cili plotësoi listën e të dhënave. Kjo listë mund të aksesohet direkt përmes instancës së klasës, ku emrat e fushave janë egzaktësisht si ato në manual.

```php
<?php
$auth = new Authenticate([/* lista e te dhenave */]);
$auth->generate();
?>

<form method="post" action="http://serveri.i.bkt">
    <input type="hidden" name="clientid" value="<?= $auth['clientid']; ?>">
    <input type="hidden" name="amount" value="<?= $auth['amount']; ?>">
    <input type="hidden" name="hash" value="<?= $auth['hash']; ?>">
    <!-- pjesa tjeter e fushave -->
</form>
```

Për t'ja u lehtësuar punën, keni edhe një metodë që gjeneron inputet html të të gjitha të dhënave. Pjesën tjetër: adresën, kompaninë, telefon, etj, do t'ju duhet ti shtoni.

```php
echo $auth->inputs();
```

## Njoftimi

Marrja e përgjigjes është faza e fundit, ku njoftoheni nëse pagesa ishte e suksesshme. Ajo që ndodh në sfond është krahasimi i hash-it të sjellë nga serveri me atë lokal, për të verifikuar që përgjigja është e vlefshme. Klasa `Notify` bën egzaktësisht këtë, duke ju treguar me vetëm 2 rreshta nëse gjithçka shkoi mirë ose jo.

Klasa kërkon 2 parametra: të dhënat nga POST dhe `storekey` (çelësi privat) për të validuar përgjigjen.

```php
use Fadion\BKT\Notify;

$notify = new Notify($_POST, 'DEF456');

if ($notify->success()) {
    // porosi e suksesshme
}

// ose

if ($notify->error()) {
    // porosi me probleme
}
```

## Përgjegjësia

As unë dhe as kjo paketë nuk ka asnjë lidhje me BKT. Qëllimi i paketës është t'ju ndihmojë të integroni sistemin eCommerce të bankës, por unë, si autor i saj, nuk mbaj asnjë përgjegjësi për probleme që mund të sjellë. Jeni të lirë ta përdorni dhe modifikoni si t'ju duket më mirë.