<?php

namespace ArsalanThange\CurrencyConverter;

use DateTime;

class CurrencyConverter
{
    /**
     * Base currency sybmol.
     *
     * @var string
     */
    protected $base;

    /**
     * Formatted and parsed XML after fetching it from www.ecb.europa.eu.
     *
     * @var array
     */
    protected $parsed_xml = [];

    /**
     * Date from when the user requests rates.
     *
     * @var string
     */
    protected $from;

    /**
     * Date to when the user requests rates.
     *
     * @var string
     */
    protected $to;

    /**
     * XML endpoint at www.ecb.europa.eu.
     * The endpoint various for Latest, 90 days prior and historical rates.
     *
     * @var string
     */
    protected $url;

    /**
     * Filename of the cached file after fetching it from www.ecb.europa.eu.
     * We cache the results to avoid unecessary hitting of API as the rates update daily.
     *
     * @var string
     */
    protected $cache_file_name;

    /**
     * Currency symbols requested by the user.
     *
     * @var array
     */
    protected $symbols = [];

    /**
     * Set base symbol to user requested base symbol.
     *
     * @param string $base Base currency symbol
     * @return void
     */
    public function __construct($base = 'EUR')
    {
        $this->base = $base;
    }

    /**
     * Set base symbol to user requested base symbol.
     *
     * @param string $from Date from which the user wants the rates
     * @param string $to Date to which the user wants the rates
     * @return \ArsalanThange\CurrencyConverter
     */
    public function load($from = false, $to = false)
    {
        /*If $from is not set, set it to yesterday.
         * Latest rates available at www.ecb.europa.eu are generally yesterdays
         */
        if (!$from) {
            $this->from = (new DateTime())->modify('-1 day')->format('Y-m-d');
        } else {
            $this->from = $from;
        }

        //If $from is set but $to is not, set $to as $from. This is to simplify the logic later on to load XML.
        if ($from && !$to) {
            $this->to = $from;
        } elseif (!$to) {
            $this->to = (new DateTime())->modify('-1 day')->format('Y-m-d');
        } else {
            $this->to = $to;
        }

        $this->loadXml();

        if ($this->base != 'EUR') {
            $this->formatWithNewBase();
        }

        return $this;
    }

    /**
     * Load XML from the API endpoint and store it in a cached file.
     *
     * @return void
     */
    protected function loadXml()
    {
        $diff = $this->getDateDifferenceDays();
        $this->getApiEndpoint($diff);

        $cache_file = 'cache/' . $this->cache_file_name;
        if (!file_exists($cache_file) || filemtime($cache_file) < time() - 28800) {
            $contents = file_get_contents('https://www.ecb.europa.eu/stats/eurofxref/' . $this->url);
            file_put_contents($cache_file, $contents);
        }
        $xml = simplexml_load_file($cache_file);

        $this->parsed_xml = $this->parseXml($xml);
    }

    /**
     * Calculates the difference in Days from requested date/date range with latest rates date.
     * This is calculated to load appropriate API Endpoint.
     *
     * @return int
     */
    protected function getDateDifferenceDays()
    {
        $yesterday = (new DateTime())->modify('-1 days');
        $from = new Datetime($this->from);
        $to = new Datetime($this->to);

        $difference_from = $yesterday->diff($from);
        $difference_to = $yesterday->diff($to);

        return max($difference_from->days, $difference_to->days);
    }

    /**
     * Gets the API Endpoint based on the date difference.
     *
     * @param int $difference Difference in days of requested dates with latest date
     * @return void
     */
    protected function getApiEndpoint($difference)
    {
        if ($difference == 0) {
            $this->url = 'eurofxref-daily.xml';
            $this->cache_file_name = 'latest_rates.xml';
        } elseif ($difference > 0 && $difference <= 90) {
            $this->url = 'eurofxref-hist-90d.xml';
            $this->cache_file_name = '90_rates.xml';
        } else {
            $this->url = 'eurofxref-hist.xml';
            $this->cache_file_name = 'historic_rates.xml';
        }
    }

    /**
     * Parse and format the XML from endpoint and store it in array.
     *
     * @param SimpleXMLElement $xml
     * @return array
     */
    protected function parseXml($xml)
    {
        $array = [];
        foreach ($xml as $first) {
            foreach ($first as $second) {
                $time = (string) $second['time'];
                if ($time >= $this->from && $time <= $this->to) {
                    foreach ($second as $third) {
                        $currency = (string) $third['currency'];
                        $array[$time]['EUR'] = 1;
                        $array[$time][$currency] = (string) $third['rate'];
                    }
                }
            }
        }

        return $array;
    }

    /**
     * Formats the loaded XML (Default EUR as base) into user selected base currency symbol.
     *
     * @return array
     */
    protected function formatWithNewBase()
    {
        $new_parsed_xml = [];
        foreach ($this->parsed_xml as $date => $symbols) {

            foreach ($symbols as $key => $value) {
                $new_parsed_xml[$date][$key] = ($value / $symbols[$this->base]);
            }

        }
        $this->parsed_xml = $new_parsed_xml;
    }

    /**
     * Returns requested rates to the user.
     *
     * @return array
     */
    public function rates()
    {
        if (count($this->symbols)) {
            $this->filterSymbols();
        }

        $response['base'] = $this->base;
        $response['data'] = $this->parsed_xml;

        return $response;
    }

    /**
     * Filters the parsed XML and removes the symbols not requested by the user.
     *
     * @return void
     */
    protected function filterSymbols()
    {
        foreach ($this->parsed_xml as $date => $symbols) {
            $this->parsed_xml[$date] = array_intersect_key($symbols, array_flip($this->symbols));
        }

    }

    /**
     * Sets currency symbols requested by the user. If not empty, other symbols are filtered out.
     *
     * @param array $symbols Currency symbols requested by user
     * @return \ArsalanThange\CurrencyConverter
     */
    public function symbols($symbols = [])
    {
        $this->symbols = $symbols;

        return $this;
    }

    /**
     * Converts the base amount requested by the user into other currencies.
     *
     * @param int $amount Base amount to be converted
     * @return array
     */
    public function convert($amount)
    {
        $response['base'] = $this->base;
        $response['base_amount'] = $amount;
        $new_parsed_xml = [];

        if (count($this->symbols)) {
            $this->filterSymbols();
        }

        foreach ($this->parsed_xml as $date => $symbols) {
            foreach ($symbols as $key => $value) {
                $new_parsed_xml[$date][$key] = ($value * $amount);
            }
        }

        $response['data'] = $new_parsed_xml;

        return $response;
    }

}
