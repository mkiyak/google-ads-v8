
### **Açıklama**
Repo içerisinde google ads apisi için kendime özel fonksiyonlar ürettim.


### **Google ADS PHP API Composer**
```
composer require googleads/google-ads-php
```

### **Fonksiyon Açıkmaları**

#### **- showReportAction()**
```
showReportAction fonksiyonu google apiye istek yaptığımız fonksiyon olup yapılan isteğe göre gelen değerleri döndürmektedir.
```

#### **- costMicros()**
Hesaba ait toplam harcanan maaliyeti hesaplamaktadır.

Kullanımı:
```
GoogleAdsAPI::costMicros($googleAdsClient, $number)
```

#### **- customer() / Müşteri (Hesap)**
Hesaba ait bugün, dün, bu hafta, geçen hafta, vs. kriterlere göre tıklanma, gösterim, dönüşüm gibi değerleri çekmektedir.

Kullanımı:
```
GoogleAdsAPI::customer($googleAdsClient, $number, "", $options, "segments.date DESC", 1000)
```

#### **- AdGroups() / Reklam Grupları**
Hesaba ait bugün, dün, bu hafta, geçen hafta, vs. kriterlere göre reklam grubuna ait belirtilen değerleri döndürmektedir.

Kullanımı:
```
GoogleAdsAPI::AdGroups($googleAdsClient, $number, $during)
```

#### **- AdGroupAd() / Reklamlar**
Hesaba ait bugün, dün, bu hafta, geçen hafta, vs. kriterlere göre reklamlara ait belirtilen değerleri döndürmektedir.

Kullanımı:
```
GoogleAdsAPI::AdGroupAd($googleAdsClient, $number, $during)
```

#### **- Campaigns() / Kampanyalar**
Hesaba ait bugün, dün, bu hafta, geçen hafta, vs. kriterlere göre kampanyalara ait belirtilen değerleri döndürmektedir.

Kullanımı:
```
GoogleAdsAPI::Campaigns($googleAdsClient, $number, $during)
```

#### **- Keywords() / Anahtar Kelimeler**
Hesaba ait bugün, dün, bu hafta, geçen hafta, vs. kriterlere göre anahtar kelimelere ve negatif kelimelere ait belirtilen değerleri döndürmektedir.

Kullanımı:
```
Pozitif Anahtar Kelimeler
GoogleAdsAPI::Keywords($googleAdsClient, $number, "", $options, "FALSE", 10000, "segments.date DESC")

Negatif Anahtar Kelimeler
GoogleAdsAPI::Keywords($googleAdsClient, $number, "", $options, "TRUE", 10000, "segments.date DESC")
```

#### **- SearchTerms() / Arama Terimleri**
Hesaba ait bugün, dün, bu hafta, geçen hafta, vs. kriterlere göre arama terimlerine ait belirtilen değerleri döndürmektedir.

Kullanımı:
```
GoogleAdsAPI::SearchTerms($googleAdsClient, $number, "", $options, 10000, "segments.date DESC")
```

## **Raporlar**

#### **- ReportGeoPerformance() / Coğrafi Raporlar**
Hesaba ait belirtilen tarih aralığında coğrafi performans kriterlerini döndürmektedir.

Kullanımı:
```
$segmentDate = "segments.date >= '2005-01-01' AND segments.date < '".date('Y-m-d')."'";
$options[] = $segmentDate;

GoogleAdsAPI::ReportGeoPerformance($googleAdsClient, $number, "", $options, 10000)
```

#### **- ReportKeywordsPerformance() / Anahtar Kelime Performans Raporları**
Hesaba ait belirtilen tarih aralığında anahtar kelimelere ait performans kriterlerini döndürmektedir.

Kullanımı:
```
$segmentDate = "segments.date >= '2005-01-01' AND segments.date < '".date('Y-m-d')."'";
$options[] = $segmentDate;

GoogleAdsAPI::ReportKeywordsPerformance($googleAdsClient, $number, $during, $options, 10000, 'metrics.impressions DESC')
```

#### **- customerAndDevicePerformans() / Müşteri ve Cihaz Performans Raporları**
Hesaba ait belirtilen tarih aralığında cihazlara ait performans kriterlerini döndürmektedir.

Kullanımı:
```
$segmentDate = "segments.date >= '2005-01-01' AND segments.date < '".date('Y-m-d')."'";
$options[] = $segmentDate;

GoogleAdsAPI::customerAndDevicePerformans($googleAdsClient, $number, "", $options, "segments.date DESC", 10000)
```

#### **select() / SQL Oluşturma**
Yukarıda ki fonksiyonlar içerisindeki kriterleri sql select komutunda düzenleyerek döndürmektedir.
