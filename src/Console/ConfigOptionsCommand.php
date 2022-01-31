<?php
namespace Metamorphosis\Console;

use Illuminate\Console\Command as BaseCommand;
use Metamorphosis\AbstractConfigManager;
use Metamorphosis\Connectors\Consumer\Config;
use Metamorphosis\Connectors\Consumer\Factory;
use Metamorphosis\Consumers\Runner;

class ConfigOptionsCommand extends BaseCommand
{
    /**
     * @var {inheritdoc}
     */
    protected $name = 'kafka:consume-config-class';

    /**
     * @var {inheritdoc}
     */
    protected $description = 'Consumes something with a based class config';

    /**
     * @var {inheritdoc}
     */
    protected $signature = 'kafka:consume-config-class {handler : handler.}';

    public function handle(Config $config)
    {
        $configManager = $config->makeWithConfigOptions($this->argument()['handler']);

        $this->writeStartingConsumer($configManager);

        $manager = Factory::make($configManager);

        $runner = app(Runner::class, compact('manager'));
        $runner->run(2);
    }

    private function writeStartingConsumer(AbstractConfigManager $configManager)
    {
        $text = 'Starting consumer for topic: '.$configManager->get('topic').PHP_EOL;
        $text .= ' on consumer group: '.$configManager->get('consumer_group').PHP_EOL;
        $text .= 'Connecting in '.$configManager->get('connections').PHP_EOL;
        $text .= 'Running consumer..';

        $this->output->writeln($text);
    }
}
