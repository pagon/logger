<?php

namespace Pagon;

use Closure;

/**
 * @method debug(string $text)
 * @method info(string $text)
 * @method warning(string $text)
 * @method error(string $text)
 * @method critical(string $text)
 */
class Logger extends LoggerInterface
{
    /**
     * @var array Options
     */
    protected $options = array(
        'file'       => 'app.log',
        'auto_write' => false,
        'format'     => false,
        'level'      => 'debug'
    );

    protected static $levels = array('debug' => 0, 'info' => 1, 'warning' => 2, 'error' => 3, 'critical' => 4);

    /**
     * @var string The log message
     */
    protected $format = '[$time] $token - $level - $text';
    protected $streams = array();

    /**
     * @param array $options
     * @return self
     */
    public function __construct(array $options = array())
    {
        $this->options = $options + $this->options;

        // Auto add current file logger to streams
        if ($this->options['level']) {
            $this->add($this->options['level'], $this);
        }

        // Set default format
        if ($this->options['format']) {
            $this->format = $this->options['format'];
        }

        // The time injector
        $this->time = function () {
            return date('Y-m-d H:i:s');
        };

        // Injector the token with share instance
        $this->token = $this->share(function () {
            return substr(sha1(uniqid()), 0, 6);
        });

        // The level
        $this->level = $this->protect(function ($level) {
            return str_pad($level, 8, ' ', STR_PAD_BOTH);
        });

        $that = $this;
        register_shutdown_function(function () use ($that) {
            $that->emit('flush');
        });
    }

    /**
     * Add stream
     *
     * @param string                         $level
     * @param string|Closure|LoggerInterface $stream
     * @param array                          $options
     * @throws \InvalidArgumentException
     */
    public function add($level, $stream, array $options = array())
    {
        if (!isset(self::$levels[$level])) {
            throw new \InvalidArgumentException('Given level "' . $level . '" is not acceptable');
        }

        if (!isset($this->streams[$level])) {
            $this->streams[$level] = array();
        }

        if ($stream instanceof LoggerInterface) {
            $this->on('flush', function () use ($stream) {
                $stream->write();
            });
        }

        $this->streams[$level][] = is_string($stream) ? array($stream, $options) : $stream;
    }

    /**
     * Log
     *
     * @param string     $text
     * @param int|string $level
     * @throws \InvalidArgumentException
     */
    public function log($text, $level = 'info')
    {
        if (!isset(self::$levels[$level])) {
            throw new \InvalidArgumentException('Given level "' . $level . '" is not acceptable');
        }

        // Default text and level
        $context = array('text' => $text, 'level' => $level);

        // The format matches to convert to context
        if (preg_match_all('/\$(\w+)/', $this->format, $matches)) {
            $matches = $matches[1];
            foreach ($matches as $match) {
                if (!isset($this->$match)) continue;

                if ($this->$match instanceof Closure) {
                    $context[$match] = call_user_func($this->$match, $context[$match]);
                } else {
                    $context[$match] = $this->$match;
                }
            }
        }

        /**
         * Prepare the variable to replace
         */
        foreach ($context as $k => $v) {
            unset($context[$k]);
            $context['$' . $k] = $v;
        }

        // Build message
        $message = strtr($this->format, $context);

        // Emit the level event
        $this->emit($level, $message);

        /**
         * Loop the streams to send message
         */
        foreach ($this->streams as $stream_level => &$streams) {
            if (self::$levels[$stream_level] > self::$levels[$level]) {
                continue;
            }

            foreach ($streams as &$stream) {
                if (is_array($stream)) {
                    $trys = array(__NAMESPACE__ . '\\Logger\\' . $stream[0], $stream[0]);

                    foreach ($trys as $try) {
                        if (!class_exists($try)) {
                            continue;
                        }

                        $stream = new $try($stream[1]);
                        $this->on('flush', function () use ($stream) {
                            $stream->write();
                        });
                    }
                } elseif ($stream instanceof \Closure) {
                    $stream($message);
                    continue;
                }

                if ($stream instanceof LoggerInterface) {
                    $stream->send($message);
                }
            }
        }
    }

    /**
     * Support level method call
     *
     * @example
     *
     *  $logger->debug('test');
     *  $logger->info('info');
     *  $logger->info('this is %s', 'a');
     *  $logger->info('this is :id', array(':id' => 1));
     *
     * @param $method
     * @param $arguments
     * @return mixed|void
     */
    public function __call($method, $arguments)
    {
        if (isset(self::$levels[$method])) {
            if (!isset($arguments[1])) {
                $text = $arguments[0];
            } else if (is_array($arguments[1])) {
                $text = strtr($arguments[0], $arguments[1]);
            } else if (is_string($arguments[1])) {
                $text = vsprintf($arguments[0], array_slice($arguments, 1));
            } else {
                $text = $arguments[0];
            }
            $this->log($text, $method);
        }
    }

    /**
     * Write log to file
     */
    public function write()
    {
        foreach ($this->messages as $message) {
            file_put_contents($this->options['file'], $message . PHP_EOL, FILE_APPEND);
        }

        $this->clear();
    }
}
