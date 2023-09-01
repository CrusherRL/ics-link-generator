<?php

class IcsLinkGenerator
{
    /**
     * Our variables we use to build our urls.
     *
     * @var mixed|null
     */
    protected ?string $DTEND = null;
    protected ?string $DTSTART = null;
    protected ?string $SUMMARY = null;
    protected ?string $LOCATION = null;
    protected ?string $DESCRIPTION = null;
    protected bool $ALLDAY = false;

    /**
     * Base urls.
     *
     * @var array|string[]
     */
    protected array $baseUrls = [
        'outlook'        => 'https://outlook.live.com/calendar/0/action/compose?',
        'outlook_mobile' => 'https://outlook.live.com/calendar/0/deeplink/compose?',
        'office'         => 'https://outlook.office.com/calendar/0/action/compose?',
        'office_mobile'  => 'https://outlook.office.com/calendar/0/deeplink/compose?',
        'google'         => 'https://calendar.google.com/calendar/render?',
        'aol'            => 'https://calendar.aol.com/?',
        'yahoo'          => 'https://calendar.yahoo.com/?',
    ];

    /**
     * Labels for our urls
     *
     * @var array
     */
    protected array $labels = [
        'outlook'        => 'Outlook',
        'outlook_mobile' => 'Outlook Mobile',
        'office'         => 'Office 365',
        'office_mobile'  => 'Office 365 Mobile',
        'google'         => 'Google',
        'aol'            => 'AOL',
        'yahoo'          => 'Yahoo',
    ];

    /**
     * Building basis for our Generator.
     * 
     * Note: make sure your dates has converted time.
     * You can also pass string like this: '2023-08-05 12:15:00+2'
     *
     * @param string|null $dtend
     * @param string|null $dtstart
     * @param string|null $summary
     * @param string|null $location
     * @param string|null $description
     * @param bool $allday
     */
    public function __construct(?string $dtend, ?string $dtstart, ?string $summary, ?string $location, ?string $description, bool $allday = false)
    {
        $this->DTEND = $dtend;
        $this->DTSTART = $dtstart;
        $this->SUMMARY = $summary;
        $this->LOCATION = $location;
        $this->DESCRIPTION = $description;
        $this->ALLDAY = $allday;

        $this->storeAsFile('tmp.json', $this->getAll());
    }

    /**
     * Building basis for our Generator from array.
     *
     * @param array $data
     * @return static
     */
    public static function make(array $data): static
    {
        return new static($data['DTEND'], $data['DTSTART'], $data['SUMMARY'], $data['LOCATION'], $data['DESCRIPTION'], $data['ALLDAY']);
    }

    /**
     * Stores serialized url as file.
     * 
     * @return bool
     */
    public function storeAsFile(string $path, mixed $content): bool
    {
        return file_put_contents($path, json_encode($content));
    }

    /**
     * Encodes prop and value to lower case.
     *
     * @param string $prop
     * @param string $value
     * @param bool $isFirst
     * @return string
     */
    protected function getEncodesPropValue(string $prop, string $value = '', bool $isFirst = false): string
    {
        $value = urlencode($value);

        return $isFirst ? "$prop=$value" : "&$prop=$value";
    }

    /**
     * Makes a serialized array of label, url and client
     * 
     * @return array
     */
    protected function getSerializedUrls(): array
    {
        $urls = [];

        foreach ($this->labels as $client => $label) {
            $urls[$client] = [
                'client' => $client
                'label' => $label,
                'url'   => $this->baseUrls[$client]
            ];
        }

        return $urls;
    }

    /**
     * Set Labels in a key value pair.
     *
     * @param array $labels
     * @return $this
     */
    public function setLabels(array $labels): static
    {
        foreach ($labels as $key => $label) {
            $this->labels[$key] = $label;
        }

        return $this;
    }

    /**
     * Generates all possible ics urls.
     *
     * @return array
     */
    public function getAll(): array
    {
        return array_map(function ($client) {
            return $client['url'] .= $this->generateUrl($client)
        }, $this->getSerializedUrls())
    }

    /**
     * Generates specific ics urls.
     *
     * @return array
     */
    public function getSpecific(array $clients): array
    {
        return array_filter($this->getSerializedUrls(), function($client) {
            return in_array($client, $clients);
        });
    }

    /**
     * Generates url matching our client like 'outlook', 'outlook_mobile' ...
     *  
     * @return array
     */
    public function generateUrl(string $client): array
    {
        return match ($client) {
            'outlook' => $this->getOutlookParameters(),
            'outlook_mobile' => $this->getOutlookParameters(),
            'office' => $this->getOfficeParameters(),
            'office_mobile' => $this->getOfficeParameters(),
            'google' => $this->getGoogleParameters(),
            'aol' => $this->getAOLParameters(),
            'yahoo' => $this->getYahooParameters(),
        };
    }

