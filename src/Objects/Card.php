<?php

namespace Egyjs\Arb\Objects;

use Illuminate\Support\Collection;
class Card
{
    public const CREDIT = 'C';
    public const DEBIT = 'D';


    protected string $expYear;
    protected string $expMonth;

    protected string $member;
    protected string $cvv2;
    protected string $cardNo;
    protected string $cardType;

    public function __construct(array $data)
    {
        $this->expYear = $data['year'];
        $this->expMonth = $data['month'];
        $this->member = $data['name'];
        $this->cvv2 = $data['cvv'];
        $this->cardNo = $data['number'];
        $this->cardType = $data['type'];
    }

    public function getYear(): string
    {
        return $this->expYear;
    }

    public function getMonth(): string
    {
        return $this->expMonth;
    }

    public function getName(): string
    {
        return $this->member;
    }

    public function getCvv(): string
    {
        return $this->cvv2;
    }

    public function getNumber(): string
    {
        return $this->cardNo;
    }

    public function getType(): string
    {
        return $this->cardType;
    }

    // return all Collection of card data using magic method
    public function toArray(): array
    {
        return [
            'expYear' => $this->getYear(),
            'expMonth' => $this->getMonth(),
            'member' => $this->getName(),
            'cvv2' => $this->getCvv(),
            'cardNo' => $this->getNumber(),
            'cardType' => $this->getType(),
        ];
    }
}
