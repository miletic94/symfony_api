<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Component\Validator\Constraints as Assert;

/** A Product */
#[Entity]
#[ApiResource()]
class Product
{
    /** The product id */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /** The product manufacturer part number */
    #[ORM\Column]
    #[Assert\NotNull()]
    private ?string $mpn = null;

    /** The product name */
    #[ORM\Column]
    #[Assert\NotBlank()]
    private string $name = '';

    /** The description of the product  */
    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank()]
    private string $description = "";

    /** The date of the issue of the product*/
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Assert\NotNull()]
    private ?\DateTimeInterface $listedDate = null;

    /** The manufacturer of the product  */
    #[ORM\ManyToOne(targetEntity: 'Manufacturer', inversedBy: "products")]
    #[Assert\NotNull()]
    private ?Manufacturer $manufacturer = null;


    /**
     * Get the value of mpn
     */
    public function getMpn()
    {
        return $this->mpn;
    }

    /**
     * Set the value of mpn
     *
     * @return  self
     */
    public function setMpn($mpn)
    {
        $this->mpn = $mpn;

        return $this;
    }

    /**
     * Get the value of name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the value of name
     *
     * @return  self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the value of description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the value of description
     *
     * @return  self
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get the value of listedDate
     */
    public function getListedDate()
    {
        return $this->listedDate;
    }

    /**
     * Set the value of listedDate
     *
     * @return  self
     */
    public function setListedDate($listedDate)
    {
        $this->listedDate = $listedDate;

        return $this;
    }

    /**
     * Get the value of id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the value of manufacturer
     */
    public function getManufacturer()
    {
        return $this->manufacturer;
    }

    /**
     * Set the value of manufacturer
     *
     * @return  self
     */
    public function setManufacturer($manufacturer)
    {
        $this->manufacturer = $manufacturer;

        return $this;
    }
}
