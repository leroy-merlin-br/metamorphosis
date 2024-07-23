## Guia Rápido

- [Configurar usando arquivos](#config)
- [Configurar usando objetos](#config-dto)
- [Consumidor](#consumer)
   - [Criando um consumidor](#creating-consumer)
   - [Executando um consumidor](#running-consumer)
- [Produtor](#producer)
  - [Produzindo mensagens](#produce-message)

<a name="config"></a>
### Configurar usando arquivos

Para começar a usar arquivos de configuração, são necessários pelo menos dois arquivos. Um arquivo para manter os tópicos
configuração e um arquivo para manter a configuração do broker e do esquema. Neste exemplo, usaremos os arquivos
`config/kafka.php` e `config/service.php`.


### Arquivo `config/kafka.php`:

Este arquivo mantém configurações sobre tópicos, consumidores e produtores.
Deve retornar um array de tópicos contendo o nome do tópico, topic_id, consumidor, produtor e as configurações de cada um deles:


```php
<?php

return [
    'topics' => [
        'this_is_your_topic_name' => [
            'topic_id' => "this_is_your_topic_id",
            'consumer' => [
                'consumer_group' => 'your-consumer-group',
                'offset_reset' => 'earliest',
                'offset' => 0,
                'partition' => 0,
                'handler' => '\App\Kafka\Consumers\ConsumerExample',
                'timeout' => 20000,
                'auto_commit' => true,
                'commit_async' => false,
                'middlewares' => [],
            ],
  
            'producer' => [
                'required_acknowledgment' => true,
                'is_async' => true,
                'max_poll_records' => 500,
                'flush_attempts' => 10,
                'middlewares' => [],
                'timeout' => 10000,
                'partition' => constant('RD_KAFKA_PARTITION_UA') ?? -1,
            ],
        ]
    ],
];
```

### Arquivo `config/service.php`

Esse arquivo possui as configurações de **broker** e **schema** utilizados.

```php
<?php

return [
    'avro_schema' => [
        'url' => '',
        'request_options' => [
            'headers' => [
                'Authorization' => [
                    'Basic ' . base64_encode(
                        env('AVRO_SCHEMA_USERNAME').':'.env('AVRO_SCHEMA_PASSWORD')
                    ),
                ],
            ],
        ],

        'ssl_verify' => true,
        'username' => 'USERNAME',
        'password' => 'PASSWORD',
    ],
    
    'broker' => [
        'connections' => 'kafka:9092',
        'auth' => [
            'type' => 'ssl', 
            'ca' => storage_path('ca.pem'),
            'certificate' => storage_path('kafka.cert'),
            'key' => storage_path('kafka.key'),
        ],
    ],
];
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

Para começar a consumir o tópico, a maneira mais simples de vê-lo funcionando é executando o comando kafka:consume junto com o nome do tópico, arquivo de configuração do tópico e arquivo de configuração do serviço:

```bash
$ php artisan kafka:consume this_is_your_topic_name --config_name=config.file --service_name=service.file
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
