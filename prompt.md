Laravel tabanlı FleetCart e-ticaret projem var. Proje kökünde çalışıyorsun. Aşağıdaki adımları, FleetCart’ın mevcut modül mimarisine uygun şekilde eksiksiz uygula. Amaç: GitHub’daki resmi Geliver PHP SDK’sını (https://github.com/GeliverApp/geliver-php) kullanarak “Geliver” adında bir modül yazmak ve:

- Siparişi Geliver’e “shipment” olarak göndermek,
- KESİNLİKLE ve ASLA otomatik etiket / kargo satın alma yapmamak (transactions()->acceptOffer kullanılmayacak),
- Geliver webhook ile kargo durumunu alıp FleetCart sipariş status alanını güncellemek,
- Posta kodu (zip) sipariş adresinde boş olabilir, bu durumda zip alanını API’ye hiç göndermemek.

Adımlar:

1) Composer ile SDK kurulumu
--------------------------------
1. Proje kökünde:
   - `composer require geliver/sdk:^1.0`
2. PHP sürümü uymuyorsa (SDK php >= 8.1 istiyor), bana uyarı ver; ama sen yine de entegrasyon kod yapısını hazırla.

2) Env ve config ayarları
---------------------------
1. `.env` dosyasına aşağıdaki env değerlerini ekle (dummy bırak, ben sonra dolduracağım):

   GELIVER_API_TOKEN=
   GELIVER_SENDER_ADDRESS_ID=            # Geliver’de oluşturulmuş senderAddressID veya sen oluşturup dolduracaksın
   GELIVER_DEFAULT_LENGTH=10.0
   GELIVER_DEFAULT_WIDTH=10.0
   GELIVER_DEFAULT_HEIGHT=10.0
   GELIVER_DEFAULT_WEIGHT=1.0
   GELIVER_TEST_MODE=true                # local/stage’de true, prod’da false olacak
   GELIVER_WEBHOOK_SECRET=               # opsiyonel, basit shared secret

2. `config/services.php` içine `geliver` adında bir dizi ekle:

   'geliver' => [
       'token'            => env('GELIVER_API_TOKEN'),
       'sender_address_id'=> env('GELIVER_SENDER_ADDRESS_ID'),
       'default_length'   => env('GELIVER_DEFAULT_LENGTH', 10.0),
       'default_width'    => env('GELIVER_DEFAULT_WIDTH', 10.0),
       'default_height'   => env('GELIVER_DEFAULT_HEIGHT', 10.0),
       'default_weight'   => env('GELIVER_DEFAULT_WEIGHT', 1.0),
       'test_mode'        => env('GELIVER_TEST_MODE', true),
       'webhook_secret'   => env('GELIVER_WEBHOOK_SECRET'),
   ],

3) Geliver modül yapısı
-------------------------
FleetCart’ın modül yapısına uygun şekilde `modules/Geliver` adında bir modül oluştur:

- module.json (standart FleetCart modül tanımı)
- Providers:
  - `Modules\Geliver\Providers\GeliverServiceProvider` (config, routes, views, translations register)
- Config:
  - `modules/Geliver/Config/geliver.php` (status mapping vs.)
- Http:
  - `modules/Geliver/Http/routes.php`
  - `modules/Geliver/Http/Controllers/OrderGeliverController.php`
  - `modules/Geliver/Http/Controllers/WebhookController.php`
- Services:
  - `modules/Geliver/Services/GeliverService.php`
- Resources:
  - `modules/Geliver/Resources/views/admin/settings.blade.php` (admin ayar sayfası)
  - Sipariş detayına ekleyeceğin buton için ek view parçası gerekirse

ServiceProvider’da:
- config/geliver.php’yi publish/merge et,
- routes dosyalarını yükle (admin + public),
- view dizinini register et.

4) DB migration’ları
----------------------
FleetCart’ın migration stiline uygun şekilde aşağıdaki alanları ekleyen migration yaz:

- `orders` tablosuna:
  - `geliver_shipment_id` (nullable string)
  - `geliver_shipment_payload` (nullable json/text; DB ne destekliyorsa)
  - `geliver_last_status` (nullable string)
  - `geliver_last_status_at` (nullable datetime)

