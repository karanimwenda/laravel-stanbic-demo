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

use function Laravel\Prompts\text;

class DemoSinglePaymentToAccountCommand extends Command
{
    protected $signature = 'demo:single-payment-to-account';

    protected $description = 'Create a demo single payment';

    public function handle()
    {
        $messageId = fake()->regexify('MSG0[A-Z0-9]{5}');
        $companyName = 'CINCH.MARKETS/CINCHH2H';
        $companyAcNo = '9040012825999';

        // 1. Create group header
        $groupHeader = GroupHeader::make()
            ->setMessageId($messageId)
            ->setCreationDate(now())
            ->setInitiatingParty(null, $companyName);

        $filePath = Pain00100103::make()
            ->setGroupHeader($groupHeader)
            ->addPaymentInfo($this->getPaymentInfo($companyName, $companyAcNo))
            ->store();

        $this->line("Saved to: \n\t{$filePath}");
    }

    public function getPaymentInfo(string $companyName, string $companyAcNo): PaymentInfo
    {
        $debtorBankCode = '190101';
        $paymentInfoId = fake()->regexify('PMTINF0[A-Z0-9]{5}');

        $paymentInfo = PaymentInfo::make()
            ->setPaymentInfoId($paymentInfoId)
            ->setPaymentMethod(PaymentMethod::CreditTransfer)
            ->setBatchBooking(true)
            ->setPaymentTypeInfo(InstructionPriority::Norm)
            ->setRequestedExecutionDate(now())
            ->setDebtor($companyName, new PostalAddress(countryCode: CountryCode::Ghana))
            ->setDebtorAccount($companyAcNo, Currency::Cedi)
            ->setDebtorAgent($debtorBankCode)
            ->setChargeBearer(ChargeBearerType::Debt)
            ->addCreditTransferTransactionInfo($this->getCreditTransferTransactionInfo());

        return $paymentInfo;
    }

    public function getCreditTransferTransactionInfo(): CreditTransferTransactionInfo
    {
        $paymentId = fake()->regexify('PMT0[A-Z0-9]{5}');
        $instructionId = fake()->regexify('INST0[A-Z0-9]{5}');
        $amount = intval(text(label: 'Amount: ', required: true));

        $creditorBankCode = text(label: 'Creditor Bank Code: ', required: true);
        $bank = text(label: 'Creditor Bank Name: ', required: true);
        $beneficiaryName = text(label: 'Beneficiary Name: ', required: true);
        $beneficiaryAcNo = text(label: 'Beneficiary Ac No: ', required: true);

        $paymentDescription = fake()->words(3, true);

        return CreditTransferTransactionInfo::make()
            ->setPaymentId($paymentId, $instructionId)
            ->setAmount($amount, Currency::Cedi)
            ->setCreditorAgent($creditorBankCode, $bank, new PostalAddress(countryCode: CountryCode::Ghana))
            ->setCreditor($beneficiaryName, new PostalAddress(
                fake()->streetName(),
                fake()->buildingNumber(),
                fake()->postcode(),
                fake()->city(),
                CountryCode::Ghana,
            ))
            ->setCreditorAccount($beneficiaryAcNo)
            ->setRemittanceInfo($paymentDescription);
    }
}
