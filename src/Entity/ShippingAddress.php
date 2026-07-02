<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
class ShippingAddress extends Address
{
    #[ORM\Column(length: 200, nullable: true)]
    private ?string $recipientName = null;

    #[ORM\Column(length: 500, nullable: true)]
    #[Assert\Length(max: 500)]
    private ?string $deliveryInstructions = null;

    public function getRecipientName(): ?string
    {
        return $this->recipientName;
    }

    public function setRecipientName(?string $recipientName): static
    {
        $this->recipientName = $recipientName;

        return $this;
    }

    public function getDeliveryInstructions(): ?string
    {
        return $this->deliveryInstructions;
    }

    public function setDeliveryInstructions(?string $deliveryInstructions): static
    {
        $this->deliveryInstructions = $deliveryInstructions;

        return $this;
    }
}