Migration’ı oluştur, register et ve çalıştırmak için gerekli artisan komutunu belirt.

5) Config: status map
-----------------------
`modules/Geliver/Config/geliver.php` içinde:

- `status_map` dizisi tanımla. Örnek:

  return [
      'status_map' => [
          'New'              => 'processing',
          'ReadyToShip'      => 'processing',
          'PickedUp'         => 'processing',
          'InTransit'        => 'processing',
          'OutForDelivery'   => 'processing',
          'Delivered'        => 'completed',
          'Exception'        => 'on_hold',
          'Canceled'         => 'canceled',
          'CanceledByCarrier'=> 'canceled',
      ],
      'final_statuses' => ['completed', 'canceled', 'refunded'],
  ];

- Bu mapping’i Webhook tarafında kullanacağız.
- final_statuses: Bu statülere gelmiş siparişi geriye almamaya dikkat et (ör. completed → tekrar processing yapma).

6) GeliverService yazımı
--------------------------
`modules/Geliver/Services/GeliverService.php` sınıfını oluştur:

- Namespace: `Modules\Geliver\Services;`
- Kullan:

  use Geliver\Client;
  use Modules\Order\Entities\Order;

- Yapıcı:

  - `new Client(config('services.geliver.token'))` ile client oluştur.
  - Token boşsa anlamlı bir Exception fırlat (örn. RuntimeException).

- `public function sendOrderToGeliver(Order $order): array` metodu:

  Gereksinimler:

  1. Eğer `$order->geliver_shipment_id` doluysa: sipariş zaten Geliver’e gönderilmiş demektir, Exception veya anlamlı hata dön.
  2. `senderAddressID`:

     - `config('services.geliver.sender_address_id')` değerini kullan.
     - Bu değer boşsa ya hata ver, ya da (opsiyonel) mağaza adresinden otomatik sender adresi oluşturmak için TODO notu bırak. Şimdilik senderAddressID’nin dolu olduğunu varsay.

  3. Shipping adresi:

     - FleetCart içindeki shipping address modelini kullan (`$order->shippingAddress` veya gerçek ismi neyse onu kullan).
     - Aşağıdaki alanları doldur:

       - name: `first_name + last_name`
       - email: önce `$order->customer_email`, yoksa shippingAddress email
       - phone: shippingAddress phone veya `$order->customer_phone`
       - address1: shippingAddress address_1 (gerekirse address_2 de ekle)
       - countryCode: sabit 'TR'
       - cityName: shippingAddress city
       - cityCode: varsa shippingAddress city_code, yoksa boş string
       - districtName: varsa shippingAddress district
       - zip: SADECE `$shippingAddress->postcode` boş DEĞİLSE ekle.
         - Yani: postcode boşsa `zip` key’ini payload’a hiç koyma. Boş string veya null gönderme.

  4. Ölçü ve ağırlıklar:

     - `length`, `width`, `height`, `weight` alanlarını config/services.geliver içinden çek:
       - `config('services.geliver.default_length')` vs.
     - Tüm bu numeric alanları string’e çevir (Geliver README’de “İstek alanları string olmalıdır” diyor).
     - `distanceUnit` → 'cm', `massUnit` → 'kg'.

  5. Order bilgisi:

     - orderNumber: `$order->id` veya sipariş numarası field’ı her ne ise o.
     - sourceIdentifier: `config('app.url')` (tam domain; README’de olduğu gibi).
     - totalAmount: siparişin toplam tutarı (örneğin `$order->total`), string olarak.
     - totalAmountCurrency: `$order->currency` veya varsayılan 'TRY'.

  6. Test/Prod seçimi:

     - Eğer `config('services.geliver.test_mode')` true ise:
       - `$client->shipments()->createTest($payload);`
     - Değilse:
       - `$client->shipments()->create($payload);`

  7. DÖNÜŞ ve KAYIT:

     - Gelen `$shipment` array’i içinde en az `id` olmalı.
     - `$order->geliver_shipment_id = $shipment['id'] ?? null;`
     - `$order->geliver_shipment_payload = $shipment;`
     - `$order->geliver_last_status = $shipment['status'] ?? null;` (varsa)
     - `$order->geliver_last_status_at = now();`
     - `$order->save();`
     - Metot geriye `$shipment` array’ini döndürsün.

  8. KRİTİK KURAL:
     - Bu serviste VEYA projede hiçbir yerde:
       - `$client->transactions()->acceptOffer(...)` ÇAĞRILMAYACAK.
     - Yani otomatik kargo teklifi kabulü / etiket satın alma yok. Sadece shipment oluşturuyoruz; etiketleri ben panelden manuel alacağım.

