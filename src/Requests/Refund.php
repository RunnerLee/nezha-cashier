<?php
/**
 * @author: RunnerLee
 * @email: runnerleer@gmail.com
 * @time: 2018-01
 */

namespace Runner\NezhaCashier\Requests;

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
                'order_id',
                'total_amount',
                'refund_amount',
            ]
        );
        $resolver->setDefaults(
            [
                'reason'    => '',
                'trade_no'  => '',
                'refund_id' => '',
            ]
        );
    }
}
