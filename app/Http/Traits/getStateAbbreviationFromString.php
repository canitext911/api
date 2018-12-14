<?php

namespace App\Http\Traits;

trait getStateAbbreviationFromString
{
    protected $stateMap = [
        'alabama'        => 'AL',
        'alaska'         => 'AK',
        'arizona'        => 'AZ',
        'arkansas'       => 'AR',
        'california'     => 'CA',
        'colorado'       => 'CO',
        'connecticut'    => 'CT',
        'delaware'       => 'DE',
        'florida'        => 'FL',
        'georgia'        => 'GA',
        'hawaii'         => 'HI',
        'idaho'          => 'ID',
        'illinois'       => 'IL',
        'indiana'        => 'IN',
        'iowa'           => 'IA',
        'kansas'         => 'KS',
        'kentucky'       => 'KY',
        'louisiana'      => 'LA',
        'maine'          => 'ME',
        'maryland'       => 'MD',
        'massachusetts'  => 'MA',
        'michigan'       => 'MI',
        'minnesota'      => 'MN',
        'mississippi'    => 'MS',
        'missouri'       => 'MO',
        'montana'        => 'MT',
        'nebraska'       => 'NE',
        'nevada'         => 'NV',
        'new hampshire'  => 'NH',
        'new jersey'     => 'NJ',
        'new mexico'     => 'NM',
        'new york'       => 'NY',
        'north carolina' => 'NC',
        'north dakota'   => 'ND',
        'ohio'           => 'OH',
        'oklahoma'       => 'OK',
        'oregon'         => 'OR',
        'pennsylvania'   => 'PA',
        'rhode island'   => 'RI',
        'south carolina' => 'SC',
        'south dakota'   => 'SD',
        'tennessee'      => 'TN',
        'texas'          => 'TX',
        'utah'           => 'UT',
        'vermont'        => 'VT',
        'virginia'       => 'VA',
        'washington'     => 'WA',
        'west virginia'  => 'WV',
        'wisconsin'      => 'WI',
        'wyoming'        => 'WY'
    ];

    /**
     * Convert full state name to abbreviation
     *
     * @param string $stateName
     * @return null
     */
    protected function getStateAbbreviationFromString(string $stateName)
    {
        return $this->stateMap[\strtolower(\trim($stateName))] ?? null;
    }

    /**
     * Check if state abbreviation exists in map
     *
     * @param string $abbrev
     * @return bool
     */
    protected function isStateAbbreviationInMap(string $abbrev)
    {
        return \in_array(\strtoupper($abbrev), \array_values($this->stateMap));
    }
}
