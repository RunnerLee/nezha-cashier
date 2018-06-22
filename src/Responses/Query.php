<?php
/**
 * @author: RunnerLee
 * @email: runnerleer@gmail.com
 * @time: 2018-01
 */

namespace Runner\NezhaCashier\Responses;

use Runner\NezhaCashier\Utils\AbstractOption;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Query extends AbstractOption
{
    /**
     * @param OptionsResolver $resolver
     */
    protected function configureResolver(OptionsResolver $resolver): void
    {
        $resolver->setRequired(
            [
                'order_id',
                'status',
                'trade_sn',
                'raw',
            ]
        );
        $resolver->setDefaults(
            [
                'amount'                => 0,
                'tax'                   => 0,
                'buyer_identifiable_id' => '',
                'paid_at'               => 0,
                'currency'              => 'CNY',
                'buyer_name'            => '',
                'buyer_email'           => '',
                'buyer_phone_number'    => '',
                'buyer_is_subscribed'   => 'no',
                'buyer_extras'          => [],
            ]
        );
        $resolver->setAllowedValues(
            'status',
            [
                'created',
                'paid',
                'closed',
                'refund',
            ]
        );
        $resolver->setAllowedTypes('raw', 'array');
        $resolver->setAllowedValues('buyer_is_subscribed', ['yes', 'no']);
    }
}
