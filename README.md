# Ics Link Generator
This project creates all kind of ics/event urls for different platforms, like Outlook + mobile, Office 365 + mobile, Google, Yahoo and AOL.


## Installation

```shell
composer require crusherrl/ics-link-generator
```

## Usage

```php
use CrusherRL\IcsLinksGenerator;

// ...

$start = '2025-08-15 15:00:00'; // required
$end = '2025-08-15 16:30:00'; // required
$summary = 'Welcome on Board!'; // optional
$location = 'any location'; // optional
$description = 'Let\'s talk about this stuff'; // optional
$allDay = 'false'; // optional - NOTE: it should be true or false as string, since we urlencode this, it would be converted to 0 (false) or 1 (true)

// Building base for the generator

$generator = new IcsLinksGenerator($start, $end, $summary, $location, $description, $allDay);
// OR
$generator = IcsLinksGenerator::make(['DTSTART' => $start, 'DTEND' => $end]);
// OR Like from an URL!
$generator = IcsLinksGenerator::fromUrl('https://example.com/create-event?summary=Meeting&description=Discuss+project&location=Office&start=20250430T100000Z&end=20250430T110000Z')
// OR Like from an URL but the query is base64 encoded
$generator = IcsLinksGenerator::fromUrl('https://example.com/create-event?c3VtbWFyeT1NZWV0aW5nJmRlc2NyaXB0aW9uPURpc2N1c3MrcHJvamVjdCZsb2NhdGlvbj1PZmZpY2Umc3RhcnQ9MjAyNTA0MzBUMTAwMDAwWiZlbmQ9MjAyNTA0MzBUMTEwMDAwWg');

// Actual Generating the urls
// Generating all possible urls
$urls = $generator->generate();

// Generating only specific urls
$urls = $generator->generateSpecific([IcsLinksGenerator::YAHOO, IcsLinksGenerator::AOL, IcsLinksGenerator::OUTLOOK_MOBILE]);
```

### Output
After generating the urls, we receive those kind of arrays.

**Serialized**

[Example of all urls](examples/serialized/all.json)

[Example of specific urls - serialized](examples/serialized/specific.json)

**Unserialized**

[Example of all urls](examples/unserialized/all.json)

[Example of specific urls](examples/unserialized/specific.json)

**Url**

[Example of all urls](examples/url/all.json)

[Example of specific urls](examples/url/specific.json)

**base64**

[Example of all urls](examples/base64/all.json)

[Example of specific urls](examples/base64/specific.json)

### Format output
If needed you can change the labels, before generating the urls. Just simply setLabels which you want to change.

```php
use CrusherRL\IcsLinksGenerator;

// ...

$generator->setLabels([IcsLinksGenerator::OUTLOOK => 'Outlook.com']);
```
Our output will change from this:
```json
{
  "OUTLOOK": {
    "client": "outlook",
    "label": "Outlook",
    "url": "https:\/\/outlook.live.com\/calendar\/0\/action\/compose?&allday=false&body=&enddt=2023-08-15T16%3A30%3A00&location=&path=%2Fcalendar%2Faction%2Fcompose&rru=addevent&startdt=2023-08-15T15%3A00%3A00&subject="
  }
}
```
to this:
```json
{
  "OUTLOOK": {
    "client": "outlook",
    "label": "Outlook.com",
    "url": "https:\/\/outlook.live.com\/calendar\/0\/action\/compose?&allday=false&body=&enddt=2023-08-15T16%3A30%3A00&location=&path=%2Fcalendar%2Faction%2Fcompose&rru=addevent&startdt=2023-08-15T15%3A00%3A00&subject="
  }
}
```

### Generating url.

In case you need to generate 1 or 2 urls without serialization. This is how you do it.
```php
use CrusherRL\IcsLinksGenerator;

// ...

// Actual Generating the urls
// Generating all possible urls
$urls = $generator->generate(false);

// Generating only specific urls
$urls = $generator->generateSpecific([IcsLinksGenerator::YAHOO, IcsLinksGenerator::AOL, IcsLinksGenerator::OUTLOOK_MOBILE], false);

// OR you can get url only, like this
$aol = $generator->makeAOLUrl();
$yahoo = $generator->makeYahooUrl();
$google = $generator->makeGoogleUrl();
$office = $generator->makeOfficeUrl();
$officeMobile = $generator->makeOfficeMobileUrl();
$outlook = $generator->makeOutlookUrl();
$outlookMobile = $generator->makeOutlookMobileUrl();
```

## How to contribute

- clone the repo
- on `composer.json` of a laravel nova application add the following:

```
{
    //...

    "require" {
        "crusherrl/ics-link-generator: "*"
    },

    //...
    "repositories": [
        {
            "type": "path",
            "url": "../path_to_your_package_folder"
        }
    ],
}
```

- run `composer update crusherrl/ics-link-generator`

You're now ready to start contributing!
