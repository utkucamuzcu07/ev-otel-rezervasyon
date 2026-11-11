# Yedekleme ve Geri Yükleme (NFR-09)

> SRS NFR-09: Veritabanı günde en az bir kez yedeklenmeli, son 7 yedek saklanmalı ve geri yükleme dönem boyunca en az bir kez denenmelidir. **Uygunluk ölçütü:** her gün için en az bir yedek vardır, son 7 yedek saklanır, en az bir geri yükleme denemesi yapılmış ve sonucu belgelenmiştir.

## Nasıl çalışır

| Konu | Uygulama |
| --- | --- |
| Günlük yedek | `php bin/gorev.php` (dakikada bir çalışan zamanlanmış görev) o gün yedek alınmamışsa yedek alır (`BackupService::createDailyIfMissing`). |
| Elle yedek | `composer yedek` veya `php bin/yedek.php` |
| Saklama | `storage/backups/yedek-<yılaygün>-<saatdakikasaniye>.json` (ör. `yedek-20261108-062847.json`); her yedekten sonra en yeni **7** yedek kalır, eskiler silinir. |
| Biçim | Tek JSON dosyası: tüm tabloların satırları, tablo başına satır sayısı ve SHA-256 özeti. Biçim depodan bağımsızdır; Aşama 3'te MySQL deposuna da aynı dosya yüklenebilecek. |
| Tutarlılık | Tüm tablolar tek dışlayıcı kilit altında okunur; yedek sırasında yarım yazma görülmez. |
| Listeleme | `php bin/yedek.php --liste` |
| Geri yükleme | `php bin/yedek.php --geri-yukle=yedek-20261108-062847.json --evet` |
| Güvenlik | Özet tutmayan (bozuk veya elle değiştirilmiş) yedek yüklenmez. Geri yüklemeden hemen önce mevcut durumun yedeği alınır. Geri yükleme tek işlemdir: bir satır bile yazılamazsa hiçbir değişiklik kalmaz. Yedek adı yalnızca `yedek-...json` biçiminde kabul edilir (klasör dışına çıkılamaz). |

Otomatik testler: `tests/Integration/BackupServiceTest.php` — geri yükleme tüm tabloları yedekteki haline döndürür; 9 yedekten en yeni 7'si kalır; günde bir yedek; değiştirilmiş yedek reddedilir; geçersiz ad reddedilir.

## Geri yükleme denemesi kaydı

| Tarih | Depo | Veri | Adımlar | Sonuç |
| --- | --- | --- | --- | --- |
| 08.11.2026 | JSON | `storage/data` kopyası (gerçek örnek veri: 20 tablo, 338 satır) | Yedek alındı (54,1 KB, 9 ms) → tüm tablolar boşaltıldı (veri kaybı taklidi, 0 satır) → yedek geri yüklendi (28 ms) | **Başarılı.** 20/20 tablo yedekteki satırlarla birebir aynı; tutarlılık denetimi (T-11) boş. |

Deneme, uygulamanın kendi `BackupService` sınıfıyla yapıldı; çalışan verinin kopyası kullanıldığı için `storage/data` değişmedi. Sonraki denemeler bu tabloya eklenmelidir (dönem boyunca en az bir kez).
