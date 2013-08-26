<?php

namespace Pagon\Logger;

class MongoDB extends LoggerInterface
{
    protected $injectors = array(
        'auto_write' => true,
        'dsn'        => 'mongodb://localhost:27017',
        'database'   => 'logs',
        'collection' => 'logs',
        'client'     => null,
        'options'    => array()
    );

    /**
     * @var \MongoClient
     */
    private $client;

    /**
     * @var \MongoCollection
     */
    private $collection;

    /**
     * @param array $injectors
     */
    public function __construct(array $injectors = array())
    {
        parent::__construct($injectors);

        if (!$this->injectors['client']) {
            $this->injectors['client'] = new \MongoClient($this->injectors['dsn'], $this->injectors['options']);
        }

        // Set client and collection
        $this->client = $this->injectors['client'];
        $this->collection = $this->client->selectCollection($this->injectors['database'], $this->injectors['collection']);
    }

    /**
     * Write messages
     *
     * @return mixed|void
     */
    public function write()
    {
        foreach ($this->messages as $message) {
            $message['log'] = $this->format($message);
            $this->collection->save($message);
        }
    }
}