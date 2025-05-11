<?php

namespace CrusherRL;

class IcsLinksGenerator
{
    const OUTLOOK = 'OUTLOOK';
    const OUTLOOK_MOBILE = 'OUTLOOK_MOBILE';
    const OFFICE = 'OFFICE';
    const OFFICE_MOBILE = 'OFFICE_MOBILE';
    const GOOGLE = 'GOOGLE';
    const AOL = 'AOL';
    const YAHOO = 'YAHOO';

    /**
     * Summary of Calendar Apps.
     * 
     * @var array
     */
    protected array $calendarApps = [
        'OUTLOOK' => [
            'client' => 'outlook',
            'label'  => 'Outlook',
            'url'    => 'https://outlook.live.com/calendar/0/action/compose?',
        ],
        'OUTLOOK_MOBILE' => [
            'client' => 'outlook_mobile',
            'label'  => 'Outlook Mobile',
            'url'    => 'https://outlook.live.com/calendar/deeplink/compose',
        ],
        'OFFICE' => [
            'client' => 'office',
            'label'  => 'Office 365',
            'url'    => 'https://outlook.office.com/calendar/0/action/compose?',
        ],
        'OFFICE_MOBILE' => [
            'client' => 'office',
            'label'  => 'Office 365 Mobile',
            'url'    => 'https://outlook.office.com/calendar/deeplink/compose?',
        ],
        'GOOGLE' => [
            'client' => 'google',
            'label'  => 'Google',
            'url'    => 'https://calendar.google.com/calendar/render?',
        ],
        'AOL' => [
            'client' => 'aol',
            'label'  => 'AOL',
            'url'    => 'https://calendar.aol.com/?',
        ],
        'YAHOO' => [
            'client' => 'yahoo',
            'label'  => 'Yahoo',
            'url'    => 'https://calendar.yahoo.com/?',
        ]
    ];

    /**
     * Our variables we use to build our urls.
     *
     * @var mixed|null
     */
    protected string $DTSTART = '';
    protected string $DTEND = '';
    protected string $SUMMARY = '';
    protected string $LOCATION = '';
    protected string $DESCRIPTION = '';
    protected string $ALLDAY = 'false';

    /**
     * Building basis for our Generator.
     *
     * Note: make sure your dates has converted time.
     * You can also pass string like this: '2023-08-05 12:15:00+2'
     *
     * @param string $dtend
     * @param string $dtstart
     * @param string $summary
     * @param string $location
     * @param string $description
     * @param string $allday
     */
    public function __construct(string $dtstart, string $dtend, string $summary = '', string $location = '', string $description = '', string $allday = 'false')
    {
        $this->DTSTART = $dtstart;
        $this->DTEND = $dtend;
        $this->SUMMARY = $summary;
        $this->LOCATION = $location;
        $this->DESCRIPTION = $description;
        $this->ALLDAY = $allday;
    }

    /**
     * Building basis for our Generator from array.
     *
     * @param array $data
     * @return static
     */
    public static function make(array $data): static
    {
        $allday = $data['ALLDAY'] ?? $data['ALL_DAY'] ?? false;
        $allday = $allday ? 'true' : 'false';

        return new static(
			$data['DTSTART'] ?? $data['START'],
			$data['DTEND'] ?? $data['END'],
            $data['SUMMARY'] ?? '',
            $data['LOCATION'] ?? '',
            $data['DESCRIPTION'] ?? '',
            $allday
        );
    }

    /**
     * Encodes Data from url.
     * 
     * Note: All parameters are handled case-insensitive.
     * - Start date => 'DTSTART' or 'START'
     * - End date => 'DTEND' or 'END'
     * - Summary => 'SUMMARY'
     * - location => 'LOCATION'
     * - Description => 'DESCRIPTION'
     * - All Day => 'ALLDAY' or 'ALL_DAY'
     * 
     * @param string $url
     * @return IcsLinksGenerator
     */
    public static function fromUrl(string $url, bool $base64 = false): static
    {
        $query = parse_url($url)['query'];

        if ($base64) {
            $query = base64_decode($query);
        }

        parse_str($query, $data);
        $data = array_change_key_case($data, CASE_UPPER);
        
        $start = $data['DTSTART'] ?? $data['START'];
        $end = $data['DTEND'] ?? $data['END'];
        $summary = $data['SUMMARY'] ?? '';
        $location = $data['LOCATION'] ?? '';
        $description = $data['DESCRIPTION'] ?? '';
        $allday = $data['ALLDAY'] ?? $data['ALL_DAY'] ?? false;
        $allday = $allday ? 'true' : 'false';
    
        return new static(
            $start,
            $end, 
            $summary,
            $location,
            $description,
            $allday
        );
    }

