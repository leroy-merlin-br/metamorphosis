## Guia rápido

- [Arquivo de configuração](#config)
- [Consumer](#consumer)
   - [Criando um Consumer](#creating-consumer)
   - [Rodando o Consumer](#running-consumer)

<a name="config"></a>
### Arquivo de configuração: `config/kafka.php`

Esse arquivo contém todas as informações sobre *brokers*, tópicos, *consumer groups* e *middlewares*.

Para começar a usar, podemos focar em duas seções:

- Brokers

    Uma lista de *brokers*, com configurações de conexão e autenticação.

    - `connections`: *obrigatório*. Pode ser uma `string` com múltiplas conexões separadas por vírgula ou uma `array` de conexões.

    - `auth`: *opcional*. É possivel se conectar sem autenticação ou usando autenticação SSL.

    ```php
      'brokers' => [
          'price_brokers' => [
              'connections' => 'localhost:8091,localhost:8092',
              'auth' => [
                  'type' => 'ssl',
                  'ca' => storage_path('ca.pem'),
                  'certificate' => storage_path('kafka.cert'),
                  'key' => storage_path('kafka.key'),
              ],
          ],
          'stock_brokers' => [
              'connections' => ['localhost:8091', 'localhost:8092'],
              'auth' => [], // pode ser uma array vazia ou até mesmo não ter essa chave aqui.
          ],
      ],
    ```

- Tópicos

    Uma lista de configuração de tópicos, como nome, qual *broker* usar, *consumer group* e *middlewares*.

    Aqui você pode especificar os *consumer groups*. Cada tópico pode ter vários grupos,
    e cada grupo tem a sua configuração para cada *consumer*, *offset_reset* (para definir um *offset* inicial) e *middlewares* que devem ser usados.

    ```php
      'topics' => [
          'price_update' => [
              'topic' => 'products.price.update',
              'broker' => 'price_brokers',
              'consumer_groups' => [
                  'default' => [
                      'offset_reset' => 'smallest',
                      'handler' => '\App\Kafka\Consumers\PriceUpdateConsumer',
                  ],
              ],
          ],
      ],
    ```

<a name="consumer"></a>
### Consumer

Depois das configurações obrigatórias, você deve criar um *consumer*. Ele será responsável por receber as mensagens
do tópico especificado na *config*.

<a name="creating-consumer"></a>
#### Criando um Consumer

Para criar um *consumer*, basta fazer o seguinte:

```bash
$ php artisan make:kafka-consumer PriceUpdateConsumer
```
Este comando irá criar uma classe `KafkaConsumer` dentro da aplicação, na pasta `app/Kafka/Consumers/`.

Está classe possui um método chamado `handler`, que irá receber as mensagens do tópico. Ela também possui
métodos para cuidar das exceções.

```php
use App\Kafka\Consumers\PriceUpdateConsumer;
use Metamorphosis\TopicHandler\Consumer\AbstractHandler;
use Metamorphosis\Record\RecordInterface;

class PriceUpdateConsumer extends AbstractHandler
{
    public $repository;

    /**
     * Create a new consumer topic handler instance.
     */
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Handle payload.
     */
    public function handle(RecordInterface $record): void
    {
        $product = $record->getPayload();

        $this->repository->update($product['id'], $product['price']);
    }
}
```

<a name="running-consumer"></a>
#### Rodando o consumer

Agora é só consumir o tópico.

A forma mais simples de ver tudo isso funcionando é rodando o comando `kafka:consume` com o nome do tópico que foi configurado:

```bash
$ php artisan kafka:consume price-update
```

Esse comando rodará em um laço infinito (while true), isso significa que ele nunca irá parar de rodar por conta própria.
Mas erros podem acontecer, então, recomendamos fortemente que você execute este comando com o auxílio de um [supervisor](http://supervisord.org/running.html), como no exemplo abaixo:

```bash
[program:kafka-consumer-price-update]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/default/artisan kafka:consume price-update --timeout=-1
autostart=true
autorestart=true
user=root
numprocs=6
redirect_stderr=true
stdout_logfile=/var/log/default/kafka-consumer-price-update.log
```

Pronto! Para mais informações sobre uso, *middlewares*, autenticação do *broker*, *consumer groups* e outros tópicos avançados, por favor, dê uma olhada em nosso [Guia Avançado](advanced.pt.md).