7) Admin ayar ekranı
----------------------
FleetCart admin panelinde, tercihen “Settings → Shipping” altında veya ayrı bir menüde “Geliver Entegrasyonu” başlıklı bir ayar sayfası oluştur:

- Alanlar:
  - Checkbox: “Geliver entegrasyonu aktif mi?” → Boolean setting (ör. `geliver_enabled`)
  - Text: “API Token” → `GELIVER_API_TOKEN`
  - Text: “Sender Address ID (Geliver)” → `GELIVER_SENDER_ADDRESS_ID`
  - Number/Text: “Varsayılan Uzunluk (cm)”, “Genişlik (cm)”, “Yükseklik (cm)”, “Ağırlık (kg)”
  - Checkbox: “Test Modu (createTest kullan)” → `GELIVER_TEST_MODE`
  - Text: “Webhook Secret (opsiyonel)” → `GELIVER_WEBHOOK_SECRET`

- Bu ayarları framework’ün mevcut settings sistemi üzerinden okuyan helper’lar ile `config()` veya doğrudan `setting()` fonksiyonuna bağla.
- Ayar sayfasında validasyon yap (token boş olamaz vs.) ama development için çok katı olma.

8) Admin: Sipariş detayında “Geliver’e Gönder” butonu
------------------------------------------------------
Admin order detay sayfasına (FleetCart’ta orders show view neresiyse) aşağıdaki mantıkta buton ekle:

- Eğer `geliver_enabled` FALSE ise butonu gösterme.
- Eğer `$order->geliver_shipment_id` doluysa:
  - “Bu sipariş Geliver’e gönderildi. Shipment ID: ...” şeklinde bir bilgi alanı göster.
- Eğer boşsa:
  - “Bu siparişi Geliver’e gönder” şeklinde bir buton göster.

Arka plan:

- Admin route:

  - `POST admin/geliver/orders/{order}/send`
  - Controller: `OrderGeliverController@send`

- `OrderGeliverController@send` içinde:

  1. Order’ı route-model binding ile al.
  2. `GeliverService::sendOrderToGeliver($order)` çağır.
  3. Başarılıysa:
     - Flash success mesajı: “Sipariş Geliver’e aktarıldı. Shipment ID: …”
  4. Hata durumunda:
     - Hata mesajını kullanıcıya göster (örneğin token yok, senderAddressID yok vb.)

9) Webhook endpoint (kargo durumu → sipariş status)
-----------------------------------------------------
Amaç: Geliver shipment status değiştiğinde gönderdiği webhook ile FleetCart order.status alanını güncellemek.

1. Public route:

   - `POST /webhook/geliver/shipment-status`
   - Route dosyası: `modules/Geliver/Http/routes.php`
   - Controller: `WebhookController@shipmentStatus`

2. CSRF muafiyeti:

   - `app/Http/Middleware/VerifyCsrfToken.php` içindeki `$except` listesine:
     - `'webhook/geliver/shipment-status'` yolunu ekle.

