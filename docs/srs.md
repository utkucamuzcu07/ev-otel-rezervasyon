# OTEL / KİRALIK EV REZERVASYON SİSTEMİ

> **Not:** Bu dosya, ekibin hazırladığı SRS v2.3 belgesinin (`docs/OTEL_KIRALIK_EV_REZERVASYON_SISTEMI_SRS_v2_3.docx`) Markdown sürümüdür. İçerik değiştirilmemiş, yalnızca biçim dönüştürülmüştür. Volere kartları iki sütunlu (Alan / Değer) tablolar olarak yazılmıştır.

Yazılım Gereksinimleri Belirtim Dokümanı (SRS)

#### Kurum, Ders ve Proje Bilgileri

| Alan | Değer |
| --- | --- |
| **Kurum / Bölüm** | Gümüşhane Üniversitesi Mühendislik ve Doğa Bilimleri Fakültesi Yazılım Mühendisliği |
| **Ders Adı** | PHP Programlama |
| **Ders Yürütücüsü** | Mustafa Nuri Ural |
| **Proje Deposu** | ev-otel-rezervasyon (otel-rezervasyon) |
| **Proje Ekibi (Roller)** | **Proje Yöneticisi:** Kaan Şahin 2407231037<br>**Mimar:** Utku Camuzcu 2407231044<br>**Veritabanı ve Test Sorumlusu:** İrem Ablak 2407231080<br>**Arayüz ve Dokümantasyon Sorumlusu:** Onur Akbalık 2407231014 |

#### Doküman Bilgisi ve Kontrolü

| Alan | Değer |
| --- | --- |
| **Doküman Sürümü** | 2.3 |
| **Durum** | Revize edildi – ekip gözden geçirmesine hazır |
| **Doküman Dili** | Türkçe |
| **Ürün Türü** | Web tabanlı rezervasyon uygulaması |
| **Hazırlanma Amacı** | Yazılım kapsamını, gereksinimlerini ve kabul koşullarını tanımlamak; çekirdek problemlerin (tarih çakışması, dinamik fiyat, iptal/iade, çift rezervasyon önleme) tasarım ve test için yeterince kesin tanımlanmasını sağlamak |

| Sürüm | Tarih | Açıklama |
| --- | --- | --- |
| 2.0 | 11.10.2026 | Sistem gereksinimleri, mimari ve kılavuz detayları güncellendi. (Önceki belgede bu satır "1.0" olarak yazılmıştı; sürüm numarası düzeltildi.) |
| 2.1 | 14.10.2026 | Çekirdek problem ayrı bir bölüm olarak (Bölüm 4) ayrıntılandırıldı; eksik fonksiyonel, fonksiyonel olmayan, iş kuralı ve kabul gereksinimleri ile veri varlıkları tamamlandı; izlenebilirlik matrisi güncellendi. |
| 2.2 | 14.10.2026 | Ders proje tanımındaki dört çekirdek problemle (tarih aralığı çakışması, sezonluk dinamik fiyat, iptal politikası ve iade hesabı, çift rezervasyon önleme) hizalandı: dinamik fiyatlandırma ve iade hesabı (Bölüm 4.10, 4.11), oda tipi, aylık takvim, doluluk/gelir raporu ve resim yükleme eklendi. |
| 2.3 | 25.10.2026 | Bölüm 3 ve 6'daki 46 fonksiyonel ve 19 fonksiyonel olmayan gereksinim Volere gereksinim kartı biçimine dönüştürüldü (gerekçe, kaynak, uygunluk ölçütü, öncelik, memnuniyet/memnuniyetsizlik, çakışma, destekleyici materyal, geçmiş); Bölüm 11.5 eklendi. Gereksinim kimlikleri korundu. |

#### v2.0 → v2.1 Değişiklik Özeti

| Bölüm | Tespit edilen eksik | Yapılan düzeltme |
| --- | --- | --- |
| 1.3 | Çekirdek problem tek cümleydi; "aynı kapasite için çift rezervasyon" ifadesi hangi kaynağın ve hangi tarih mantığının kastedildiğini belirtmiyordu. | Ölçülebilir problem tanımı ve başarı ölçütü yazıldı (1.3 ve 4.1). |
| 4 (yeni) | Çakışmanın tanımı, sınır günü (çıkış = giriş), hangi durumların tarihi engellediği, süre aşımı ve eşzamanlılık hiçbir yerde tanımlı değildi. | Yarı açık aralık kuralı, durum makinesi, karar kuralı, eşzamanlılık güvenceleri, 12 kritik senaryo, test gereksinimleri ve çekirdek riskler eklendi. |
| 3 | FR-11, FR-12, FR-13, FR-18 belirsizdi; Reddedildi/Süresi Doldu durumları, süre aşımı, simüle ödeme, tarih kapatma çakışması, ilan kaldırma etkisi, denetim kaydı ve oda/birim yönetimi için FR yoktu. | Mevcut FR'ler netleştirildi, ÇEKİRDEK etiketi eklendi; FR-33 – FR-42 eklendi. |
| 6 | NFR-01, 05, 07, 09, 12 ölçülemez ifadeler içeriyordu; CSRF/XSS/SQL enjeksiyonu, oturum güvenliği, zaman tutarlılığı ve test edilebilirlik yoktu. | Sayısal hedefler eklendi; NFR-13 – NFR-18 eklendi. |
| 7 | "İlan/oda ID" belirsizdi; Oda/Birim varlığı yoktu; fiyat kalemleri, vergi kuralları, denetim kaydı ve parola jetonu tablosu eksikti; doluluk iki yerde (takvim ve rezervasyon) tutulabiliyordu. | BookableUnit, ReservationNight, ReservationCharge, PricingRule, AuditLog, PasswordResetToken eklendi; tek doğruluk kaynağı ilkesi getirildi. |
| 8 | BR-05 kesinleşme koşulunu "tanımlanmalıdır" diyerek erteliyordu; tarih sınırları, bekletme süreleri ve iptal politikası sayısal değildi. | BR-05 kesinleştirildi; BR-11 – BR-22 eklendi. |
| 9 | Yalnızca 3 kullanım senaryosu vardı; iptal ve ev sahibi onay akışı yoktu; UC-02 çakışma ve süre aşımını kapsamıyordu. | UC-02 yeniden yazıldı; UC-04 (iptal) ve UC-05 (talep onayı) eklendi. |
| 10 | AC-05 tek satırdı; sınır günü, süre dolumu, çift gönderim, atomiklik ve kapatma çakışması için kabul koşulu yoktu. | AC-11 – AC-22 eklendi. |
| v2.2 | Proje tanımındaki çekirdek problemlerden sezonluk dinamik fiyat ve iptal/iade hesabı SRS'te yoktu (dinamik fiyatlandırma kapsam dışı yazılmıştı); oda tipi, aylık takvim, doluluk ve gelir raporu, resim yükleme eksikti. | Bölüm 4.10 ve 4.11 eklendi; FR-43 – FR-46, BR-23 – BR-30, AC-23 – AC-32, T-12 – T-15 eklendi; FR-07, 10, 14, 17, 30, 42 güncellendi; kapsam dışı listesi düzeltildi. |
| Ek B | NFR-08 – 12 dışındaki yeni kalemler ve kullanıcı hikâyeleri izlenemiyordu. | Matris yeni kimliklerle ve US → FR eşlemesiyle güncellendi. |
| 11.4 (yeni) | Sayısal kararlar (süreler, limitler, iade oranları) kaynaksız ve onaysızdı. | Ekibin doğrulaması gereken varsayımlar tablosu eklendi. |
| **3, 6** | Gereksinimler tek satırlık tablo satırlarıydı; gerekçe, kaynak, öncelik, ölçülebilir uygunluk ölçütü, memnuniyet/memnuniyetsizlik, çakışma ve geçmiş bilgisi yoktu; bazı fonksiyonel olmayan gereksinimlerin ölçütü sınanabilir değildi. | v2.3: 46 FR ve 19 NFR Volere gereksinim kartına dönüştürüldü; konulara/Volere türlerine göre gruplandı; uygunluk ölçütleri AC/T/CS bağlantılarıyla yazıldı (†: öneri değerler). |
| **1.4, 1.5** | Volere terimleri sözlükte yoktu. | v2.3: Volere, gereksinim kartı ve uygunluk ölçütü terimleri eklendi; doküman yapısı güncellendi. |
| **11.5 (yeni)** | Öncelik, kaynak ve memnuniyet değerlerinin ve yeni ölçütlerin dayanağı belgelenmemişti. | v2.3: Varsayımlar ve iki açık karar (FR-08 uygunluk sıralaması, FR-31 profil alanları) tablo olarak eklendi. |

## İçindekiler

- 1\. Giriş
- 2\. Genel Açıklama
- 3\. Sistem Özellikleri ve Fonksiyonel Gereksinimler
- 4\. Çekirdek Problemler
- 5\. Harici Arayüz Gereksinimleri
- 6\. Fonksiyonel Olmayan Gereksinimler
- 7\. Veri Gereksinimleri
- 8\. İş Kuralları
- 9\. Kullanım Senaryoları
- 10\. Kabul Kriterleri
- 11\. Varsayımlar, Kısıtlar ve Kapsam Dışı Konular
- Ek A. Önerilen Asgari Uygulama Kapsamı
- Ek B. Gereksinim İzlenebilirliği Matrisi

## 1. Giriş
### 1.1 Amaç
Bu dokümanın amacı, Otel / Kiralık Ev Rezervasyon Sistemi'nin işlevlerini, kullanıcı rollerini, teknik ve kalite gereksinimlerini tanımlamaktır. Doküman; geliştirme, test ve değerlendirme süreçlerinde ortak referans olarak kullanılacaktır.

### 1.2 Ürün Kapsamı
Sistem, kullanıcıların şehir veya konum, tarih, kişi sayısı ve konaklama türüne göre otel, otel odası veya kiralık ev aramasına olanak tanıyan web tabanlı bir uygulamadır. Kullanıcılar ilan detaylarını inceleyebilir, müsaitlik kontrolü yapabilir ve rezervasyon oluşturabilir. Ev sahipleri/işletmeler ilanlarını, takvimlerini ve rezervasyonlarını yönetebilir. Yöneticiler kullanıcıları, ilanları ve şikâyetleri denetleyebilir.

### 1.3 Hedefler ve Çekirdek Problem
- Konaklama seçeneklerini tek bir platformda listelemek.
- Kullanıcıların tarih ve kişi sayısına uygun seçenekleri kolayca bulmasını sağlamak.
- Rezervasyon durumunu ve fiyat bilgisini kullanıcıya açık biçimde göstermek.
- İlan sahipleri ile müşterilerin işlemlerini yönetilebilir hâle getirmek.

Ders proje tanımına göre çekirdek problem dört parçadan oluşur: **(1) tarih aralığı çakışması, (2) sezonluk dinamik fiyat, (3) iptal politikası ve iade hesabı, (4) çift rezervasyonun önlenmesi.** Aşağıdaki not 1. ve 4. parçayı özetler; 2. ve 3. parça Bölüm 4.10 ve 4.11'de tanımlıdır.

**Çekirdek Problem (tarih çakışması ve çift rezervasyon / double booking):** Aynı kiralanabilir birim (otel odası veya kiralık ev) için konaklama geceleri kesişen birden fazla rezervasyonun aynı anda geçerli (Beklemede veya Onaylandı) durumda bulunması — eşzamanlı isteklerde de dahil — hiçbir koşulda mümkün olmamalıdır. Başarı ölçütü: aynı birim ve aynı gece için en fazla bir engelleyen rezervasyon; tolerans sıfırdır. Ayrıntılar Bölüm 4'tedir.

Çekirdek problemler, projenin geri kalan özelliklerinden önce tasarlanır, geliştirilir ve test edilir; backlog'ta "çekirdek" etiketiyle öncelikli tutulur.

### 1.4 Terimler ve Kısaltmalar

| Terim | Açıklama |
| --- | --- |
| **SRS** | Software Requirements Specification; Yazılım Gereksinimleri Belirtim Dokümanı. |
| **FR / NFR / BR / AC** | Fonksiyonel gereksinim / fonksiyonel olmayan gereksinim / iş kuralı / kabul kriteri. |
| **Kullanıcı (müşteri)** | Konaklama arayan ve rezervasyon yapan kişi. |
| **İlan sahibi** | Otel, oda veya kiralık ev ilanını yöneten yetkili kişi/işletme. |
| **Yönetici** | Platformu ve içerikleri yöneten yetkili rol. |
| **İlan (Property)** | Kullanıcıya gösterilen konaklama kaydı: bir otel veya bir kiralık ev. |
| **Birim (BookableUnit)** | Rezervasyonun yapıldığı tekil kaynak. Otel ilanında her oda ayrı bir birimdir; kiralık ev ilanında ilanın tek birimi vardır. Çakışma yalnızca aynı birim içinde aranır. |
| **Oda tipi** | Birimin sınıfı (ör. tek kişilik, çift kişilik, aile odası, süit, müstakil ev). Arama ve filtrelemede kullanılır; çakışma yine tekil birim bazındadır. |
| **Fiyat kuralı** | İlan sahibinin tanımladığı hafta sonu farkı, sezon farkı veya uzun konaklama indirimi (Bölüm 4.10). |
| **Gece** | Bir takvim gününün akşamından ertesi sabaha kadar olan konaklama. Rezervasyon, giriş gününden çıkış gününden bir gün öncesine kadar her gece için bir birim tutar. |
| **Konaklama aralığı** | Yarı açık aralık [giriş, çıkış): giriş günü dahil, çıkış günü hariç. |
| **Rezervasyon** | Bir birimin belirli bir konaklama aralığı için müşteriye ayrılmasını temsil eden kayıt. |
| **Engelleyen durum** | Birimin gecelerini başkalarına kapatan rezervasyon durumu: Beklemede (süresi dolmamış) ve Onaylandı. |
| **Çakışma** | Aynı birimde, her ikisi de engelleyen durumda olan iki rezervasyonun en az bir gece paylaşması (Bölüm 4.2). |
| **Bekletme (hold)** | Beklemede durumundaki rezervasyonun, süre dolana kadar gecelerini geçici olarak tutması. |
| **Atomik işlem** | Ya tüm adımları başarıyla tamamlanan ya da hiçbir iz bırakmadan geri alınan işlem (veritabanı transaction). |
| **Idempotency anahtarı** | Aynı formun iki kez gönderilmesi hâlinde ikinci gönderimin yeni kayıt üretmesini engelleyen tek kullanımlık anahtar. |
| **Volere** | Gereksinimleri tanımlamak için kullanılan Volere gereksinim şablonu; her gereksinim bir gereksinim kartıyla (requirement shell) belirtilir. |
| **Gereksinim kartı** | Bir gereksinimin kimlik, tür, açıklama, gerekçe, kaynak, uygunluk ölçütü, öncelik, memnuniyet, çakışma, destekleyici materyal ve geçmiş alanlarıyla yazıldığı kart (Bölüm 3.1). |
| **Uygunluk ölçütü** | Gereksinimin karşılandığının sınanabilir biçimde gösterildiği ölçüt (Fit Criterion). |

### 1.5 Dokümanın Yapısı
Doküman; genel ürün açıklamasını (2), sistem özelliklerini (3), çekirdek problemlerin ayrıntılı tanımını (4), arayüzleri (5), kalite gereksinimlerini (6), veri yapısını (7), iş kurallarını (8), kullanım senaryolarını (9) ve kabul koşullarını (10) içerir. Ek B, gereksinimlerin tasarım bileşenlerine ve testlere izlenebilirliğini sağlar. Bölüm 3 ve 6'daki gereksinimler Volere gereksinim kartı biçiminde yazılmıştır (Bölüm 3.1).

## 2. Genel Açıklama
### 2.1 Ürün Perspektifi
Ürün, tarayıcı üzerinden erişilen bir web uygulamasıdır. Ön yüz kullanıcı arayüzünü, arka uç iş kurallarını ve veri erişimini, veritabanı ise kullanıcı, ilan ve rezervasyon kayıtlarını yönetir. Gerçek ödeme sağlayıcısı ve harita servisi gibi harici servisler ileriki sürümlerde entegre edilebilir. Uygulama katmanlı mimari kurallarına göre public/index.php → Router → Controller → Service → Repository sırasıyla çalışır; her katman yalnızca altındaki katmanı çağırır.

Bu akışın dışında, bekletme süresi dolan rezervasyonları ve çıkış tarihi geçen rezervasyonları işleyen bir **zamanlanmış görev** (CLI betiği) bulunur; görev de aynı Service katmanını kullanır.

### 2.2 Kullanıcı Sınıfları ve Kullanıcı Hikâyeleri

| Rol | Yetkiler |
| --- | --- |
| **Ziyaretçi** | İlanları arama, filtreleme ve ilan detaylarını görüntüleme. |
| **Kayıtlı müşteri** | Ziyaretçi işlemlerine ek olarak rezervasyon oluşturma, kendi rezervasyonlarını görüntüleme/iptal etme, favori kaydetme ve uygun rezervasyonlara yorum yapma. |
| **İlan sahibi / işletme** | Kendi ilanlarını ve birimlerini (odalarını) oluşturma ve düzenleme, fiyat ve müsaitlik yönetimi, kendisine ait rezervasyonları görüntüleme ve durumlarını yönetme. |
| **Yönetici** | Kullanıcıları ve ilanları yönetme, uygunsuz ilanları kaldırma, şikâyetleri inceleme ve sistem raporlarını görüntüleme. |
| **Sistem (zamanlanmış görev)** | Bekletme süresi dolan rezervasyonları Süresi Doldu, çıkış tarihi geçen Onaylandı rezervasyonları Tamamlandı durumuna almak. |

#### En Önemli 3 Kullanıcı Hikâyesi (Sunum 1 Kapsamı)
**US-01 (Konaklama Arama):** Bir ziyaretçi veya kayıtlı müşteri olarak, şehir/konum, giriş-çıkış tarihi, kişi sayısı ve konaklama türüne göre arama yapmak istiyorum; böylece kriterlerime uygun ve müsait olan konaklama seçeneklerini bulabileyim.

**US-02 (Rezervasyon Oluşturma ve Çakışma Önleme) — ÇEKİRDEK:** Bir kayıtlı müşteri olarak, seçtiğim konaklama için müsaitlik ve kapasite doğrulamasından geçerek gecelik dökümü yapılmış toplam ücreti görüp rezervasyon oluşturmak istiyorum; böylece başka biriyle tarih çakışması yaşamadan, aynı anda yapılan isteklerde bile konaklamamı kesinleştirebileyim.

**US-03 (İlan, Fiyat ve Takvim Yönetimi):** Bir ilan sahibi olarak, kendi konaklama ilanlarımı ve odalarımı oluşturup fiyat kurallarını (hafta sonu, sezon, uzun konaklama) ve tarih aralığına göre müsaitlik takvimini yönetmek istiyorum; böylece ilanlarımı ve gelen rezervasyonları kontrol altında tutabileyim.

#### Ek Kullanıcı Hikâyesi
**US-04 (İptal, İade ve Tarihlerin Serbest Kalması) — ÇEKİRDEK:** Bir kayıtlı müşteri olarak, iptal politikasına göre ne kadar iade alacağımı görüp rezervasyonumu iptal edebilmek ve iptalin ardından tarihlerin hemen başkalarına açılmasını istiyorum; böylece ev sahibi de boş kalan geceleri yeniden satabilsin.

