## Pagon Logger [![Build Status](https://travis-ci.org/pagon/logger.png)](https://travis-ci.org/pagon/logger)

非常简单的日志库，支持debug, info, warn, error, critical等日志等级，也支持以stream的方式添加handler

[pagon/fiber]: https://github.com/pagon/fiber

## 依赖

- PHP 5.3.9+
- Composer

库依赖：

[pagon/fiber]

## 使用

### 基本
	
```php
$logger = new \Pagon\Logger();
$logger->debug('User->%s is logged with params: %s', $username, $params);
// 2013-05-02 13:11:00 - s3f9da -   debug  - User->hfcorriez is logged with params: return=/status
$logger->info('User->:username login to homepage', array(':username' => $username))
// 2013-05-02 13:11:00 - s3f9da -   info   - User->hfcorriez login to homepage
```

### 配置

```php
$logger = new \Pagon\Logger(array(
    'level' => 'info',
    'file'  => '/tmp/app.log'
));
```

### 格式

使用[pagon/fiber]实现

```php
$logger = new \Pagon\Logger(array(
	'format' => '$time - $level - $text'
));
```

自定义格式参数

```php
$logger = new \Pagon\Logger(array(
	'format' => '$time - $level - $file - $text'
));
$logger->file = __FILE__;
$logger->info('Some info');	// 2013-05-02 13:11:00 - info - /home/hfcorriez/myfile.php - Some info
```

### Handler

目前只提供一个Console Handler用于在console输出Log信息

```php
$logger = new \Pagon\Logger();
$logger->add('debug', new \Pagon\Logger\Console());
```

自定义Handler

```php
class YourCustomHanlder extends \Pagon\LoggerInterface
{
    public function write()
    {
        if (empty($this->messages)) return;

        $message = join("\n", $this->buildAll()) . "\n";
        
        mail('your@exapmle.com', 'Some error logs', $message);
    }
}

$logger = new \Pagon\Logger();
$logger->add('error', new YourCustomHanlder());
```

### 事件

```php
$logger = new \Pagon\Logger();
$logger->on('flush', function() {
    // Some thing before the flush
});
```

## License

(The MIT License)

Copyright (c) 2012 hfcorriez &lt;hfcorriez@gmail.com&gt;

Permission is hereby granted, free of charge, to any person obtaining
a copy of this software and associated documentation files (the
'Software'), to deal in the Software without restriction, including
without limitation the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software, and to
permit persons to whom the Software is furnished to do so, subject to
the following conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED 'AS IS', WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.