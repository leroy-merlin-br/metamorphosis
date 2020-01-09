## Guia Avançado

- [Autenticação](#authentication)
- [Middlewares](#middlewares)
- [Brokers](#brokers)
- [Schemas](#schemas)
- [Comandos](#commands)
   - [Criando um Consumer](#commands-consumer)
   - [Criando um Middleware](#commands-middleware)
   - [Executando um Consumer](#commands-running-consumer)
        - [Parâmetros](#options)

<a name="authentication"></a>
### Autenticação
Você pode configurar qual tipo de autenticação cada broker precisa para se conectar.

Para isso, basta preencher a chave `auth` nas configurações do broker:

``` php
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
          'auth' => [], // can be an empty array or even don't have this key in the broker config
      ],
  ],
```

Se a chave `type` for configurada para `ssl`, uma **Autenticação SSL** será utilizada e você precisará fornecer alguns campos extras junto com o tipo.
Os campos são: `ca` com o arquivo `ca.pem`, `certificate` com o arquivo `.cert` e `key` com o arquivo `.key`.

Se um broker não precisar de nenhuma autenticação para se conectar, você pode deixar a chave `auth` com uma *array* vazia ou até mesmo a omitir.

---

<a name="middlewares"></a>
### Middlewares

*Middlewares* trabalham entre o dado recebido do *broker* e antes de o mesmo ser manipulado pelos *consumers*.

Eles se comportam de modo similar aos [PSR-15](https://www.php-fig.org/psr/psr-15/) *middlewares*. A principal diferença é que ao invés de retornar uma `Response`, eles são usados para transformar, validar ou fazer qualquer tipo de manipulação no `payload` do registro.

Depois disso, o processo é delegado de volta para o `MiddlewareHandler`. Pode-se evitar que o dado chegue ao *consumer* lançando uma `Exception`.

Essa biblioteca vem com os seguintes *middlewares*:

- `\Metamorphosis\Middlewares\JsonDecode`
- `\Metamorphosis\Middlewares\Log`
- `\Metamorphosis\Middlewares\AvroSchemaDecoder`

Você pode criar o seu próprio *middleware* usando o comando `php artisan make:kafka-middleware`.

Exemplo:

Vamos supor que todas as mensagens que você consome do Kafka são formatados em `JSON`. Você pode usar um *middleware* para decodificá-las.

Para criar o comando, basta executar:

```bash
$ php artisan make:kafka-middleware JsonDeserializer
```

O comando criará uma classe em `app/Kafka/Middlewares` parecida com o seguinte:

```php
<?php
namespace App\Kafka\Middlewares;

use Metamorphosis\Middlewares\Handler\MiddlewareHandlerInterface;
use Metamorphosis\Middlewares\MiddlewareInterface;
use Metamorphosis\Record\RecordInterface;

class JsonDeserializer implements MiddlewareInterface
{
    public function process(RecordInterface $record, MiddlewareHandlerInterface $handler): void
    {
        // Here you can manipulate your record before handle it in your consumer

        $handler->handle($record);
    }
}

```

Você pode sobrescrever o conteúdo do `payload` chamando `$record->setPayload()`:

```php
public function process(RecordInterface $record, MiddlewareHandlerInterface $handler): void
{
    $payload = $record->getPayload();

    $record->setPayload(json_decode($payload));

    $handler->handle($record);
}
```

Então, você poderá configurar seu novo *middleware* para ser executado para todas as mensagens, adicionando-o no arquivo de configuração `config/kafka.php`:

```php
// ...
'middlewares' => [
    'consumer' => [
        \Metamorphosis\Middlewares\Log::class,
        \App\Kafka\Middlewares\JsonDeserializer::class,
    ],
],
// ...
```

Se preferir, você pode configurar o *middleware* para rodar no nível do tópico ou no nível do *consumer group*:

```php
'topics' => [
    'price_update' => [
        'topic' => 'products.price.update',
        'broker' => 'price_brokers',
        'consumer_groups' => [
            'default' => [
                'offset' => 0,
                'handler' => '\App\Kafka\Consumers\PriceUpdateHandler',
                'middlewares' => [
                    \App\Kafka\Middlewares\ConsumerGroupMiddlewareExample::class,
                ],
            ],
        ],
        'middlewares' => [
            \App\Kafka\Middlewares\TopicMiddlewareExample::class,
        ],
    ],
],
```

A ordem é importante aqui: os *middlewares* serão executados como uma fila, do escopo mais geral para o mais específico (escopo global > escopo de tópico > escopo de *group_consumers*).

<a name="schemas"></a>
### Schemas

Quando estiver usando o *middleware* **Avro decoder**, você deverá executar uma API para obter o `Avro Scheme`, de modo a tratar a mensagem codificada recebida.

Um *Schema* é basicamente um *template* Avro, indicando como manipular o registro recebido. Isso deve ser usado tanto para consumir quanto para produzir a mensagem.

Como um *Schema* pode ter uma forma de autenticação diferente do `brocker`, para fornecer flexibilidade em como manipular a autenticação, nós criamos uma chave `request_options` na configuração. Este campo será utilizado através da biblioteca `GuzzleHttp`. Então, quaisquer opções aqui serão enviadas para o `GuzzleHttp`.

```php
'avro_schemas' => [
    'default' => [
        'url' => '',
        'request_options' => [
            'headers' => [
                'Authorization' => [
                    'Basic '.base64_encode(
                        env('AVRO_SCHEMA_USERNAME').':'.env('AVRO_SCHEMA_PASSWORD')
                    ),
                ],
            ],
        ],
    ],
],
```

<a name="commands"></a>
### Comandos

Há alguns comandos para ajudar a automatizar a criação de classes e para executar o *consumer*.

<a name="commands-consumer"></a>
#### Criando um Consumer

Voce pode criar uma classe *consumer*, que irá manipular todos os registros recebidos do tópico, usando o seguinte comando:

```bash
$ php artisan make:kafka-consumer PriceUpdateHandler
```

Isto irá criar uma classe `KafkaConsumer` dentro da aplicação, no diretório `app/Kafka/Consumers/`.

Nela, haverá um método `handler`, para o qual serão enviados todos os registros do tópico para o `Consumer`.

Métodos estão disponíveis para manipular exceções:

- `warning`: este método será chamado toda vez que algo não crítico for recebido do tópico.
    Como, por exemplo, uma mensagem informando que não há mais registros para serem consumidos.
- `failure`: este método será executado toda vez que algo crítico ocorrer. Por exemplo, um erro na decodificação do registro.

```php
use App\Repository;
use Exception;
use Metamorphosis\Exceptions\ResponseWarningException;
use Metamorphosis\Record\RecordInterface;
use Metamorphosis\TopicHandler\Consumer\AbstractHandler;

class PriceUpdateHandler extends AbstractHandler
{
    public $repository;

    /**
     * Create a new consumer topic handler instance.
     *
     * @return void
     */
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Handle payload.
     *
     * @param Record $record
     *
     * @return void
     */
    public function handle(RecordInterface $record): void
    {
        $product = $record->getPayload();

        $this->repository->update($product['id'], $product['price']);
    }

    public function warning(ResponseWarningException $exception): void
    {
        // handle warning exception
    }

    public function failed(Exception $exception): void
    {
        // handle failure exception
    }
}
```


<a name="commands-middleware"></a>
#### Criando um Middleware

Você pode criar uma classe *middleware*, que trabalhará entre os dados recebidos do *broker* e antes dos mesmos serem transmitidos para os *consumers*, usando o seguinte comando:

```bash
$ php artisan make:kafka-middleware PriceTransformerMiddleware
```

Este comando criará uma classe `PriceTransformerMiddleware` dentro da aplicação, no diretório `app/Kafka/Middlewares/`.
Você pode configurar seu uso no arquivo `config/kafka.php`, colocando-o em um dos três níveis, dependendo de quão genérico ou específico é o *middleware*.

Para mais detalhes sobre *middlewares*, veja [this section](#middlewares).

<a name="commands-running-consumer"></a>
#### Rodando um Consumer
Este comando serve para iniciar o consumo do kafka e o recebimento dos dados pelos seus `consumers`.
O uso mais básico é obtido com o comando a seguir:

```bash
$ php artisan kafka:consume price-update
```

Este comando irá ser executado em um laço `while true`, isto é, ele não irá parar a execução por conta própria.
Mas, erros podem ocorrer, então, nós recomendamos fortemente que você execute este comando junto com o [supervisor](http://supervisord.org/running.html), como no exemplo a seguir:

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

##### Parâmetros

Embora você possa executar este comando simples, ele provê algumas opções que você pode informar para torná-lo mais adequado às suas necessidades.

- `--broker=`

    Algumas vezes, você pode querer indicar ao qual *broker* o *consumer* deverá se conectar (talvez, para fins de teste ou depuração).
    Para isso, você precisa somente executar o comando com a opção `--broker`, indicando outra chave de conexão que esteja configurada no arquivo `config/kafka.php`.

    `$ php artisan kafka:consume price-update --broker='some-other-broker'`

- `--offset=`

    E se você precisar iniciar o consumo de um tópico em um ponto específico (isto pode ser útil para fins de depuração), você pode passar a opção `--offset=`. Mas, nesse caso, será necessário também indicar a partição.

    `$ php artisan kafka:consume price-update --partition=2 --offset=34`

- `--partition=`

    Se você desejar especificar em qual partição o `consumer` deve ser anexado, você pode definir a opção `--partition=`.

    `$ php artisan kafka:consume price-update --partition=2 --offset=34`

- `--timeout=`

   Você pode especificar qual o tempo limite de execução do `consumer`, através da opção `--timeout=`. O tempo deve estar em milissegundos.

   `$ php artisan kafka:consume price-update --timeout=23000`

