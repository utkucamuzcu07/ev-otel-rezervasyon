# Yapay Zekâ Kullanım Günlüğü

Ders kuralı: kodlamada yapay zekâ kullanılabilir, ancak grubun her üyesi projenin her bölümünü açıklayabilmelidir. Bu dosyada her yapay zekâ kullanımı; kimin, ne zaman, hangi araçla, ne amaçla kullandığı ve çıktının nasıl doğrulandığıyla birlikte kayıt altına alınır. Her üye kendi kullanımını yeni bir satır/başlık olarak ekler.

## Kayıt biçimi

| Alan | Açıklama |
| --- | --- |
| Tarih | Kullanım tarihi |
| Üye | Aracı kullanan grup üyesi |
| Araç / model | Örn. Claude Code (Claude Opus 5.5), ChatGPT, Copilot |
| Amaç | Ne için kullanıldı |
| İstem özeti | Yapay zekâya ne verildi (bağlam: SRS, tasarım, ders kuralları) |
| Çıktı | Ne üretildi (dosyalar) |
| Doğrulama | Üyenin nasıl kontrol ettiği, neyi değiştirdiği |

## Kayıtlar

### 07.11.2026 · Kaan Şahin · Claude Code (Claude Opus 5.5)

| Alan | İçerik |
| --- | --- |
| Amaç | Aşama 2 (veritabanısız geliştirme) prototipinin yazılması ve Aşama 1 belgelerindeki eksiklerin giderilmesi |
| İstem özeti | Ders proje belgesindeki ortak şartlar (zorunlu ve bonus), proje 12 kartı (zorunlu özellikler), depodaki SRS v2.3, tasarim.md ve 6 arayüz taslağı bağlam olarak verildi. Yalnızca Aşama 2'ye uyan zorunlu ve bonus maddelerin yapılması, SRS ve ders kurallarından sapılmaması istendi. Mimari tablo (Controller → Service → Repository) ve SRS Bölüm 4.7 sınıf eşlemesi her adımda kural olarak uygulandı (risk R7). |
| Çıktı | `src/` (katmanlı mimari, JSON depo, çekirdek servisler, Strategy ve State desenleri), `views/` (Bootstrap arayüz), `config/`, `bin/`, `tests/` (137 PHPUnit testi), `docs/srs.md` (SRS'in .docx'ten Markdown'a dönüşümü), `docs/tasarim.md` düzeltmeleri, `README.md` |
| Doğrulama | Tüm testler çalıştırıldı (137 test, 429 doğrulama; 50 paralel süreçle çift rezervasyon testi dahil). PHPStan seviye 6 ve PSR-12 denetimi hatasız. Uygulama tarayıcıda uçtan uca denendi: arama, ilan detayı, takvim, giriş, rezervasyon, simüle ödeme, iptal ve iade, ilan sahibi onayı, resim yükleme, rapor ve CSV. SRS örnek hesapları (AC-23: 4.420,00 TL; AC-24: 9.523,80 TL; iade örnekleri) testlerle birebir doğrulandı. |
| Dikkat edilecekler (07.11) | Her üyenin sunumda açıklayabilmesi için kodun okunması gerekir. Önerilen okuma sırası: `config/routes.php` → `src/Core/Kernel.php` → `src/Controller/ReservationController.php` → `src/Service/ReservationService.php` → `src/Service/BookingConflictService.php` → `src/Service/PricingService.php` ve `Pricing/` → `src/Service/RefundService.php` → `src/Service/State/` → `src/Repository/Json/JsonDatabase.php`. SRS'nin açık bıraktığı noktalarda verilen kararlar tasarim.md bölüm 12.2'dedir; ekip ve ders yürütücüsüyle teyit edilmelidir. |

### 08.11.2026 · Kaan Şahin · Claude Code (Claude Opus 5.5)

| Alan | İçerik |
| --- | --- |
| Amaç | Projenin ders standart tablosuna (dil, mimari, tasarım, veri erişimi, veritabanı, eşzamanlılık, güvenlik, etkileşim, test, kod kalitesi, süreç) göre yeniden taranması; eksik ve yanlışların düzeltilmesi; SRS'teki her gereksinimin GitHub'da aşamalandırılması. |
| İstem özeti | Standart tablosunun görüntüsü, önceki zorunlu şartlar ve SRS v2.3 verildi. Verilerin JSON'da kalması, mimarinin SRS'e göre düzeltilmesi, framework kullanılmaması ve ekip deposuna hiçbir şey gönderilmemesi istendi. |
| Çıktı | JSON AJAX ucu (`ApiController`, `/api/musaitlik`); SRS 4.7 düzeltmeleri (arama `AvailabilityService`'i kullanıyor, `ReservationController` yalnızca `ReservationService`'e bağlı, `reserveNights()`, oda bazında fotoğraf FR-46); PDO + MySQL katmanı (`src/Repository/MySql`, 17 depo, `Migrator`), `database/migrations` (3NF, FK/UNIQUE/CHECK), JOIN'li rapor sorgusu (`reportRows`), birim satır kilidi (`SELECT … FOR UPDATE`) ve kilit zaman aşımında yeniden deneme; NFR-09 yedekleme (`BackupService`, `bin/yedek.php`, günlük yedek); gereksinim–test izlenebilirlik üreticisi (`bin/izlenebilirlik.php`, `docs/test-izlenebilirlik.md`); 56 yeni test (toplam 193); `.gitattributes` (LF); GitHub'da 3 aşama milestone'u, 27 etiket ve 76 issue (her FR/NFR bir issue). |
| Doğrulama | 193 testin tamamı hem JSON hem MySQL 8.4 deposunda geçti (MySQL'de 50 paralel süreçli testler dahil; SRS 4.8 gereği uygulama kontrolü kapalıyken de yalnızca UNIQUE kısıtıyla tek kayıt). NFR-16 kapsamı dört çekirdek serviste %96–%99,7. PHPStan seviye 6 (testler dahil) ve PSR-12 hatasız. MySQL modunda sayfalar ve JSON API gerçek sunucuyla denendi; geri yükleme denemesi JSON, MySQL ve JSON → MySQL için yapıldı ([yedekleme.md](yedekleme.md)). 50 eşzamanlı aramada p95: JSON 597 ms, MySQL 631 ms. |
| Dikkat edilecekler (08.11) | Varsayılan depo bilinçli olarak JSON bırakıldı (Aşama 2 kuralı); finalde MySQL'e geçiş GitHub #77'de. Tarayıcı uyumluluğu (NFR-07/AC-10) ile klavye ve kontrast denetimi (NFR-12) elle yapılmalı (#57, #51). GitHub Projects panosu için hesap sahibinin `gh auth refresh -s project` çalıştırması gerekiyor (#71). Sunumda açıklanması önerilen yeni dosyalar: `src/Repository/MySql/MySqlDatabase.php`, `MySqlReservationRepository.php` (`lockUnit`, `reportRows`), `database/migrations/003_rezervasyonlar.sql`, `src/Core/Container.php` (`repository()`), `src/Service/BackupService.php`. |

### 08.11.2026 · Kaan Şahin · Claude Code (Claude Opus 5.5) · Projects panosu

| Alan | İçerik |
| --- | --- |
| Amaç | GitHub Projects backlog panosunun kurulması (#71) ve issue önceliklerinin SRS ile denetlenmesi |
| İstem özeti | Hesap sahibi `gh auth refresh -s project` ile pano yetkisini verdi ve kurulumu onayladı. Ders standart tablosundaki "GitHub Projects backlog" şartı ve #71'in kabul ölçütü (Yapılacak / Yapılıyor / Bitti sütunları, milestone'a göre gruplama) kural olarak verildi. |
| Çıktı | [GitHub Projects panosu](https://github.com/users/Kayazilim/projects/1) (private, depoya bağlı): 76 issue ve 2 PR; Türkçe Status sütunları (seçenek kimlikleri korunarak yeniden adlandırıldı, böylece hazır otomasyonlar çalışmaya devam ediyor); SRS önceliğinden doldurulan Öncelik alanı; Pano (Kanban), Backlog ve aşama başına üç filtreli görünüm. 18 çekirdek FR issue'suna eksik `zorunlu` etiketi eklendi. README'deki süreç satırı panoya bağlandı. |
| Doğrulama | SRS'teki 65 gereksinimin önceliği issue etiketleri ve pano alanıyla tek tek karşılaştırıldı; düzeltmeden sonra uyuşmazlık 0. Aşama görünümlerindeki kart sayıları (2 / 68 / 6) milestone'larla aynı. #71 kapatılınca kartı kendiliğinden Bitti sütununa geçti (otomasyon canlı denendi). Açık kalan issue'lar: #51, #57, #77. |
| Dikkat edilecekler (08.11) | Düzeltilen hata: önceki issue planı SRS'teki "Zorunlu · ÇEKİRDEK" biçimini tanımadığı için 18 FR'de (#9, #13–#16, #18–#22, #24–#26, #28, #30, #33, #34, #48) `zorunlu` etiketi eksik kalmıştı. Pano ve depo private; ekip üyelerinin ve ders yürütücüsünün görmesi için ikisine de erişim verilmeli. Bir kart elle Bitti sütununa çekilirse issue'su da kapanır ("Auto-close issue" otomasyonu). Tek tabloda milestone'a göre gruplama arayüzden açılır (Backlog → ▾ → Group by → Milestone); GitHub API bu ayarı desteklemiyor. |

### 08.11.2026 · Kaan Şahin · Claude Code (Claude Opus 5.5) · Parola sıfırlama e-postası

| Alan | İçerik |
| --- | --- |
| Amaç | Yerel sunucuda bildirilen sorunun giderilmesi: kayıtlı olmayan e-postada da "gönderildi" deniyor, sıfırlama bağlantısı gelmiyor, gelse de e-posta kutusunda görünmüyor (FR-04). |
| İstem özeti | Kullanıcının hata bildirimi; ardından "devam et, yapamadığını yap". Ders kuralları (framework ve harici kütüphane yok, katmanlı mimari) ve SRS FR-04 bağlam olarak verildi. |
| Çıktı | Kök neden: akış kayıtlı adreste çalışıyordu (ileti `storage/mail`'e yazılıyor, bağlantı açılıyordu); kayıtlı olmayan adreste aynı yanıt bilinçliydi (hesap varlığı sızdırılmaz); simüle e-posta tarayıcıdan görülemiyordu; dosya yazılamazsa hata yutuluyordu. Yapılanlar: `/posta-kutusu` geliştirme sayfası (`FileMailbox`, `MailboxController`; yalnızca yerel istek, `EVOTEL_POSTA_KUTUSU` ile kapanır, `Cache-Control: no-store`); isteğe bağlı gerçek gönderim (`SmtpMailer`: STARTTLS/TLS, AUTH PLAIN/LOGIN, UTF-8 konu, kütüphanesiz; ayarlar git'e girmeyen `config/yerel.php`'de, deneme aracı `bin/eposta-dene.php`); yazma hatasında `MailDeliveryException` → 503 + günlük; sayfa ve bildirim metinleri netleştirildi. 16 yeni test (`FileMailerTest`, `FileMailboxTest`, `SmtpMailerTest` + sahte SMTP sunucusu, `PasswordResetWebTest`, `SettingsTest`). |
| Doğrulama | 209 testin tamamı JSON ve MySQL 8.4'te geçti; PHPStan ve PHPCS hatasız (eski bir satır uzunluğu uyarısı da giderildi). SMTP istemcisi testte açılan sahte bir sunucuyla, TLS yolu gerçek sunucuyla denendi (smtp.gmail.com:587; kimlik bilgisi olmadan, ileti göndermeden): STARTTLS ve Windows'ta sertifika doğrulaması çalıştı. Yerel sunucuda deneme hesabıyla uçtan uca denendi: kayıt → sıfırlama talebi → posta kutusu → bağlantı → yeni parola → yeni parolayla giriş; bağlantının ikinci kullanımı reddedildi. |
| Dikkat edilecekler (08.11) | Posta kutusu sayfası tüm sıfırlama bağlantılarını gösterir; yalnızca geliştirmede ve bu bilgisayardan açılır, bir sunucuya kurulursa kapatılmalı. Gmail için normal parola değil uygulama şifresi gerekir ve yalnızca `config/yerel.php`'de tutulur. Gerçek Gmail gönderimi hesap sahibinin uygulama şifresiyle ayrıca denenmeli. SMTP kipinde gönderim eşzamanlı olduğu için yanıt süresi hesap varlığını sezdirebilir (tasarim.md 12.2'de not edildi). |

### 08.11.2026 · Kaan Şahin · Claude Code (Claude Opus 5.5) + Lovable · Arayüz, örnek veri ve şikâyet akışı

| Alan | İçerik |
| --- | --- |
| Amaç | Yerel arayüzün resmi ve minimalist biçimde iyileştirilmesi, örnek veride daha çok yorum ve otel/ev fotoğrafı, şikâyetin nereye düştüğünün netleştirilmesi (veriler JSON'da kalır). |
| İstem özeti | Kullanıcı: "arayüzü güzel yap, resmi minimalist; şikâyet edince nereye düşecek unutma; yorum sayısı daha fazla olsun; otellerin ve evlerin resimleri olsun; Lovable bağladım". Ders kuralları (framework yasak; HTML/CSS/Bootstrap; resim/ikon dosyası yok) bağlam olarak verildi. |
| Çıktı | Lovable'da yalnızca ön yüzlü bir tasarım prototipi hazırlandı ("EvOtel Konaklama Rehberi", 7 sayfa, 4 kredi); renk, yazı ve boşluk değerleri ve sayfa düzenleri PHP görünümlerine Bootstrap 5 ile aktarıldı (`public/assets/app.css`, `views/`), prototipin React kodu kullanılmadı. Şikâyet akışı: yöneticilere bildirim, Şikâyetlerim sayfası, yönetici yanıtında şikâyet sahibine bildirim, formda ilan seçimi. Örnek veri: 6 yeni müşteri, 36 geçmiş konaklama ve ilana özgü metinlerle 43 yorum; `DemoPhotoPainter` ile GD üzerinden çizilen 36 ilan/oda fotoğrafı (depoda resim dosyası yok). İlan sayfasında galeri, oda fotoğrafları ve puan dağılımı. 9 yeni test. |
| Doğrulama | 218 testin tamamı JSON ve MySQL 8.4'te geçti; PHPStan ve PHPCS hatasız; T-11 tutarlılık denetimi boş. Sayfalar ekran görüntüsüyle kontrol edildi (ana sayfa, ilan detayı, arama); 375 px genişlikte yatay taşma ölçüldü ve ilan sayfasındaki taşma (sütun boşluğu) giderildi. Yerel veri yeniden yüklenmeden önce yedek alındı (`storage/backups/`). |
| Dikkat edilecekler (08.11) | Lovable prototipi yalnızca tasarım kaynağıdır; projeye kod olarak girmez (framework yasağı). Fotoğraflar sade çizimlerdir, gerçek fotoğraf değildir; gerçek fotoğraflar ilan sahibi panelinden yüklenebilir. `composer seed` verileri sıfırlar: yeni hesaplar ve kayıtlar silinir, örnek veri yeniden çizilir (yaklaşık 3–4 sn). |

### 09.11.2026 · Kaan Şahin · Claude Code (Claude Opus 5.5) · Kural denetimi

| Alan | İçerik |
| --- | --- |
| Amaç | Projenin ders belgesindeki zorunlu kurallara göre baştan denetlenmesi. |
| İstem özeti | Kullanıcının o ana kadar verdiği bütün talimatlar ve ders belgesi konuşma kaydından yeniden okunup proje madde madde karşılaştırıldı. |
| Çıktı | Bulunan ihlal: ortak şartlardaki "Resim, ikon veya çizim dosyası hazırlanmaz" kuralına karşın örnek veri GD ile 36 çizim üretiyordu. `demo_photos` kapatıldı, örnek veri emoji yer tutucuya döndü; README ve tasarim.md düzeltildi. Raporlanan diğer konular: kod yalnızca hesap sahibinin private deposunda (ekip deposunda ve diğer üyelerin commit'lerinde Aşama 2 kodu yok), kapsamın vizeden geniş olması, CDN bağımlılığı, indeks gerekçesinin yazılı olmaması. |
| Doğrulama | Yerel veri yedeklenip yeniden yüklendi (fotoğraf 0); testler çalıştırıldı. |
| Dikkat edilecekler (09.11) | Kural dışı istekler (ör. örnek veride görsel) uygulanmadan önce kurala aykırılığı kullanıcıya sorulmalı. |

### 10.11.2026 · Kaan Şahin · Claude Code (Claude Fable 5) · Aşama 2 denetimi ve düzeltmeler

| Alan | İçerik |
| --- | --- |
| Amaç | Projenin Aşama 2 kurallarına (ortak şartlar, kısmi yenileme kalıbı, 12 numaralı kart) göre baştan sona denetlenmesi ve bulunan eksik ve hataların düzeltilmesi. |
| İstem özeti | "Eksik var mı, yanlış var mı bak; varsa düzelt; hocanın talimatlarına kesin uy; şu an yalnızca Aşama 2'deyiz." Bağlam: ders belgesindeki zorunlu ve bonus şartlar, 3. bölümdeki kısmi yenileme kalıbı ve dikkat notları, 12 numaralı proje kartı. |
| Çıktı | (1) Oturum zaman aşımı çalışmıyordu: 5 saniyede bir yenilenen bölümler oturumu canlı tutuyordu. `/parca/` istekleri artık kullanıcı işlemi sayılmıyor; oturumu olmayan arka plan isteği giriş sayfası yerine `401` alıyor ve bildirim sayacı betiği giriş sayfasına gidiyor (`src/Core/Kernel.php`, `views/layout.php`). (2) Parça isteğinde hata olunca bölümün içine tam hata sayfası basılıyordu; artık tek satırlık uyarı dönüyor (`views/errors/fragment.php`). (3) Yönetici şikâyet formu: 2000 karakterden uzun not "beklenmeyen hata" veriyor, listede olmayan durum sessizce "İnceleniyor" oluyordu; ikisi de anlaşılır mesajla reddediliyor (`AdminController`, `views/admin/complaints.php`). (4) UTF-8 olmayan girdi 500 yerine açıklamalı `400` alıyor (`Request::hasInvalidEncoding`). (5) Sayfa yenilenmeden gönderilen favori formu dönüş adresini koruyor (`ProfileController`). (6) İlan formundaki seçim alanlarının hata mesajları gösteriliyor; fiyat kuralları tablosu telefonda kendi içinde kayıyor (`views/host/`). (7) 09.11'de yalnızca kapatılan çizim kodu depodan silindi: `DemoPhotoPainter`, `demo_photos` ayarı ve çizim testleri. 9 yeni test yazıldı, çizimle ilgili 3 test kaldırıldı; README ve tasarim.md güncellendi. |
| Doğrulama | 224 testin tamamı JSON deposunda (1653 doğrulama; MySQL'e özel 7 test atlanır) ve MySQL 8.4'te (1707 doğrulama) geçti; PHPStan ve PHPCS hatasız; izlenebilirlik tablosu yeniden üretildi. Ayrı veri klasörüyle çalışan geçici yerel sunucuda elle denendi: oturumsuz parça ve `fetch` istekleri `401`; bozuk kodlanmış girdi `400` (sayfa, parça ve JSON biçiminde); parça hataları kısa uyarı; şikâyet formunda uzun not ve geçersiz durum; 375 px genişlikte fiyat sayfasında yatay taşma yok; zaman aşımı 1 dakikaya indirilerek tarayıcıda sayaç isteklerinin oturumu uzatmadığı ve sürenin sonunda giriş sayfasına dönüldüğü görüldü. Örnek veride `<img>` yok, yükleme klasörü boş. |
| Dikkat edilecekler (10.11) | Davranış değişikliği: şikâyet güncellemede listede olmayan durum değeri artık reddediliyor. Doğrulama sayısı 1745'ten 1653'e düştü; nedeni çizim testlerinin kaldırılmasıdır, test sayısı arttı (218 → 224). `Kernel` içindeki 422 ve 409 dalları önlem amaçlıdır (denetleyiciler bu hataları kendileri karşılar), ayrı testleri yoktur. Bildirim sayacı dışındaki yenileme betikleri ders kalıbındaki gibi yanıtı doğrudan bölüme yazar; oturum bittiğinde yönlendirmeyi sayaç betiği yapar. Sunumdan önce `composer seed` çalıştırılmalı (bekletme süreleri tarihe bağlıdır). |

### 10.11.2026 · Kaan Şahin · Claude Code (Claude Opus 5.5) · Kural, plan ve kod çelişkisi denetimi

| Alan | İçerik |
| --- | --- |
| Amaç | Hocanın kurallarıyla kod arasında ve bizim planımızla (SRS, tasarim.md, README, GitHub panosu) kod arasında çelişki ya da eksik olup olmadığının denetlenmesi. |
| İstem özeti | "Eksik var mı kontrol et; hocanın yazdıklarında kodda kendisiyle çelişen, bizim yazdığımız plana göre kendisiyle çelişen var mı?" |
| Çıktı | Çelişki bulunan yer SRS NFR-12 (WCAG 2.1 AA) oldu: (1) ikincil metin rengi kırık beyaz zeminde 4,47:1, açık zeminde 4,21:1 idi; `#5f6672` yapıldı (tüm zeminlerde en az 4,5:1). Takvimde geçmiş gün numarası 2,16:1 idi, düzeltildi. (2) Klavye odağı %20 saydam gölgeyle neredeyse görünmüyordu; her öğeye 2 px koyu yeşil çerçeve eklendi. (3) Dört seçim listesi değer değişince formu kendiliğinden gönderiyordu (klavyede ok tuşu her basışta sayfayı yeniliyordu, WCAG 3.2.2); yanlarına "Göster" / "Uygula" düğmesi kondu. (4) Yönetici yorum listesinde puan yalnızca yıldızla gösteriliyordu; metin karşılığı eklendi. tasarim.md klasör yapısındaki eksik dosyalar tamamlandı ve erişilebilirlik kararı 12.2'ye yazıldı. 1 yeni test (`WebTest::testSelectsDoNotSubmitOnChange`). |
| Doğrulama | Kod tarafı: SRS iş kuralları BR-01–BR-30 (iade oranları, bekletme süreleri, sınırlar), katman kuralları (servislerde `$_POST`/HTML yok, controller'da SQL yok), tek çakışma kuralı, GET ile durum değiştiren rota olmaması, belgelerde adı geçen her dosya, rota, sınıf ve diyagram metodunun kodda bulunması tek tek denetlendi; çelişki çıkmadı. Projects panosu (82 bitti, 3 yapılacak) açık issue'larla tutarlı. Kontrast, geçici yerel sunucuda 26 sayfada (dört rol) tarayıcıda ölçüldü; klavyeyle oda seçimi denendi; 375 px genişlikte yatay taşma yok. 225 test JSON deposunda (1656 doğrulama; MySQL'e özel 7 test atlanır) ve MySQL 8.4'te (1710 doğrulama) geçti; PHPStan ve PHPCS hatasız; izlenebilirlik tablosu yeniden üretildi. |
| Dikkat edilecekler (10.11) | #51'in kontrast ve klavye maddeleri artık karşılanıyor, ama Lighthouse/axe ile resmî bir denetim yapılmadı; issue kapatılmadan önce elle bir kez daha bakılmalı. #57 (tarayıcı uyumluluğu, Safari ve iOS dahil) elle yapılacak. Kartın "önerilen desen" ve "önerilen yenilenen bölüm" satırları görülmediği için karta göre denetlenemedi. |

### 10.11.2026 · Kaan Şahin · Claude Code (Claude Opus 5.5) · Ekip deposuna aktarım paketleri

| Alan | İçerik |
| --- | --- |
| Amaç | Kaan Şahin'in kendi deposunda yapay zekâ ile geliştirilen Aşama 2 kodunun ekip deposuna, her üyenin kendi hesabıyla ve PR ile yüklenmesi için katmanlara bölünmesi. |
| İstem özeti | "Bu commitleri nasıl ayırıp herkes kendi başına düşeni asıl repoya yükleyecek?" Karar: MySQL katmanı Aşama 3'e bırakılsın. |
| Çıktı | Ekip deposu için yalnızca JSON kullanan sürüm hazırlandı: MySQL depo sınıfları, migration'lar, `bin/migrate.php` ve MySQL'e özel testler çıkarıldı; `Container`, `Settings`, testler ve belgeler buna göre sadeleştirildi (tasarim.md bölüm 13 "Aşama 3 planı" oldu). Kod, ayrı ayrı anlatılabilen 20 konuya (commit) bölündü: Utku 4 (proje iskeleti; yardımcı sınıflar; HTTP çekirdeği; Container ve test altyapısı), Onur 4 (modeller; istisnalar ve depo arayüzleri; JSON deposu; eşzamanlılık testleri), İrem 4 (fiyatlandırma; iptal ve iade; rezervasyon çekirdeği; diğer servisler), Kaan 8 (iki controller grubu; yerleşim ve tema; ziyaretçi, müşteri ve panel sayfaları; örnek veri ve araçlar; belgeler). Her commit kendi testleriyle birlikte; her üye kendi commit'leri için bir PR açar. |
| Doğrulama | JSON sürümünde 218 test / 1615 doğrulama geçti (atlanan yok), PHPStan ve PHPCS hatasız; izlenebilirlik tablosu bu sürümün sonuçlarıyla üretildi. 20 commit ekip deposunun son hâline sırayla uygulanarak denendi; sonuç kaynakla birebir aynı. |
| Dikkat edilecekler (10.11) | Kod yapay zekâ ile Kaan Şahin tarafından üretildi; her üye kendi paketini yüklemeden önce okuyup anlamalı ve bu günlüğe kendi inceleme notunu eklemelidir (ders kuralı: her üye projenin her bölümünü açıklayabilmeli). MySQL katmanı Kaan Şahin'in deposunda hazır; Aşama 3'te ayrı PR olarak eklenecek. |
