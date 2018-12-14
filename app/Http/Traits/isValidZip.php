<?php

namespace App\Http\Traits;

trait isValidZip
{
    /**
     * Check zip code validity
     *
     * @param string $input
     * @return bool
     */
    protected function isValidZip(string $input)
    {
        return \is_numeric($input) && \strlen($input) === 5;
    }
}
