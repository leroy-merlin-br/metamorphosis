<?php
namespace Metamorphosis\Console;

use Illuminate\Console\Command as BaseCommand;
use Metamorphosis\Config\Validate;
use Metamorphosis\RunnerFactory;

class ConsumerCommand extends BaseCommand
{
    /**
     * @var {inheritdoc}
     */
    protected $name = 'kafka:consume';

    /**
     * @var {inheritdoc}
     */
    protected $description = 'Consumes something';

    /**
     * @var {inheritdoc}
     */
    protected $signature = 'kafka:consume
        {topic : topic.}
        {consumer-group? : consumer group name.}
        {--offset= : Sets the offset at which to start consumption.}
        {--partition= : Sets the partition to consume.}
        {--broker= : Override broker connection from config.}
        {--timeout= : Sets timeout for consumer.}';

    public function handle(RunnerFactory $runnerFactory, Validate $validate)
    {
        $validate->setOptionConfig($this->option(), $this->argument());

        $this->writeStartingConsumer();

        $runner = $runnerFactory->make();
        $runner->run();
    }

    private function writeStartingConsumer()
    {
        $text = 'Starting consumer for topic: '.config('kafka.runtime.topic');
        $text .= ' on consumer group: '.config('kafka.runtime.consumerGroupId');
        $text .= 'Connecting in '.config('kafka.runtime.connections');
        $text .= 'Running consumer..';

        $this->output->writeln($text);
    }
}
