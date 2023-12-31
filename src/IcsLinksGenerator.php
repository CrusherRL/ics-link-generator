<?php

namespace CrusherRL;

class IcsLinksGenerator
{
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
     * Base urls.
     *
     * @var array|string[]
     */
    protected array $baseUrls = [
        'outlook'        => 'https://outlook.live.com/calendar/0/action/compose?',
        'outlook_mobile' => 'https://outlook.live.com/calendar/deeplink/compose?',
        'office'         => 'https://outlook.office.com/calendar/0/action/compose?',
        'office_mobile'  => 'https://outlook.office.com/calendar/deeplink/compose?',
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
        return new static(
			$data['DTSTART'],
			$data['DTEND'],
				$data['SUMMARY'] ?? '',
				$data['LOCATION'] ?? '',
				$data['DESCRIPTION'] ?? '',
				$data['ALLDAY'] ?? 'false');
    }

	// ====================
	//
	//	Refactoring output
	//
	// ====================

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
				'client' => $client,
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
        return array_map(function ($client) use ($serialize) {
            $client['url'] .= $this->getParameters($client['client']);
			return $serialize ? $client : $client['url'];
        }, $this->getSerializedUrls());
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
            'outlook', 'outlook_mobile' => $this->getOutlookParameters(),
            'office', 'office_mobile' => $this->getOfficeParameters(),
            'google' => $this->getGoogleParameters(),
            'aol' => $this->getAOLParameters(),
            'yahoo' => $this->getYahooParameters(),
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
		return $this->makeUrlFromClient('outlook');
	}

	/**
	 * Make and return Outlook Mobile url.
	 *
	 * @return string
	 */
	public function makeOutlookMobileUrl(): string
	{
		return $this->makeUrlFromClient('outlook_mobile');
	}

	/**
	 * Make and return Office url.
	 *
	 * @return string
	 */
	public function makeOfficeUrl(): string
	{
		return $this->makeUrlFromClient('office');
	}

	/**
	 * Make and return Office Mobile url.
	 *
	 * @return string
	 */
	public function makeOfficeMobileUrl(): string
	{
		return $this->makeUrlFromClient('office_mobile');
	}

	/**
	 * Make and return Google url.
	 *
	 * @return string
	 */
	public function makeGoogleUrl(): string
	{
		return $this->makeUrlFromClient('google');
	}

	/**
	 * Make and return AOL url.
	 *
	 * @return string
	 */
	public function makeAOLUrl(): string
	{
		return $this->makeUrlFromClient('aol');
	}

	/**
	 * Make and return Yahoo url.
	 *
	 * @return string
	 */
	public function makeYahooUrl(): string
	{
		return $this->makeUrlFromClient('yahoo');
	}

	/**
	 * Making and return non-serialized url.
	 *
	 * @param string $client
	 * @return string
	 */
	private function makeUrlFromClient(string $client): string
	{
		return $this->baseUrls[$client] . $this->getParameters($client);
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
        $enddt= $this->getEncodedPropValue('enddt', $dtend);
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
