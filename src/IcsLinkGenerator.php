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

    protected string $timezone = 'Europe/Berlin';

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
   * Sets timezone for our urls.
   *
   * @param string $timezone
   * @return $this
   */
    public function setTimezone(string $timezone): static
    {
      $this->timezone = $timezone;

      return $this;
    }

    /**
     * Generates all Ics Urls.
     *
     * @return array
     */
    public function getAll(): array
    {
        foreach ($this->baseUrls as $client => $value) {
            $this->baseUrls[$client] .= match ($client) {
                'outlook' => $this->makeOutlookParameters(),
                'office' => $this->makeOfficeParameters(),
                'google' => $this->makeGoogleParameters(),
                'aol' => $this->makeAOLParameters(),
                'yahoo' => $this->makeYahooParameters(),
            };
        }

        return $this->baseUrls;
    }

  /**
   * @param string $datetime
   * @param string $format
   * @return string
   * @throws Exception
   */
  protected function makeDatetimeIncludedTimezone(string $datetime, string $format = 'Ymd\THis\Z'): string
  {
    $timezone = new DateTimeZone($this->timezone);
    $date = DateTime::createFromFormat($format, $datetime, $timezone);

    return $date->format($format);
  }
}
