<?php

namespace App\Console\Commands;

use Akika\LaravelStanbic\Data\AggregateRoots\Pain00100103;
use Akika\LaravelStanbic\Data\ValueObjects\CreditTransferTransactionInfo;
use Akika\LaravelStanbic\Data\ValueObjects\GroupHeader;
use Akika\LaravelStanbic\Data\ValueObjects\PaymentInfo;
use Akika\LaravelStanbic\Data\ValueObjects\PostalAddress;
use Akika\LaravelStanbic\Enums\ChargeBearerType;
use Akika\LaravelStanbic\Enums\CountryCode;
use Akika\LaravelStanbic\Enums\Currency;
use Akika\LaravelStanbic\Enums\InstructionPriority;
use Akika\LaravelStanbic\Enums\PaymentMethod;
use Illuminate\Console\Command;

class DemoSinglePaymentCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo:single-payment';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a demo single payment';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $messageId = fake()->regexify("MSG0[A-Z0-9]{5}");
        $companyName = fake()->company();
        $companyAcNo = fake()->randomNumber(8, true);
        $amount = fake()->numberBetween(10_000, 99_999);

        $paymentId = fake()->regexify("PMT0[A-Z0-9]{5}");
        $instructionId = fake()->regexify("INST0[A-Z0-9]{5}");
        $bankCode = '190101';
        $bank = 'Stanbic Bank Ghana Ltd';
        $beneficiaryName = fake()->name();
        $beneficiaryAcNo = fake()->randomNumber(8, true);
        $paymentDescription = fake()->words(3, true);

        $paymentInfoId = fake()->regexify("PMTINF0[A-Z0-9]{5}");
        $companyAcNo = fake()->randomNumber(8, true);

        // 1. Create group header
        $groupHeader = GroupHeader::make()
            ->setMessageId($messageId)
            ->setCreationDate(now())
            ->setNumberOfTransactions(1)
            ->setControlSum($amount)
            ->setInitiatingParty($companyName, $companyAcNo);

        // 2. Create transaction info
        $transactionInfo = CreditTransferTransactionInfo::make()
            ->setPaymentId($paymentId, $instructionId)
            ->setAmount($amount, Currency::Cedi)
            ->setCreditorAgent($bankCode, $bank, new PostalAddress(countryCode: CountryCode::Ghana))
            ->setCreditor($beneficiaryName, new PostalAddress(
                fake()->streetName(),
                fake()->buildingNumber(),
                fake()->postcode(),
                fake()->city(),
                CountryCode::Ghana,
            ))
            ->setCreditorAccount($beneficiaryAcNo)
            ->setRemittanceInfo($paymentDescription);

        // 3. Create payment info
        $paymentInfo = PaymentInfo::make()
            ->setPaymentInfoId($paymentInfoId)
            ->setPaymentMethod(PaymentMethod::CreditTransfer)
            ->setBatchBooking(true)
            ->setPaymentTypeInfo(InstructionPriority::Norm)
            ->setRequestedExecutionDate(now())
            ->setDebtor($companyName, new PostalAddress(countryCode: CountryCode::Ghana))
            ->setDebtorAccount($companyAcNo, Currency::Cedi)
            ->setDebtorAgent($bankCode)
            ->setChargeBearer(ChargeBearerType::Debt)
            ->setCreditTransferTransactionInfo($transactionInfo);

        // 4. Generate and store XML
        $filePath = Pain00100103::make()
            ->setGroupHeader($groupHeader)
            ->setPaymentInfo($paymentInfo)
            ->store(); // Returns the stored file path

        $this->line("Saved to: \n\t{$filePath}");
    }
}
