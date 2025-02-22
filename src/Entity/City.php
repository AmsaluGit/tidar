<?php

namespace App\Entity;

use App\Repository\CityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CityRepository::class)
 */
class City
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity=Country::class, inversedBy="cities")
     * @ORM\JoinColumn(nullable=false)
     */
    private $country;

    /**
     * @ORM\OneToMany(targetEntity=Organization::class, mappedBy="city")
     */
    private $organizations;

    /**
     * @ORM\OneToMany(targetEntity=User::class, mappedBy="city")
     */
    private $usersInCity;



    public function __construct()
    {
        $this->organizations = new ArrayCollection();
        $this->usersInCity = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCountry(): ?Country
    {
        return $this->country;
    }

    public function setCountry(?Country $country): self
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @return Collection|Organization[]
     */
    public function getOrganizations(): Collection
    {
        return $this->organizations;
    }

    public function addOrganization(Organization $organization): self
    {
        if (!$this->organizations->contains($organization)) {
            $this->organizations[] = $organization;
            $organization->setCity($this);
        }

        return $this;
    }

    public function removeOrganization(Organization $organization): self
    {
        if ($this->organizations->removeElement($organization)) {
            // set the owning side to null (unless already changed)
            if ($organization->getCity() === $this) {
                $organization->setCity(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsersInCity(): Collection
    {
        return $this->usersInCity;
    }

    public function addUsersInCity(User $usersInCity): self
    {
        if (!$this->usersInCity->contains($usersInCity)) {
            $this->usersInCity[] = $usersInCity;
            $usersInCity->setCity($this);
        }

        return $this;
    }

    public function removeUsersInCity(User $usersInCity): self
    {
        if ($this->usersInCity->removeElement($usersInCity)) {
            // set the owning side to null (unless already changed)
            if ($usersInCity->getCity() === $this) {
                $usersInCity->setCity(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->name;
    }

}
