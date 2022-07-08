<?php

use Elastic\Apm\ElasticApm;

class GenericInstrumenter
{
    /**
     * @param object $object
     * @param string $type
     */
    public function __construct(
        private object $object,
        private string $type
    ) {
    }

    /**
     * Magic call, capturing a child span
     *
     * @param string $name
     * @param array $arguments
     *
     * @return void
     */
    public function __call(string $name, array $arguments)
    {
        ElasticApm::getCurrentExecutionSegment()->captureChildSpan(sprintf('%s->%s()', $this->object::class, $name), $this->type, function () use ($name, $arguments) {
            $this->object->$name(...$arguments);
        });
    }

    /**
     * Magic get
     *
     * @param string $name
     *
     * @return mixed
     */
    public function __get(string $name)
    {
        return $this->object->$name;
    }

    /**
     * Magic set
     *
     * @param string $name
     * @param $value
     *
     * @return void
     */
    public function __set(string $name, $value): void
    {
        $this->object->$name = $value;
    }

    /**
     * Magic isset
     *
     * @param string $name
     *
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return isset($this->object->$name);
    }
}
