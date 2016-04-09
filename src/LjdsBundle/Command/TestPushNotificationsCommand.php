<?php

namespace LjdsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestPushNotificationsCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this
			->setName('ljds:push:test');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$response = $this->getContainer()->get('app.push_notifications')->test();

		$output->writeln($response['statusCode'] . ' - ' . $response['reasonPhrase']);

		if ($response['statusCode'] != 200)
			$output->write($response['content']);
	}
}
