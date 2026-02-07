YakNet Accessibility Console
Web sitelerinizdeki erişilebilirlik (WCAG 2.1) hatalarını otomatik olarak tarayan, raporlayan ve hatanın kaynağını (dosya ve satır numarası) tespit eden profesyonel bir PHP kütüphanesidir.
Geliştirici: YakNet Bilişim
Özellikler
• 
Kapsamlı Tarama: WCAG 2.1 standartlarına göre görseller, formlar, renk kontrastı ve başlık hiyerarşisi gibi birçok kuralı denetler.
• 
Akıllı Kaynak Tespiti: Tespit edilen hatanın projenizdeki hangi dosyada ve hangi satırda olduğunu otomatik olarak bulur.
• 
Görsel Raporlama: Hataları, PHP'nin yerleşik hata arayüzüne benzer şık bir panel ile sayfanın altında sunar.
• 
Ortam Kontrolü: Middleware desteği sayesinde sadece geliştirme ortamında çalışacak veya özel bir parametre ile tetiklenecek şekilde yapılandırılabilir.
Kurulum
Yöntem 1: Composer (Önerilen)
Terminalinizde şu komutu çalıştırarak kütüphaneyi projenize dahil edebilirsiniz:
composer require yaknet/accessibility-console
Yöntem 2: Manuel (Hosting / FTP)
Composer kullanamıyorsanız src klasörünü projenize yükleyip şu şekilde dahil edebilirsiniz:
require_once 'path/to/src/accessibility-console-autoload.php';
Kullanım
En basit haliyle, HTML çıktısını ekrana basmadan önce tarayıcıdan geçirin:
use YakNet\AccessibilityConsole\Scanner; use YakNet\AccessibilityConsole\Reporter\HtmlReporter; use YakNet\AccessibilityConsole\Rules\StandardRules; use YakNet\AccessibilityConsole\SourceLocator;
// 1. Tarayıcıyı ve Kuralları Hazırla $scanner = new Scanner(); StandardRules::apply($scanner);
// 2. HTML Çıktısını Tara $html = "<html>...</html>"; $violations = $scanner->scan($html);
// 3. Kaynak Dosyaları Belirle (Opsiyonel) $locator = new SourceLocator(DIR . '/views'); foreach ($violations as $violation) { $location = $locator->locate($violation->snippet); if ($location) { $violation->setSourceLocation($location['file'], $location['line']); } }
// 4. Raporu Ekrana Bas if (!empty($violations)) { $reporter = new HtmlReporter(); echo $reporter->render($violations); }
Lisans
Bu yazılım YakNet Bilişim tarafından geliştirilmiştir. MIT Lisansı kapsamında özgürce kullanılabilir ve dağıtılabilir.