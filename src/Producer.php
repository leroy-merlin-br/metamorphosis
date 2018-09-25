<?php
namespace Metamorphosis;

use JsonException;
use Metamorphosis\Config\Producer as ProducerConfig;
use Metamorphosis\Middlewares\Handler\Dispatcher;
use Metamorphosis\Middlewares\Handler\Producer as ProducerMiddleware;
use Metamorphosis\Record\ProducerRecord;

class Producer
{
    /**
     * @var Dispatcher
     */
    public $middlewareDispatcher;

    /**
     * @param array|string $record An array or string with the payload to be send in a topic.
     *                             If an array is passed, it will be json_encoded before send.
     *                             If string is passed, it will already be treated as json
     * @param string       $topic The key name for the topic which the record should be send to.
     *                            This key is the one set inside the config/kafka.php file.
     * @param int|null     $partition The partition where the record should be send.
     * @param string|null  $key The key that defines which partition kafka will put the record.
     *                          If a key is passed, kafka can guarantee order inside a group of consumers.
     *                          If no key is passed, kafka cannot guarantee that the record will be delivery
     *                          in any order, even when inside a same consumer group.
     *
     * @throws JsonException When an array is passed and something wrong happens while encoding the array into json.
     */
    public function produce($record, string $topic, int $partition = null, string $key = null): void
    {
        $config = new ProducerConfig($topic);

        $this->setMiddlewareDispatcher($config->getMiddlewares());

        if (is_array($record)) {
            $record = $this->encodeRecord($record);
        }

        $record = new ProducerRecord($record, $topic, $partition, $key);
        $this->middlewareDispatcher->handle($record);
    }

    protected function setMiddlewareDispatcher(array $middlewares)
    {
        $middlewares[] = app(ProducerMiddleware::class);
        $this->middlewareDispatcher = new Dispatcher($middlewares);
    }

    private function encodeRecord(array $record): string
    {
        $record = json_encode($record);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new JsonException('Cannot convert data into a valid JSON: '.json_last_error_msg());
        }

        return $record;
    }
}
