=== CJ Flooring Landing ===
Contributors: grupo30
Tags: landing page, flooring, local seo, lead form, schema
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Flooring landing page for CJ Remodeling Group (Miami, FL): same visual identity as the main site, SEO / AEO / GEO structured data, contact buttons and a lead form.

== Description ==

* Creates a **draft** page called "Flooring Installation in Miami, FL" (slug `flooring-installation-miami`) that uses its own standalone template. Nothing goes public until you publish it.
* Same look as cjremodeling.services: dark surfaces, gold accents, Instrument Sans (bundled, no Google Fonts requests), pill buttons, rounded cards, gold check bullets.
* Uses the photos and logo that are already in your Media Library.
* One `<h1>`, a clean H2/H3 outline, descriptive alt text, responsive images, answer-first copy, comparison table and FAQ.
* Structured data (JSON-LD): GeneralContractor (LocalBusiness), Service, FAQPage, WebPage, BreadcrumbList, WebSite. No invented reviews, ratings, licenses or hours.
* Contact buttons: header button, hero buttons, floating WhatsApp button, mobile bottom bar (Call / WhatsApp / Free estimate).
* Lead form (quick form at the top + full form at the bottom): validation, honeypot, per-IP rate limit, fresh nonce on every submit (safe with page caching), e-mail notification and a private "Flooring Leads" inbox in the WordPress admin so no lead is ever lost.
* Conversion tracking events for Google Tag Manager (`dataLayer`), GA4 (`generate_lead`) and Meta Pixel (`Lead`).
* "Fast, clean mode": the theme's CSS / JavaScript is not loaded on this page (faster, no style conflicts). Tracking code you added to the theme still works.

== Installation ==

1. Plugins > Add New > Upload Plugin > choose `cj-flooring-landing.zip` > Install Now > Activate.
2. Open the notice that appears (or Pages) and review the draft page. Click **Publish** when you are happy with it.
3. Flooring Leads > Settings: check the phone, WhatsApp, notification e-mail and service areas. Use **Send test email**.

== Frequently Asked Questions ==

= Where do I edit the text? =
Settings (phone, e-mail, SEO title / description / H1, service areas...) live in Flooring Leads > Settings. The page copy is in `includes/class-cjfl-content.php` (plain PHP array, easy to edit) and can also be changed with the `cjfl_content` filter.

= Can I use it with Yoast / Rank Math? =
Yes. When one of them is active it controls the title, description and social tags; this plugin keeps adding the business, service and FAQ structured data.

= What happens if I deactivate the plugin? =
The page stays in WordPress but shows the theme's default (empty) layout. Re-activate to bring the landing back.

== Changelog ==

= 1.0.0 =
* First release.
