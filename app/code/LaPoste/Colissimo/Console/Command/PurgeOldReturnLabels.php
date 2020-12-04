<?php
/*******************************************************
 * Copyright (C) 2018 La Poste.
 *
 * This file is part of La Poste - Colissimo module.
 *
 * La Poste - Colissimo module can not be copied and/or distributed without the express
 * permission of La Poste.
 *******************************************************/
namespace LaPoste\Colissimo\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use \LaPoste\Colissimo\Cron\TruncateLogs as CronTruncate;

class PurgeOldReturnLabels extends Command
{
    protected $state;
    protected $purgeOldReturnLabelsCron;

    public function __construct(
        \Magento\Framework\App\State $state,
        \LaPoste\Colissimo\Cron\PurgeOldReturnLabels $purgeOldReturnLabelsCron
    ) {
        $this->state = $state;
        $this->purgeOldReturnLabelsCron= $purgeOldReturnLabelsCron;

        return parent::__construct();
    }

    protected function configure()
    {
        $this->setName('lpc:purgeOldReturnLabels')
            ->setDescription('Purge old return labels');
        parent::configure();
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        try {
            // this is needed for a Command
            $this->state->setAreaCode(
                \Magento\Framework\App\Area::AREA_ADMINHTML
            );

            $this->purgeOldReturnLabelsCron->execute();
            $output->writeln('Purge successful');
        } catch (\Exception $exc) {
            $output->writeln($exc->getMessage());
        }
    }
}