	// ====================
	//
	//	Refactoring output
	//
	// ====================

    /**
     * Set Labels in a key value pair.
     *
     * @param array $labels
     * @return $this
     */
    public function setLabels(array $labels): static
    {
        foreach ($labels as $key => $label) {
            $this->calendarApps[$key]['label'] = $label;
        }

        return $this;
    }

	// ====================
	//
	//	Generating urls
	//
	// ====================

	/**
	 * Generates all possible ics urls.
	 *
	 * @param bool $serialize
	 * @return array
	 */
    public function generate(bool $serialize = true): array
    {
        $data = [];

        foreach ($this->calendarApps as $key => $client) {
            $client['url'] .= $this->getParameters($key);
            $data[$key] = $serialize ? $client : $client['url'];
        }

        return $data;
    }

	/**
	 * Generates specific ics urls.
	 *
	 * @param array $clients
	 * @param bool $serialize
	 * @return array
	 */
    public function generateSpecific(array $clients, bool $serialize = true): array
    {
		return array_filter($this->generate($serialize), function($url, $client) use ($clients) {
			return in_array($client, $clients);
		}, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * Generates uri matching our client like 'outlook', 'outlook_mobile' ...
     *
     * @param string $client
     * @return string
     */
    public function getParameters(string $client): string
    {
        return match ($client) {
            static::OUTLOOK, static::OUTLOOK_MOBILE => $this->getOutlookParameters(),
            static::OFFICE, static::OFFICE_MOBILE   => $this->getOfficeParameters(),
            static::GOOGLE                          => $this->getGoogleParameters(),
            static::AOL                             => $this->getAOLParameters(),
            static::YAHOO                           => $this->getYahooParameters(),
        };
    }

	// ====================
	//
	//	Generate full urls
	//
	// ====================

	/**
	 * Make and return Outlook url.
	 *
	 * @return string
	 */
	public function makeOutlookUrl(): string
	{
		return $this->makeUrlFromClient(static::OUTLOOK);
	}

	/**
	 * Make and return Outlook Mobile url.
	 *
	 * @return string
	 */
	public function makeOutlookMobileUrl(): string
	{
		return $this->makeUrlFromClient(static::OUTLOOK_MOBILE);
	}

	/**
	 * Make and return Office url.
	 *
	 * @return string
	 */
	public function makeOfficeUrl(): string
	{
		return $this->makeUrlFromClient(static::OFFICE);
	}

	/**
	 * Make and return Office Mobile url.
	 *
	 * @return string
	 */
	public function makeOfficeMobileUrl(): string
	{
		return $this->makeUrlFromClient(static::OFFICE_MOBILE);
	}

	/**
	 * Make and return Google url.
	 *
	 * @return string
	 */
	public function makeGoogleUrl(): string
	{
		return $this->makeUrlFromClient(static::GOOGLE);
	}

	/**
	 * Make and return AOL url.
	 *
	 * @return string
	 */
	public function makeAOLUrl(): string
	{
		return $this->makeUrlFromClient(static::AOL);
	}

	/**
	 * Make and return Yahoo url.
	 *
	 * @return string
	 */
	public function makeYahooUrl(): string
	{
		return $this->makeUrlFromClient(static::YAHOO);
	}

	/**
	 * Making and return non-serialized url.
	 *
	 * @param string $client
	 * @return string
	 */
	private function makeUrlFromClient(string $client): string
	{
		return $this->calendarApps[$client]['url'] . $this->getParameters($client);
	}

	// ====================
	//
	//	Building uri
	//
	// ====================

    /**
     * Creates Uri parameters for Outlook
     *
     * @return string
     */
    protected function getOutlookParameters(): string
    {
        $date_format = 'Y-m-d\TH:i:s';
        $dtend = $this->makeDatetimeIncludedTimezone($this->DTEND, $date_format);
        $dtstart = $this->makeDatetimeIncludedTimezone($this->DTSTART, $date_format);

        $allday = "&allday=$this->ALLDAY";
        $body = $this->getEncodedPropValue('body', $this->SUMMARY);
        $enddt = $this->getEncodedPropValue('enddt', $dtend);
        $location = $this->getEncodedPropValue('location', $this->LOCATION);
        $path = $this->getEncodedPropValue('path', '/calendar/action/compose');
        $rru = $this->getEncodedPropValue('rru', 'addevent');
        $startdt = $this->getEncodedPropValue('startdt', $dtstart);
        $subject = $this->getEncodedPropValue('subject', $this->DESCRIPTION);

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
     * @return string
     */
    protected function getOfficeParameters(): string
    {
        return $this->getOutlookParameters();

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
     * @return string
     */
    protected function getGoogleParameters(): string
    {
        $date_format = $this->parseToBool($this->ALLDAY) ? 'Ymd' : 'Ymd\THis';

        $dtend = $this->makeDatetimeIncludedTimezone($this->DTEND, $date_format);
        $dtstart = $this->makeDatetimeIncludedTimezone($this->DTSTART, $date_format);

        $action = $this->getEncodedPropValue('action', 'TEMPLATE');
        $dates = $this->getEncodedPropValue('dates', "$dtstart/$dtend");
        $details = $this->getEncodedPropValue('details', $this->SUMMARY);
        $location = $this->getEncodedPropValue('location', $this->LOCATION);
        $title = $this->getEncodedPropValue('text', $this->DESCRIPTION);

        return $action . $dates . $details . $location . $title;

        // google example
        //
        // action=TEMPLATE
        // &dates=20230824T151500%2F20230825T164500 OR IF ALLDAY &dates=20230824%2F20230825
        // &details=summary
        // &location=location
        // &text=title
    }

    /**
     * Creates Uri parameters for AOL
     *
     * @return string
     */
    protected function getAOLParameters(): string
    {
        return $this->getYahooParameters();

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
     * @return string
     */
    protected function getYahooParameters(): string
    {
        $is_allday = $this->parseToBool($this->ALLDAY);
        $date_format = $is_allday ? 'Ymd' : 'Ymd\THis\Z';

        $dtend = $this->makeDatetimeIncludedTimezone($this->DTEND, $date_format);
        $dtstart = $this->makeDatetimeIncludedTimezone($this->DTSTART, $date_format);

        $description = $this->getEncodedPropValue('desc', $this->SUMMARY);
        $duration = $this->getEncodedPropValue('dur', $is_allday ? 'allday' : 'false');
        $et = $this->getEncodedPropValue('et', $dtend);
        $in_loc = $this->getEncodedPropValue('in_loc', $this->LOCATION);
        $st = $this->getEncodedPropValue('st', $dtstart);
        $title = $this->getEncodedPropValue('title', $this->DESCRIPTION);
        $v = $this->getEncodedPropValue('v', '60');

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

	// ====================
	//
	//	Helpers
	//
	// ====================

	/**
	 * Parses a string to boolean if possible.
	 *
	 * @param string $str
	 * @return bool
	 */
	protected function parseToBool(string $str): bool
	{
		return filter_var($str, FILTER_VALIDATE_BOOLEAN);
	}

	/**
	 * Encodes prop and value to lower case.
	 *
	 * @param string $prop
	 * @param string $value
	 * @param bool $isFirst
	 * @return string
	 */
	protected function getEncodedPropValue(string $prop, string $value = '', bool $isFirst = false): string
	{
		$value = urlencode($value);

		return $isFirst ? "$prop=$value" : "&$prop=$value";
	}

	/**
	 * Formats our datetime.
	 *
	 * @param string $datetime
	 * @param string $format
	 * @return string
	 */
	protected function makeDatetimeIncludedTimezone(string $datetime, string $format = 'Ymd\THis\Z'): string
	{
		return date($format, strtotime($datetime));
	}
}