  /**
   * Formats our datetime.
   * 
   * @param string $datetime
   * @param string $format
   * @return string
   * @throws Exception
   */
  protected function makeDatetimeIncludedTimezone(string $datetime, string $format = 'Ymd\THis\Z'): string
  {
    return date($format, $strtotime($datetime));
  }

    /**
     * Creates Uri parameters for Outlook
     *
     * @param array $event
     * @return string
     */
    protected function getOutlookParameters(): string
    {
        $date_format = 'Y-m-d\TH:i:s';
        $dtend = $this->makeDatetimeIncludedTimezone($this->DTEND, $date_format);
        $dtstart = $this->makeDatetimeIncludedTimezone($this->DTSTART, $date_format);

        $allday = $this->makePropValueEncoded('allday', $this->ALLDAY, true);
        $body = $this->makePropValueEncoded('body', $this->SUMMARY);
        $enddt= $this->makePropValueEncoded('enddt', $dtend);
        $location = $this->makePropValueEncoded('location', $this->LOCATION);
        $path = $this->makePropValueEncoded('path', '/calendar/action/compose');
        $rru = $this->makePropValueEncoded('rru', 'addevent');
        $startdt = $this->makePropValueEncoded('startdt', $dtstart);
        $subject = $this->makePropValueEncoded('subject', $this->DESCRIPTION);

        return $allday . $body . $enddt . $location . $path . $rru . $startdt . $subject;

        // outlook example
        //
        // allday=false
        // &body=summary
        // &enddt=2023-08-24T14:45:00
        // &location=location
        // &path=%2Fcalendar%2Faction%2Fcompose
        // &rru=addevent
        // &startdt=2023-08-24T14%3A15%3A00
        // &subject=title
    }

    /**
     * Creates Uri parameters for Office
     * Is equal to Outlook url.
     *
     * @param array $event
     * @return string
     */
    protected function makeOfficeParameters(): string
    {
        return $this->makeOutlookParameters();

        // office example
        //
        // allday=false
        // &body=summary
        // &enddt=2023-08-24T14%3A45%3A00%2B00%3A00
        // &location=location
        // &path=%2Fcalendar%2Faction%2Fcompose
        // &rru=addevent
        // &startdt=2023-08-24T14%3A15%3A00%2B00%3A00
        // &subject=title
    }

    /**
     * Creates Uri parameters for Google
     *
     * @param array $event
     * @return string
     */
    protected function makeGoogleParameters(array $event): string
    {
        $date_format = $this->ALLDAY ? 'Ymd' : 'Ymd\THis\Z';
        
        $dtend = $this->makeDatetimeIncludedTimezone($this->DTEND, $date_format);
        $dtstart = $this->makeDatetimeIncludedTimezone($this->DTSTART, $date_format);

        $action = $this->makePropValueEncoded('action', 'TEMPLATE');
        $dates = $this->makePropValueEncoded('dates', "$dtstart/$dtend");
        $details = $this->makePropValueEncoded('details', $this->SUMMARY);
        $location = $this->makePropValueEncoded('location', $this->LOCATION);
        $title = $this->makePropValueEncoded('text', $this->DESCRIPTION);

        return $action . $dates . $details . $location . $title;

        // google example
        //
        // action=TEMPLATE
        // &dates=20230824T151500Z%2F20230825T164500Z OR IF ALLDAY &dates=20230824%2F20230825
        // &details=summary
        // &location=location
        // &text=title
    }

    /**
     * Creates Uri parameters for AOL
     *
     * @param array $event
     * @return string
     */
    protected function makeAOLParameters(array $event): string
    {
        return $this->makeYahooParameters($event);

        // aol example
        //
        // action=TEMPLATE
        // &dates=20230824T151500Z%2F20230825T164500Z
        // &details=summary
        // &location=location
        // &text=title
    }

    /**
     * Creates Uri parameters for Yahoo
     *
     * @param array $event
     * @return string
     */
    protected function makeYahooParameters(array $event): string
    {
        $date_format = $this->ALLDAY ? 'Ymd' : 'Ymd\THis\Z';

        $dtend = $this->makeDatetimeIncludedTimezone($this->DTEND);
        $dtstart = $this->makeDatetimeIncludedTimezone($this->DTSTART);

        $description = $this->makePropValueEncoded('desc', $this->SUMMARY);
        $duration = $this->makePropValueEncoded('dur');
        $et = $this->makePropValueEncoded('et', $dtend);
        $in_loc = $this->makePropValueEncoded('in_loc', $this->LOCATION);
        $st = $this->makePropValueEncoded('st', $dtstart);
        $title = $this->makePropValueEncoded('title', $this->DESCRIPTION);
        $v = $this->makePropValueEncoded('v', '60');

        return $description . $duration . $et . $in_loc . $st . $title . $v;

        // yahoo example
        //
        // desc=summary
        // &et=20230825T164500Z OR IF ALLDAY 20230825
        // &in_loc=location
        // &st=20230824T151500Z OR IF ALLDAY 20230824
        // &title=title
        // &v=60
    }
}
