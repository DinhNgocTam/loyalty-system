<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\WalletConsistencyService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:wallet:balance:check', description: 'Check wallet balance consistency against point ledger.')]
final class WalletBalanceCheckCommand extends Command
{
    public function __construct(private readonly WalletConsistencyService $walletConsistencyService)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('limit', null, InputOption::VALUE_REQUIRED, 'Maximum wallets to inspect', '100')
            ->addOption('fix', null, InputOption::VALUE_NONE, 'Recalculate mismatched wallet balances');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $limit = max(1, (int) $input->getOption('limit'));
        $withFix = (bool) $input->getOption('fix');

        $mismatches = $this->walletConsistencyService->findMismatches($limit);

        if ($mismatches === []) {
            $io->success('All checked wallets are consistent: wallet.balance == SUM(points.point_amount).');

            return Command::SUCCESS;
        }

        $io->warning(sprintf('Found %d inconsistent wallet(s).', count($mismatches)));
        $io->table(
            ['wallet_id', 'member_id', 'stored_balance', 'computed_balance', 'diff'],
            array_map(
                static fn (array $row): array => [
                    $row['wallet_id'],
                    $row['member_id'],
                    $row['stored_balance'],
                    $row['computed_balance'],
                    $row['diff'],
                ],
                $mismatches
            )
        );

        if (!$withFix) {
            $io->note('Run with --fix to recalculate balances from point logs.');

            return Command::FAILURE;
        }

        $summary = $this->walletConsistencyService->fixMismatches($limit);
        $io->success(sprintf(
            'Recalculation completed. fixed_wallets=%d, remaining_mismatches=%d',
            $summary['fixed_wallets'],
            $summary['remaining_mismatches']
        ));

        return $summary['remaining_mismatches'] > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
