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

$start = '2023-08-15 15:00:00'; // required
$end = '2023-08-15 16:30:00'; // required
$summary = 'summaryHey!'; // optional
$location = 'locationHey!'; // optional
$description = 'descriptionHey!'; // optional
$allDay = 'false'; // optional - NOTE: it should be true or false as string, since we urlencode this, it would be converted to 0 (false) or 1 (true)

// Building base for the generator

$generator = new IcsLinksGenerator($start, $end, $summary, $location, $description, $allDay);
// OR
$generator = IcsLinksGenerator::make(['DTSTART' => $start, 'DTEND' => $end]);

// Actual Generating the urls
// Generating all possible urls
$urls = $generator->generate();

// Generating only specific urls
$urls = $generator->generateSpecific(['yahoo', 'aol', 'outlook_mobile']);
```

### Output
After generating the urls, we receive those kind of arrays.

**Serialized**

[Example of all urls](examples/serialized/all.json)

[Example of specific urls - serialized](examples/serialized/specific.json)

**Unserialized**

[Example of all urls](examples/unserialized/all.json)

[Example of specific urls](examples/unserialized/specific.json)

### Format output
If needed you can change the labels, before generating the urls. Just simply setLabels which you want to change.

```php
use CrusherRL\IcsLinksGenerator;

// ...

$generator->setLabels(['outlook' => 'Outlook.com']);
```
Our output will change from this:
```json
{
  "outlook": {
    "client": "outlook",
    "label": "Outlook",
    "url": "https:\/\/outlook.live.com\/calendar\/0\/action\/compose?&allday=false&body=&enddt=2023-08-15T16%3A30%3A00&location=&path=%2Fcalendar%2Faction%2Fcompose&rru=addevent&startdt=2023-08-15T15%3A00%3A00&subject="
  }
}
```
to this:
```json
{
  "outlook": {
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
$urls = $generator->generateSpecific(['yahoo', 'aol', 'outlook_mobile'], false);

// OR you can get url only like this
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
