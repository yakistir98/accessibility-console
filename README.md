# YakNet Accessibility Console

Web sitelerinizdeki erişilebilirlik (WCAG) hatalarını otomatik olarak tarayan, raporlayan ve hatanın kaynağını (dosya ve satır numarası) tespit etmeye çalışan PHP kütüphanesi.

**Geliştirici:** YakNet Bilişim

## Özellikler
- **Otomatik Tarama:** Sayfa çıktılarını analiz eder.
- **Kaynak Tespiti:** Hatanın hangi view/template dosyasında olduğunu bulmaya çalışır.
- **Görsel Raporlama:** Sayfanın altına "Fatal Error" stilinde detaylı bir rapor paneli ekler.
- **Dinamik İçerik Desteği:** Döngüler ve dinamik içerikler içindeki hataları yakalar.

## Kurulum

### Yöntem 1: Composer (Önerilen)
Bu kütüphane şu an yerel/private bir paket olduğu için `composer.json` dosyanıza şu şekilde ekleyin:

```json
"repositories": [
    {
        "type": "path",
        "url": "./yaknet/accessibility-console" 
    }
],
"require": {
    "yaknet/accessibility-console": "@dev"
}
```
*Not: `url` kısmını kütüphaneyi indirdiğiniz klasöre göre düzenleyin.*

### Yöntem 2: Manuel (Hosting / FTP)
Composer kullanamıyorsanız:
1. `src` klasörünü sunucunuza yükleyin.
2. Projenize `autoload.php` dosyasını dahil edin:

```php
require_once 'path/to/src/accessibility-console-autoload.php';
```

## Kullanım

En basit haliyle, HTML çıktısını ekrana basmadan önce tarayıcıdan geçirin:

```php
use YakNet\AccessibilityConsole\Scanner;
use YakNet\AccessibilityConsole\Reporter\HtmlReporter;
use YakNet\AccessibilityConsole\Rules\StandardRules;

// 1. Tarayıcıyı Başlat
$scanner = new Scanner();

// 2. Kuralları Ekle (Hepsini tek seferde ekle)
StandardRules::apply($scanner);

/* Veya tek tek ekleyebilirsiniz:
$scanner->addRule(new ImgAltText());
...
*/

// 3. HTML'i Tara
$html = "<html>...</html>"; // Sitenizin çıktısı
$violations = $scanner->scan($html);

// 4. Raporu Göster
if (!empty($violations)) {
    $reporter = new HtmlReporter();
    
    // İsteğe bağlı: Başarılı olduğunda da yeşil widget göster (varsayılan: false)
    // $reporter->setShowSuccess(true);

    echo $reporter->render($violations);
}
```

## Lisans
Bu yazılım **YakNet Bilişim** tarafından geliştirilmiştir.
İstediğiniz gibi değiştirebilir, kullanabilir ve dağıtabilirsiniz (MIT License).
