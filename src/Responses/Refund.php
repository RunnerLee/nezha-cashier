<?php
/**
 * @author: RunnerLee
 * @email: runnerleer@gmail.com
 * @time: 2018-01
 */

namespace Runner\NezhaCashier\Responses;

use Runner\NezhaCashier\Utils\AbstractOption;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Refund extends AbstractOption
{
    /**
     * @param OptionsResolver $resolver
     */
    protected function configureResolver(OptionsResolver $resolver): void
    {
        $resolver->setRequired(
            [
                'refund_sn',
                'refund_amount',
                'raw',
            ]
        );
        $resolver->setAllowedTypes('raw', 'array');
    }
}
