<?php

namespace App\Contracts;

interface ExchangeRateProvider
{
    /**
     * Get the factor an amount in $from must be multiplied by to obtain the
     * equivalent amount in $to. Both are ISO 4217 codes, case insensitive.
     *
     * @throws \InvalidArgumentException When the provider cannot quote the pair.
     */
    public function rate(string $from, string $to): float;
}
