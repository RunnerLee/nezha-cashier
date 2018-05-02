<?php
/**
 * @author: RunnerLee
 * @email: runnerleer@gmail.com
 * @time: 2018-01
 */

namespace Runner\NezhaCashier\Responses;

use Runner\NezhaCashier\Utils\AbstractOption;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Charge extends AbstractOption
{
    /**
     * @param OptionsResolver $resolver
     */
    protected function configureResolver(OptionsResolver $resolver): void
    {
        $resolver->setRequired(
            [
                'charge_url',
            ]
        );
        $resolver->setDefaults(
            [
                'parameters' => [],
            ]
        );
        $resolver->setAllowedTypes('parameters', 'array');
    }
}
