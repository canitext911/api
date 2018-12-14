<?php

namespace App\Http\Traits;

trait isValidStateAbbreviation
{
    use getStateAbbreviationFromString;

    /**
     * Check state abbreviation validity
     *
     * @param string $input
     * @return bool
     */
    protected function isValidStateAbbreviation(string $input)
    {
        return \strlen($input) === 2 && $this->isStateAbbreviationInMap($input);
    }
}