3. WebhookController@shipmentStatus davranışı:

   - Request JSON body’sini al.
   - Opsiyonel güvenlik:
     - Eğer `config('services.geliver.webhook_secret')` doluysa, şu mantığı uygula:
       - Header’da veya query’de `X-Geliver-Secret` gibi bir değer bekle (sen uygun bir isim seç, kodu ona göre yaz).
       - Gelen değer `config('services.geliver.webhook_secret')` ile eşleşmiyorsa 401 dön.
   - Payload’tan şu alanları çek:
     - Shipment ID: `id`
     - Status: `status`
     - (Varsa) order bilgisi: `order.orderNumber` veya `order.sourceIdentifier`
   - `id` veya `status` yoksa 400 dön.
   - Order bulma:
     1. Önce `Order::where('geliver_shipment_id', $shipmentId)->first()`
     2. Bulunamazsa ve payload’ta `order.orderNumber` varsa:
        - `Order::where('id', $orderNumber)->first()` veya senin sisteminde sipariş numarası hangi alandaysa ona göre.
   - Order bulunamadıysa:
     - Log kaydı yaz ve 404 yerine 200/202 dönebilirsin ama response içeriğine “order not found” yaz.

   - Status mapping:
     - `config('geliver.status_map')` içinden `$newStatus = $statusMap[$payloadStatus] ?? null;`
     - `config('geliver.final_statuses')` içinden `$finals = [...]` al.
     - Eğer `$newStatus` null ise:
       - Bu status için hiçbir işlem yapma, “Status not mapped, ignored” şeklinde 200 dön.
     - Eğer `$order->status` zaten final_statuses içindeyse ve `$newStatus` bu final’lerden GERİDE ise:
       - Sipariş durumunu geriye çekme, sadece `geliver_last_status` alanını güncelle ve 200 dön.
   - Aksi halde:
     - `$order->status = $newStatus;`
     - `$order->geliver_last_status = $payloadStatus;`
     - `$order->geliver_last_status_at = now();`
     - `$order->save();`

   - Son olarak 200 JSON döndür (örn. `['message' => 'ok']`).

4. Loglama:
   - Hem başarı hem hata durumlarında anlamlı log’lar yaz (channel: daily logs vs.).
   - Özellikle: unknown shipment ID, unknown status vs. durumlarını logla.

10) Test senaryosu
--------------------
Benim lokal ortamımda test edebilmem için şu adımları hazırla ve bana net şekilde yaz:

1. `.env` içine test token’ımı yazmam için alan bırak (GELIVER_API_TOKEN).
2. Gönderici adresi:

   - İstersen küçük bir artisan komutu veya adminden buton yap:
     - Mağaza adres bilgilerimi config’den kullanarak `addresses()->createSender([...])` çağır ve gelen `id`’yi `GELIVER_SENDER_ADDRESS_ID`’ye kaydet.
   - Bu adımı yapmayacaksan, manuel olarak panelden aldığım senderAddressID’yi `.env`’ye yazacağım, sen buna göre çalış.

3. Test shipment oluşturma:

   - Admin order detayında “Geliver’e Gönder” butonuna bastığımda:
     - Test modunda `createTest` fonksiyonunu çağır.
     - Gelen response’u DB’ye kaydet.

4. Webhook testi:

   - Şimdilik gerçek Geliver webhook yerine:
     - `POST /webhook/geliver/shipment-status` endpoint’ine örnek bir JSON payload ile (shipment id ve status içeren) test yapabileceğim bir örnek payload ve curl komutu yaz ki, postman/curl ile deneyebileyim.

11) GENEL KURALLAR
--------------------
- Projede HİÇBİR yerde `transactions()->acceptOffer(...)` çağırma. Sadece shipment oluşturulacak, etiket/teklif alma kısmı tamamen manuel, ben Geliver panelinden yapacağım.
- Numeric alanlar (length, width, height, weight, totalAmount vs.) Geliver’e GÖNDERİLİRKEN string olmalı.
- Posta kodu (zip) boş olursa, zip alanını payload’ta hiç gönderme. Boş string veya null gönderme.
- Tüm kodları FleetCart’ın mevcut namespace, klasör yapısı ve kod stiline uygun yaz.
- Bütün oluşturduğun dosya yollarını ve önemli method/route isimlerini en sonda özetlemeyi unutma; ben projede nereye ne eklendiğini toplu görmek istiyorum.

Tüm bu adımları eksiksiz uygula, sonra bana:
- Eklediğin/oluşturduğun dosya yollarının listesi
- Eklediğin route’lar ve HTTP metodları
- Test etmek için hangi URL’leri çağıracağımı
- Örnek webhook JSON payload’ını
yazılı olarak özetle.
