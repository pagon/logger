<?php

namespace Pagon\Logger;

class Redis extends LoggerInterface
{
    protected $injectors = array(
        'auto_write' => true,
        'host'       => 'localhost',
        'port'       => '6379',
        'db'         => 0,
        'key'        => 'logs',
        'client'     => null,
    );

    /**
     * @var \Redis
     */
    private $client;

    /**
     * @param array $injectors
     */
    public function __construct(array $injectors = array())
    {
        parent::__construct($injectors);

        if (!$this->injectors['client']) {
            $this->injectors['client'] = new \Redis();
            $this->injectors['client']->connect($this->injectors['host'], $this->injectors['port']);
            $this->injectors['client']->select($this->injectors['db']);
        }

        // Save client
        $this->client = $this->injectors['client'];
    }

    /**
     * Write to redis
     *
     * @return mixed|void
     */
    public function write()
    {
        foreach ($this->formattedMessages() as $message) {
            $this->client->rPush($this->injectors['key'], $message);
        }
    }
}