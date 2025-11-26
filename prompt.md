KONU – ÖZET

FleetCart’ta Unit sistemi ekli. Metre gibi ondalıklı birimlerde hâlâ 3 ciddi problem var:

Ürün detayda qty=1.5 ile “Add to Cart” deyince sepet satırı 2 olarak gidiyor. Ama side cart / sepet içinde qty’yi 1.5 → 2.5 olarak arttırınca doğru çalışıyor. Yani ilk AddToCart akışında qty yuvarlanıyor.

Müşteri input’a 1.2 yazınca 1.2 kabul edilmiyor, 1.5’e zorlanıyor. Min=0.5, step=0.5 için geçersiz değerlerde düzgün bir validasyon/mesaj yok, rastgele normalize oluyor.

Admin panelde metrelik üründe stok alanına 10.5 yazınca 11’e yuvarlıyor ve stok kolonunda her zaman “Adet” yazıyor. Stok hem integer’a cast ediliyor hem de unit suffix yanlış.

Bu 3 sorunu KÖKTEN çözmek istiyorum. Aşağıdaki maddeleri eksiksiz uygula.

1) GENEL KURAL: Decimal unit’ler için qty ASLA int’e çevrilmeyecek

Unit modeli:

is_decimal_stock = true olan birimlerde:

qty float/decimal(10,2) tutulacak.

Ne PHP’de (int), intval, round, floor, ceil; ne JS’te parseInt, Math.round kullanılmayacak.

is_decimal_stock = false birimlerde:

Mevcut adet mantığı (1,2,3…) devam edebilir.

Kod taraması yap:

Global search:

(int, intval(, round(, ceil(, floor(, number_format($qty, 0

parseInt(, Math.round(

qty, quantity, qtyToAdd

Özellikle şu dosyalara bak:

modules/Cart/Http/Controllers/CartController.php

modules/Cart/CheckItemStock.php

modules/Cart/** (Cart, CartItem, Services)

modules/Order/Entities/OrderProduct.php

Admin inventory / product save: SaveProductRequest, ProductRepository, vs.

JS: CartItem.js, sepet/mini-cart ile ilgili scriptler.

Yeni mantık (psödo):

$rawQty = $request->input('qty', 1);
$unit   = $product->getEffectiveUnit();

if ($unit->isDecimalStock()) {
    $qty = (float) $rawQty;
} else {
    $qty = (int) $rawQty;
}


AddToCart, cart update, order create dahil HER yerde bu kural geçerli olsun.

Model cast’leri:

cart_items.qty, order_products.qty, products.qty, product_variants.qty kolonları:

DB tipi: decimal(10,2) (zaten değilse migration ile değiştir).

Model cast: 'qty' => 'decimal:2' veya en azından 'float', asla 'integer' değil.

2) Min / step validasyonu – 1.2 gibi ara değerler

Unit içinde merkezi helper’lar kullan:

// Modules\Unit\Entities\Unit.php

public function isValidQuantity(float $qty): bool
{
    $min  = (float) $this->min;
    $step = (float) $this->step;

    if ($qty < $min) return false;
    if ($step <= 0)  return true;

    $epsilon = 1e-6;
    $steps   = ($qty - $min) / $step;

    return abs($steps - round($steps)) < $epsilon;
}


Ek olarak (isteğe bağlı ama güzel):

public function normalizeQuantity(float $qty): float
{
    $min  = (float) $this->min;
    $step = (float) $this->step;

    if ($qty < $min) {
        $qty = $min;
    }

    if ($step > 0) {
        $steps = round(($qty - $min) / $step);
        $qty   = $min + $steps * $step;
    }

    return $this->is_decimal_stock
        ? (float) number_format($qty, 2, '.', '')
        : (float) round($qty);
}


KULLANIM (AddToCart & cart update):

$unit = $product->getEffectiveUnit();
$qty  = $unit->isDecimalStock()
    ? (float) $request->input('qty', $unit->min)
    : (int) $request->input('qty', 1);

if (! $unit->isValidQuantity($qty)) {
    throw ValidationException::withMessages([
        'qty' => __('Bu ürün :step adımlarla ve en az :min miktarda satılır. Örnek: :examples', [
            'step'    => $unit->step,
            'min'     => $unit->min,
            'examples'=> '0.5, 1.0, 1.5, 2.0',
        ]),
    ]);
}

// İstersen normalize edip öyle kaydet:
$qty = $unit->normalizeQuantity($qty);


Yani:

Kullanıcı 1.2 girerse → backend bunu geçersiz saymalı ve düzgün mesaj göstermeli; otomatik gizli 1.5’e yuvarlamasın.

Frontend’de de bu mesajı göstermek için validate error’u yakala.

3) ADD TO CART akışında 1.5 → 2’ye yuvarlama hatasını düzelt

Bu hata özellikle ilk eklemede oluyor, sepet içi güncellemede değil. Demek ki:

Cart update kodu parseFloat/decimal çalışıyor,

Ama AddToCart path’inde hâlâ (int) veya parseInt kullanılıyor.

Yapılacak:

AddToCart controller/servis’te yukarıdaki unit tabanlı qty okuma mantığını kullan. Hiçbir yerde:

(int)$request->qty
intval($request->qty)
round($request->qty)


kalmayacak.

JS tarafında “Add to cart” butonunda qty’yi toplayan kodda parseInt yerine parseFloat kullan:

let qty  = parseFloat(this.qty || 1);
let step = parseFloat(this.step || 1);
// + / - butonlarında da:
qty = qty + step;


1.5 gönderip network request payload’ını kontrol et:

Request body gerçekten qty: 1.5 mi?

Response’da ve DB’de 1.5 kalmalı.

4) Admin stok girişinde 10.5 → 11 yuvarlanması

Sorun: admin’de stok field’ı hem frontend hem backend’de integer gibi davranıyor.

Backend

SaveProductRequest veya benzeri request class’ında:

'qty' => 'numeric|min:0',


olmalı; integer veya digits gibi kısıtlar olmasın.

Product model cast: 'qty' => 'decimal:2' veya 'float'.

Frontend (admin inventory form)

Vue component (örn. modules/Product/Resources/assets/admin/js/components/General.vue veya Inventory.vue):

Stok input:

<input
  type="number"
  v-model.number="form.qty"
  :step="unitStep"
  :min="unitMin"
/>


unitStep ve unitMin değerleri seçili sale_unit’e göre gelsin.

JS’te stok değeriyle ilgili parseInt, Math.round vs. kullanılmasın.

5) Admin ürün listesinde stok + birim gösterimi

İstenilen davranış:

Eğer ürüne unit atanmışsa → stok 10.5 m, 8.5 m gibi.

Eğer ürüne unit atanmadıysa → sadece 10 (hiç “Adet” yazmasın).

Product modelinde:

public function saleUnit()
{
    return $this->belongsTo(\Modules\Unit\Entities\Unit::class, 'sale_unit_id');
}

public function getEffectiveUnit(): ?\Modules\Unit\Entities\Unit
{
    if ($this->relationLoaded('saleUnit') ? $this->saleUnit : $this->saleUnit()->first()) {
        return $this->saleUnit;
    }

    // Default unit sadece hesaplama için kullanılabilir ama
    // UI’da suffix yazılmayacak, o yüzden burada null döndermek OK.
    return \Modules\Unit\Entities\Unit::where('is_default', true)->first();
}

public function getFormattedStock(): string
{
    $qty = (float) $this->qty;
    $unit = $this->saleUnit; // UI için sadece ürünün kendi unit’i

    if ($unit && $unit->isDecimalStock()) {
        $value = rtrim(rtrim(number_format($qty, 2, '.', ''), '0'), '.');
        $suffix = trim($unit->getDisplaySuffix());
        return $suffix !== '' ? "{$value} {$suffix}" : $value;
    }

    // unit yoksa sade sayı
    return (string) (int) round($qty);
}


modules/Product/Admin/ProductTable.php:

->editColumn('in_stock', function (Product $product) {
    return e($product->getFormattedStock());
})


Ayrıca Unit::getDisplaySuffix() asla '/' gibi saçma bir fallback üretmesin:

public function getDisplaySuffix(): string
{
    if (! empty($this->short_suffix)) return $this->short_suffix;
    if (! empty($this->label))        return $this->label;
    return '';
}

6) TEST SENARYOLARI (mutlaka çalıştır)

Metre birimi (min=0.5, step=0.5, decimal=true) atanmış bir üründe:

Admin stok alanına 10.5 gir → kaydet → admin listede 10.5 m gör.

Frontend stok gösterimi bozulmasın.

Ürün detayda:

Qty=1.5 → Add to Cart → side cart, normal sepet, checkout özetinde qty=1.5 ve satır fiyatı (1.5 * price) görünsün.

Qty inputunu 1.2 yapıp ekle → hata mesajı: “Bu ürün 0.5 adımlarla satılır…”; 1.2’yi sessizce 1.5’e çevirmesin.

Unit atanMAmış bir adet ürün:

Admin stok: 10 → listede 10 görünsün, “Adet” yazmasın.

Add to cart davranışı eskisi gibi kalsın (1,2,3…).

Yaptığın değişikliklerde; özellikle qty’yi int’e çeviren yerleri, AddToCart akışını, admin stok kaydetmeyi ve ProductTable stok cell’ini tek tek not olarak listele.