### 2.3 Çalışma Ortamı
- Güncel masaüstü ve mobil web tarayıcıları desteklenmelidir.
- Uygulama istemci-sunucu mimarisiyle (PHP >= 8.2, Composer PSR-4 otomatik yükleme yapısıyla) çalışmalıdır.
- Veriler merkezi bir veritabanında (ve Aşama 2'de aynı tablo yapısını izleyen JSON dosyalarında) tutulmalıdır.
- Sunucuda dakikada bir çalıştırılabilen bir CLI betiği (zamanlanmış görev) çalışabilmelidir.

### 2.4 Tasarım ve Uygulama Kısıtları
- Yetki kontrolü yalnızca arayüzde değil, sunucu tarafında da yapılmalıdır.
- Rezervasyon ve müsaitlik işlemleri eş zamanlı isteklerde veri tutarlılığını korumalıdır; çift rezervasyon engeli yalnızca uygulama kodunda değil, veri katmanında da güvence altına alınmalıdır (Bölüm 4.5).
- Kart bilgileri doğrudan uygulama veritabanında saklanmamalıdır.
- Kişisel veriler yalnızca gerekli amaçlar için işlenmeli ve yetkisiz erişime karşı korunmalıdır.
- Katmanlı mimaride Controller asla SQL yazmaz ve hesap yapmaz; Service $_POST okumaz ve HTML üretmez; Repository iş kuralı içermez. Çakışma kuralı yalnızca Service katmanında (BookingConflictService) bulunur.
- Çakışma kuralının tek bir kopyası olmalıdır; arama, takvim ve rezervasyon aynı kural kodunu kullanır.

### 2.5 Varsayımlar ve Bağımlılıklar
- Kullanıcıların internete erişimi olduğu varsayılır.
- İlan sahipleri ilan bilgilerinin ve fiyatların doğruluğundan sorumludur.
- Gerçek ödeme, e-posta/SMS ve harita entegrasyonları yapılandırılmış harici hizmetlere bağlıdır.
- İlk sürümde para birimi TRY'dir; dil Türkçedir.
- Zaman kararları sunucu saatiyle ve Europe/Istanbul saat dilimiyle verilir. Konaklama tarih (gün) çözünürlüğündedir; standart giriş saati 14:00 ve çıkış saati 12:00 yalnızca bilgi amaçlıdır.

## 3. Sistem Özellikleri ve Fonksiyonel Gereksinimler
Bu bölümdeki fonksiyonel gereksinimler, Volere gereksinim şablonunun gereksinim kartı (requirement shell) biçiminde yazılmıştır. Her gereksinim için ayrı bir kart vardır; kartlar konularına göre gruplanmıştır. **ÇEKİRDEK** etiketi taşıyan gereksinimler çift rezervasyon önleme problemine doğrudan bağlıdır ve backlog'ta "çekirdek" etiketiyle önceliklendirilir. FR-33 – FR-42 v2.1'de, FR-43 – FR-46 v2.2'de eklenmiştir; ayrıntı her kartın Geçmiş alanındadır.

### 3.1 Volere Gereksinim Kartı Biçimi
Aşağıdaki tablo kart alanlarını ve bu belgedeki kullanımlarını gösterir. Aynı kart biçimi Bölüm 6'daki fonksiyonel olmayan gereksinimler için de kullanılır.

| Kart alanı | Anlamı ve bu belgedeki kullanımı |
| --- | --- |
| **Gereksinim No** | Requirement #: Benzersiz kimlik. Önceki sürümlerdeki FR-01 – FR-46 ve NFR-01 – NFR-19 kimlikleri değiştirilmemiştir; izlenebilirlik matrisi (Ek B) geçerliliğini korur. |
| **Gereksinim Türü** | Requirement Type: Volere gereksinim türü. Fonksiyonel gereksinimler Tür 9; fonksiyonel olmayan gereksinimler Tür 11 – 15 altındadır (Bölüm 6). |
| **Olay / Kullanım Senaryosu** | Event / BUC / PUC #: Gereksinimi tetikleyen olay veya ilgili kullanım senaryosu (UC-01 – UC-05, Bölüm 9). |
| **Açıklama** | Description: Gereksinimin niyetini anlatan kısa ifade. |
| **Gerekçe** | Rationale: Gereksinimin neden var olduğu. |
| **Kaynak** | Originator: Gereksinimi öne süren paydaşın rolü. |
| **Uygunluk Ölçütü** | Fit Criterion: Gereksinimin karşılandığını sınanabilir biçimde gösteren ölçüt. Mevcut kabul kriterlerine (AC) ve testlere (T, CS) bağlantı verilmiştir. |
| **Müşteri Memnuniyeti** | Customer Satisfaction: Gereksinim karşılanırsa müşterinin ne kadar memnun olacağı (1 = çok az, 5 = çok yüksek). |
| **Müşteri Memnuniyetsizliği** | Customer Dissatisfaction: Gereksinim karşılanmazsa müşterinin ne kadar rahatsız olacağı (1 = çok az, 5 = çok yüksek). |
| **Öncelik** | Priority: Zorunlu (olmadan ürün teslim edilemez), Gerekli (olmadan kullanılabilir ama eksik), İsteğe bağlı (zaman kalırsa). Çekirdek problemle ilgili gereksinimler ayrıca ÇEKİRDEK olarak işaretlenmiştir. |
| **Çakışmalar** | Conflicts: Bu gereksinimle gerilim veya çelişki içinde olan diğer gereksinimler. |
| **Destekleyici Materyal** | Supporting Materials: Önceki sürümdeki "Karşılayan Controller → Service" bilgisi ile ilgili iş kuralı (BR), kabul kriteri (AC), test (T), senaryo (UC) ve bölüm bağlantıları. |
| **Geçmiş** | History: Gereksinimin hangi sürümde eklendiği ve değiştiği. |

Öncelik, Kaynak, Müşteri Memnuniyeti ve Müşteri Memnuniyetsizliği değerleri bu revizyonda öneri olarak girilmiştir; ekip ve ders yürütücüsü tarafından doğrulanmalıdır (Bölüm 11.5). Açıklama alanı kısa tutulmuş, önceki ifadedeki ölçülebilir ayrıntılar Uygunluk Ölçütü alanına taşınmıştır.

### 3.2 Hesap ve Yetkilendirme

| Alan | Değer |
| --- | --- |
| **Gereksinim No** | **FR-01** |
| **Gereksinim Türü** | 9 – Fonksiyonel |
| **Olay / Kullanım Senaryosu** | Ziyaretçi hesap oluşturur |
| **Öncelik** | Zorunlu |
| **Açıklama** | Sistem, yeni kullanıcıların ad, soyad, e-posta ve parola bilgileriyle hesap oluşturmasına izin vermelidir. |
| **Gerekçe** | Rezervasyon, favori ve yorum gibi kişiye bağlı işlemler kimliği bilinen bir kullanıcı gerektirir. |
| **Kaynak** | Müşteri (ziyaretçi / kayıtlı müşteri) |
| **Çakışmalar** | Yok. |
| **Uygunluk Ölçütü** | Dört alan da geçerli girildiğinde hesap oluşur (AC-01). Alanlardan biri eksik veya geçersizse ya da e-posta zaten kayıtlıysa hesap oluşmaz ve ilgili alanı işaretleyen hata mesajı gösterilir (e-posta benzersizdir, Bölüm 7). |
| **Müşteri Memnuniyeti** | 3 / 5 |
| **Müşteri Memnuniyetsizliği** | 5 / 5 |
| **Destekleyici Materyal** | AuthController → AuthService; AC-01; NFR-02; Bölüm 7 (Kullanıcı). |
| **Geçmiş** | v2.0: tanımlandı; v2.3: Volere gereksinim kartı biçimine dönüştürüldü. |

| Alan | Değer |
| --- | --- |
| **Gereksinim No** | **FR-02** |
| **Gereksinim Türü** | 9 – Fonksiyonel |
| **Olay / Kullanım Senaryosu** | Kayıtlı kullanıcı oturum açar |
| **Öncelik** | Zorunlu |
| **Açıklama** | Sistem, kayıtlı kullanıcıların geçerli kimlik bilgileriyle oturum açmasına izin vermelidir. |
| **Gerekçe** | Rol bazlı yetkiler ve kullanıcıya özel veriler (rezervasyonlar, favoriler) oturumla ilişkilendirilir. |
| **Kaynak** | Müşteri; ilan sahibi; yönetici |
| **Çakışmalar** | NFR-15: Giriş kısıtı, hatalı parola giren meşru kullanıcıyı da geçici olarak engelleyebilir (NFR-06 ile olası gerilim). |
| **Uygunluk Ölçütü** | Geçerli e-posta/parola çiftiyle oturum açılır; geçersiz çiftle açılmaz. Girişte oturum kimliği yenilenir ve art arda 5 başarısız girişten sonra giriş 15 dakika kısıtlanır (NFR-15). |
| **Müşteri Memnuniyeti** | 3 / 5 |
| **Müşteri Memnuniyetsizliği** | 5 / 5 |
| **Destekleyici Materyal** | AuthController → AuthService; NFR-02; NFR-15. |
| **Geçmiş** | v2.0: tanımlandı; v2.3: Volere gereksinim kartı biçimine dönüştürüldü. |

| Alan | Değer |
| --- | --- |
| **Gereksinim No** | **FR-03** |
| **Gereksinim Türü** | 9 – Fonksiyonel |
| **Olay / Kullanım Senaryosu** | Oturum açmış kullanıcı çıkış yapar |
| **Öncelik** | Zorunlu |
| **Açıklama** | Sistem, oturum açmış kullanıcıya güvenli biçimde çıkış yapma olanağı sunmalıdır. |
| **Gerekçe** | Ortak kullanılan cihazlarda oturumun kapatılabilmesi hesabı yetkisiz kullanıma karşı korur. |
| **Kaynak** | Müşteri (ziyaretçi / kayıtlı müşteri) |
| **Çakışmalar** | Yok. |
| **Uygunluk Ölçütü** | Çıkıştan sonra oturum geçersiz olur; korumalı bir sayfa istendiğinde kullanıcı giriş sayfasına yönlendirilir. |
| **Müşteri Memnuniyeti** | 3 / 5 |
| **Müşteri Memnuniyetsizliği** | 4 / 5 |
| **Destekleyici Materyal** | AuthController → AuthService; NFR-15. |
| **Geçmiş** | v2.0: tanımlandı; v2.3: Volere gereksinim kartı biçimine dönüştürüldü. |

| Alan | Değer |
| --- | --- |
| **Gereksinim No** | **FR-04** |
| **Gereksinim Türü** | 9 – Fonksiyonel |
| **Olay / Kullanım Senaryosu** | Kullanıcı parolasını unutur |
| **Öncelik** | Gerekli |
| **Açıklama** | Sistem, kullanıcıların doğrulanmış e-posta veya yapılandırılmış doğrulama yöntemiyle parola sıfırlama talebi oluşturmasını sağlamalıdır; sıfırlama jetonu tek kullanımlık olmalı ve 60 dakika sonra geçerliliğini yitirmelidir. |
| **Gerekçe** | Parolasını unutan kullanıcı hesabına yeniden erişebilmelidir; jetonun tek kullanımlık ve kısa ömürlü olması ele geçirilmiş bağlantının kötüye kullanılmasını sınırlar. |
| **Kaynak** | Müşteri; ilan sahibi |
| **Çakışmalar** | Yok. |
| **Uygunluk Ölçütü** | Bir jeton ikinci kez kullanıldığında reddedilir; oluşturulmasından 60 dakika sonra kullanılan jeton reddedilir. Veritabanında jetonun kendisi değil özeti saklanır (Bölüm 7, Parola Sıfırlama Jetonu). |
| **Müşteri Memnuniyeti** | 3 / 5 |
| **Müşteri Memnuniyetsizliği** | 3 / 5 |
| **Destekleyici Materyal** | AuthController → AuthService; Bölüm 7 (PasswordResetToken); NFR-02. |
| **Geçmiş** | v2.0: tanımlandı; v2.3: Volere gereksinim kartı biçimine dönüştürüldü. |

| Alan | Değer |
| --- | --- |
| **Gereksinim No** | **FR-05** |
| **Gereksinim Türü** | 9 – Fonksiyonel |
| **Olay / Kullanım Senaryosu** | Kullanıcı yetkisi olmayan bir işlevi çağırır |
| **Öncelik** | Zorunlu |
| **Açıklama** | Sistem, müşteri, ilan sahibi ve yönetici işlemlerini rol bazında sınırlandırmalıdır. |
| **Gerekçe** | Rollerin işlevleri farklıdır; yetkisiz erişim veri bozulmasına, para kaybına ve gizlilik ihlaline yol açar. |
| **Kaynak** | Proje ekibi (mimari) |
| **Çakışmalar** | Yok. |
| **Uygunluk Ölçütü** | Müşteri rolündeki bir kullanıcının ilan sahibi veya yönetici işlevlerine, ilan sahibinin yönetici işlevlerine yaptığı istek (arayüzde gizli olsa bile doğrudan istek dahil) sunucu tarafında reddedilir (AC-06, AC-07). |
| **Müşteri Memnuniyeti** | 3 / 5 |
| **Müşteri Memnuniyetsizliği** | 5 / 5 |
| **Destekleyici Materyal** | AuthController → AuthService; NFR-03; Bölüm 2.2 (kullanıcı sınıfları); Bölüm 2.4. |
| **Geçmiş** | v2.0: tanımlandı; v2.3: Volere gereksinim kartı biçimine dönüştürüldü. |

### 3.3 Arama, Filtreleme ve İlan Görüntüleme

| Alan | Değer |
| --- | --- |
| **Gereksinim No** | **FR-06** |
| **Gereksinim Türü** | 9 – Fonksiyonel |
| **Olay / Kullanım Senaryosu** | UC-01 Konaklama arama |
| **Öncelik** | Zorunlu **· ÇEKİRDEK** |
| **Açıklama** | Sistem; konum, giriş tarihi, çıkış tarihi, kişi sayısı ve konaklama türüne göre arama yapabilmeli; yalnızca aralığın tüm gecelerinde müsait (Bölüm 4.4), aktif ve kapasitesi yeten en az bir birimi olan ilanları listelemeli, otel ilanlarında müsait oda sayısını göstermelidir. |
| **Gerekçe** | Kullanıcı, tarih ve kişi sayısına uygun seçenekleri kolayca bulabilmelidir (Bölüm 1.3). Aramada görünüp rezervasyonda reddedilen sonuçlar güveni zedeler. |
| **Kaynak** | Müşteri (ziyaretçi / kayıtlı müşteri); Ders proje tanımı (ders yürütücüsü) |
| **Çakışmalar** | Yok. |
| **Uygunluk Ölçütü** | Dolu veya kapalı birimi olan ilan, o aralık için sonuçta çıkmaz; aramada çıkan birim, eşzamanlı istek yoksa rezervasyonda kabul edilir (AC-02, AC-20, T-08). Arama sonuçlarının %95'i 3 saniye içinde gelir (NFR-01). |
| **Müşteri Memnuniyeti** | 5 / 5 |
| **Müşteri Memnuniyetsizliği** | 5 / 5 |
| **Destekleyici Materyal** | PropertyController → AvailabilityService; UC-01; US-01; Bölüm 4.4; BR-01, BR-02, BR-14; AC-02, AC-20; T-08. |
| **Geçmiş** | v2.0: tanımlandı; v2.3: Volere gereksinim kartı biçimine dönüştürüldü. |

| Alan | Değer |
| --- | --- |
| **Gereksinim No** | **FR-07** |
| **Gereksinim Türü** | 9 – Fonksiyonel |
| **Olay / Kullanım Senaryosu** | UC-01 Konaklama arama (filtre uygulama) |
| **Öncelik** | Gerekli |
| **Açıklama** | Sistem, arama sonuçlarını fiyat aralığı, puan, konaklama türü, oda tipi ve olanaklara göre filtrelemelidir. |
| **Gerekçe** | Kullanıcı sonuç kümesini kendi bütçesine ve ihtiyacına göre daraltabilmelidir. |
| **Kaynak** | Müşteri (ziyaretçi / kayıtlı müşteri) |
| **Çakışmalar** | Yok. |
| **Uygunluk Ölçütü** | Her filtre uygulandığında sonuç yalnızca ölçütü sağlayan ilanları içerir; filtre kaldırıldığında sonuç kümesi önceki hâline döner. Oda tipi filtresi örnek verideki en az 3 oda tipiyle doğrulanır (AC-29). |
| **Müşteri Memnuniyeti** | 4 / 5 |
| **Müşteri Memnuniyetsizliği** | 3 / 5 |
| **Destekleyici Materyal** | PropertyController → PropertyService; UC-01; AC-29; Bölüm 7 (Birim: oda tipi). |
| **Geçmiş** | v2.0: tanımlandı; v2.2: güncellendi; v2.3: Volere gereksinim kartı biçimine dönüştürüldü. |

| Alan | Değer |
| --- | --- |
| **Gereksinim No** | **FR-08** |
| **Gereksinim Türü** | 9 – Fonksiyonel |
| **Olay / Kullanım Senaryosu** | UC-01 Konaklama arama (sıralama) |
| **Öncelik** | Gerekli |
| **Açıklama** | Sistem, arama sonuçlarını fiyat, kullanıcı puanı veya uygunluk gibi ölçütlere göre sıralamalıdır. |
| **Gerekçe** | Kullanıcı, kendisi için en önemli ölçüte göre en iyi seçenekleri üstte görmek ister. |
| **Kaynak** | Müşteri (ziyaretçi / kayıtlı müşteri) |
| **Çakışmalar** | Yok. |
| **Uygunluk Ölçütü** | Fiyata göre artan sıralamada listedeki her ilanın fiyatı bir sonrakinden büyük değildir; puana göre azalan sıralamada her ilanın puanı bir sonrakinden küçük değildir. "Uygunluk" ölçütünün formülü Bölüm 11.5'te açık karar olarak listelenmiştir; tanımlanana kadar uygunluk ölçütü için kabul testi yazılamaz. |
| **Müşteri Memnuniyeti** | 3 / 5 |
| **Müşteri Memnuniyetsizliği** | 3 / 5 |
| **Destekleyici Materyal** | PropertyController → PropertyService; UC-01; Bölüm 11.5 (açık karar). |
| **Geçmiş** | v2.0: tanımlandı; v2.3: Volere gereksinim kartı biçimine dönüştürüldü. |

| Alan | Değer |
| --- | --- |
| **Gereksinim No** | **FR-09** |
| **Gereksinim Türü** | 9 – Fonksiyonel |
| **Olay / Kullanım Senaryosu** | Kullanıcı ilan detayını açar |
| **Öncelik** | Zorunlu |
| **Açıklama** | Sistem; açıklama, fotoğraflar, konum, kapasite, olanaklar, fiyat, rezervasyon modu ve iptal koşullarını ilan detayında göstermelidir. |
| **Gerekçe** | Kullanıcı rezervasyon kararını verebilmek için ilanın ve koşullarının tamamını görmelidir; iptal koşulları rezervasyon öncesinde gösterilmek zorundadır (BR-06). |
| **Kaynak** | Müşteri (ziyaretçi / kayıtlı müşteri) |
| **Çakışmalar** | Yok. |
| **Uygunluk Ölçütü** | Aktif bir ilanın detay sayfasında sayılan sekiz öğenin hiçbiri eksik değildir. |
| **Müşteri Memnuniyeti** | 4 / 5 |
| **Müşteri Memnuniyetsizliği** | 4 / 5 |
| **Destekleyici Materyal** | PropertyController → PropertyService; BR-06; Bölüm 5.1. |
| **Geçmiş** | v2.0: tanımlandı; v2.3: Volere gereksinim kartı biçimine dönüştürüldü. |

| Alan | Değer |
| --- | --- |
| **Gereksinim No** | **FR-10** |
| **Gereksinim Türü** | 9 – Fonksiyonel |
| **Olay / Kullanım Senaryosu** | UC-01 / UC-02 Müsaitlik görüntüleme |
| **Öncelik** | Zorunlu **· ÇEKİRDEK** |
| **Açıklama** | Sistem, ilan detayında seçilen tarih aralığı için birim bazında müsaitlik durumunu göstermeli ve ay ileri/geri gezilebilen aylık takvimde dolu ve kapalı geceleri ayırt edilebilir biçimde işaretlemelidir. |
| **Gerekçe** | İlan sahibi ve müşteri boş geceleri tek bakışta görebilmelidir; takvim ile rezervasyon aynı kuralı kullanmazsa tutarsızlık doğar (Bölüm 4.4). |
| **Kaynak** | Müşteri (ziyaretçi / kayıtlı müşteri); Ders proje tanımı (ders yürütücüsü) |
| **Çakışmalar** | Yok. |
| **Uygunluk Ölçütü** | Aylık takvim ay ileri/geri gezilebilir; dolu ve kapalı geceler birbirinden ayırt edilir (AC-32). Takvimdeki dolu/kapalı sonucu, rezervasyon anındaki kararla aynı kural koduyla üretilir (T-08). |
| **Müşteri Memnuniyeti** | 4 / 5 |
| **Müşteri Memnuniyetsizliği** | 4 / 5 |
| **Destekleyici Materyal** | PropertyController → AvailabilityService; Bölüm 4.4; AC-32; T-08. |
| **Geçmiş** | v2.0: tanımlandı; v2.2: güncellendi; v2.3: Volere gereksinim kartı biçimine dönüştürüldü. |

| Alan | Değer |
| --- | --- |
| **Gereksinim No** | **FR-44** |
| **Gereksinim Türü** | 9 – Fonksiyonel |
| **Olay / Kullanım Senaryosu** | UC-01 / UC-02 Fiyat görüntüleme |
| **Öncelik** | Zorunlu **· ÇEKİRDEK** |
| **Açıklama** | Sistem, arama sonuçlarında ve ilan detayında seçilen tarih aralığı için toplam fiyatı ve gecelik dökümü, rezervasyon anındakiyle aynı hesaplama koduyla göstermelidir. |
| **Gerekçe** | Kullanıcıya gösterilen fiyatla ödeyeceği fiyat farklıysa güven ve gelir kaybedilir (Bölüm 4.10). |
| **Kaynak** | Ders proje tanımı (ders yürütücüsü) |
| **Çakışmalar** | NFR-01: Gece bazlı fiyat hesabı arama süresini artırabilir; 3 saniyelik hedef korunmalıdır. |
| **Uygunluk Ölçütü** | Aynı birim, aralık ve kural kümesi için arama sonucu, ilan detayı ve rezervasyon ekranındaki toplam fiyat kuruşu kuruşuna aynıdır; gecelik döküm toplamı toplam konaklama tutarına eşittir (T-12, T-13). |
| **Müşteri Memnuniyeti** | 4 / 5 |
| **Müşteri Memnuniyetsizliği** | 5 / 5 |
| **Destekleyici Materyal** | PropertyController → PricingService; Bölüm 4.10; BR-23 – BR-26; T-12, T-13. |
| **Geçmiş** | v2.2: tanımlandı; v2.3: Volere gereksinim kartı biçimine dönüştürüldü. |

### 3.4 Rezervasyon Oluşturma ve Çakışma Önleme

| Alan | Değer |
| --- | --- |
| **Gereksinim No** | **FR-11** |
| **Gereksinim Türü** | 9 – Fonksiyonel |
| **Olay / Kullanım Senaryosu** | UC-02 Rezervasyon oluşturma (adım 4) |
| **Öncelik** | Zorunlu **· ÇEKİRDEK** |
| **Açıklama** | Sistem, rezervasyon kaydı oluşturulmadan hemen önce ve aynı işlem içinde tarih aralığını, kişi kapasitesini, ilan ve birim durumunu, fiyatı ve gece bazında müsaitliği (Bölüm 4.4) yeniden doğrulamalıdır. Kullanıcının önceki ekranda gördüğü müsaitlik bilgisi geçerli sayılmamalıdır. |
| **Gerekçe** | Kontrol ile kayıt arasında başka bir istek aynı geceleri alabilir; ekranda görülen bilgi eskimiş olabilir (CS-04). |
| **Kaynak** | Ders proje tanımı (ders yürütücüsü) |
| **Çakışmalar** | Yok. |
| **Uygunluk Ölçütü** | Kullanıcı fiyat ekranında beklerken başka bir müşteri aynı geceleri almışsa onay isteği reddedilir ve hiçbir kayıt oluşmaz (CS-04, AC-12, T-05). |
| **Müşteri Memnuniyeti** | 5 / 5 |
| **Müşteri Memnuniyetsizliği** | 5 / 5 |
| **Destekleyici Materyal** | ReservationController → BookingConflictService; UC-02; Bölüm 4.4; CS-04; AC-12; T-05. |
| **Geçmiş** | v2.0: tanımlandı; v2.1: netleştirildi; v2.3: Volere gereksinim kartı biçimine dönüştürüldü. |

| Alan | Değer |
| --- | --- |
| **Gereksinim No** | **FR-12** |
| **Gereksinim Türü** | 9 – Fonksiyonel |
| **Olay / Kullanım Senaryosu** | UC-02 Rezervasyon oluşturma (adım 4) |
| **Öncelik** | Zorunlu **· ÇEKİRDEK** |
| **Açıklama** | Sistem, oturum açmış kullanıcının uygun bir birim için rezervasyon talebi oluşturmasını sağlamalı; talep Beklemede durumunda ve bitiş zamanıyla (expires_at) kaydedilmelidir. |
| **Gerekçe** | Beklemede kayıt, ödeme veya onay tamamlanana kadar geceleri tutar (BR-05); bitiş zamanı bekletmenin sınırlandırılmasını sağlar. |
| **Kaynak** | Müşteri (ziyaretçi / kayıtlı müşteri); Ders proje tanımı (ders yürütücüsü) |
| **Çakışmalar** | Yok. |
| **Uygunluk Ölçütü** | Başarılı talepte kayıt Beklemede durumundadır ve expires_at, oluşturulma anından itibaren anında modda 10 dakika, onaylı modda 24 saat sonradır (BR-15). |
| **Müşteri Memnuniyeti** | 5 / 5 |
| **Müşteri Memnuniyetsizliği** | 5 / 5 |
| **Destekleyici Materyal** | ReservationController → ReservationService; UC-02; BR-05; BR-15; Bölüm 4.3. |
| **Geçmiş** | v2.0: tanımlandı; v2.1: netleştirildi; v2.3: Volere gereksinim kartı biçimine dönüştürüldü. |

| Alan | Değer |
| --- | --- |
| **Gereksinim No** | **FR-13** |
| **Gereksinim Türü** | 9 – Fonksiyonel |
| **Olay / Kullanım Senaryosu** | UC-02 Rezervasyon oluşturma (adım 4) |
| **Öncelik** | Zorunlu |
| **Açıklama** | Sistem, her rezervasyon için RZ-YYYYMMDD-XXXXXX biçiminde (X: rastgele harf/rakam) benzersiz bir rezervasyon numarası üretmeli; numara çakışırsa yeniden üretmelidir. |
| **Gerekçe** | Müşteri, ilan sahibi ve yönetici rezervasyona tekil ve okunabilir bir numarayla başvurabilmelidir. |
| **Kaynak** | Müşteri (ziyaretçi / kayıtlı müşteri); İlan sahibi / işletme |
| **Çakışmalar** | Yok. |
| **Uygunluk Ölçütü** | Her rezervasyonun numarası belirtilen biçime uyar ve benzersizdir (AC-04). Üretilen numara mevcut bir numarayla çakışırsa yeni numara üretilir ve rezervasyon yine tek kayıt olarak oluşur. |
| **Müşteri Memnuniyeti** | 3 / 5 |
| **Müşteri Memnuniyetsizliği** | 4 / 5 |
| **Destekleyici Materyal** | ReservationController → ReservationService; AC-04; Bölüm 7 (Rezervasyon: numara UK). |
| **Geçmiş** | v2.0: tanımlandı; v2.1: netleştirildi; v2.3: Volere gereksinim kartı biçimine dönüştürüldü. |

| Alan | Değer |
| --- | --- |
| **Gereksinim No** | **FR-18** |
| **Gereksinim Türü** | 9 – Fonksiyonel |
| **Olay / Kullanım Senaryosu** | UC-02 Rezervasyon oluşturma |
| **Öncelik** | Zorunlu **· ÇEKİRDEK** |
| **Açıklama** | Sistem, aynı birim için kesişen konaklama aralıklarında birden fazla engelleyen (Beklemede veya Onaylandı) rezervasyon oluşmasını, eş zamanlı istekler dahil, uygulama, işlem ve veritabanı düzeylerinde engellemelidir. |
| **Gerekçe** | Çift rezervasyon platformun varlık nedenini ortadan kaldırır; en kötü senaryo "iki kişiye aynı oda satıldı" durumudur (Bölüm 4.1). |
| **Kaynak** | Ders proje tanımı (ders yürütücüsü) |
| **Çakışmalar** | NFR-13: Birim satırı kilidi (çift rezervasyon güvencesi) kilit bekleme süresini uzatır; kilit zaman aşımı 5 saniye ile sınırlıdır. |
| **Uygunluk Ölçütü** | Aynı birim ve aralık için 50 eşzamanlı istekte tam 1 rezervasyon oluşur, 49 istek çakışma hatası alır ve gece kaydı sayısı gece sayısına eşit olur (AC-05, AC-13, T-05). Tüm çekirdek testlerden sonra tutarlılık denetim sorgusu boş küme döner (AC-22, T-11). |
| **Müşteri Memnuniyeti** | 5 / 5 |
| **Müşteri Memnuniyetsizliği** | 5 / 5 |
| **Destekleyici Materyal** | ReservationController → BookingConflictService; Bölüm 4.1 – 4.5; BR-11, BR-12, BR-13; NFR-05; AC-05, AC-13, AC-22; T-05, T-06, T-11. |
| **Geçmiş** | v2.0: tanımlandı; v2.1: netleştirildi; v2.3: Volere gereksinim kartı biçimine dönüştürüldü. |

| Alan | Değer |
| --- | --- |
| **Gereksinim No** | **FR-33** |
| **Gereksinim Türü** | 9 – Fonksiyonel |
| **Olay / Kullanım Senaryosu** | UC-02 Rezervasyon oluşturma (adım 4) |
| **Öncelik** | Zorunlu **· ÇEKİRDEK** |
| **Açıklama** | Sistem, rezervasyon oluşturma işlemini (yeniden doğrulama, rezervasyon kaydı, gecelerin ayrılması) tek bir atomik işlemde yürütmeli; herhangi bir adım başarısız olursa rezervasyon kaydı ve gece kaydı kalmamalıdır. |
| **Gerekçe** | "Rezervasyon var ama geceler yok" veya tersi tutarsızlık, hem çift rezervasyona hem hayalet bloklara yol açar. |
| **Kaynak** | Ders proje tanımı (ders yürütücüsü) |
| **Çakışmalar** | Yok. |
| **Uygunluk Ölçütü** | Gece kaydı eklenirken hata enjekte edildiğinde rezervasyon kaydı da geri alınır; yarım rezervasyon kalmaz (CS-12, T-07, AC-12). |
| **Müşteri Memnuniyeti** | 4 / 5 |
| **Müşteri Memnuniyetsizliği** | 5 / 5 |
| **Destekleyici Materyal** | ReservationService → BookingConflictService; Bölüm 4.5; CS-12; T-07. |
| **Geçmiş** | v2.1: tanımlandı; v2.3: Volere gereksinim kartı biçimine dönüştürüldü. |

| Alan | Değer |
| --- | --- |
| **Gereksinim No** | **FR-34** |
| **Gereksinim Türü** | 9 – Fonksiyonel |
| **Olay / Kullanım Senaryosu** | UC-02 Rezervasyon oluşturma / zamanlanmış görev |
| **Öncelik** | Zorunlu **· ÇEKİRDEK** |
| **Açıklama** | Sistem, Beklemede rezervasyonlara bekletme süresi (BR-15) uygulamalı; süresi dolanları Süresi Doldu durumuna alıp gecelerini serbest bırakmalıdır. Süresi dolmuş kayıt, zamanlanmış görev çalışmamış olsa bile engelleyici sayılmamalı ve çakışma kontrolü sırasında temizlenmelidir. |
| **Gerekçe** | Ödenmeyen veya onaylanmayan talepler geceleri sonsuza dek bloke etmemelidir; doğruluk, zamanlanmış görevin zamanında çalışmasına bağlı olmamalıdır (Bölüm 4.5, lazy expiry). |
| **Kaynak** | Ders proje tanımı (ders yürütücüsü) |
| **Çakışmalar** | Yok. |
| **Uygunluk Ölçütü** | Süresi dolmuş bir Beklemede kayıt varken aynı geceler için gelen yeni istek kabul edilir ve eski kayıt Süresi Doldu olur (CS-05, T-03, AC-15). |
| **Müşteri Memnuniyeti** | 4 / 5 |
| **Müşteri Memnuniyetsizliği** | 4 / 5 |
| **Destekleyici Materyal** | ReservationService → BookingConflictService; BR-15; Bölüm 4.5; CS-05; T-03; AC-15. |
| **Geçmiş** | v2.1: tanımlandı; v2.3: Volere gereksinim kartı biçimine dönüştürüldü. |

| Alan | Değer |
| --- | --- |
| **Gereksinim No** | **FR-35** |
| **Gereksinim Türü** | 9 – Fonksiyonel |
| **Olay / Kullanım Senaryosu** | UC-02 Rezervasyon oluşturma (A4 Çift gönderim) |
| **Öncelik** | Zorunlu **· ÇEKİRDEK** |
| **Açıklama** | Sistem, aynı rezervasyon formunun art arda gönderilmesini (çift tıklama, sayfa yenileme, geri tuşu) idempotency anahtarıyla tanımalı; ikinci gönderimde yeni kayıt oluşturmak yerine mevcut rezervasyonu göstermelidir. |
| **Gerekçe** | Kullanıcı davranışı (çift tıklama, yenileme) kazara iki rezervasyon ve çift ödeme üretmemelidir. |
| **Kaynak** | Ders proje tanımı (ders yürütücüsü) |
| **Çakışmalar** | Yok. |
| **Uygunluk Ölçütü** | Aynı idempotency anahtarıyla gönderilen iki istek tek rezervasyon kaydı üretir; ikinci istek mevcut rezervasyonu gösterir (CS-06, T-04, AC-17). |
| **Müşteri Memnuniyeti** | 4 / 5 |
| **Müşteri Memnuniyetsizliği** | 4 / 5 |
| **Destekleyici Materyal** | ReservationController → ReservationService; CS-06; T-04; AC-17; Bölüm 4.5. |
| **Geçmiş** | v2.1: tanımlandı; v2.3: Volere gereksinim kartı biçimine dönüştürüldü. |

| Alan | Değer |
| --- | --- |
| **Gereksinim No** | **FR-36** |
| **Gereksinim Türü** | 9 – Fonksiyonel |
| **Olay / Kullanım Senaryosu** | UC-02 Rezervasyon oluşturma (A1 Çakışma) |
| **Öncelik** | Zorunlu **· ÇEKİRDEK** |
| **Açıklama** | Sistem, çakışma nedeniyle rezervasyonu reddettiğinde hiçbir kayıt oluşturmamalı, kullanıcıya nedeni açıklamalı ve aynı birim/ilan için aynı gece sayısında en fazla 3 yakın müsait aralık önermelidir. |
| **Gerekçe** | Reddedilen kullanıcı işlemi bırakmak yerine uygun bir alternatife yönlendirilirse platform satışı kaybetmez. |
| **Kaynak** | Müşteri (ziyaretçi / kayıtlı müşteri); Ders proje tanımı (ders yürütücüsü) |
| **Çakışmalar** | Yok. |
| **Uygunluk Ölçütü** | Çakışma nedeniyle reddedilen istek hiçbir rezervasyon veya gece kaydı bırakmaz; kullanıcı açık bir mesaj ve en çok 3 alternatif aralık görür; mesaj başka bir kullanıcının bilgisini içermez (AC-12, NFR-04). |
| **Müşteri Memnuniyeti** | 4 / 5 |
| **Müşteri Memnuniyetsizliği** | 3 / 5 |
| **Destekleyici Materyal** | ReservationController → BookingConflictService; Bölüm 5.1; AC-12; NFR-04; Bölüm 11.4 (alternatif öneri sayısı). |
| **Geçmiş** | v2.1: tanımlandı; v2.3: Volere gereksinim kartı biçimine dönüştürüldü. |

| Alan | Değer |
| --- | --- |
| **Gereksinim No** | **FR-37** |
| **Gereksinim Türü** | 9 – Fonksiyonel |
| **Olay / Kullanım Senaryosu** | UC-02 Rezervasyon oluşturma (adım 5, A3 Ödeme başarısız) |
| **Öncelik** | Zorunlu |
| **Açıklama** | Sistem, anında rezervasyon modunda simüle ödeme akışı sunmalı ve Payment kaydı oluşturmalıdır; başarılı ödemede rezervasyon Onaylandı olmalı, başarısız ödemede Beklemede kalmalı ve bekletme süresi dolana kadar yeniden denenebilmelidir. Kart verisi saklanmamalıdır. |
| **Gerekçe** | İlk sürümde gerçek para tahsilatı kapsam dışıdır (Bölüm 11.2); ancak ödeme akışının durum davranışı gerçek sağlayıcıdan önce doğrulanmalıdır. |
| **Kaynak** | İlan sahibi / işletme; Müşteri (ziyaretçi / kayıtlı müşteri) |
| **Çakışmalar** | Yok. |
| **Uygunluk Ölçütü** | Başarısız ödemede rezervasyon Onaylandı olmaz, Beklemede kalır ve süre dolana kadar yeniden denenebilir; süre dolunca Süresi Doldu olur (AC-19, CS-09). Başarılı ödemede Onaylandı olur ve Payment kaydı vardır; veritabanında kart numarası alanı bulunmaz. |
| **Müşteri Memnuniyeti** | 3 / 5 |
| **Müşteri Memnuniyetsizliği** | 4 / 5 |
| **Destekleyici Materyal** | ReservationController → PaymentService; UC-02; BR-05; CS-09; AC-19; Bölüm 7 (Ödeme Kaydı). |
| **Geçmiş** | v2.1: tanımlandı; v2.3: Volere gereksinim kartı biçimine dönüştürüldü. |

### 3.5 Fiyatlandırma

| Alan | Değer |
| --- | --- |
| **Gereksinim No** | **FR-14** |
| **Gereksinim Türü** | 9 – Fonksiyonel |
| **Olay / Kullanım Senaryosu** | UC-02 Rezervasyon oluşturma (adım 2, 4) |
| **Öncelik** | Zorunlu **· ÇEKİRDEK** |
| **Açıklama** | Sistem, konaklama fiyatını her gece için ayrı hesaplamalı (temel fiyat, sezon ve hafta sonu farkları, takvim istisnası); uzun konaklama indirimini, vergi ve ek ücretleri ayrı kalemler olarak içeren gecelik dökümü onaydan önce sunmalı ve hesaplanan kalemleri rezervasyonla birlikte saklamalıdır (fiyat anlık görüntüsü, BR-16). |
| **Gerekçe** | Fiyat, kullanıcının en çok karşılaştırdığı ve yanlış hesaplandığında doğrudan para kaybettiren değerdir (Bölüm 4.10). |
| **Kaynak** | Ders proje tanımı (ders yürütücüsü); Müşteri (ziyaretçi / kayıtlı müşteri) |
| **Çakışmalar** | Yok. |
| **Uygunluk Ölçütü** | Temel fiyat 1.000 TL, Temmuz sezonu +%30, hafta sonu +%20 iken 9–12 Temmuz 2027 konaklaması 1.560,00 + 1.560,00 + 1.300,00 = 4.420,00 TL verir (AC-23). 12–19 Temmuz 2027 (7 gece) %10 indirim ve %10 vergiyle 9.523,80 TL olur (AC-24). Rezervasyon sonrası kural değişikliği tutarı değiştirmez; kalemlerin toplamı toplam fiyata eşittir (AC-21, T-12, T-13). |
| **Müşteri Memnuniyeti** | 5 / 5 |
| **Müşteri Memnuniyetsizliği** | 5 / 5 |
| **Destekleyici Materyal** | ReservationController → PricingService; Bölüm 4.10; BR-07, BR-16, BR-23 – BR-26; AC-08, AC-21, AC-23 – AC-25; T-12, T-13. |
| **Geçmiş** | v2.0: tanımlandı; v2.2: güncellendi; v2.3: Volere gereksinim kartı biçimine dönüştürüldü. |

| Alan | Değer |
| --- | --- |
| **Gereksinim No** | **FR-43** |
| **Gereksinim Türü** | 9 – Fonksiyonel |
| **Olay / Kullanım Senaryosu** | UC-03 İlan, fiyat ve takvim yönetimi (adım 3) |
| **Öncelik** | Zorunlu **· ÇEKİRDEK** |
| **Açıklama** | Sistem, ilan sahibinin ilan (veya birim) için hafta sonu farkı, tarih aralığına bağlı sezon farkı ve en az gece sayısına bağlı uzun konaklama indirimi (yüzde veya sabit tutar) tanımlamasına izin vermelidir; kurallar BR-23 – BR-26'ya göre uygulanmalıdır. |
| **Gerekçe** | İlan sahibi fiyatını sezona ve talebe göre kendisi yönetebilmelidir; kurallar tek bir hesap sırasıyla uygulanmazsa fiyat tutarsızlaşır. |
| **Kaynak** | Ders proje tanımı (ders yürütücüsü); İlan sahibi / işletme |
| **Çakışmalar** | Yok. |
| **Uygunluk Ölçütü** | İlan sahibi üç kural türünü de tanımlayabilir. Aynı geceyi kapsayan iki sezon kuralında yalnızca yüksek öncelikli olan uygulanır; önceliği eşit iki sezon kuralı tanımlanırken hata gösterilir (BR-26). Takvim istisnası olan gecede sezon ve hafta sonu farkı uygulanmaz (AC-25, T-12). |
| **Müşteri Memnuniyeti** | 4 / 5 |
| **Müşteri Memnuniyetsizliği** | 4 / 5 |
| **Destekleyici Materyal** | HostController → PricingService; Bölüm 4.10; BR-23 – BR-26; AC-25; T-12; Bölüm 7 (Fiyat Kuralı). |
| **Geçmiş** | v2.2: tanımlandı; v2.3: Volere gereksinim kartı biçimine dönüştürüldü. |

### 3.6 Rezervasyon Yaşam Döngüsü, İptal ve İade

| Alan | Değer |
| --- | --- |
| **Gereksinim No** | **FR-15** |
| **Gereksinim Türü** | 9 – Fonksiyonel |
| **Olay / Kullanım Senaryosu** | UC-02 / UC-04 / UC-05 Durum değişiklikleri |
| **Öncelik** | Zorunlu **· ÇEKİRDEK** |
| **Açıklama** | Sistem, rezervasyon durumunu Beklemede, Onaylandı, İptal Edildi, Reddedildi, Süresi Doldu ve Tamamlandı olarak takip etmeli; yalnızca Bölüm 4.3'teki izinli durum geçişlerine izin vermelidir. |
| **Gerekçe** | Hangi durumların geceleri tuttuğu çakışma kuralının temelidir; izinsiz geçiş tutarsız doluluk üretir. |
| **Kaynak** | Ders proje tanımı (ders yürütücüsü) |
| **Çakışmalar** | Yok. |
| **Uygunluk Ölçütü** | Bölüm 4.3'teki izinli geçişler gerçekleşir ve geceleri doğru tutar/serbest bırakır; tabloda olmayan her geçiş (son durumlardan çıkış dahil) reddedilir ve denetim kaydına yazılır (T-02, FR-41). |
| **Müşteri Memnuniyeti** | 4 / 5 |
| **Müşteri Memnuniyetsizliği** | 5 / 5 |
| **Destekleyici Materyal** | ReservationController → ReservationService; Bölüm 4.3; BR-12; T-02. |
| **Geçmiş** | v2.0: tanımlandı; v2.3: Volere gereksinim kartı biçimine dönüştürüldü. |

| Alan | Değer |
| --- | --- |
| **Gereksinim No** | **FR-16** |
| **Gereksinim Türü** | 9 – Fonksiyonel |
| **Olay / Kullanım Senaryosu** | Müşteri rezervasyonlarını listeler |
| **Öncelik** | Gerekli |
| **Açıklama** | Sistem, kullanıcının geçmiş ve gelecek rezervasyonlarını durumlarıyla birlikte listelemesini sağlamalıdır. |
| **Gerekçe** | Müşteri, rezervasyonunun durumunu ve iptal edebileceği kayıtları görebilmelidir. |
| **Kaynak** | Müşteri (ziyaretçi / kayıtlı müşteri) |
| **Çakışmalar** | Yok. |
| **Uygunluk Ölçütü** | Kullanıcı yalnızca kendi rezervasyonlarını, durumlarıyla birlikte geçmiş ve gelecek ayrımıyla görür; başkasının rezervasyonuna erişemez (AC-06). |
| **Müşteri Memnuniyeti** | 4 / 5 |
| **Müşteri Memnuniyetsizliği** | 4 / 5 |
| **Destekleyici Materyal** | ReservationController → ReservationService; AC-06; NFR-04; Bölüm 5.1. |
| **Geçmiş** | v2.0: tanımlandı; v2.3: Volere gereksinim kartı biçimine dönüştürüldü. |

| Alan | Değer |
| --- | --- |
| **Gereksinim No** | **FR-17** |
| **Gereksinim Türü** | 9 – Fonksiyonel |
| **Olay / Kullanım Senaryosu** | UC-04 Rezervasyon iptali |
| **Öncelik** | Zorunlu **· ÇEKİRDEK** |
| **Açıklama** | Sistem, BR-17 ve BR-18'e uygun rezervasyonların kullanıcı tarafından iptal edilmesini sağlamalı; iptalden önce iade tutarını ve kesintiyi göstermeli (FR-45); iptal, durum değişikliği, gecelerin serbest bırakılması ve simüle iade kaydını aynı işlemde yapmalıdır. |
| **Gerekçe** | İptal edilen tarihler hemen satışa açılmazsa ilan sahibi boş geceyi yeniden satamaz; müşteri de iade tutarını önceden bilmelidir. |
| **Kaynak** | Ders proje tanımı (ders yürütücüsü); Müşteri (ziyaretçi / kayıtlı müşteri) |
| **Çakışmalar** | Yok. |
| **Uygunluk Ölçütü** | İptalden sonra geceler aramada ve takvimde müsait görünür ve yeniden rezerve edilebilir (AC-16). Giriş günü ve sonrasında ya da son durumdaki rezervasyonda iptal reddedilir (BR-18). Hata durumunda rezervasyon önceki durumunda ve geceleri tutulu kalır (CS-07, T-02). |
| **Müşteri Memnuniyeti** | 5 / 5 |
| **Müşteri Memnuniyetsizliği** | 5 / 5 |
| **Destekleyici Materyal** | ReservationController → ReservationService; UC-04; US-04; BR-17, BR-18; AC-16, AC-26, AC-27; T-02, T-14. |
| **Geçmiş** | v2.0: tanımlandı; v2.2: güncellendi; v2.3: Volere gereksinim kartı biçimine dönüştürüldü. |

| Alan | Değer |
| --- | --- |
| **Gereksinim No** | **FR-40** |
| **Gereksinim Türü** | 9 – Fonksiyonel |
| **Olay / Kullanım Senaryosu** | Zamanlanmış görev: çıkış tarihi geçen rezervasyon |
| **Öncelik** | Zorunlu |
| **Açıklama** | Sistem, çıkış tarihi geçen Onaylandı rezervasyonları otomatik olarak Tamamlandı durumuna almalıdır. |
| **Gerekçe** | Yorum yapılabilmesi (BR-09) ve doluluk/gelir raporları (BR-30) tamamlanmış konaklamaları gerektirir. |
| **Kaynak** | Proje ekibi (mimari) |
| **Çakışmalar** | Yok. |
| **Uygunluk Ölçütü** | Zamanlanmış görev (dakikada bir, Bölüm 11.4) çalıştıktan sonra çıkış tarihi geçmiş her Onaylandı rezervasyon Tamamlandı olur ve gece kayıtları silinir; çıkış tarihi gelmemiş rezervasyonlar değişmez (Bölüm 4.3). |
| **Müşteri Memnuniyeti** | 3 / 5 |
| **Müşteri Memnuniyetsizliği** | 4 / 5 |
| **Destekleyici Materyal** | Zamanlanmış görev → ReservationService; Bölüm 2.1; Bölüm 4.3; BR-09; BR-30. |
| **Geçmiş** | v2.1: tanımlandı; v2.3: Volere gereksinim kartı biçimine dönüştürüldü. |

| Alan | Değer |
| --- | --- |
| **Gereksinim No** | **FR-45** |
| **Gereksinim Türü** | 9 – Fonksiyonel |
| **Olay / Kullanım Senaryosu** | UC-04 Rezervasyon iptali (adım 2, 4) |
| **Öncelik** | Zorunlu **· ÇEKİRDEK** |
| **Açıklama** | Sistem, iptal politikasına (esnek / orta / katı) göre iade tutarını ve kesintiyi hesaplamalı, iptal onayından önce kullanıcıya göstermeli ve iptal sonrası iade (simüle) kaydını oluşturmalıdır; hesap, rezervasyon oluşturulurken saklanan politika anlık görüntüsüne göre yapılmalıdır (BR-27, BR-28, BR-29). |
| **Gerekçe** | İade hesabı yanlışsa müşteri ile ilan sahibi arasında doğrudan anlaşmazlık doğar; politika sonradan değişirse mevcut rezervasyonların koşulu da değişmemelidir. |
| **Kaynak** | Ders proje tanımı (ders yürütücüsü) |
| **Çakışmalar** | Yok. |
| **Uygunluk Ölçütü** | Orta politikada girişe 5 gün kala %100, 4 gün kala %50; Katı politikada 14 gün kala %50, 13 gün kala %0; Esnek politikada 1 gün kala %100 iade; giriş gününde iptal reddedilir (AC-26). Ödenen 9.523,80 TL ile Orta politikada 3 gün kala iade 4.761,90 TL, kesinti 4.761,90 TL'dir; iade ödenen tutarı aşmaz. İlan politikası sonradan değişse de hesap rezervasyon anındaki politikayla yapılır (AC-28, T-14). |
| **Müşteri Memnuniyeti** | 5 / 5 |
| **Müşteri Memnuniyetsizliği** | 5 / 5 |
| **Destekleyici Materyal** | ReservationController → RefundService; Bölüm 4.11; BR-17, BR-27, BR-28, BR-29; AC-26, AC-27, AC-28; T-14. |
| **Geçmiş** | v2.2: tanımlandı; v2.3: Volere gereksinim kartı biçimine dönüştürüldü. |

### 3.7 İlan Sahibi İşlevleri

| Alan | Değer |
| --- | --- |
| **Gereksinim No** | **FR-20** |
| **Gereksinim Türü** | 9 – Fonksiyonel |
| **Olay / Kullanım Senaryosu** | UC-03 İlan, fiyat ve takvim yönetimi (adım 2) |
| **Öncelik** | Zorunlu |
| **Açıklama** | Sistem, yetkili ilan sahibinin yeni konaklama ilanı oluşturmasına izin vermelidir. |
| **Gerekçe** | Platformun içeriği ilan sahiplerinin girdiği ilanlardan oluşur. |
| **Kaynak** | İlan sahibi / işletme |
| **Çakışmalar** | Yok. |
| **Uygunluk Ölçütü** | İlan sahibi rolündeki kullanıcı yeni ilan oluşturabilir; müşteri rolündeki kullanıcının aynı isteği reddedilir (AC-07, FR-05). |
| **Müşteri Memnuniyeti** | 4 / 5 |
| **Müşteri Memnuniyetsizliği** | 5 / 5 |
| **Destekleyici Materyal** | HostController → PropertyService; UC-03; US-03; AC-07. |
| **Geçmiş** | v2.0: tanımlandı; v2.3: Volere gereksinim kartı biçimine dönüştürüldü. |

| Alan | Değer |
| --- | --- |
| **Gereksinim No** | **FR-21** |
| **Gereksinim Türü** | 9 – Fonksiyonel |
| **Olay / Kullanım Senaryosu** | UC-03 İlan, fiyat ve takvim yönetimi |
| **Öncelik** | Zorunlu |
| **Açıklama** | Sistem, ilan sahibinin yalnızca kendisine ait ilanları düzenlemesine, pasife almasına veya silmesine izin vermelidir. Pasife alma yalnızca yeni rezervasyonları durdurur; gelecekte Onaylandı rezervasyonu bulunan ilan silinemez; silme mantıksaldır (durum = kaldırıldı). |
| **Gerekçe** | Pasife alma veya silme mevcut müşterilerin rezervasyonunu bozmamalı; fiziksel silme geçmiş rezervasyon kayıtlarını bozar (Bölüm 7.1). |
| **Kaynak** | İlan sahibi / işletme |
| **Çakışmalar** | Yok. |
| **Uygunluk Ölçütü** | Başkasının ilanını düzenleme isteği reddedilir (AC-07). Pasife alınan ilanda mevcut Beklemede/Onaylandı rezervasyonlar değişmez ve yeni rezervasyon reddedilir. Gelecekte Onaylandı rezervasyonu olan ilanın silme isteği reddedilir; silinen ilan veritabanında durum "kaldırıldı" ile kalır. |
| **Müşteri Memnuniyeti** | 4 / 5 |
| **Müşteri Memnuniyetsizliği** | 4 / 5 |
| **Destekleyici Materyal** | HostController → PropertyService; AC-07; BR-08; Bölüm 7.1; Bölüm 11.4 (ilan silme). |
| **Geçmiş** | v2.0: tanımlandı; v2.3: Volere gereksinim kartı biçimine dönüştürüldü. |

| Alan | Değer |
| --- | --- |
| **Gereksinim No** | **FR-22** |
| **Gereksinim Türü** | 9 – Fonksiyonel |
| **Olay / Kullanım Senaryosu** | UC-03 İlan, fiyat ve takvim yönetimi (adım 4, 5) |
| **Öncelik** | Zorunlu **· ÇEKİRDEK** |
| **Açıklama** | Sistem, ilan sahibinin tarih aralığına göre özel fiyat ve müsaitlik (açık/kapalı) tanımlamasını sağlamalıdır; Beklemede veya Onaylandı rezervasyonla çakışan geceler kapatılamamalıdır. |
| **Gerekçe** | İlan sahibi bakım, kişisel kullanım veya özel fiyat için geceleri yönetebilmeli; ancak kabul edilmiş rezervasyonu tek taraflı bozmamalıdır. |
| **Kaynak** | İlan sahibi / işletme; Ders proje tanımı (ders yürütücüsü) |
| **Çakışmalar** | Yok. |
| **Uygunluk Ölçütü** | Beklemede/Onaylandı rezervasyonla çakışan geceyi kapatma isteği reddedilir ve ilgili rezervasyon bilgisi gösterilir (AC-18, T-09, CS-08). Rezervasyonsuz kapatılan gece aramada müsait görünmez. Özel gece fiyatı fiyat hesabında takvim istisnası olarak uygulanır (AC-25). |
| **Müşteri Memnuniyeti** | 4 / 5 |
| **Müşteri Memnuniyetsizliği** | 5 / 5 |
| **Destekleyici Materyal** | HostController → AvailabilityService; UC-03; BR-22; CS-08; AC-18; T-09; Bölüm 7 (Takvim İstisnası). |
| **Geçmiş** | v2.0: tanımlandı; v2.3: Volere gereksinim kartı biçimine dönüştürüldü. |

| Alan | Değer |
| --- | --- |
| **Gereksinim No** | **FR-23** |
| **Gereksinim Türü** | 9 – Fonksiyonel |
| **Olay / Kullanım Senaryosu** | UC-05 Rezervasyon talebinin onaylanması / reddedilmesi |
| **Öncelik** | Zorunlu **· ÇEKİRDEK** |
| **Açıklama** | Sistem, ilan sahibinin kendi ilanlarına ait rezervasyonları görüntülemesini ve onaylı modda Beklemede olan talepleri onaylamasını veya reddetmesini sağlamalıdır. Onay yalnızca bekletme süresi dolmadan yapılabilir; ret hâlinde geceler serbest bırakılır. |
| **Gerekçe** | Onaylı modda ilan sahibi kimin konaklayacağına karar verir; yanıtsız talep geceleri süresiz bloke etmemelidir. |
| **Kaynak** | İlan sahibi / işletme |
| **Çakışmalar** | Yok. |
| **Uygunluk Ölçütü** | Onay, bekletme süresi (24 saat) dolmadan yapılabilir ve rezervasyon Onaylandı olur. Süre dolduktan sonra onay reddedilir ("talep süresi doldu") ve rezervasyon Süresi Doldu durumundadır (CS-10). Ret hâlinde rezervasyon Reddedildi olur ve geceler serbest kalır. İlan sahibi başka bir sahibin rezervasyonunu yönetemez (AC-07). |
| **Müşteri Memnuniyeti** | 4 / 5 |
| **Müşteri Memnuniyetsizliği** | 5 / 5 |
| **Destekleyici Materyal** | HostController → ReservationService; UC-05; BR-05, BR-08, BR-15; CS-10; AC-07. |
| **Geçmiş** | v2.0: tanımlandı; v2.3: Volere gereksinim kartı biçimine dönüştürüldü. |

| Alan | Değer |
| --- | --- |
| **Gereksinim No** | **FR-38** |
| **Gereksinim Türü** | 9 – Fonksiyonel |
| **Olay / Kullanım Senaryosu** | UC-03 İlan, fiyat ve takvim yönetimi (adım 3) |
| **Öncelik** | Zorunlu |
| **Açıklama** | Sistem, ilan sahibinin ilan için rezervasyon modunu (anında / onaylı) ve iptal politikasını (esnek / orta / katı) seçmesine izin vermelidir. |
| **Gerekçe** | İlan sahipleri farklı iş modellerine ve risk tercihlerine sahiptir (Bölüm 11.3). |
| **Kaynak** | İlan sahibi / işletme |
| **Çakışmalar** | Yok. |
| **Uygunluk Ölçütü** | İlan sahibi her iki seçimi de yapabilir; seçimler ilan detayında görünür (FR-09). Politika değişikliği yalnızca yeni rezervasyonlara uygulanır; mevcut rezervasyonların iade koşulu değişmez (AC-28). |
| **Müşteri Memnuniyeti** | 4 / 5 |
| **Müşteri Memnuniyetsizliği** | 4 / 5 |
| **Destekleyici Materyal** | HostController → PropertyService; BR-05; BR-17; Bölüm 11.3; AC-28. |
| **Geçmiş** | v2.1: tanımlandı; v2.3: Volere gereksinim kartı biçimine dönüştürüldü. |

| Alan | Değer |
| --- | --- |
| **Gereksinim No** | **FR-42** |
| **Gereksinim Türü** | 9 – Fonksiyonel |
| **Olay / Kullanım Senaryosu** | UC-03 İlan, fiyat ve takvim yönetimi (adım 2) |
| **Öncelik** | Zorunlu |
| **Açıklama** | Sistem, ilan sahibinin ilanı altında ad, oda tipi, kapasite ve temel gecelik fiyatla birim (oda) tanımlamasına izin vermelidir; kiralık ev ilanı tam bir birim, otel ilanı bir veya daha fazla birim içerebilir. |
| **Gerekçe** | Rezervasyon ve çakışma kontrolü tekil kaynak (birim) bazında yapılır; otel odaları ayrı ayrı satılabilmelidir (BR-04, Bölüm 4.2). |
| **Kaynak** | İlan sahibi / işletme; Proje ekibi (mimari) |
| **Çakışmalar** | Yok. |
| **Uygunluk Ölçütü** | Kiralık ev ilanı tam bir birim, otel ilanı bir veya daha fazla birim içerir; birim adı ilan içinde benzersizdir. Örnek veride en az 10 birim ve en az 3 farklı oda tipi bulunur ve arama oda tipine göre filtrelenebilir (AC-29). |
| **Müşteri Memnuniyeti** | 4 / 5 |
| **Müşteri Memnuniyetsizliği** | 5 / 5 |
| **Destekleyici Materyal** | HostController → PropertyService; BR-04; Bölüm 4.2; AC-29; Bölüm 7 (Birim). |
| **Geçmiş** | v2.1: tanımlandı; v2.2: güncellendi; v2.3: Volere gereksinim kartı biçimine dönüştürüldü. |

| Alan | Değer |
| --- | --- |
| **Gereksinim No** | **FR-46** |
| **Gereksinim Türü** | 9 – Fonksiyonel |
| **Olay / Kullanım Senaryosu** | UC-03 İlan, fiyat ve takvim yönetimi (adım 3) |
| **Öncelik** | Gerekli |
| **Açıklama** | Sistem, ilan sahibinin ilan ve birimler için resim yüklemesine, sıralamasına ve silmesine izin vermelidir; yükleme NFR-19'daki güvenlik kurallarına uymalıdır. |
| **Gerekçe** | Fotoğraflar kullanıcının seçim kararında belirleyicidir; yükleme ise saldırı yüzeyi oluşturduğundan güvenlik kurallarına bağlanmıştır. |
| **Kaynak** | İlan sahibi / işletme |
| **Çakışmalar** | Yok. |
| **Uygunluk Ölçütü** | İlan sahibi JPEG/PNG/WebP resim yükleyebilir, sıralayabilir ve silebilir. Farklı tür, 2 MB'tan büyük veya sahte uzantılı dosya ve başkasının ilanına yükleme reddedilir (AC-31). |
| **Müşteri Memnuniyeti** | 4 / 5 |
| **Müşteri Memnuniyetsizliği** | 3 / 5 |
| **Destekleyici Materyal** | HostController → PhotoService; NFR-19; AC-31; Bölüm 7 (Fotoğraf). |
| **Geçmiş** | v2.2: tanımlandı; v2.3: Volere gereksinim kartı biçimine dönüştürüldü. |

### 3.8 Yorum, Favori, Profil, Bildirim ve Şikâyet

| Alan | Değer |
| --- | --- |
| **Gereksinim No** | **FR-19** |
| **Gereksinim Türü** | 9 – Fonksiyonel |
| **Olay / Kullanım Senaryosu** | Müşteri ilanı favoriye ekler / çıkarır |
| **Öncelik** | İsteğe bağlı |
| **Açıklama** | Sistem, kayıtlı kullanıcının ilanları favorilerine eklemesine ve favorilerinden çıkarmasına izin vermelidir. |
| **Gerekçe** | Kullanıcı karar vermeden önce ilanları kısa listeye alıp sonra dönebilmelidir. |
| **Kaynak** | Müşteri (ziyaretçi / kayıtlı müşteri) |
| **Çakışmalar** | Yok. |
| **Uygunluk Ölçütü** | Kayıtlı kullanıcı bir ilanı favorilerine ekleyip çıkarabilir; aynı ilan aynı kullanıcının favorilerine iki kez eklenemez (Bölüm 7, Favori birleşik anahtarı). |
| **Müşteri Memnuniyeti** | 3 / 5 |
| **Müşteri Memnuniyetsizliği** | 2 / 5 |
| **Destekleyici Materyal** | ProfileController → FavoriteService; Bölüm 7 (Favori). |
| **Geçmiş** | v2.0: tanımlandı; v2.3: Volere gereksinim kartı biçimine dönüştürüldü. |

| Alan | Değer |
| --- | --- |
| **Gereksinim No** | **FR-24** |
| **Gereksinim Türü** | 9 – Fonksiyonel |
| **Olay / Kullanım Senaryosu** | Müşteri tamamlanan konaklamayı değerlendirir |
| **Öncelik** | Gerekli |
| **Açıklama** | Sistem, tamamlanmış ve doğrulanmış konaklamalar için müşterinin puan ve yorum göndermesine izin vermelidir. |
| **Gerekçe** | Puan ve yorumlar diğer kullanıcıların kararını destekler; yalnızca gerçek konaklamalara bağlı olmaları güvenilirliği korur. |
| **Kaynak** | Müşteri (ziyaretçi / kayıtlı müşteri) |
| **Çakışmalar** | Yok. |
| **Uygunluk Ölçütü** | Yalnızca Tamamlandı durumundaki rezervasyon için, rezervasyon başına en çok bir yorum ve 1–5 arası tam sayı puan kabul edilir; diğer durumlar için yorum reddedilir (BR-09, BR-21). |
| **Müşteri Memnuniyeti** | 3 / 5 |
| **Müşteri Memnuniyetsizliği** | 3 / 5 |
| **Destekleyici Materyal** | ReviewController → ReviewService; BR-09; BR-21; Bölüm 7 (Yorum). |
| **Geçmiş** | v2.0: tanımlandı; v2.3: Volere gereksinim kartı biçimine dönüştürüldü. |

| Alan | Değer |
| --- | --- |
| **Gereksinim No** | **FR-25** |
| **Gereksinim Türü** | 9 – Fonksiyonel |
| **Olay / Kullanım Senaryosu** | Yönetici yorumları denetler |
| **Öncelik** | Gerekli |
| **Açıklama** | Sistem, yöneticinin kullanım kurallarını ihlal eden yorumları incelemesine ve gerekli işlemi kaydetmesine izin vermelidir. |
| **Gerekçe** | Uygunsuz yorumlar platformun güvenilirliğini ve ilan sahibinin itibarını zedeler. |
| **Kaynak** | Yönetici |
| **Çakışmalar** | Yok. |
| **Uygunluk Ölçütü** | Yönetici bir yorumun denetim durumunu değiştirebilir; işlem neden ve zamanla kaydedilir (BR-10). Yönetici dışındaki roller bu işlemi yapamaz. |
| **Müşteri Memnuniyeti** | 3 / 5 |
| **Müşteri Memnuniyetsizliği** | 3 / 5 |
| **Destekleyici Materyal** | AdminController → ReviewService; BR-10; Bölüm 7 (Yorum: denetim durumu). |
| **Geçmiş** | v2.0: tanımlandı; v2.3: Volere gereksinim kartı biçimine dönüştürüldü. |

| Alan | Değer |
| --- | --- |
| **Gereksinim No** | **FR-26** |
| **Gereksinim Türü** | 9 – Fonksiyonel |
| **Olay / Kullanım Senaryosu** | UC-02 / UC-04 / UC-05 Bildirim üretimi |
| **Öncelik** | Gerekli |
| **Açıklama** | Sistem; rezervasyon oluşturma, onaylama, reddetme, iptal, süresi dolma ve durum değişikliği gibi önemli işlemler için ilgili kullanıcıya uygulama içi bildirim göstermelidir. |
| **Gerekçe** | Kullanıcı ve ilan sahibi, kendilerini ilgilendiren durum değişikliklerinden zamanında haberdar olmalıdır. |
| **Kaynak** | Müşteri (ziyaretçi / kayıtlı müşteri); İlan sahibi / işletme |
| **Çakışmalar** | Yok. |
| **Uygunluk Ölçütü** | Sayılan olayların her birinde ilgili kullanıcı için bir bildirim kaydı oluşur ve kullanıcının bildirim listesinde görünür (UC-02 adım 6, UC-04 adım 5, UC-05 adım 4). |
| **Müşteri Memnuniyeti** | 3 / 5 |
| **Müşteri Memnuniyetsizliği** | 3 / 5 |
| **Destekleyici Materyal** | ReservationController → NotificationService; UC-02, UC-04, UC-05; Bölüm 7 (Bildirim). |
| **Geçmiş** | v2.0: tanımlandı; v2.3: Volere gereksinim kartı biçimine dönüştürüldü. |

| Alan | Değer |
| --- | --- |
| **Gereksinim No** | **FR-27** |
| **Gereksinim Türü** | 9 – Fonksiyonel |
| **Olay / Kullanım Senaryosu** | Kullanıcı şikâyet oluşturur |
| **Öncelik** | Gerekli |
| **Açıklama** | Sistem, kullanıcının ilan veya rezervasyon hakkında şikâyet oluşturmasını sağlamalıdır. |
| **Gerekçe** | Kullanıcılar sorunlu ilan veya rezervasyonları yöneticiye iletebilmelidir. |
| **Kaynak** | Müşteri (ziyaretçi / kayıtlı müşteri) |
| **Çakışmalar** | Yok. |
| **Uygunluk Ölçütü** | Kullanıcı bir ilan veya rezervasyon için şikâyet gönderdiğinde kayıt; gönderen, ilgili ilan/rezervasyon, açıklama ve durumla oluşur ve yönetici panelinde listelenir (Bölüm 5.1). |
| **Müşteri Memnuniyeti** | 3 / 5 |
| **Müşteri Memnuniyetsizliği** | 3 / 5 |
| **Destekleyici Materyal** | ComplaintController → ComplaintService; Bölüm 5.1; Bölüm 7 (Şikâyet). |
| **Geçmiş** | v2.0: tanımlandı; v2.3: Volere gereksinim kartı biçimine dönüştürüldü. |

| Alan | Değer |
| --- | --- |
| **Gereksinim No** | **FR-31** |
| **Gereksinim Türü** | 9 – Fonksiyonel |
| **Olay / Kullanım Senaryosu** | Kullanıcı profilini yönetir |
| **Öncelik** | Gerekli |
| **Açıklama** | Sistem, kullanıcının kişisel profil bilgilerini görüntülemesine ve izin verilen alanları güncellemesine olanak tanımalıdır. |
| **Gerekçe** | Kullanıcı kendi bilgilerini güncel tutabilmeli; ancak rol gibi yetki alanları değiştirilememelidir. |
| **Kaynak** | Müşteri (ziyaretçi / kayıtlı müşteri) |
| **Çakışmalar** | Yok. |
| **Uygunluk Ölçütü** | Kullanıcı profilini görür ve yalnızca izin verilen alanları günceller; izin verilmeyen bir alanı (ör. rol) değiştirme isteği reddedilir. Güncellenebilir alanların listesi Bölüm 11.5'te açık karardır. |
| **Müşteri Memnuniyeti** | 3 / 5 |
| **Müşteri Memnuniyetsizliği** | 3 / 5 |
| **Destekleyici Materyal** | ProfileController → UserService; NFR-03; Bölüm 11.5 (açık karar). |
| **Geçmiş** | v2.0: tanımlandı; v2.3: Volere gereksinim kartı biçimine dönüştürüldü. |

| Alan | Değer |
| --- | --- |
| **Gereksinim No** | **FR-32** |
| **Gereksinim Türü** | 9 – Fonksiyonel |
| **Olay / Kullanım Senaryosu** | UC-02 / UC-05 İletişim bilgisine erişim |
| **Öncelik** | Gerekli |
| **Açıklama** | Sistem, rezervasyon için gerekli iletişim bilgilerini göstermeli ve bu bilgilere erişimi yetki ve gizlilik kurallarıyla sınırlandırmalıdır. |
| **Gerekçe** | İlan sahibi ve müşteri konaklamayı koordine edebilmeli; üçüncü kişiler kişisel veriye erişmemelidir. |
| **Kaynak** | İlan sahibi / işletme; Müşteri (ziyaretçi / kayıtlı müşteri) |
| **Çakışmalar** | Yok. |
| **Uygunluk Ölçütü** | Rezervasyonun iletişim bilgilerini yalnızca rezervasyonun müşterisi ve ilgili ilanın sahibi görür; başka bir müşteri veya başka ilanın sahibi erişemez (AC-06, AC-07, NFR-04). |
| **Müşteri Memnuniyeti** | 3 / 5 |
| **Müşteri Memnuniyetsizliği** | 4 / 5 |
| **Destekleyici Materyal** | ReservationController → ReservationService; NFR-04; AC-06, AC-07. |
| **Geçmiş** | v2.0: tanımlandı; v2.3: Volere gereksinim kartı biçimine dönüştürüldü. |

### 3.9 Yönetim, Raporlama ve Denetim

| Alan | Değer |
| --- | --- |
| **Gereksinim No** | **FR-28** |
| **Gereksinim Türü** | 9 – Fonksiyonel |
| **Olay / Kullanım Senaryosu** | Yönetici ilanı inceler / yayından kaldırır |
| **Öncelik** | Gerekli |
| **Açıklama** | Sistem, yöneticinin ilanları incelemesine, yayından kaldırmasına ve işlem nedenini kaydetmesine izin vermelidir. |
| **Gerekçe** | Uygunsuz veya kurallara aykırı ilanlar platformdan kaldırılabilmelidir; neden kaydı hesap verebilirlik sağlar (BR-10). |
| **Kaynak** | Yönetici |
| **Çakışmalar** | Yok. |
| **Uygunluk Ölçütü** | Yönetici bir ilanı nedenini girerek yayından kaldırabilir; nedensiz kaldırma reddedilir (BR-10). Kaldırılan ilan aramada çıkmaz ve yeni rezervasyon almaz (AC-09). |
| **Müşteri Memnuniyeti** | 3 / 5 |
| **Müşteri Memnuniyetsizliği** | 4 / 5 |
| **Destekleyici Materyal** | AdminController → PropertyService; BR-10; AC-09. |
| **Geçmiş** | v2.0: tanımlandı; v2.3: Volere gereksinim kartı biçimine dönüştürüldü. |

| Alan | Değer |
| --- | --- |
| **Gereksinim No** | **FR-29** |
| **Gereksinim Türü** | 9 – Fonksiyonel |
| **Olay / Kullanım Senaryosu** | Yönetici kullanıcı hesaplarını yönetir |
| **Öncelik** | Gerekli |
| **Açıklama** | Sistem, yöneticinin kullanıcı hesaplarını görüntülemesine ve politika ihlallerinde hesap durumunu yönetmesine izin vermelidir. |
| **Gerekçe** | Politika ihlali yapan kullanıcılar platformu kullanmaktan alıkonulabilmelidir. |
| **Kaynak** | Yönetici |
| **Çakışmalar** | Yok. |
| **Uygunluk Ölçütü** | Yönetici hesapları listeleyebilir ve hesap durumunu değiştirebilir; her durum değişikliği neden ve zamanla denetim kaydına yazılır (BR-10, FR-41). Yönetici dışındaki roller bu işlemi yapamaz. |
| **Müşteri Memnuniyeti** | 3 / 5 |
| **Müşteri Memnuniyetsizliği** | 3 / 5 |
| **Destekleyici Materyal** | AdminController → UserService; BR-10; FR-41; Bölüm 7 (Kullanıcı: hesap durumu). |
| **Geçmiş** | v2.0: tanımlandı; v2.3: Volere gereksinim kartı biçimine dönüştürüldü. |

| Alan | Değer |
| --- | --- |
| **Gereksinim No** | **FR-30** |
| **Gereksinim Türü** | 9 – Fonksiyonel |
| **Olay / Kullanım Senaryosu** | Yönetici / ilan sahibi rapor görüntüler |
| **Öncelik** | Gerekli |
| **Açıklama** | Sistem, yöneticinin (tüm ilanlar) ve ilan sahibinin (kendi ilanları) seçilen tarih aralığı için doluluk oranı, net gelir, rezervasyon sayısı ve durum dağılımını ilan, birim, oda tipi ve ay kırılımında görüntülemesini sağlamalıdır; formüller BR-30'dadır. |
| **Gerekçe** | İlan sahibi performansını, yönetici ise platformu izleyebilmelidir; hatalı formül yanlış iş kararlarına yol açar. |
| **Kaynak** | İlan sahibi / işletme; Yönetici; Ders proje tanımı (ders yürütücüsü) |
| **Çakışmalar** | Yok. |
| **Uygunluk Ölçütü** | Bilinen test verisiyle üretilen doluluk oranı ve net gelir, BR-30 formüllerinin elle hesaplanmış sonuçlarıyla birebir aynıdır; yönetici tüm ilanları, ilan sahibi yalnızca kendi ilanlarını görür (AC-30, T-15). |
| **Müşteri Memnuniyeti** | 4 / 5 |
| **Müşteri Memnuniyetsizliği** | 3 / 5 |
| **Destekleyici Materyal** | AdminController → ReportService; BR-30; AC-30; T-15. |
| **Geçmiş** | v2.0: tanımlandı; v2.2: güncellendi; v2.3: Volere gereksinim kartı biçimine dönüştürüldü. |

| Alan | Değer |
| --- | --- |
| **Gereksinim No** | **FR-39** |
| **Gereksinim Türü** | 9 – Fonksiyonel |
| **Olay / Kullanım Senaryosu** | Yönetici ilanı kaldırır (UC-04 yönetici iptali) |
| **Öncelik** | Zorunlu **· ÇEKİRDEK** |
| **Açıklama** | Sistem, yönetici bir ilanı yayından kaldırdığında ilanın gelecekteki Beklemede/Onaylandı rezervasyonlarını iptal nedeni kaydıyla iptal etmeli, gecelerini serbest bırakmalı ve etkilenen müşterilere bildirim göndermelidir. |
| **Gerekçe** | Kaldırılan ilanın hayalet rezervasyonları kalırsa müşteri var olmayan bir konaklamaya gider; geceler bloke kalır. |
| **Kaynak** | Yönetici; Ders proje tanımı (ders yürütücüsü) |
| **Çakışmalar** | Yok. |
| **Uygunluk Ölçütü** | İlan kaldırıldığında tüm gelecek Beklemede/Onaylandı rezervasyonlar iptal nedeniyle İptal Edildi olur, geceleri serbest kalır, ödenen tutarın %100'ü iade edilir (BR-28) ve etkilenen her müşteri için bildirim oluşur. Kaldırma sonrası gelen yeni rezervasyon isteği reddedilir (AC-09, AC-27, CS-11). |
| **Müşteri Memnuniyeti** | 4 / 5 |
| **Müşteri Memnuniyetsizliği** | 5 / 5 |
| **Destekleyici Materyal** | AdminController → ReservationService; BR-10, BR-28; CS-11; AC-09, AC-27. |
| **Geçmiş** | v2.1: tanımlandı; v2.3: Volere gereksinim kartı biçimine dönüştürüldü. |

| Alan | Değer |
| --- | --- |
| **Gereksinim No** | **FR-41** |
| **Gereksinim Türü** | 9 – Fonksiyonel |
| **Olay / Kullanım Senaryosu** | Yönetici işlemi / durum geçişi gerçekleşir |
| **Öncelik** | Gerekli |
| **Açıklama** | Sistem, BR-10 kapsamındaki yönetici işlemlerini ve tüm rezervasyon durum geçişlerini (kim, ne zaman, eski/yeni durum, neden) denetim kaydına yazmalıdır. |
| **Gerekçe** | Anlaşmazlıkların ve olası tutarsızlıkların izi sürülebilmelidir. |
| **Kaynak** | Yönetici; Proje ekibi (mimari) |
| **Çakışmalar** | Yok. |
| **Uygunluk Ölçütü** | BR-10 kapsamındaki her yönetici işlemi ve her durum geçişi için; işlemi yapan, zaman, eski/yeni değer ve neden alanlarını içeren bir denetim kaydı oluşur. Sistem işlemlerinde işlemi yapan alanı boştur (Bölüm 7, Denetim Kaydı). |
| **Müşteri Memnuniyeti** | 3 / 5 |
| **Müşteri Memnuniyetsizliği** | 3 / 5 |
| **Destekleyici Materyal** | AdminController, ReservationService → AuditService; BR-10; NFR-18; Bölüm 7 (Denetim Kaydı). |
| **Geçmiş** | v2.1: tanımlandı; v2.3: Volere gereksinim kartı biçimine dönüştürüldü. |

## 4. Çekirdek Problemler
Ders proje tanımında çekirdek problem dört parça olarak verilmiştir. Bu bölüm her birini tasarım ve test için yeterince kesin tanımlar. ÇEKİRDEK etiketli gereksinimler (FR-06, 10, 11, 12, 14, 15, 17, 18, 22, 23, 33–36, 39, 43–45), iş kuralları ve kabul kriterleri bu bölümdeki kurallara dayanır.

| Kod | Çekirdek problem (proje tanımı) | Bu belgede |
| --- | --- | --- |
| **ÇP-1** | Tarih aralığı çakışması (giriş–çıkış sınırı: bir misafir 10'unda çıkıyorsa başka misafir aynı gün girebilir) | Bölüm 4.2, 4.3, 4.4 |
| **ÇP-2** | Sezonluk dinamik fiyat (hafta sonu, yüksek sezon, uzun konaklama indirimi) | Bölüm 4.10 |
| **ÇP-3** | İptal politikası ve iade hesabı | Bölüm 4.11 |
| **ÇP-4** | Çift rezervasyonun önlenmesi | Bölüm 4.1, 4.5, 4.6, 4.8, 4.9 |

### 4.1 Problem Tanımı
**Çift rezervasyon**, aynı birim için konaklama geceleri kesişen iki veya daha fazla rezervasyonun aynı anda engelleyen durumda (Beklemede veya Onaylandı) bulunmasıdır. Bir rezervasyon platformunda bu hata, müşterinin konaksız kalmasına, ev sahibinin güvenini yitirmesine ve platformun varlık nedeninin ortadan kalkmasına yol açar. Bu nedenle sistemin en kötü senaryosu "arama yavaş" değil, "iki kişiye aynı oda satıldı" durumudur.

**Başarı ölçütü:** Her koşulda — eş zamanlı istekler, çift gönderim, süre aşımı, iptal ve ev sahibi takvim değişiklikleri dahil — aynı birim ve aynı gece için en fazla bir engelleyen rezervasyon bulunur. Bu ölçütün ihlali, ne kadar nadir olursa olsun, projenin kritik hatasıdır.

**Kapsam içi:** arama ile müsaitlik gösterimi, rezervasyon oluşturma, bekletme süresi, onay/ret, iptal, süre dolumu, ev sahibinin tarih kapatması, ilanın kaldırılması. **Kapsam dışı:** bilinçli aşırı satış (overbooking) politikaları ve harici kanal yöneticisi senkronizasyonu (Bölüm 11.2).

### 4.2 Temel Kavramlar ve Çakışma Tanımı
Çakışma tanımı tek ve kesin olmalıdır; sınır günü hataları (off-by-one) bu tür sistemlerin en yaygın hatasıdır.

- **Birim:** çakışma yalnızca aynı birim içinde aranır. Aynı otelin iki farklı odası birbirini engellemez (Bölüm 11.3: tekil kaynak bazlı yönetim).
- **Konaklama aralığı yarı açık [giriş, çıkış)** olarak yorumlanır. n = çıkış − giriş gece tutulur; çıkış günü gece sayılmaz.
- **Çakışma:** aynı birimde, her ikisi de engelleyen durumda olan A ve B rezervasyonları için A.giriş < B.çıkış **ve** B.giriş < A.çıkış ise çakışma vardır.
- **Arka arkaya konaklama serbesttir:** bir rezervasyonun çıkış günü, başka bir rezervasyonun giriş günü olabilir (BR-13).

#### Örnek: Birimde 10–12 Kasım 2026 aralığında Onaylandı rezervasyon var (geceler: 10 ve 11 Kasım)

| Yeni istek | Çakışma? | Gerekçe |
| --- | --- | --- |
| **12–14 Kasım** | Hayır | Arka arkaya: mevcut çıkış günü = yeni giriş günü. Kabul edilir. |
| **8–10 Kasım** | Hayır | Yeni çıkış günü = mevcut giriş günü. Kabul edilir. |
| **9–11 Kasım** | Evet | 10 Kasım gecesi ortak. |
| **11–13 Kasım** | Evet | 11 Kasım gecesi ortak. |
| **10–12 Kasım** | Evet | Aynı aralık; tüm geceler ortak. |
| **9–13 Kasım** | Evet | Yeni aralık mevcut olanı kapsıyor. |
| **10–11 Kasım** | Evet | Yeni aralık mevcut olanın içinde (10 Kasım gecesi). |
| **12–12 Kasım** | Geçersiz | Sıfır gece; BR-02 gereği tarih doğrulamasında reddedilir (çakışma kontrolüne ulaşmaz). |

### 4.3 Rezervasyon Durum Makinesi
Durumlar ve geçişler tek bir yerde (ReservationService, State deseni önerilir) yönetilir. Engelleyen durumlar gecelerini tutar; diğerleri tutmaz.

| Durum | Anlamı | Geceleri tutar mı? | Çıkabileceği durumlar |
| --- | --- | --- | --- |
| **Beklemede** | Kayıt oluştu; anında modda ödeme, onaylı modda ev sahibi onayı bekleniyor. expires_at vardır. | **Evet** (süre dolana kadar) | Onaylandı, Reddedildi, İptal Edildi, Süresi Doldu |
| **Onaylandı** | Kesinleşmiş rezervasyon. | **Evet** | İptal Edildi, Tamamlandı |
| **İptal Edildi** | Müşteri veya yönetici iptal etti. | Hayır | — (son durum) |
| **Reddedildi** | İlan sahibi onaylı moddaki talebi reddetti. | Hayır | — (son durum) |
| **Süresi Doldu** | Bekletme süresi içinde ödeme/onay gerçekleşmedi. | Hayır | — (son durum) |
| **Tamamlandı** | Çıkış tarihi geçti. | Hayır (geceler geçmişte) | — (son durum) |

| Geçiş | Tetikleyen | Koşul | Geceler üzerindeki etki |
| --- | --- | --- | --- |
| **(yok) → Beklemede** | Müşteri | Bölüm 4.4 kuralının tümü sağlanır | Tüm geceler aynı işlemde ayrılır |
| **Beklemede → Onaylandı** | Sistem (başarılı simüle ödeme) veya ilan sahibi (onay) | expires_at geçmemiş | Geceler korunur |
| **Beklemede → Reddedildi** | İlan sahibi | Onaylı mod | Geceler serbest bırakılır |
| **Beklemede → Süresi Doldu** | Sistem (zamanlanmış görev veya çakışma kontrolü sırasında) | expires_at geçmiş | Geceler serbest bırakılır |
| **Beklemede / Onaylandı → İptal Edildi** | Müşteri veya yönetici (FR-17, FR-39) | Müşteri için BR-18; yönetici için neden zorunlu | Geceler aynı işlemde serbest bırakılır |
| **Onaylandı → Tamamlandı** | Sistem (FR-40) | Çıkış tarihi geçmiş | Gece kayıtları silinir (geçmiş) |

Son durumlardan hiçbir geçiş yapılamaz. Tabloda olmayan her geçiş reddedilir ve denetim kaydına yazılır (FR-15, FR-41).

### 4.4 Müsaitlik ve Çakışma Karar Kuralı
Bir (birim, giriş, çıkış, kişi sayısı) isteği **ancak aşağıdaki koşulların tümü sağlanırsa** kabul edilir; biri sağlanmazsa hiçbir kayıt oluşturulmaz:

1. Tarih aralığı geçerlidir: giriş < çıkış, giriş bugünden önce değil, en çok 30 gece, en çok 365 gün ileri (BR-01, BR-02, BR-14).
1. İlan aktiftir (pasif veya kaldırılmış değil) ve birim aktiftir.
1. Kişi sayısı birim kapasitesini aşmaz (BR-03).
1. Aralıktaki hiçbir gece ilan sahibi tarafından kapatılmamıştır (BR-22).
1. Aralıktaki hiçbir gece, engelleyen durumdaki (Beklemede ve expires_at geçmemiş, veya Onaylandı) başka bir rezervasyona ait değildir (BR-12).

**Tek kural, tek kod yolu:** Arama (FR-06), takvim gösterimi (FR-10) ve rezervasyon oluşturma (FR-11) bu kuralı aynı sınıf üzerinden kullanır. Böylece "aramada görünen ama rezervasyonda reddedilen" tutarsızlık yapısal olarak önlenir. Arama sonucu yalnızca bir ön bilgidir; kesin karar her zaman rezervasyon anında, atomik işlem içinde yeniden verilir (FR-11).

### 4.5 Eşzamanlılık Gereksinimleri
Uygulama kodunda "önce kontrol et, sonra kaydet" yapmak tek başına yeterli değildir: iki istek aynı anda kontrolü geçip ikisi de kaydedebilir (yarış durumu). Bu nedenle üç katmanlı güvence zorunludur:

| Güvence katmanı | Gereksinim | Gerekçe |
| --- | --- | --- |
| **1. Uygulama (Service)** | BookingConflictService, Bölüm 4.4 kuralını uygular, anlaşılır hata ve alternatifler üretir. | Kullanıcıya doğru mesaj; ortak kural kopyası. |
| **2. İşlem (transaction)** | Yeniden doğrulama, rezervasyon kaydı ve gece kayıtları tek veritabanı işleminde yapılır. İşlem başında ilgili birim satırı kilitlenir (ör. SELECT … FOR UPDATE), böylece aynı birime yönelik istekler sıraya girer. | Kontrol ile yazma arasındaki boşluğu kapatır; FR-33 atomikliği. |
| **3. Veritabanı kısıtı (son savunma)** | Gece tablosunda (birim, gece) çifti için UNIQUE kısıtı bulunur (Bölüm 7). Kısıt ihlalinde işlem geri alınır ve "çakışma" hatası döner. | Uygulama kodunda hata olsa bile çift kaydı veri düzeyinde reddeder. |
| **Aşama 2 (JSON depo)** | Okuma–kontrol–yazma adımları dosya kilidi (flock, LOCK_EX) altında tek kritik bölümde yapılır; yazma önce geçici dosyaya, sonra yeniden adlandırarak yapılır. | Dosya tabanlı depoda aynı garantiyi sağlamak; dosya bozulmasını önlemek. |
| **Kilit zaman aşımı / kilitlenme** | En çok 2 yeniden deneme; sonra işlem geri alınır ve "yoğunluk, lütfen tekrar deneyin" mesajı gösterilir. Hiçbir durumda yarım kayıt bırakılmaz. | NFR-13; FR-33. |
| **Çift gönderim** | Rezervasyon formu tek kullanımlık idempotency anahtarı taşır; aynı anahtarla gelen ikinci istek mevcut rezervasyonu döndürür. | FR-35. |

**Süresi dolmuş bekletmeler:** Çakışma kontrolü, aynı işlem içinde ilgili birimin süresi dolmuş Beklemede kayıtlarını Süresi Doldu durumuna alıp gecelerini serbest bırakmalıdır (lazy expiry). Zamanlanmış görev bu işlemi toplu olarak da yapar, ancak doğruluk görevin zamanında çalışmasına bağlı olamaz (FR-34).

### 4.6 Kritik Senaryolar ve Beklenen Davranış

| Kod | Senaryo | Beklenen davranış |
| --- | --- | --- |
| **CS-01** | İki müşteri aynı birim ve aynı aralık için aynı anda rezervasyon gönderir. | Tam olarak biri başarılı olur; diğeri "tarihler artık müsait değil" mesajı ve alternatiflerle reddedilir; kısmi kayıt kalmaz. |
| **CS-02** | İki müşteri kısmen kesişen aralıkları (10–12 ve 11–13) aynı anda gönderir. | Tam olarak biri başarılı olur. |
| **CS-03** | İki müşteri arka arkaya aralıkları (10–12 ve 12–14) aynı anda gönderir. | İkisi de başarılı olur (BR-13). |
| **CS-04** | Müşteri fiyat ekranında beklerken başka müşteri aynı geceleri alır; ilk müşteri onaylar. | Onay anındaki yeniden doğrulama (FR-11) rezervasyonu reddeder; eski ekran bilgisi geçerli sayılmaz. |
| **CS-05** | Bir Beklemede kaydın süresi dolmuş ama zamanlanmış görev henüz çalışmamıştır; başka müşteri aynı geceleri ister. | Eski kayıt işlem içinde Süresi Doldu yapılır ve yeni rezervasyon kabul edilir (lazy expiry). |
| **CS-06** | Müşteri çift tıklar, sayfayı yeniler veya geri tuşuyla formu tekrar gönderir. | Tek rezervasyon oluşur; ikinci gönderim mevcut kaydı gösterir (FR-35). |
| **CS-07** | Bir rezervasyon iptal edilirken başka müşteri aynı geceleri ister. | İptal işlemi tamamlanmadan istek reddedilir, tamamlandıktan sonra kabul edilir; hiçbir anda iki engelleyen kayıt olmaz. |
| **CS-08** | İlan sahibi bir geceyi kapatırken müşteri aynı gece için rezervasyon yapar. | İşlemler aynı birim kilidiyle sıralanır: biri başarılı olur. Kapatma, mevcut Beklemede/Onaylandı rezervasyonla çakışıyorsa reddedilir (FR-22). |
| **CS-09** | Anında modda simüle ödeme başarısız olur. | Rezervasyon Onaylandı olmaz; Beklemede kalır, süre dolana dek yeniden denenebilir; süre dolunca Süresi Doldu olur ve geceler serbest kalır (FR-37). |
| **CS-10** | Onaylı modda ilan sahibi, 24 saatlik süre dolduktan sonra onay vermeye çalışır. | Onay reddedilir ("talep süresi doldu"); rezervasyon Süresi Doldu durumundadır (FR-23). |
| **CS-11** | Yönetici ilanı kaldırırken müşteri aynı ilan için rezervasyon oluşturur. | İlan durumu işlem içinde yeniden okunur; kaldırma tamamlandıktan sonra gelen istek reddedilir. Mevcut gelecek rezervasyonlar FR-39 uyarınca iptal edilir. |
| **CS-12** | Rezervasyon kaydı yazıldıktan sonra gece kaydı eklenirken hata oluşur veya sunucu kesilir. | Tüm işlem geri alınır; "rezervasyon var ama geceler yok" veya tersi tutarsızlık oluşmaz (FR-33). |

### 4.7 Çekirdek Problemin Mimari Eşlemesi
Çekirdek problem, kılavuzdaki ilkeye uygun olarak tek bir servis sınıfına, problemle ilişkili bir adla verilir: **BookingConflictService**. Aşağıdaki sınıf listesi, tasarım belgesindeki (docs/tasarim.md) sınıf diyagramına temel oluşturur.

| Sınıf | Katman | Çekirdek sorumluluk |
| --- | --- | --- |
| **ReservationController** | Controller | Formu ve idempotency anahtarını alır, yalnızca ReservationService'i çağırır; çakışma hatasında formu mesaj ve alternatiflerle yeniden gösterir. SQL yazmaz, çakışma hesabı yapmaz. |
| **ReservationService** | Service | Rezervasyon akışını yönetir: PricingService ile fiyat, BookingConflictService ile çakışma, PaymentService ile ödeme, NotificationService ile bildirim; durum geçişlerini uygular. |
| **BookingConflictService** | Service (çekirdek) | Bölüm 4.4 kuralını uygular: assertAvailable(), reserveNights(), releaseNights(), süresi dolmuş bekletmeleri temizleme ve alternatif aralık önerme. Çakışma kuralının tek sahibidir. |
| **AvailabilityService** | Service | Arama ve takvim için müsait birimleri hesaplar; kuralı kendisi yazmaz, BookingConflictService üzerinden kullanır. |
| **PricingService** | Service (Strategy) | Gece bazlı fiyat hesabı (Bölüm 4.10): PricingStrategyInterface; BaseRateStrategy, SeasonalStrategy, WeekendStrategy, LongStayDiscountStrategy; vergi/ek ücret kalemleri. Yan etkisizdir; aynı girdi aynı sonucu verir. |
| **RefundService** | Service (Strategy) | İade hesabı (Bölüm 4.11): CancellationPolicyInterface; FlexiblePolicy, ModeratePolicy, StrictPolicy. Politika, rezervasyondaki anlık görüntüden okunur. |
| **ReportService** | Service | Doluluk ve net gelir hesabı (BR-30); rezervasyon ve ödeme verisini Repository arayüzleri üzerinden okur. |
| **ReservationState ve alt durumlar** | Service (State) | PendingState, ConfirmedState, CancelledState, RejectedState, ExpiredState, CompletedState; izinli geçişleri Bölüm 4.3'e göre denetler. |
| **ReservationRepositoryInterface** | Repository | findOverlapping(), createWithNights() (atomik yazma sözleşmesi), releaseNights(), findExpiredHolds(), findByIdempotencyKey(). İş kuralı içermez; JsonReservationRepository ve MySqlReservationRepository uygular. |
| **Reservation, ReservationNight, BookableUnit, CalendarEntry** | Model | Veriyi taşıyan sade sınıflar; veritabanına erişmez. |

- **Kontrol sorusu:** "Veritabanını değiştirsem hangi dosyalar değişir?" — yalnızca Repository sınıfları. Çakışma kuralı ve durum geçişleri değişmez.
- **Tasarım desenleri (öneri):** Repository (zorunlu); State (rezervasyon durum makinesi); Strategy (fiyat hesabı ve iptal/iade politikaları). Desenlerin dört cümlelik gerekçesi docs/tasarim.md 5. bölümde yazılacaktır.
- **Katman atlama yasağı:** Controller'dan Repository'ye doğrudan ok yoktur; çakışma sorgusu Controller'da veya View'da yapılmaz.

### 4.8 Çekirdek Problem Test Gereksinimleri

| Kod | Test | Tür | Beklenen sonuç |
| --- | --- | --- | --- |
| **T-01** | Bölüm 4.2 örnek tablosundaki 8 durum (veri güdümlü). | Birim (PHPUnit) | Tablodaki Evet/Hayır/Geçersiz sonuçları birebir. |
| **T-02** | Tüm geçerli ve geçersiz durum geçişleri (Bölüm 4.3). | Birim | Geçersiz geçişler reddedilir; geçerlilerde geceler doğru serbest kalır/korunur. |
| **T-03** | Süresi dolmuş bekletme varken yeni istek (CS-05). | Birim / entegrasyon | Eski kayıt Süresi Doldu olur, yeni rezervasyon kabul edilir. |
| **T-04** | Aynı idempotency anahtarıyla iki gönderim (CS-06). | Birim | Tek rezervasyon kaydı. |
| **T-05** | 50 paralel istek: aynı birim, aynı aralık (CS-01). | Eşzamanlılık | Tam 1 başarı, 49 çakışma hatası; gece kaydı sayısı = gece sayısı. |
| **T-06** | Paralel kısmi kesişen (CS-02) ve arka arkaya (CS-03) istekler. | Eşzamanlılık | CS-02: tek başarı; CS-03: iki başarı. |
| **T-07** | Gece kaydı eklenirken hata enjekte edilir (CS-12). | Entegrasyon | Rezervasyon kaydı da geri alınır. |
| **T-08** | Arama–rezervasyon tutarlılığı: aramada çıkan birim rezervasyonda kabul edilir; dolu/kapalı birim aramada çıkmaz. | Entegrasyon | Aynı kural iki yolda da aynı sonucu verir. |
| **T-09** | İlan sahibi, rezervasyonlu geceyi kapatmaya çalışır (CS-08). | Birim | Kapatma reddedilir. |
| **T-10** | JSON depoda iki süreçten eşzamanlı yazma (Aşama 2). | Eşzamanlılık | Dosya bozulmaz; çift kayıt oluşmaz. |
| **T-11** | Tutarlılık denetim sorgusu: aynı (birim, gece) için birden fazla engelleyen kayıt. | Denetim | Tüm testlerden sonra sonuç boş küme. |

Hedef: çekirdek servis sınıflarında en az %90 satır kapsamı (NFR-16). Eşzamanlılık testleri, Aşama 3'te veritabanı kısıtının gerçekten devrede olduğunu doğrulamak için en az bir kez uygulama kontrolü devre dışı bırakılarak da çalıştırılmalıdır.

### 4.9 Çekirdek Probleme İlişkin Riskler
Genel proje riskleri docs/tasarim.md 7. bölümde listelenecektir; aşağıdakiler çekirdek probleme özgüdür.

| Risk | Olasılık | Etki | Önlem |
| --- | --- | --- | --- |
| Eş zamanlı isteklerde "kontrol et, sonra kaydet" yarışı nedeniyle çift rezervasyon oluşması | Orta | Yüksek | Transaction + birim satırı kilidi + UNIQUE(birim, gece) kısıtı; T-05 / T-06 paralel testleri. |
| Sınır günü hatası (çıkış günü gece sayılıp sayılmaması) | Yüksek | Yüksek | Yarı açık aralık tek tanımı; veri güdümlü T-01 testi; tüm kodda tek karşılaştırma fonksiyonu. |
| Arama ile rezervasyonun farklı kural uygulaması | Orta | Orta | Kuralın tek kopyası (BookingConflictService); T-08. |
| Süresi dolmuş "hayalet" bekletmelerin geceleri gereksiz yere bloke etmesi | Orta | Orta | Lazy expiry + zamanlanmış görev; T-03. |
| Aşama 2 JSON deposunda eşzamanlı yazmayla dosya bozulması veya çift kayıt | Orta | Yüksek | flock(LOCK_EX), geçici dosyaya yazıp yeniden adlandırma; T-10. |
| Yapay zekâ üretimi kodun çakışma kontrolünü Controller'a veya yanlış katmana koyması | Yüksek | Orta | Her istemde mimari tablo ve Bölüm 4.7 bağlam olarak verilir; PR incelemesinde katman kuralı kontrol edilir. |

### 4.10 Sezonluk Dinamik Fiyatlandırma (ÇP-2)
Fiyat, kullanıcının en çok karşılaştırdığı ve yanlış hesaplandığında doğrudan para kaybettiren değerdir. Bu nedenle fiyat, gece bazında ve tek bir hesap koduyla (PricingService) üretilir; arama, ilan detayı ve rezervasyon aynı kodu kullanır (FR-44).

#### Fiyat kuralları (ilan sahibi tanımlar, FR-43)

| Kural türü | Tanım | Parametreler |
| --- | --- | --- |
| **Takvim istisnası** | İlan sahibinin belirli bir gece için girdiği özel fiyat. Varsa o gecenin fiyatı **kesindir**; sezon ve hafta sonu farkları o geceye uygulanmaz. | Birim, tarih, fiyat |
| **Sezon farkı** | Belirli tarih aralığındaki (ör. 1 Temmuz – 31 Ağustos) her gecenin fiyatına yüzde artış (veya indirim). | Başlangıç, bitiş, yüzde, öncelik |
| **Hafta sonu farkı** | Cuma ve Cumartesi geceleri için yüzde artış. Gece, konaklamanın başladığı güne göre sayılır (Cuma gecesi = Cuma günü giriş yapılan gece). | Yüzde |
| **Uzun konaklama indirimi** | Toplam gece sayısı eşiğe ulaştığında konaklama toplamına yüzde indirim. Birden fazla eşik tanımlıysa ulaşılan **en yüksek** eşik uygulanır. | Asgari gece sayısı, yüzde |

#### Hesap sırası (BR-23 – BR-26)
1. Her gece için temel fiyat belirlenir: takvim istisnası varsa o fiyat; yoksa birimin temel gecelik fiyatı.
1. Takvim istisnası yoksa: gece fiyatı = temel fiyat × (1 + sezon yüzdesi) × (1 + hafta sonu yüzdesi). Aynı geceyi kapsayan birden fazla sezon kuralı varsa yalnızca en yüksek öncelikli olan uygulanır.
1. Her gecenin fiyatı kuruşa (2 ondalık) yarıdan yukarı yuvarlanır. Yuvarlama gece bazında yapılır; böylece gecelik döküm toplamı her zaman toplam konaklama tutarına eşit olur.
1. Konaklama toplamı = gece fiyatlarının toplamı.
1. Uzun konaklama indirimi = konaklama toplamı × indirim yüzdesi (yuvarlanır), ayrı bir eksi kalem olarak gösterilir.
1. Vergi ve ek ücretler, indirimli konaklama tutarı üzerinden (yüzdeyse) veya sabit olarak eklenir ve ayrı kalemler olarak gösterilir.
1. Toplam fiyat = konaklama toplamı − indirim + vergiler + ek ücretler.

#### Örnek hesap
Birim temel fiyatı 1.000 TL; sezon (Temmuz) +%30; hafta sonu +%20; uzun konaklama (≥7 gece) %10 indirim; vergi %10 (örnek). 9 Temmuz 2027 Cuma günüdür.

| Konaklama | Gece fiyatları | Konaklama toplamı | İndirim | Vergi (%10) | Toplam |
| --- | --- | --- | --- | --- | --- |
| 9–12 Temmuz (3 gece: Cum, Cmt, Paz) | Cum 1.560,00 · Cmt 1.560,00 · Paz 1.300,00 | 4.420,00 | — | 442,00 | **4.862,00** |
| 12–19 Temmuz (7 gece) | 5 gece × 1.300,00 (Pzt, Sal, Çar, Per, Paz) + 2 gece × 1.560,00 (Cum, Cmt) | 9.620,00 | −962,00 (%10) | 865,80 | **9.523,80** |

#### Doğrulama (test) gereksinimleri

| Kod | Test | Beklenen sonuç |
| --- | --- | --- |
| **T-12** | Fiyat matrisi: yalnızca temel fiyat; yalnızca hafta sonu; yalnızca sezon; sezon + hafta sonu birlikte; takvim istisnası olan gece; sezon başlangıç/bitiş sınır geceleri; uzun konaklama eşik sınırı (6, 7 ve 28 gece); çakışan iki sezon kuralında öncelik. | Yukarıdaki örnek tablo birebir; takvim istisnası kural farklarını devre dışı bırakır; sınır geceleri doğru aralığa girer. |
| **T-13** | Gecelik döküm tutarlılığı ve yuvarlama (ör. 333,33 TL temel fiyat). | Döküm kalemlerinin toplamı toplam fiyata eşittir; aynı girdi iki çağrıda aynı sonucu verir. |

### 4.11 İptal Politikası ve İade Hesabı (ÇP-3)
İade hesabı, rezervasyon oluşturulduğu anda **rezervasyonla birlikte saklanan politika anlık görüntüsüne** göre yapılır (BR-29); ilan sahibi politikayı sonradan değiştirse de mevcut rezervasyonların iade koşulu değişmez. İade, müşterinin ödediği toplam tutar üzerinden hesaplanır.

| Politika | Kalan gün ≥ eşik | İade oranı | Diğer durumlar |
| --- | --- | --- | --- |
| **Esnek** | En az 1 gün kala | %100 | Giriş günü ve sonrası iptal edilemez (BR-18). |
| **Orta** | En az 5 gün kala | %100 | 1–4 gün kala %50; giriş günü ve sonrası iptal edilemez. |
| **Katı** | En az 14 gün kala | %50 | 1–13 gün kala iade yok (%0); giriş günü ve sonrası iptal edilemez. |

- **Kalan gün** = giriş tarihi − iptal tarihi (gün farkı; sunucu tarihi, Europe/Istanbul). Saat dikkate alınmaz.
- **İade tutarı** = ödenen toplam × iade oranı (kuruşa yarıdan yukarı yuvarlanır); **kesinti** = ödenen toplam − iade tutarı. İade, ödenen tutarı aşamaz.
- Beklemede durumundaki ve henüz ödemesi alınmamış rezervasyonun iptalinde iade ve kesinti sıfırdır (BR-18).
- **İlan sahibi reddi, ilan sahibi veya yönetici kaynaklı iptal (FR-39) ve süre dolumunda** ödenmiş tutarın %100'ü iade edilir (BR-28).
- İade tutarı ve kesinti, iptal onayından önce kullanıcıya gösterilir; iptal sonrası Payment kaydında iade (simüle) olarak izlenir.

#### Örnek hesap
Ödenen toplam 9.523,80 TL.

| Politika | İptal zamanı | Oran | İade | Kesinti |
| --- | --- | --- | --- | --- |
| **Orta** | Girişe 3 gün kala | %50 | 4.761,90 | 4.761,90 |
| **Orta** | Girişe 5 gün kala | %100 | 9.523,80 | 0,00 |
| **Katı** | Girişe 14 gün kala | %50 | 4.761,90 | 4.761,90 |
| **Katı** | Girişe 10 gün kala | %0 | 0,00 | 9.523,80 |
| **Esnek** | Girişe 2 gün kala | %100 | 9.523,80 | 0,00 |
| **Esnek** | Giriş günü | — | İptal edilemez | — |

| Kod | Test | Beklenen sonuç |
| --- | --- | --- |
| **T-14** | İade matrisi: üç politika için eşik gününün hemen öncesi ve sonrası (13/14 gün, 4/5 gün, 0/1 gün); ödemesiz Beklemede iptali; ilan sahibi reddi; yönetici iptali; ilan politikası sonradan değiştirilmiş rezervasyon; yuvarlama. | Yukarıdaki örnek tablo birebir; eşik günlerinde oran doğru; anlık görüntü politikası kullanılır; iade ödenen tutarı aşmaz. |
| **T-15** | Doluluk ve net gelir raporu formülleri (BR-30) bilinen veriyle. | Elle hesaplanmış değerlerle birebir eşleşir. |

#### Fiyat ve iade risklerinin eklenmesi

| Risk | Olasılık | Etki | Önlem |
| --- | --- | --- | --- |
| Fiyat kurallarının (sezon + hafta sonu + indirim) yanlış sırada uygulanması veya yuvarlama farkı | Orta | Yüksek | Tek hesap sırası (BR-23 – BR-26), gece bazlı yuvarlama; T-12, T-13. |
| Sezon/eşik sınır günlerinde (off-by-one) yanlış fiyat veya iade oranı | Yüksek | Orta | Sınır değerleri veri güdümlü testle doğrulanır; T-12, T-14. |
| İlan politikası değişince eski rezervasyonların iade koşulunun değişmesi | Orta | Orta | Politika anlık görüntüsü rezervasyonda saklanır (BR-29); T-14. |

## 5. Harici Arayüz Gereksinimleri
### 5.1 Kullanıcı Arayüzü
- Ana sayfada konum, tarih, kişi sayısı ve konaklama türü arama alanları bulunmalıdır.
- Arama sonuçları ilan kartları biçiminde; temel fotoğraf, ad, konum, fiyat ve puan bilgileriyle gösterilmelidir.
- İlan detay sayfasında müsaitlik takvimi (dolu ve kapalı geceler ayırt edilebilir), açıklama, olanaklar, fiyat, iptal politikası ve rezervasyon alanı bulunmalıdır.
- Rezervasyon onay ekranında kalemlere ayrılmış fiyat dökümü, iptal koşulları ve (Beklemede durumunda) kalan bekletme süresi gösterilmelidir.
- Çakışma hâlinde kullanıcıya "Seçtiğiniz tarihler artık müsait değil." benzeri açık bir mesaj ve en çok 3 alternatif aralık gösterilmelidir (FR-36). Mesaj, başka bir kullanıcının bilgisini içermemelidir.
- Müşteri panelinde profil, favoriler ve rezervasyonlarım (durumlarıyla) bölümleri bulunmalıdır.
- İlan sahibi panelinde ilanlar, birimler (odalar), takvim/fiyat yönetimi ve rezervasyonlar bölümleri bulunmalıdır; takvimde rezervasyonlu geceler kilitli (kapatılamaz) gösterilmelidir.
- Yönetici panelinde kullanıcı, ilan, şikâyet ve rapor ekranları bulunmalıdır.
- Form hataları kullanıcıya anlaşılır ve ilgili alanı belirten mesajlarla gösterilmelidir.

### 5.2 Yazılım Arayüzleri
- Uygulama, kullanıcı, ilan ve rezervasyon verilerini Repository arayüzleri (RepositoryInterface) aracılığıyla yönetmelidir. Aşama 2'de JSON, Aşama 3'te veritabanı uygulamaları aynı arayüzü gerçekleştirir.
- ReservationRepositoryInterface, rezervasyon ve gece kayıtlarını atomik yazma sözleşmesiyle (createWithNights) sunmalıdır (Bölüm 4.7).
- Harici ödeme sağlayıcısı entegre edilirse ödeme durumu sağlayıcının doğrulanmış yanıtıyla güncellenmelidir. İlk sürümde ödeme simüle edilir (FR-37).
- E-posta/SMS servisi entegre edilirse gönderim hataları kaydedilmeli ve mümkünse tekrar denenmelidir.
- Harita servisi kullanılırsa konum gösterimi için servis anahtarı güvenli biçimde yapılandırılmalıdır.

### 5.3 İletişim Arayüzleri
İstemci ile sunucu arasındaki iletişim HTTPS üzerinden yürütülmelidir. Harici servislerle iletişimde ilgili servisin kimlik doğrulama ve veri biçimi kurallarına uyulmalıdır.

## 6. Fonksiyonel Olmayan Gereksinimler
Bu bölümdeki fonksiyonel olmayan gereksinimler de Bölüm 3.1'deki Volere gereksinim kartı biçimindedir ve Volere gereksinim türlerine (Tür 11 – 15) göre gruplanmıştır. NFR-13 – NFR-18 v2.1'de, NFR-19 v2.2'de eklenmiştir. † işaretli değerler bu revizyonda öneri olarak eklenmiştir (Bölüm 11.5). Volere Tür 10 (Görünüm ve Hissiyat), Tür 16 (Kültürel ve Politik) ve Tür 17 (Yasal) için bu sürümde ayrı gereksinim yoktur; kişisel verilerin korunmasına ilişkin yasal yükümlülükler (ör. KVKK) kapsama alınacaksa Tür 17 altında eklenmelidir.

### 6.1 Kullanılabilirlik ve İnsan Faktörleri (Tür 11)

| Alan | Değer |
| --- | --- |
| **Gereksinim No** | **NFR-06** |
| **Gereksinim Türü** | 11 – Kullanılabilirlik ve İnsan Faktörleri (11.1 Kullanım kolaylığı) |
| **Olay / Kullanım Senaryosu** | UC-01, UC-02 |
| **Öncelik** | Gerekli |
| **Açıklama** | Temel arama ve rezervasyon akışları açık etiketlere, anlaşılır hata mesajlarına ve tutarlı gezinmeye sahip olmalıdır. |
| **Gerekçe** | Kullanıcı ilk kullanımda yardım almadan arama yapıp rezervasyon tamamlayabilmelidir; aksi hâlde işlem yarıda bırakılır. |
| **Kaynak** | Müşteri (ziyaretçi / kayıtlı müşteri) |
| **Çakışmalar** | NFR-15: Başarısız giriş kısıtı kullanım kolaylığını geçici olarak düşürebilir. |
| **Uygunluk Ölçütü** | Platformu ilk kez kullanan 5 test kullanıcısının en az 4'ü, arama–rezervasyon akışını yardım almadan 5 dakika içinde tamamlar†. Her form hatası, ilgili alanı belirten bir mesajla gösterilir (Bölüm 5.1). |
| **Müşteri Memnuniyeti** | 4 / 5 |
| **Müşteri Memnuniyetsizliği** | 4 / 5 |
| **Destekleyici Materyal** | Bölüm 5.1; UC-01, UC-02; Bölüm 11.5 (†). |
| **Geçmiş** | v2.0: tanımlandı; v2.3: Volere gereksinim kartı biçimine dönüştürüldü. |

| Alan | Değer |
| --- | --- |
| **Gereksinim No** | **NFR-12** |
| **Gereksinim Türü** | 11 – Kullanılabilirlik ve İnsan Faktörleri (11.5 Erişilebilirlik) |
| **Olay / Kullanım Senaryosu** | UC-01, UC-02 |
| **Öncelik** | Gerekli |
| **Açıklama** | Form alanları etiketlenmeli, klavye ile temel gezinme desteklenmeli ve metin kontrastı WCAG 2.1 AA düzeyinde (en az 4,5:1) olmalıdır. |
| **Gerekçe** | Engelli kullanıcılar ve düşük görme koşullarındaki kullanıcılar da platformu kullanabilmelidir. |
| **Kaynak** | Müşteri (ziyaretçi / kayıtlı müşteri) |
| **Çakışmalar** | Yok. |
| **Uygunluk Ölçütü** | Giriş, arama ve rezervasyon sayfalarında otomatik erişilebilirlik denetimi (ör. Lighthouse veya axe) etiket ve kontrast ihlali bildirmez; arama–rezervasyon akışı yalnızca klavyeyle tamamlanabilir. |
| **Müşteri Memnuniyeti** | 3 / 5 |
| **Müşteri Memnuniyetsizliği** | 3 / 5 |
| **Destekleyici Materyal** | Bölüm 5.1; UC-01, UC-02. |
| **Geçmiş** | v2.0: tanımlandı; v2.1: ölçülebilir hâle getirildi; v2.3: Volere gereksinim kartı biçimine dönüştürüldü. |

### 6.2 Performans (Tür 12)

| Alan | Değer |
| --- | --- |
| **Gereksinim No** | **NFR-01** |
| **Gereksinim Türü** | 12 – Performans (12.1 Hız) |
| **Olay / Kullanım Senaryosu** | UC-01 Konaklama arama |
| **Öncelik** | Gerekli |
| **Açıklama** | Normal yük koşullarında (50 eşzamanlı kullanıcı) arama sonuçlarının yüzde 95'i 3 saniye içinde görüntülenmelidir. |
| **Gerekçe** | Arama kullanıcının ilk adımıdır; yavaş arama kullanıcıyı platformdan uzaklaştırır. |
| **Kaynak** | Müşteri (ziyaretçi / kayıtlı müşteri) |
| **Çakışmalar** | FR-44: Gece bazlı fiyat hesabı arama süresini artırabilir. |
| **Uygunluk Ölçütü** | 50 eşzamanlı sanal kullanıcıyla yapılan yük testinde arama isteklerinin %95'i 3 saniye içinde tamamlanır. |
| **Müşteri Memnuniyeti** | 4 / 5 |
| **Müşteri Memnuniyetsizliği** | 4 / 5 |
| **Destekleyici Materyal** | FR-06, FR-44; UC-01; NFR-11. |
| **Geçmiş** | v2.0: tanımlandı; v2.1: ölçülebilir hâle getirildi; v2.3: Volere gereksinim kartı biçimine dönüştürüldü. |

| Alan | Değer |
| --- | --- |
| **Gereksinim No** | **NFR-13** |
| **Gereksinim Türü** | 12 – Performans (12.1 Hız) |
| **Olay / Kullanım Senaryosu** | UC-02 Rezervasyon oluşturma |
| **Öncelik** | Gerekli |
| **Açıklama** | Rezervasyon oluşturma işleminin (çakışma kontrolü dahil) yüzde 95'i normal yükte 2 saniye içinde tamamlanmalıdır; kilit bekleme süresi en çok 5 saniye olmalıdır. |
| **Gerekçe** | Rezervasyon sırasında beklemek, kullanıcının işlemi tekrar göndermesine veya terk etmesine yol açar. |
| **Kaynak** | Müşteri (ziyaretçi / kayıtlı müşteri); Ders proje tanımı (ders yürütücüsü) |
| **Çakışmalar** | NFR-05: Birim satırı kilidi ve UNIQUE kısıtı gecikmeyi artırabilir; kilit zaman aşımı bu nedenle 5 saniye ile sınırlandırılmıştır. |
| **Uygunluk Ölçütü** | 50 eşzamanlı rezervasyon isteğinde isteklerin %95'i 2 saniye içinde tamamlanır. Kilit bekleme 5 saniyeyi aşarsa işlem geri alınır, hiçbir yarım kayıt kalmaz ve kullanıcıya "tekrar deneyin" mesajı gösterilir (UC-02 A7). |
| **Müşteri Memnuniyeti** | 4 / 5 |
| **Müşteri Memnuniyetsizliği** | 4 / 5 |
| **Destekleyici Materyal** | FR-18, FR-33; UC-02; Bölüm 4.5. |
| **Geçmiş** | v2.1: tanımlandı; v2.3: Volere gereksinim kartı biçimine dönüştürüldü. |

| Alan | Değer |
| --- | --- |
| **Gereksinim No** | **NFR-11** |
| **Gereksinim Türü** | 12 – Performans (12.7 Ölçeklenebilirlik) |
| **Olay / Kullanım Senaryosu** | UC-01, UC-02 |
| **Öncelik** | Gerekli |
| **Açıklama** | Arama ve rezervasyon bileşenleri artan kullanıcı yüküne göre iyileştirilebilir bir mimaride tasarlanmalı; arama ve çakışma sorguları (birim, tarih) üzerinde indeks kullanmalıdır. |
| **Gerekçe** | Kullanıcı ve ilan sayısı arttıkça arama ve çakışma sorgularının yavaşlaması hem NFR-01'i hem NFR-13'ü tehdit eder. |
| **Kaynak** | Proje ekibi (mimari) |
| **Çakışmalar** | Yok. |
| **Uygunluk Ölçütü** | Veritabanı şemasında arama ve çakışma sorguları için indeks tanımlıdır; bu sorguların açıklama planı (ör. EXPLAIN) tam tablo taraması yerine indeks kullandığını gösterir. |
| **Müşteri Memnuniyeti** | 3 / 5 |
| **Müşteri Memnuniyetsizliği** | 3 / 5 |
| **Destekleyici Materyal** | NFR-01, NFR-13; Bölüm 4.5; Bölüm 7. |
| **Geçmiş** | v2.0: tanımlandı; v2.1: ölçülebilir hâle getirildi; v2.3: Volere gereksinim kartı biçimine dönüştürüldü. |

| Alan | Değer |
| --- | --- |
| **Gereksinim No** | **NFR-17** |
| **Gereksinim Türü** | 12 – Performans (12.3 Kesinlik) |
| **Olay / Kullanım Senaryosu** | UC-02, zamanlanmış görev |
| **Öncelik** | Zorunlu |
| **Açıklama** | Bekletme süresi dolumu ve geçmiş tarih kontrolü gibi tüm zaman kararları sunucu saatiyle ve Europe/Istanbul saat dilimiyle alınmalı; istemci saatine güvenilmemelidir. |
| **Gerekçe** | İstemci saati değiştirilebilir; zaman kararları tutarsız olursa süresi dolmuş veya geçmiş tarihli rezervasyonlar kabul edilebilir. |
| **Kaynak** | Proje ekibi (mimari) |
| **Çakışmalar** | Yok. |
| **Uygunluk Ölçütü** | Sunucu saati sabitlenmiş testte: expires_at'ten bir saniye sonra kayıt Süresi Doldu sayılır, bir saniye öncesinde geçerlidir; giriş tarihi bugün için kabul edilir, dün için reddedilir (BR-14). İstemciden gönderilen tarih/saat değeri bu kararları etkilemez. |
| **Müşteri Memnuniyeti** | 3 / 5 |
| **Müşteri Memnuniyetsizliği** | 5 / 5 |
| **Destekleyici Materyal** | BR-14, BR-15; FR-34; Bölüm 2.5. |
| **Geçmiş** | v2.1: tanımlandı; v2.3: Volere gereksinim kartı biçimine dönüştürüldü. |

| Alan | Değer |
| --- | --- |
| **Gereksinim No** | **NFR-09** |
| **Gereksinim Türü** | 12 – Performans (12.4 Güvenilirlik ve süreklilik) |
| **Olay / Kullanım Senaryosu** | İşletim |
| **Öncelik** | Gerekli |
| **Açıklama** | Veritabanı günde en az bir kez yedeklenmeli, son 7 yedek saklanmalı ve geri yükleme dönem boyunca en az bir kez denenmelidir. |
| **Gerekçe** | Veri kaybı rezervasyon ve ödeme kayıtlarının yitirilmesi anlamına gelir; yedeğin geri yüklenebildiği denenmemişse güvence yoktur. |
| **Kaynak** | Proje ekibi (mimari) |
| **Çakışmalar** | Yok. |
| **Uygunluk Ölçütü** | Her gün için en az bir yedek vardır ve son 7 yedek saklanır; proje dönemi boyunca en az bir geri yükleme denemesi yapılmış ve sonucu belgelenmiştir. |
| **Müşteri Memnuniyeti** | 3 / 5 |
| **Müşteri Memnuniyetsizliği** | 4 / 5 |
| **Destekleyici Materyal** | Bölüm 2.3; Bölüm 7. |
| **Geçmiş** | v2.0: tanımlandı; v2.1: ölçülebilir hâle getirildi; v2.3: Volere gereksinim kartı biçimine dönüştürüldü. |

### 6.3 İşletim ve Çevresel (Tür 13)

| Alan | Değer |
| --- | --- |
| **Gereksinim No** | **NFR-07** |
| **Gereksinim Türü** | 13 – İşletim ve Çevresel (13.1 Beklenen teknolojik ortam) |
| **Olay / Kullanım Senaryosu** | UC-01, UC-02, UC-04 |
| **Öncelik** | Gerekli |
| **Açıklama** | Arayüz; Chrome, Firefox, Edge ve Safari'nin son iki ana sürümünde ve mobil tarayıcılarda temel akışları (arama, rezervasyon, iptal) korumalıdır. |
| **Gerekçe** | Kullanıcılar farklı tarayıcı ve cihazlardan gelir; temel akışların bozulması doğrudan rezervasyon kaybıdır. |
| **Kaynak** | Müşteri (ziyaretçi / kayıtlı müşteri) |
| **Çakışmalar** | Yok. |
| **Uygunluk Ölçütü** | Sayılan dört tarayıcının son iki ana sürümünün her birinde ve en az bir mobil tarayıcıda† arama, rezervasyon ve iptal akışları hatasız tamamlanır (AC-10). |
| **Müşteri Memnuniyeti** | 3 / 5 |
| **Müşteri Memnuniyetsizliği** | 4 / 5 |
| **Destekleyici Materyal** | Bölüm 2.3; AC-10; UC-01, UC-02, UC-04; Bölüm 11.5 (†). |
| **Geçmiş** | v2.0: tanımlandı; v2.1: ölçülebilir hâle getirildi; v2.3: Volere gereksinim kartı biçimine dönüştürüldü. |

### 6.4 Bakım ve Destek (Tür 14)

| Alan | Değer |
| --- | --- |
| **Gereksinim No** | **NFR-08** |
| **Gereksinim Türü** | 14 – Bakım ve Destek (14.1 Bakım) |
| **Olay / Kullanım Senaryosu** | Geliştirme |
| **Öncelik** | Gerekli |
| **Açıklama** | Kod; kullanıcı, ilan, müsaitlik ve rezervasyon işlevleri ayrı bileşenler olarak geliştirilebilecek biçimde (katmanlı mimariyle) düzenlenmelidir. |
| **Gerekçe** | Ekip üyeleri paralel çalışabilmeli; veri katmanı değiştiğinde (JSON → veritabanı) iş kuralları etkilenmemelidir. |
| **Kaynak** | Proje ekibi (mimari) |
| **Çakışmalar** | Yok. |
| **Uygunluk Ölçütü** | Kod incelemesinde Controller'da SQL veya iş kuralı hesabı, Service'te $_POST okuma veya HTML üretimi, Repository'de iş kuralı bulunmaz (Bölüm 2.4). Repository uygulaması JSON'dan veritabanına değiştirildiğinde yalnızca Repository sınıfları değişir (Bölüm 4.7 kontrol sorusu). |
| **Müşteri Memnuniyeti** | 3 / 5 |
| **Müşteri Memnuniyetsizliği** | 3 / 5 |
| **Destekleyici Materyal** | Bölüm 2.1, 2.4, 4.7; NFR-16. |
| **Geçmiş** | v2.0: tanımlandı; v2.3: Volere gereksinim kartı biçimine dönüştürüldü. |

| Alan | Değer |
| --- | --- |
| **Gereksinim No** | **NFR-10** |
| **Gereksinim Türü** | 14 – Bakım ve Destek (14.2 Destek) |
| **Olay / Kullanım Senaryosu** | İşletim |
| **Öncelik** | İsteğe bağlı |
| **Açıklama** | Beklenmeyen sunucu hataları, parola veya ödeme gibi hassas veriler kayda yazılmadan izlenebilmelidir. |
| **Gerekçe** | Üretimde oluşan hataların nedeni bulunabilmeli, ancak günlük dosyaları gizli veri sızıntısı kaynağı olmamalıdır. |
| **Kaynak** | Proje ekibi (mimari) |
| **Çakışmalar** | Yok. |
| **Uygunluk Ölçütü** | Test sırasında enjekte edilen bir sunucu hatası günlüğe zaman damgası ve hata türüyle yazılır; günlük dosyalarında parola, kart verisi veya parola sıfırlama jetonu bulunmaz (içerik taramasında sonuç boştur). |
| **Müşteri Memnuniyeti** | 2 / 5 |
| **Müşteri Memnuniyetsizliği** | 3 / 5 |
| **Destekleyici Materyal** | NFR-18; Bölüm 2.4. |
| **Geçmiş** | v2.0: tanımlandı; v2.3: Volere gereksinim kartı biçimine dönüştürüldü. |

| Alan | Değer |
| --- | --- |
| **Gereksinim No** | **NFR-16** |
| **Gereksinim Türü** | 14 – Bakım ve Destek (14.1 Bakım – test edilebilirlik) |
| **Olay / Kullanım Senaryosu** | Geliştirme |
| **Öncelik** | Gerekli |
| **Açıklama** | BookingConflictService, ReservationService, AvailabilityService ve PricingService en az %90 satır kapsamıyla PHPUnit birim testlerine sahip olmalı; Repository arayüzleri sahte (mock) uygulamalarla test edilebilmelidir. |
| **Gerekçe** | Çekirdek problemlerin doğruluğu otomatik testlerle güvence altına alınmalıdır (Bölüm 4.8). |
| **Kaynak** | Ders proje tanımı (ders yürütücüsü); Proje ekibi (mimari) |
| **Çakışmalar** | Yok. |
| **Uygunluk Ölçütü** | PHPUnit kapsam raporunda dört servis sınıfının her biri en az %90 satır kapsamına sahiptir; birim testleri gerçek veritabanı bağlantısı olmadan, Repository sahte uygulamalarıyla çalışır. |
| **Müşteri Memnuniyeti** | 3 / 5 |
| **Müşteri Memnuniyetsizliği** | 4 / 5 |
| **Destekleyici Materyal** | Bölüm 4.7, 4.8; NFR-08. |
| **Geçmiş** | v2.1: tanımlandı; v2.3: Volere gereksinim kartı biçimine dönüştürüldü. |

### 6.5 Güvenlik (Tür 15)

| Alan | Değer |
| --- | --- |
| **Gereksinim No** | **NFR-02** |
| **Gereksinim Türü** | 15 – Güvenlik (15.1 Erişim) |
| **Olay / Kullanım Senaryosu** | FR-01, FR-02 |
| **Öncelik** | Zorunlu |
| **Açıklama** | Parolalar güçlü ve güncel bir parola özetleme algoritmasıyla (ör. PHP password_hash) saklanmalı; düz metin olarak tutulmamalıdır. |
| **Gerekçe** | Veritabanı sızıntısı hâlinde kullanıcı parolalarının çözülememesi gerekir. |
| **Kaynak** | Proje ekibi (mimari) |
| **Çakışmalar** | Yok. |
| **Uygunluk Ölçütü** | Kullanıcı tablosunda düz metin parola bulunmaz; parola alanı password_hash çıktısı biçimindedir ve girişte password_verify ile doğrulanır (kod incelemesi ve örnek kayıt kontrolü). |
| **Müşteri Memnuniyeti** | 3 / 5 |
| **Müşteri Memnuniyetsizliği** | 5 / 5 |
| **Destekleyici Materyal** | FR-01, FR-02; Bölüm 7 (Kullanıcı: parola özeti). |
| **Geçmiş** | v2.0: tanımlandı; v2.3: Volere gereksinim kartı biçimine dönüştürüldü. |

| Alan | Değer |
| --- | --- |
| **Gereksinim No** | **NFR-03** |
| **Gereksinim Türü** | 15 – Güvenlik (15.1 Erişim) |
| **Olay / Kullanım Senaryosu** | Tüm korumalı işlemler |
| **Öncelik** | Zorunlu |
| **Açıklama** | Her korumalı işlemde kullanıcının rolü ve ilgili kaydın sahibi olup olmadığı sunucu tarafında doğrulanmalıdır. |
| **Gerekçe** | Arayüzde gizlenen bir işlev, doğrudan istekle yine de çağrılabilir; yetki kontrolü yalnızca sunucuda güvenilirdir (Bölüm 2.4). |
| **Kaynak** | Proje ekibi (mimari) |
| **Çakışmalar** | Yok. |
| **Uygunluk Ölçütü** | Başka bir kullanıcının rezervasyon veya ilan kimliğiyle yapılan ve rol ya da sahiplik kuralını ihlal eden tüm istekler sunucu tarafında reddedilir; üç rol için yapılan yetkisiz erişim denemelerinin tamamı reddedilir (AC-06, AC-07). |
| **Müşteri Memnuniyeti** | 3 / 5 |
| **Müşteri Memnuniyetsizliği** | 5 / 5 |
| **Destekleyici Materyal** | FR-05; BR-08; AC-06, AC-07; Bölüm 2.4. |
| **Geçmiş** | v2.0: tanımlandı; v2.3: Volere gereksinim kartı biçimine dönüştürüldü. |

| Alan | Değer |
| --- | --- |
| **Gereksinim No** | **NFR-04** |
| **Gereksinim Türü** | 15 – Güvenlik (15.3 Gizlilik) |
| **Olay / Kullanım Senaryosu** | UC-02 (A1), UC-05 |
| **Öncelik** | Zorunlu |
| **Açıklama** | Kişisel veriler yalnızca gerekli amaçlarla işlenmeli; yetkisiz kullanıcıların özel rezervasyon bilgilerine erişimi engellenmelidir. Çakışma mesajları başka kullanıcıların bilgilerini içermemelidir. |
| **Gerekçe** | Kişisel verilerin ve rezervasyon bilgilerinin ifşası kullanıcıya zarar verir ve güveni yok eder. |
| **Kaynak** | Müşteri (ziyaretçi / kayıtlı müşteri); Proje ekibi (mimari) |
| **Çakışmalar** | Yok. |
| **Uygunluk Ölçütü** | Bir müşteri başka bir müşterinin rezervasyonunu göremez (AC-06); çakışma mesajında başka bir kullanıcının adı, e-postası veya rezervasyon numarası görünmez (AC-12). |
| **Müşteri Memnuniyeti** | 3 / 5 |
| **Müşteri Memnuniyetsizliği** | 5 / 5 |
| **Destekleyici Materyal** | FR-32, FR-36; AC-06, AC-12; Bölüm 2.4. |
| **Geçmiş** | v2.0: tanımlandı; v2.1: ölçülebilir hâle getirildi; v2.3: Volere gereksinim kartı biçimine dönüştürüldü. |

| Alan | Değer |
| --- | --- |
| **Gereksinim No** | **NFR-05** |
| **Gereksinim Türü** | 15 – Güvenlik (15.2 Bütünlük) |
| **Olay / Kullanım Senaryosu** | UC-02 Rezervasyon oluşturma |
| **Öncelik** | Zorunlu **· ÇEKİRDEK** |
| **Açıklama** | Eş zamanlı rezervasyon isteklerinde aynı birim ve gece için en fazla bir engelleyen rezervasyon bulunabilir; bu garanti uygulama kodunun yanı sıra veri katmanında (UNIQUE kısıtı / Aşama 2'de dosya kilidi) sağlanmalıdır. |
| **Gerekçe** | Çift rezervasyon çekirdek problemin kendisidir; uygulama kodundaki bir hata veri düzeyinde yakalanmalıdır (Bölüm 4.5). |
| **Kaynak** | Ders proje tanımı (ders yürütücüsü) |
| **Çakışmalar** | NFR-13: Kilit ve kısıtlar gecikmeyi artırabilir (kilit zaman aşımı 5 sn). |
| **Uygunluk Ölçütü** | 50 eşzamanlı istekli testte hiçbir çift kayıt oluşmaz (T-05). Aşama 3'te uygulama kontrolü devre dışı bırakılsa da UNIQUE (birim, gece) kısıtı çift kaydı reddeder; Aşama 2'de iki süreçten eşzamanlı yazmada dosya bozulmaz (T-10). Tutarlılık denetim sorgusu boş küme döner (T-11). |
| **Müşteri Memnuniyeti** | 5 / 5 |
| **Müşteri Memnuniyetsizliği** | 5 / 5 |
| **Destekleyici Materyal** | FR-18, FR-33; Bölüm 4.5; Bölüm 7.1; T-05, T-10, T-11; AC-13, AC-22. |
| **Geçmiş** | v2.0: tanımlandı; v2.1: ölçülebilir hâle getirildi; v2.3: Volere gereksinim kartı biçimine dönüştürüldü. |

| Alan | Değer |
| --- | --- |
| **Gereksinim No** | **NFR-14** |
| **Gereksinim Türü** | 15 – Güvenlik (15.5 Bağışıklık) |
| **Olay / Kullanım Senaryosu** | Tüm formlar |
| **Öncelik** | Zorunlu |
| **Açıklama** | SQL işlemleri parametreli sorgularla (PDO prepared statements) yapılmalı; View çıktıları HTML'e kaçışlanmalı (XSS); durum değiştiren tüm formlar CSRF jetonu içermelidir. |
| **Gerekçe** | SQL enjeksiyonu, XSS ve CSRF web uygulamalarında en yaygın saldırı türleridir; bir açık tüm rezervasyon verisini tehlikeye atar. |
| **Kaynak** | Proje ekibi (mimari) |
| **Çakışmalar** | Yok. |
| **Uygunluk Ölçütü** | Kod incelemesinde tüm SQL ifadeleri parametrelidir; kullanıcı girdisi içeren View çıktıları kaçışlıdır (test girdisi olarak girilen betik etiketi metin olarak görünür); CSRF jetonu olmayan durum değiştiren istek reddedilir. |
| **Müşteri Memnuniyeti** | 3 / 5 |
| **Müşteri Memnuniyetsizliği** | 5 / 5 |
| **Destekleyici Materyal** | Bölüm 2.4; NFR-15. |
| **Geçmiş** | v2.1: tanımlandı; v2.3: Volere gereksinim kartı biçimine dönüştürüldü. |

| Alan | Değer |
| --- | --- |
| **Gereksinim No** | **NFR-15** |
| **Gereksinim Türü** | 15 – Güvenlik (15.1 Erişim) |
| **Olay / Kullanım Senaryosu** | FR-02 Oturum açma |
| **Öncelik** | Zorunlu |
| **Açıklama** | Girişte oturum kimliği yenilenmeli (session fixation); art arda 5 başarısız girişten sonra giriş 15 dakika kısıtlanmalı; oturum çerezleri HttpOnly ve (HTTPS'te) Secure olmalıdır. |
| **Gerekçe** | Oturum ele geçirme ve parola deneme saldırıları hesap güvenliğini tehdit eder. |
| **Kaynak** | Proje ekibi (mimari) |
| **Çakışmalar** | NFR-06: Giriş kısıtı, hatalı parola giren meşru kullanıcıyı da geçici olarak engelleyebilir. |
| **Uygunluk Ölçütü** | Başarılı girişte oturum kimliği değişir; art arda 5 başarısız girişten sonra 15 dakika boyunca giriş reddedilir; oturum çerezleri HttpOnly'dir ve HTTPS bağlantısında Secure işaretlidir. |
| **Müşteri Memnuniyeti** | 3 / 5 |
| **Müşteri Memnuniyetsizliği** | 4 / 5 |
| **Destekleyici Materyal** | FR-02; NFR-14. |
| **Geçmiş** | v2.1: tanımlandı; v2.3: Volere gereksinim kartı biçimine dönüştürüldü. |

| Alan | Değer |
| --- | --- |
| **Gereksinim No** | **NFR-18** |
| **Gereksinim Türü** | 15 – Güvenlik (15.4 Denetim) |
| **Olay / Kullanım Senaryosu** | UC-02, UC-04, zamanlanmış görev |
| **Öncelik** | Gerekli |
| **Açıklama** | Çakışma reddi, süre dolumu ve durum geçişleri hassas veri içermeden günlüğe yazılmalı; tutarlılık ihlali (aynı birim ve gece için birden fazla engelleyen kayıt) bir denetim sorgusuyla tespit edilebilmelidir. |
| **Gerekçe** | Çekirdek problemin gerçekten çözüldüğü yalnızca sürekli denetlenerek kanıtlanabilir (Bölüm 4.8). |
| **Kaynak** | Ders proje tanımı (ders yürütücüsü); Proje ekibi (mimari) |
| **Çakışmalar** | Yok. |
| **Uygunluk Ölçütü** | Her çakışma reddi, süre dolumu ve durum geçişi hassas veri içermeden günlüğe yazılır. Denetim sorgusu sağlıklı veride boş küme döner; testte bilinçli oluşturulan çift engelleyen kayıt bu sorguyla bulunur (T-11). |
| **Müşteri Memnuniyeti** | 3 / 5 |
| **Müşteri Memnuniyetsizliği** | 4 / 5 |
| **Destekleyici Materyal** | FR-15, FR-41; T-11; AC-22. |
| **Geçmiş** | v2.1: tanımlandı; v2.3: Volere gereksinim kartı biçimine dönüştürüldü. |

| Alan | Değer |
| --- | --- |
| **Gereksinim No** | **NFR-19** |
| **Gereksinim Türü** | 15 – Güvenlik (15.2 Bütünlük) |
| **Olay / Kullanım Senaryosu** | FR-46 Resim yükleme |
| **Öncelik** | Gerekli |
| **Açıklama** | Yüklenen resimler yalnızca JPEG, PNG veya WebP olmalı (uzantı ve gerçek MIME türü doğrulanmalı), her biri en çok 2 MB olmalı; dosya adı sunucuda rastgele üretilmeli, yükleme klasöründe betik çalıştırılmamalı ve yetkisiz kullanıcı başkasının ilanına resim yükleyememelidir. |
| **Gerekçe** | Dosya yükleme, sunucuda kod çalıştırılmasına yol açabilecek saldırı yüzeyidir. |
| **Kaynak** | Proje ekibi (mimari) |
| **Çakışmalar** | Yok. |
| **Uygunluk Ölçütü** | JPEG/PNG/WebP resimler kabul edilir; farklı tür, 2 MB'tan büyük veya sahte uzantılı dosya reddedilir; yüklenen dosya adı rastgeledir; yükleme klasörüne konan bir betik dosyası çalıştırılmaz; başkasının ilanına yükleme reddedilir (AC-31). |
| **Müşteri Memnuniyeti** | 3 / 5 |
| **Müşteri Memnuniyetsizliği** | 5 / 5 |
| **Destekleyici Materyal** | FR-46; AC-31; Bölüm 11.4 (resim sınırları). |
| **Geçmiş** | v2.2: tanımlandı; v2.3: Volere gereksinim kartı biçimine dönüştürüldü. |

## 7. Veri Gereksinimleri
Sistemde en az aşağıdaki temel veri varlıklarının bulunması öngörülür. Kılavuz gereği para birimleri DECIMAL(10,2), tarihler DATE/DATETIME, sabit seçenekler ENUM olarak tanımlanır ve her kalıcı veri bir Model ve Repository arayüzüne karşılık gelir.

| Varlık | Örnek Alanlar / Açıklama | Model ve Repository Karşılığı |
| --- | --- | --- |
| **Kullanıcı** | Kullanıcı ID (PK), ad, soyad, e-posta (UK), parola özeti, rol (ENUM), hesap durumu (ENUM), oluşturulma tarihi (DATETIME). | User / UserRepositoryInterface |
| **Konaklama İlanı** | İlan ID (PK), sahibi ID (FK → Kullanıcı), ad, tür (ENUM: otel / kiralık ev), açıklama, adres, şehir, rezervasyon modu (ENUM: anında / onaylı), iptal politikası (ENUM: esnek / orta / katı; yeni rezervasyonlara uygulanacak güncel değer), durum (ENUM: taslak / aktif / pasif / kaldırıldı). | Property / PropertyRepositoryInterface |
| **Birim (Oda / Ev)** | Birim ID (PK), ilan ID (FK), ad (ilan içinde UK), oda tipi (ENUM: tek kişilik / çift kişilik / aile / süit / müstakil ev / daire), kapasite, temel gecelik fiyat (DECIMAL(10,2)), durum (ENUM: aktif / pasif). Rezervasyonun yapıldığı tekil kaynaktır; kiralık evde ilan başına tek birim, otelde oda başına bir birim bulunur. | BookableUnit / BookableUnitRepositoryInterface |
| **Fotoğraf** | Fotoğraf ID (PK), ilan ID (FK), birim ID (FK, boş olabilir), sunucudaki rastgele dosya adı, MIME türü, boyut, sıralama bilgisi. | Photo / PhotoRepositoryInterface |
| **Olanak** | Olanak ID (PK), ad; ilan–olanak ilişkisi N-M ara tablo (property_amenities) ile kurulur. | Amenity / AmenityRepositoryInterface |
| **Takvim İstisnası (Müsaitlik ve Fiyat)** | Kayıt ID (PK), birim ID (FK), tarih (DATE), durum (ENUM: açık / kapalı), özel gecelik fiyat (DECIMAL(10,2), boş olabilir); (birim, tarih) UK. Yalnızca ilan sahibinin tanımladığı istisnaları tutar; **rezervasyon doluluğu burada tutulmaz** (tek doğruluk kaynağı: Rezervasyon Gecesi). | CalendarEntry / CalendarRepositoryInterface |
| **Rezervasyon** | Rezervasyon ID (PK), rezervasyon numarası (UK), müşteri ID (FK), birim ID (FK), giriş ve çıkış tarihi (DATE; CHECK çıkış > giriş), kişi sayısı, durum (ENUM: Beklemede / Onaylandı / İptal Edildi / Reddedildi / Süresi Doldu / Tamamlandı), bitiş zamanı expires_at (DATETIME), toplam fiyat (DECIMAL(10,2)), **iptal politikası anlık görüntüsü** (ENUM), idempotency anahtarı (UK), iptal/ret nedeni, iptal zamanı, iade tutarı (DECIMAL(10,2)), oluşturulma ve güncellenme zamanı. (ID, birim ID) çifti benzersizdir (gece tablosundaki bileşik FK için). | Reservation / ReservationRepositoryInterface |
| **Rezervasyon Gecesi** | Rezervasyon ID (FK), birim ID, gece tarihi (DATE); PK (rezervasyon ID, gece tarihi); **UK (birim ID, gece tarihi)**; (rezervasyon ID, birim ID) bileşik FK → Rezervasyon. Yalnızca engelleyen durumdaki rezervasyonların geceleri için satır bulunur; durum engellemeyen olduğunda satırlar silinir. Çift rezervasyonun veri düzeyindeki son savunmasıdır. | ReservationNight / ReservationRepositoryInterface |
| **Rezervasyon Kalemi** | Kalem ID (PK), rezervasyon ID (FK), tür (ENUM: konaklama gecesi / indirim / vergi / ek ücret), gece tarihi (yalnızca konaklama gecesi için), açıklama, tutar (DECIMAL(10,2); indirim eksi değerdir). Gecelik döküm ve fiyat anlık görüntüsüdür (BR-16). | ReservationCharge / ReservationRepositoryInterface |
| **Fiyat Kuralı** | Kural ID (PK), ilan ID (FK), birim ID (FK, boş olabilir), tür (ENUM: sezon / hafta sonu / uzun konaklama), başlangıç ve bitiş tarihi (DATE, yalnızca sezon), asgari gece (yalnızca uzun konaklama), değer (DECIMAL(10,2)), değer türü (ENUM: yüzde / sabit), öncelik. Bölüm 4.10 kurallarını taşır. | PriceRule / PriceRuleRepositoryInterface |
| **Vergi / Ek Ücret Kuralı** | Kural ID (PK), ilan ID (FK), ad, tür (ENUM: vergi / ek ücret), hesaplama (ENUM: yüzde / gecelik sabit / rezervasyon başına sabit), değer (DECIMAL(10,2)). | PricingRule / PricingRuleRepositoryInterface |
| **Ödeme Kaydı** | Ödeme ID (PK), rezervasyon ID (FK), sağlayıcı/simülasyon referansı, tutar (DECIMAL(10,2)), iade tutarı (DECIMAL(10,2)), para birimi, ödeme durumu (ENUM: beklemede / başarılı / başarısız / kısmen iade / iade edildi (simüle)); kart numarası gibi hassas veriler saklanmaz. | Payment / PaymentRepositoryInterface |
| **Yorum** | Yorum ID (PK), rezervasyon ID (FK, UK), müşteri ID (FK), puan (1–5), yorum metni, oluşturulma tarihi (DATETIME), denetim durumu (ENUM). | Review / ReviewRepositoryInterface |
| **Favori** | Kullanıcı ID (FK), ilan ID (FK), eklenme tarihi (DATETIME); PK (kullanıcı ID, ilan ID). | Favorite / FavoriteRepositoryInterface |
| **Bildirim** | Bildirim ID (PK), kullanıcı ID (FK), tür, mesaj, okunma durumu, oluşturulma tarihi (DATETIME). | Notification / NotificationRepositoryInterface |
| **Şikâyet** | Şikâyet ID (PK), gönderen ID (FK), ilgili ilan/rezervasyon (FK), açıklama, durum (ENUM), yönetici notu. | Complaint / ComplaintRepositoryInterface |
| **Denetim Kaydı** | Kayıt ID (PK), işlemi yapan kullanıcı ID (FK, sistem işlemleri için boş), varlık türü, varlık ID, işlem, eski değer, yeni değer, neden, zaman (DATETIME). | AuditLog / AuditLogRepositoryInterface |
| **Parola Sıfırlama Jetonu** | Jeton ID (PK), kullanıcı ID (FK), jeton özeti (UK), bitiş zamanı (DATETIME), kullanım zamanı (boş olabilir). | PasswordResetToken / PasswordResetTokenRepositoryInterface |

### 7.1 Veri Bütünlüğü ve 3NF Kuralları
- Her kullanıcı, ilan, birim, rezervasyon ve ödeme kaydının benzersiz bir kimliği (PK) olmalıdır.
- Rezervasyonun çıkış tarihi giriş tarihinden sonra olmalıdır.
- Rezervasyon, mevcut bir kullanıcıya ve geçerli bir birime yabancı anahtarla (FK) bağlı olmalıdır.
- **Çekirdek kısıt:** Rezervasyon Gecesi tablosunda (birim ID, gece tarihi) çifti benzersiz olmalıdır. Engelleyen durumdaki her rezervasyonun gece satırı sayısı (çıkış − giriş) kadar olmalı, engellemeyen durumdaki rezervasyonun gece satırı bulunmamalıdır (T-11 denetim sorgusuyla doğrulanır).
- Doluluk bilgisi yalnızca Rezervasyon Gecesi tablosundan türetilir; Takvim İstisnası tablosu rezervasyon doluluğunu tutmaz (tek doğruluk kaynağı).
- Toplam fiyat ve rezervasyon durumu, rezervasyon oluşturulurken kullanılan fiyat/koşul bilgileriyle ilişkilendirilebilmelidir (Rezervasyon Kalemi).
- İlanlar fiziksel olarak silinmez; durum alanı "kaldırıldı" yapılır, böylece geçmiş rezervasyonlar bozulmaz. Pasif veya kaldırılmış ilanlar için yeni rezervasyon oluşturulması engellenmelidir.
- Tablolar 1NF (her hücrede tek değer), 2NF (tam anahtar bağımlılığı) ve 3NF (anahtar olmayan sütunların birbirine bağımlı olmaması) kurallarına uygun olmalıdır.

#### Bilinçli iki istisna (Sunum 1'de açıklanacak)
- **Rezervasyon Gecesi.birim ID:** birim ID, rezervasyon ID'ye bağlı olduğundan sıkı 3NF/2NF açısından yinelenen bir sütundur. Ancak veritabanının (birim, gece) benzersizliğini zorlayabilmesi için gereklidir. Tutarsızlık, (rezervasyon ID, birim ID) bileşik yabancı anahtarıyla engellenir.
- **Rezervasyon.toplam fiyat:** Rezervasyon Kalemleri'nin toplamından türetilebilir; ancak fiyat anlık görüntüsü olarak bilinçli saklanır ve testle kalemlerin toplamına eşitliği doğrulanır.

## 8. İş Kuralları

| Kimlik | Kural |
| --- | --- |
| **BR-01** | Giriş tarihi, çıkış tarihinden önce olmalıdır; geçersiz tarih aralığı kabul edilmemelidir. |
| **BR-02** | Konaklama süresi, çıkış tarihi eksi giriş tarihi olarak hesaplanır; aynı gün giriş ve çıkış temel sürümde geçersizdir. |
| **BR-03** | Rezervasyondaki kişi sayısı, ilgili birimin kapasitesini aşmamalıdır. |
| **BR-04** | Müsaitlik, ilgili birim ve tarih aralığı bazında kontrol edilmelidir (oda tipi bazında değil, tekil birim bazında). |
| **BR-05** | Rezervasyonun kesinleşme koşulu ilanın rezervasyon moduna göre belirlidir: **Anında** modda başarılı (simüle) ödeme; **Onaylı** modda ilan sahibinin onayı. Her iki modda da rezervasyon, kesinleşene kadar Beklemede durumunda gecelerini tutar. |
| **BR-06** | İptal ve iade koşulları rezervasyon öncesinde kullanıcıya gösterilmelidir. |
| **BR-07** | Rezervasyonun toplam fiyatı, kullanıcı onayından önce kalemleriyle birlikte gösterilmelidir. |
| **BR-08** | Yalnızca ilan sahibi kendi ilanını ve o ilana ait rezervasyonları yönetebilmelidir. |
| **BR-09** | Yorum yalnızca tamamlanmış ve doğrulanmış bir rezervasyona bağlı olarak gönderilebilmelidir. |
| **BR-10** | Yönetici işlemleri izlenebilir olmalı; ilan kaldırma ve hesap kısıtlama gibi önemli işlemlerin nedeni kaydedilmelidir. |
| **BR-11** | **(Çekirdek)** Çakışma, yarı açık aralık kuralıyla tanımlanır: aynı birimde A.giriş < B.çıkış ve B.giriş < A.çıkış ise A ve B çakışır (Bölüm 4.2). |
| **BR-12** | **(Çekirdek)** Yalnızca Beklemede (expires_at geçmemiş) ve Onaylandı durumları geceleri engeller; İptal Edildi, Reddedildi, Süresi Doldu ve Tamamlandı engellemez. |
| **BR-13** | **(Çekirdek)** Bir rezervasyonun çıkış günü, aynı birimdeki başka bir rezervasyonun giriş günü olabilir. |
| **BR-14** | Giriş tarihi bugünden (sunucu saatiyle) önce olamaz; konaklama en çok 30 gece sürebilir; giriş tarihi en çok 365 gün ilerisinde olabilir. |
| **BR-15** | **(Çekirdek)** Bekletme süresi: anında modda 10 dakika, onaylı modda 24 saattir. Süre, Beklemede kaydın oluşturulduğu andan başlar. |
| **BR-16** | Rezervasyon oluşturulurken hesaplanan gecelik fiyatlar ve vergi/ek ücret kalemleri rezervasyonla saklanır; sonradan yapılan fiyat veya kural değişiklikleri mevcut rezervasyonu etkilemez. Rezervasyon onayı ile oluşturma arasında fiyat değişmişse kullanıcıya yeni döküm gösterilir ve yeniden onay alınır. |
| **BR-17** | **(Çekirdek)** İptal politikaları (simüle iade oranı, giriş tarihinden önceki kalan güne göre): **Esnek** — en az 1 gün kala %100; **Orta** — en az 5 gün kala %100, 1–4 gün kala %50; **Katı** — en az 14 gün kala %50, 1–13 gün kala %0. Gerçek iade yapılmaz. Hesap ayrıntısı Bölüm 4.11'dedir. |
| **BR-18** | İptal yalnızca Beklemede veya Onaylandı durumundaki, giriş tarihi henüz gelmemiş rezervasyonlar için yapılabilir; giriş günü ve sonrasında iptal edilemez. Bekletme süresi içindeki Beklemede rezervasyonun müşteri tarafından iptali iade gerektirmez. |
| **BR-19** | İlan sahibi kendi ilanı için müşteri olarak rezervasyon yapamaz. |
| **BR-20** | Bir rezervasyon tek bir birime aittir; birden fazla oda için ayrı rezervasyon yapılır (temel sürümde grup/sepet rezervasyonu yoktur). |
| **BR-21** | Bir rezervasyon için en çok bir yorum yapılabilir; puan 1–5 arası tam sayıdır. |
| **BR-22** | **(Çekirdek)** İlan sahibinin kapattığı geceler yeni rezervasyon alamaz; kapatma mevcut Beklemede/Onaylandı rezervasyonları etkilemez ve onların gecelerini kapsayan aralıkta yapılamaz. |
| **BR-23** | **(Çekirdek)** Gece fiyatı: takvim istisnası varsa o fiyat kesindir; yoksa temel fiyat × (1 + sezon yüzdesi) × (1 + hafta sonu yüzdesi). Hafta sonu geceleri Cuma ve Cumartesi geceleridir. |
| **BR-24** | **(Çekirdek)** Uzun konaklama indirimi, konaklama toplamına, ulaşılan en yüksek asgari gece eşiğinin yüzdesiyle uygulanır ve ayrı eksi kalem olarak gösterilir. Vergi ve ek ücretler indirimli tutar üzerinden hesaplanır. |
| **BR-25** | **(Çekirdek)** Tüm parasal değerler DECIMAL(10,2) ve TRY'dir; her gece fiyatı, indirim ve vergi kalemi kuruşa yarıdan yukarı yuvarlanır. Gecelik döküm toplamı toplam konaklama tutarına eşit olmalıdır. |
| **BR-26** | **(Çekirdek)** Aynı geceyi kapsayan birden fazla sezon kuralı varsa yalnızca en yüksek öncelikli kural uygulanır; öncelikleri eşitse ilan sahibine tanımlama sırasında hata gösterilir. |
| **BR-27** | **(Çekirdek)** İade tutarı = ödenen toplam × politika oranı; kesinti = ödenen − iade; iade ödenen tutarı aşamaz. Kalan gün = giriş tarihi − iptal tarihi. |
| **BR-28** | **(Çekirdek)** İlan sahibi reddi, ilan sahibi/yönetici kaynaklı iptal ve süre dolumunda ödenmiş tutarın %100'ü iade edilir. |
| **BR-29** | **(Çekirdek)** İptal politikası, rezervasyon oluşturulurken rezervasyonla saklanır; ilan politikası sonradan değişse de mevcut rezervasyonun iade koşulu değişmez. |
| **BR-30** | Doluluk oranı (%) = Onaylandı ve Tamamlandı rezervasyonların dönemle kesişen gece sayısı ÷ (aktif birim sayısı × dönemdeki gece sayısı) × 100. Net gelir = giriş tarihi dönem içinde olan rezervasyonların başarılı ödemeleri toplamı − iadeler (iptal kesintileri gelir sayılır). |

## 9. Kullanım Senaryoları
### 9.1 Senaryo UC-01: Konaklama Arama

| Alan | Değer |
| --- | --- |
| **Aktör** | Ziyaretçi veya kayıtlı müşteri |
| **Ön koşul** | Sistem erişilebilir durumda olmalıdır. |
| **Temel akış** | 1. Kullanıcı konum, tarih, kişi sayısı ve konaklama türünü girer.<br>2. Aramayı başlatır.<br>3. Sistem girdileri doğrular (BR-01, BR-02, BR-14).<br>4. Sistem, Bölüm 4.4 kuralına göre aralığın tüm gecelerinde müsait birimi olan aktif ilanları listeler.<br>5. Kullanıcı filtre veya sıralama uygular. |
| **Alternatif akış** | Geçersiz tarih veya kişi sayısı girilmişse sistem ilgili alanı işaretleyen hata mesajı gösterir. Sonuç bulunamazsa sistem boş sonuç mesajı gösterir. |
| **Son koşul** | Kullanıcı uygun ilanları görüntüleyebilir. Sonuçtaki müsaitlik ön bilgidir; kesin karar rezervasyon anında verilir. |

### 9.2 Senaryo UC-02: Rezervasyon Oluşturma (ÇEKİRDEK)

| Alan | Değer |
| --- | --- |
| **Aktör** | Kayıtlı müşteri; ikincil aktörler: sistem, ilan sahibi |
| **Ön koşul** | Kullanıcı oturum açmış olmalı; ilan ve birim aktif olmalı. |
| **Temel akış** | 1. Kullanıcı ilanı ve birimi seçer, tarih ve kişi sayısını girer.<br>2. Sistem ön kontrol yapar (müsaitlik, kapasite) ve gecelik dökümü, indirimi, vergi/ek ücretleri ve iptal koşullarını içeren fiyat dökümünü gösterir (Bölüm 4.10). Bu aşamada geceler henüz tutulmaz.<br>3. Kullanıcı rezervasyonu onaylar; form idempotency anahtarını taşır.<br>4. Sistem tek atomik işlemde: birim satırını kilitler, süresi dolmuş bekletmeleri temizler, Bölüm 4.4 kuralını yeniden doğrular, fiyatı yeniden hesaplar, rezervasyonu Beklemede olarak (expires_at, fiyat kalemleri ve iptal politikası anlık görüntüsüyle) kaydeder, gecelerini ayırır ve benzersiz numara üretir (FR-11, 12, 13, 33).<br>5. Anında modda sistem simüle ödemeyi başlatır; başarılıysa durum Onaylandı olur. Onaylı modda ilan sahibine bildirim gider ve talep Beklemede kalır.<br>6. Sistem durumu, numarayı ve (Beklemede ise) kalan süreyi gösterir; ilgili bildirimleri oluşturur. |
| **Alternatif akış** | **A1 — Çakışma:** Tarihler başka bir engelleyen rezervasyonla çakışıyorsa veya geceler kapatılmışsa işlem geri alınır, hiçbir kayıt oluşmaz; kullanıcıya mesaj ve en çok 3 alternatif aralık gösterilir (FR-36).<br>**A2 — Geçersiz giriş:** Geçersiz tarih veya kapasiteyi aşan kişi sayısı form hatası olarak gösterilir.<br>**A3 — Ödeme başarısız:** Rezervasyon Onaylandı olmaz; Beklemede kalır, süre dolana dek yeniden denenebilir; süre dolunca Süresi Doldu olur.<br>**A4 — Çift gönderim:** Aynı idempotency anahtarı, mevcut rezervasyonu gösterir; yeni kayıt oluşmaz.<br>**A5 — Fiyat değişmiş:** Yeni döküm gösterilir ve kullanıcıdan yeniden onay alınır (BR-16).<br>**A6 — Oturum yok:** Kullanıcı girişe yönlendirilir; seçimleri korunur.<br>**A7 — Sistem hatası / kilit zaman aşımı:** İşlem geri alınır, kullanıcıya "tekrar deneyin" mesajı gösterilir. |
| **Son koşul** | Başarıda: bir rezervasyon kaydı, çıkış − giriş adet gece kaydı ve bildirim vardır. Başarısızlıkta: sistem durumu işlem öncesiyle aynıdır. |

### 9.3 Senaryo UC-03: İlan, Fiyat ve Takvim Yönetimi

| Alan | Değer |
| --- | --- |
| **Aktör** | İlan sahibi / işletme |
| **Ön koşul** | İlan sahibi oturum açmış olmalı ve ilan yönetimi yetkisine sahip olmalıdır. |
| **Temel akış** | 1. İlan sahibi yönetim panelini açar.<br>2. Düzenlemek istediği ilanı seçer veya yeni ilan oluşturur; odaları (birimleri) oda tipi, kapasite ve temel fiyatla tanımlar.<br>3. İlan bilgilerini, konumu, olanakları, resimleri (yükleyerek), rezervasyon modunu, iptal politikasını ve fiyat kurallarını (hafta sonu, sezon, uzun konaklama) girer.<br>4. Tarih aralıklarına göre özel fiyat ve açık/kapalı geceleri takvim üzerinden tanımlar.<br>5. Sistem girdileri ve yetki durumunu doğrular, kapatılmak istenen gecelerin Beklemede/Onaylandı rezervasyonla çakışmadığını denetler ve değişiklikleri kaydeder. |
| **Alternatif akış** | Eksik veya hatalı bilgi girildiyse sistem ilgili alanları işaret ederek hata mesajı gösterir. İlan sahibi yetkili olmadığı başka bir ilanı değiştirmeye çalışırsa yetkisiz erişim engellenir. Rezervasyonlu geceyi kapatmaya çalışırsa işlem reddedilir ve ilgili rezervasyon bilgisi gösterilir. |
| **Son koşul** | İlan bilgileri, özel fiyatlandırma ve takvim müsaitliği başarıyla güncellenir ve arama sonuçlarına yansıtılır. |

### 9.4 Senaryo UC-04: Rezervasyon İptali ve Tarihlerin Serbest Bırakılması (ÇEKİRDEK)

| Alan | Değer |
| --- | --- |
| **Aktör** | Kayıtlı müşteri (yönetici de FR-39 kapsamında iptal edebilir) |
| **Ön koşul** | Rezervasyon müşteriye aittir, Beklemede veya Onaylandı durumundadır ve giriş tarihi gelmemiştir. |
| **Temel akış** | 1. Müşteri rezervasyonlarım ekranından iptal etmek istediği rezervasyonu seçer.<br>2. Sistem rezervasyonla saklanan iptal politikasına göre kalan günü, iade oranını, iade tutarını ve kesintiyi gösterir (Bölüm 4.11) ve onay ister.<br>3. Müşteri onaylar.<br>4. Sistem tek işlemde durumu İptal Edildi yapar, gece kayıtlarını siler, iade tutarını Payment kaydına işler ve denetim kaydı yazar.<br>5. Sistem müşteriye ve ilan sahibine bildirim gönderir; tarihler anında yeniden müsait olur. |
| **Alternatif akış** | Giriş günü gelmişse veya rezervasyon son durumdaysa iptal reddedilir (BR-18). Yetkisiz kullanıcı başkasının rezervasyonunu iptal etmeye çalışırsa erişim engellenir. |
| **Son koşul** | Rezervasyon İptal Edildi durumundadır; geceleri arama ve rezervasyon için müsaittir. |

### 9.5 Senaryo UC-05: Rezervasyon Talebinin Onaylanması / Reddedilmesi

| Alan | Değer |
| --- | --- |
| **Aktör** | İlan sahibi |
| **Ön koşul** | İlan onaylı moddadır; ilgili rezervasyon Beklemede durumundadır ve bekletme süresi dolmamıştır. |
| **Temel akış** | 1. İlan sahibi gelen talepler listesinden rezervasyonu açar.<br>2. Onaylar veya reddeder.<br>3. Onayda durum Onaylandı olur, geceler korunur. Retde durum Reddedildi olur ve geceler serbest bırakılır.<br>4. Sistem müşteriye bildirim gönderir. |
| **Alternatif akış** | Bekletme süresi (24 saat) dolmuşsa onay reddedilir ve rezervasyon Süresi Doldu durumundadır. İlan sahibi başka bir sahibin rezervasyonunu yönetmeye çalışırsa erişim engellenir. |
| **Son koşul** | Rezervasyon Onaylandı veya Reddedildi durumundadır; müşteri bilgilendirilmiştir. |

## 10. Kabul Kriterleri
AC-05 netleştirilmiş, AC-03 ve AC-09 genişletilmiş, AC-11 – AC-32 yeni eklenmiştir. Son sütun, kriterin çekirdek test gereksinimiyle (Bölüm 4.8) bağlantısını gösterir.

| Kimlik | Kabul Koşulu | Test |
| --- | --- | --- |
| **AC-01** | Geçerli bilgilerle yeni kullanıcı hesabı oluşturulabilmelidir. |  |
| **AC-02** | Arama, girilen konum ve tarih aralığına uygun aktif ilanları listelemelidir. |  |
| **AC-03** | Geçersiz tarih aralığı (çıkış ≤ giriş, geçmiş tarih, 30 geceden uzun) ve kapasiteyi aşan kişi sayısı reddedilmelidir. | T-01 |
| **AC-04** | Müsait bir birim için rezervasyon oluşturulduğunda benzersiz rezervasyon numarası üretilmelidir. |  |
| **AC-05** | Aynı birim için çakışan tarih aralıklarında eş zamanlı iki istek, iki engelleyen rezervasyon oluşturamamalıdır. | T-05 |
| **AC-06** | Kullanıcı yalnızca kendi rezervasyonlarını görebilmeli ve kurallara uygun olanları iptal edebilmelidir. |  |
| **AC-07** | İlan sahibi yalnızca yetkili olduğu ilanları düzenleyebilmeli ve bu ilanlara ait rezervasyonları görebilmelidir. |  |
| **AC-08** | Toplam fiyat, kullanıcı rezervasyonu onaylamadan önce gecelik döküm, indirim, vergi ve ek ücret kalemleriyle açıkça gösterilmelidir. | T-13 |
| **AC-09** | Yönetici ilanı yayından kaldırdığında yeni rezervasyon oluşturulması engellenmeli ve ilanın gelecekteki rezervasyonları iptal edilmelidir. |  |
| **AC-10** | Temel akışlar desteklenen masaüstü ve mobil tarayıcılarda kullanılabilir olmalıdır. |  |
| **AC-11** | 10–12 Kasım için Onaylandı rezervasyon varken 12–14 Kasım ve 8–10 Kasım istekleri kabul edilmeli; 9–11, 11–13, 10–12, 9–13 ve 10–11 istekleri reddedilmelidir. | T-01 |
| **AC-12** | Çakışma nedeniyle reddedilen istek hiçbir rezervasyon veya gece kaydı bırakmamalı; kullanıcıya açık mesaj ve en çok 3 alternatif aralık gösterilmelidir. | T-07 |
| **AC-13** | 50 eşzamanlı istekte (aynı birim, aynı aralık) tam olarak 1 rezervasyon oluşmalı, gece kaydı sayısı gece sayısına eşit olmalıdır. | T-05 |
| **AC-14** | Arka arkaya (çıkış günü = giriş günü) iki rezervasyon eşzamanlı gönderildiğinde ikisi de kabul edilmelidir; kısmen kesişen iki istekten yalnızca biri kabul edilmelidir. | T-06 |
| **AC-15** | Bekletme süresi dolan Beklemede rezervasyon, zamanlanmış görev çalışmamış olsa bile yeni bir rezervasyonu engellememeli; süresi dolan kayıt Süresi Doldu olmalıdır. | T-03 |
| **AC-16** | İptal, ret veya süre dolumundan sonra geceler arama sonuçlarında ve takvimde müsait görünmeli ve yeniden rezerve edilebilmelidir. | T-02 |
| **AC-17** | Aynı idempotency anahtarıyla art arda gönderilen iki istek tek rezervasyon oluşturmalıdır. | T-04 |
| **AC-18** | İlan sahibi, Beklemede/Onaylandı rezervasyonla çakışan geceleri kapatamamalıdır. | T-09 |
| **AC-19** | Başarısız simüle ödemede rezervasyon Onaylandı olmamalıdır. |  |
| **AC-20** | Dolu veya kapalı birimi olan ilan, o tarih aralığı için aramada müsait görünmemeli; aramada görünen birim, eşzamanlı istek yoksa rezervasyonda kabul edilmelidir. | T-08 |
| **AC-21** | Rezervasyon sonrası fiyat veya vergi kuralı değişikliği mevcut rezervasyonun tutarını değiştirmemeli; kalemlerin toplamı toplam fiyata eşit olmalıdır. |  |
| **AC-22** | Tüm çekirdek testler tamamlandığında tutarlılık denetim sorgusu (aynı birim ve gece için birden fazla engelleyen kayıt) boş sonuç vermelidir. | T-11 |
| **AC-23** | Temel fiyat 1.000 TL, Temmuz sezonu +%30, hafta sonu +%20 iken 9–12 Temmuz 2027 (Cum, Cmt, Paz) konaklaması 1.560,00 + 1.560,00 + 1.300,00 = 4.420,00 TL konaklama tutarı vermelidir. | T-12 |
| **AC-24** | 12–19 Temmuz 2027 (7 gece) konaklamasında %10 uzun konaklama indirimi ayrı eksi kalem olarak uygulanmalı; %10 vergiyle toplam 9.523,80 TL olmalıdır. | T-12 |
| **AC-25** | Takvim istisnası olan gecede sezon ve hafta sonu farkı uygulanmamalı; çakışan iki sezon kuralında yüksek öncelikli olan uygulanmalı; gecelik döküm toplamı toplam konaklama tutarına eşit olmalıdır. | T-12, T-13 |
| **AC-26** | Orta politikada girişe 5 gün kala iptalde %100, 4 gün kala %50 iade; Katı politikada 14 gün kala %50, 13 gün kala %0 iade; Esnek politikada 1 gün kala %100 iade; giriş gününde iptal reddedilmelidir. | T-14 |
| **AC-27** | İlan sahibi reddi, ilan sahibi/yönetici kaynaklı iptal ve süre dolumunda ödenmiş tutarın tamamı iade edilmeli; ödemesiz Beklemede iptalinde iade ve kesinti 0 olmalıdır. | T-14 |
| **AC-28** | İlan sahibi ilan politikasını değiştirdiğinde mevcut rezervasyonların iade hesabı rezervasyon anındaki politikaya göre yapılmalıdır. | T-14 |
| **AC-29** | Örnek veride en az 10 birim ve en az 3 farklı oda tipi bulunmalı; arama oda tipine göre filtrelenebilmelidir. |  |
| **AC-30** | Doluluk ve net gelir raporu, bilinen test verisiyle BR-30 formüllerinin elle hesaplanmış sonuçlarına eşit olmalı; yönetici tüm ilanları, ilan sahibi yalnızca kendi ilanlarını görmelidir. | T-15 |
| **AC-31** | İlan sahibi JPEG/PNG/WebP resim yükleyebilmeli; farklı tür, 2 MB'tan büyük veya sahte uzantılı dosya ve başkasının ilanına yükleme reddedilmelidir. |  |
| **AC-32** | İlan detayında aylık takvim ay ileri/geri gezilebilmeli; dolu ve kapalı geceler ayırt edilebilmelidir. |  |

## 11. Varsayımlar, Kısıtlar ve Kapsam Dışı Konular
### 11.1 İlk Sürüm Varsayımları
- İlk sürüm web uygulaması olarak geliştirilecektir; yerel Android/iOS uygulaması kapsamda değildir.
- İlan sahiplerinin kimlik veya işletme doğrulama süreci, proje gereksinimlerine göre manuel olarak yürütülebilir.
- Sistem otel odası ve tüm ev kiralama türlerini destekleyecektir; her oda ve her ev ayrı bir birim olarak modellenir, çakışma birim bazında denetlenir.

### 11.2 İlk Sürümde Kapsam Dışında Bırakılabilecek Özellikler
- Gerçek para tahsilatı ve otomatik iade entegrasyonu.
- Canlı sohbet ve gerçek zamanlı mesajlaşma.
- Çoklu dil ve çoklu para birimi desteği.
- Talebe göre otomatik değişen fiyatlandırma ve yapay zekâ tabanlı öneriler (kural tabanlı sezon, hafta sonu ve uzun konaklama fiyatlandırması kapsam **içindedir**, Bölüm 4.10).
- Harici harita, kimlik doğrulama veya kanal yöneticisi (OTA senkronizasyonu) entegrasyonları.
- Bilinçli aşırı satış (overbooking) politikaları; oda tipi bazında (belirli oda atamadan) envanter yönetimi; grup/sepet rezervasyonu.

### 11.3 Açık Kararlar ve Proje Karar Yanıtları

| Açık Karar Sorusu | Alınan Proje Kararı |
| --- | --- |
| **Rezervasyonlar anında mı kesinleşecek, yoksa ilan sahibinin onayı mı gerekecek?** | İlan başına seçilebilir rezervasyon modu vardır (FR-38). Anında modda rezervasyon Beklemede kaydedilir (10 dk) ve başarılı simüle ödemeyle Onaylandı olur; onaylı modda Beklemede kalır (24 saat) ve ilan sahibinin onayıyla (FR-23) Onaylandı olur. Her iki modda da Beklemede geceleri tutar (BR-05, BR-15). |
| **İptal ve iade kuralları nasıl belirlenecek?** | İlan başına üç politika (esnek/orta/katı) vardır ve ilan detayında (FR-09) ile rezervasyon öncesinde (BR-06) gösterilir. İptal giriş gününden önce yapılabilir (BR-18), iade simüle hesaplanır (BR-17), iptalde geceler aynı işlemde serbest bırakılır (FR-17). |
| **Ödeme ilk sürümde simüle mi edilecek, yoksa gerçek bir sağlayıcı mı kullanılacak?** | Bölüm 11.2 uyarınca gerçek para tahsilatı kapsam dışıdır; ödeme simüle edilecek (FR-37) ve kart bilgileri veritabanında saklanmayacaktır. |
| **Otel rezervasyonları oda bazında mı, oda tipi bazında mı yönetilecek?** | Her otel odası ve her kiralık ev ayrı bir birimdir (BookableUnit); müsaitlik ve çakışma birim ve tarih aralığı bazında yönetilir (BR-04, FR-42). |
| **Hangi para birimi, vergi ve ek ücret kuralları kullanılacak?** | İlk sürümde tek para birimi (TRY) ve DECIMAL(10,2) kullanılır; fiyat gece bazında, sezon ve hafta sonu farklarıyla hesaplanır, uzun konaklama indirimi ve ilan bazında tanımlı vergi/ek ücret kuralları ayrı kalemlerle gösterilir (FR-14, FR-43, BR-07, BR-23 – BR-26). |
| **Sezonluk dinamik fiyat nasıl hesaplanacak? (çekirdek problem)** | Kural tabanlı, gece bazlı: takvim istisnası kesindir; aksi hâlde temel × sezon × hafta sonu; ardından uzun konaklama indirimi, vergi ve ek ücret (Bölüm 4.10). |
| **İptal ve iade hesabı nasıl yapılacak? (çekirdek problem)** | Rezervasyonla saklanan politika anlık görüntüsüne ve kalan güne göre oran uygulanır; ilan sahibi/yönetici kaynaklı iptallerde %100 iade (Bölüm 4.11). |
| **Çakışma tam olarak nasıl tanımlanacak? (çekirdek problem)** | Yarı açık aralık [giriş, çıkış) kullanılır; aynı gün çıkış–giriş serbesttir; yalnızca Beklemede (süresi dolmamış) ve Onaylandı geceleri engeller (BR-11, BR-12, BR-13). |
| **Eşzamanlılık nasıl garanti edilecek? (çekirdek problem)** | Uygulama kontrolü + atomik işlem ve birim kilidi + (birim, gece) UNIQUE kısıtı; Aşama 2'de dosya kilidi (Bölüm 4.5). |

### 11.4 Ekibin Doğrulaması Gereken Varsayımlar
Aşağıdaki sayısal değerler bu revizyonda öneri olarak belirlenmiştir; ekip veya ders yürütücüsü farklı bir değer isterse ilgili BR/NFR güncellenmelidir.

| Konu | Önerilen değer | Etkilediği gereksinimler |
| --- | --- | --- |
| **Bekletme süresi** | Anında mod 10 dakika; onaylı mod 24 saat | BR-15, FR-34, AC-15 |
| **Konaklama sınırları** | En çok 30 gece; en çok 365 gün ileri | BR-14, AC-03 |
| **İptal politikası oranları** | Esnek %100 (≥1 gün); Orta %100 (≥5 gün) / %50; Katı %50 (≥14 gün), sonrası %0 | BR-17, BR-27 |
| **Hafta sonu geceleri** | Cuma ve Cumartesi geceleri | BR-23 |
| **Uzun konaklama eşikleri ve oranlar** | İlan sahibi tanımlar (örnek: ≥7 gece %10); en yüksek ulaşılan eşik uygulanır | BR-24 |
| **Örnek veri** | En az 10 birim ve 3 oda tipi | AC-29 |
| **Resim sınırları** | JPEG/PNG/WebP, en çok 2 MB | NFR-19 |
| **Alternatif tarih önerisi** | En çok 3 öneri | FR-36, AC-12 |
| **Eşzamanlılık test yükü** | 50 paralel istek | NFR-05, NFR-13, AC-13 |
| **Çekirdek servis test kapsamı** | %90 satır kapsamı | NFR-16 |
| **Zamanlanmış görev sıklığı** | Dakikada bir | FR-34, FR-40 |
| **İlan silme** | Mantıksal silme; gelecekte Onaylandı rezervasyonu olan ilan silinemez | FR-21 |
| **Birimin saat çözünürlüğü** | Gün (saatlik konaklama yok); giriş 14:00 / çıkış 12:00 yalnızca bilgi | BR-13, 2.5 |

### 11.5 Volere Alanlarına İlişkin Varsayımlar ve Açık Kararlar
Aşağıdaki değerler ve kararlar Volere kartlarına dönüştürme sırasında öneri olarak belirlenmiştir; ekip veya ders yürütücüsü farklı bir değer isterse ilgili kart güncellenmelidir.

| Konu | Önerilen değer / varsayım | Etkilediği gereksinimler |
| --- | --- | --- |
| **Öncelik ölçeği** | Zorunlu / Gerekli / İsteğe bağlı. ÇEKİRDEK etiketli gereksinimlerin tümü Zorunlu; diğerleri Ek A'daki asgari uygulama kapsamına göre atanmıştır. Atamalar öneridir. | Tüm FR ve NFR |
| **Memnuniyet ve memnuniyetsizlik puanları** | 1 – 5 ölçeği. Puanlar bu revizyonda öneri olarak girilmiştir; müşteri/ders yürütücüsü ile gözden geçirilmelidir. | Tüm FR ve NFR |
| **Kaynak (Originator)** | Kişi adı yerine paydaş rolü yazılmıştır. Çekirdek problemler için "Ders proje tanımı" kullanılmıştır. Her gereksinimin gerçek kaynağı belge geçmişinden doğrulanamadığı için ekip teyit etmelidir. | Tüm FR ve NFR |
| **† Kullanılabilirlik testi** | 5 test kullanıcısı; en az 4'ü arama–rezervasyon akışını yardım almadan 5 dakika içinde tamamlar. | NFR-06 |
| **† Mobil tarayıcı kapsamı** | En az bir mobil tarayıcıda test edilir. Hangi tarayıcı ve cihaz kümesinin kullanılacağı ekip tarafından belirlenmelidir. | NFR-07, AC-10 |
| **Açık karar: "uygunluk" sıralama ölçütü** | Formülü tanımlı değildir. Tanımlanana kadar FR-08 yalnızca fiyat ve puan sıralamasıyla doğrulanır. | FR-08 |
| **Açık karar: profilde güncellenebilir alanlar** | Liste tanımlı değildir. Öneri: ad, soyad ve parola güncellenebilir; rol hiçbir koşulda kullanıcı tarafından değiştirilemez. | FR-31 |
| **Dönüştürme kapsamı** | Bu revizyonda yalnızca FR ve NFR kartlandı. Bölüm 2.4 kısıtları, Bölüm 5 arayüz maddeleri, iş kuralları (Bölüm 8) ve kabul kriterleri (Bölüm 10) mevcut biçimiyle korunmuş; kabul kriterleri uygunluk ölçütlerinde bağlantı olarak kullanılmıştır. | Bölüm 2.4, 5, 8, 10 |

## Ek A. Önerilen Asgari Uygulama Kapsamı
Ders projesinin ilk çalışan sürümü için üyelik/giriş, konaklama arama, ilan ve birim detayları (en az 10 birim ve 3 oda tipi), aylık müsaitlik takvimi, **çift rezervasyon önleyen rezervasyon oluşturma**, gecelik fiyat dökümü, iptal ve iade, resim yükleme, ilan sahibi paneli, doluluk ve gelir raporu ve basit yönetici paneli önerilir. Gerçek ödeme, mesajlaşma ve harita entegrasyonları sonraki sürüme bırakılabilir.

**Geliştirme sırası önerisi:** (1) proje iskeleti ve Composer; (2) BookableUnit, Reservation, ReservationNight modelleri ve repository arayüzleri; (3) BookingConflictService ve T-01/T-02/T-05 testleri; (4) PricingService ve T-12/T-13 testleri; (5) rezervasyon akışı (UC-02) ve RefundService ile iptal (UC-04, T-14); (6) diğer özellikler. Dört çekirdek problem, diğer özelliklerden önce çalışır ve test edilmiş olmalıdır.

## Ek B. Gereksinim İzlenebilirliği Matrisi
Geliştirme ve test sırasında her FR/NFR kimliği için ilgili tasarım bileşeni ve test senaryosu aşağıda belirlenmiştir; böylece her gereksinimin uygulanıp uygulanmadığı ve test edilip edilmediği takip edilebilir.

| Gereksinim Kimliği | İlgili Tasarım Bileşeni (src/) | Doğrulama ve Test Senaryosu (tests/ ve Kabul Kriterleri) |
| --- | --- | --- |
| FR-01, 02, 03, 04, 05, NFR-02, 03, 14, 15 | AuthController, AuthService, UserRepositoryInterface, User, PasswordResetToken | AC-01: hesap oluşturma, parola özetleme, sunucu taraflı rol kontrolü, oturum ve CSRF testleri. |
| FR-06, 07, 08, 09, 10, NFR-01, 06, 07 | PropertyController, PropertyService, AvailabilityService (BookingConflictService kuralı), PropertyRepositoryInterface, Property, BookableUnit, CalendarEntry, Photo, Amenity | AC-02, AC-20, UC-01, T-08: konum, tarih ve kişi sayısına göre aktif ve müsait ilanları filtreleme/sıralama. |
| **ÇEKİRDEK** FR-11, 12, 13, 15, 16, 17, 18, 33, 34, 35, 36; BR-11, 12, 13, 14, 15, 18; NFR-04, 05, 13, 16, 17, 18 | ReservationController, ReservationService, BookingConflictService, ReservationState (+ alt durumlar), ReservationRepositoryInterface (Json/MySql), Reservation, ReservationNight | AC-03, 04, 05, 06, 11, 12, 13, 14, 15, 16, 17, 22; UC-02, UC-04; T-01 – T-07, T-10, T-11. |
| **ÇEKİRDEK** FR-14, 43, 44; BR-02, 07, 16, 23, 24, 25, 26 | PricingService (Strategy: BaseRate, Seasonal, Weekend, LongStay), PriceRule, PricingRule, ReservationCharge, HostController, PropertyController | AC-08, 21, 23, 24, 25; T-12, T-13: gece bazlı fiyat, sezon/hafta sonu/uzun konaklama, yuvarlama, gecelik döküm ve fiyat anlık görüntüsü. |
| **ÇEKİRDEK** FR-45; BR-17, 27, 28, 29 | RefundService (Strategy: Flexible, Moderate, Strict), ReservationService, Payment, Reservation | AC-26, 27, 28; UC-04; T-14: politika eşikleri, iade/kesinti hesabı, politika anlık görüntüsü. |
| FR-37 | PaymentService (simüle), PaymentRepositoryInterface, Payment | AC-19: başarısız ödemede Onaylandı olmama, yeniden deneme ve süre dolumu testi. |
| FR-19, 31 | ProfileController, FavoriteService, UserService, FavoriteRepositoryInterface, Favorite | Profil güncelleme ve favori ekleme/çıkarma testi. |
| **ÇEKİRDEK** FR-22, 23; FR-20, 21, 38, 42; BR-08, 19, 20, 22 | HostController, PropertyService, AvailabilityService, ReservationService, BookableUnitRepositoryInterface, CalendarRepositoryInterface | AC-07, AC-18; UC-03, UC-05; T-09: yalnızca kendi ilanı, kapatma çakışması ve onay/ret testi. |
| FR-46, NFR-19 | HostController, PhotoService, Photo | AC-31: resim türü/boyut/yetki doğrulama testi. |
| FR-24, 25, BR-09, 21 | ReviewController, AdminController, ReviewService, ReviewRepositoryInterface, Review | Yalnızca tamamlanmış konaklamaya tek yorum ve yönetici yorum denetimi testi. |
| **ÇEKİRDEK** FR-39; FR-26, 27, 28, 29, 30, 40, 41; BR-30; BR-10; NFR-08, 09, 10, 11, 12 | AdminController, ComplaintController, NotificationService, ComplaintService, ReportService, AuditService, Notification, Complaint, AuditLog | AC-09, AC-30; T-15: ilan kaldırılınca yeni rezervasyon engeli ve gelecek rezervasyonların iptali; bildirim, şikâyet, doluluk/gelir raporu ve denetim kaydı testi. |

### B.1 Kullanıcı Hikâyesi → Gereksinim Eşlemesi

| Hikâye | Fonksiyonel gereksinimler | Senaryo | Kabul kriterleri |
| --- | --- | --- | --- |
| **US-01** | FR-06, 07, 08, 09, 10 | UC-01 | AC-02, AC-20 |
| **US-02 (çekirdek)** | FR-11, 12, 13, 14, 15, 16, 18, 33, 34, 35, 36, 37, 44 | UC-02 | AC-03, 04, 05, 08, 11 – 15, 17, 19, 21 – 25 |
| **US-03** | FR-20, 21, 22, 23, 38, 42, 43, 46 | UC-03, UC-05 | AC-07, AC-18, AC-29, AC-31, AC-32 |
| **US-04 (çekirdek)** | FR-17, 34, 39, 40, 45 | UC-04 | AC-06, AC-09, AC-16, AC-26, AC-27, AC-28 |
