<?php
/**
 * @author: RunnerLee
 * @email: runnerleer@gmail.com
 * @time: 2018-01
 */

namespace Runner\NezhaCashier\Utils;

use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractOption extends Collection
{
    /**
     * @var OptionsResolver[]
     */
    protected static $resolvers = [];

    /**
     * AbstractForm constructor.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $class = get_class($this);
        if (!isset(static::$resolvers[$class])) {
            $resolver = new OptionsResolver();
            $resolver
                ->setDefaults(
                    [
                        'extras' => [],
                    ]
                )
                ->setAllowedTypes('extras', 'array');
            $this->configureResolver($resolver);
            static::$resolvers[$class] = $resolver;
        }

        parent::__construct(static::$resolvers[$class]->resolve($data));
    }

    /**
     * @param OptionsResolver $resolver
     */
    abstract protected function configureResolver(OptionsResolver $resolver): void;
}
