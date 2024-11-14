<?php

namespace App\Entity;

use App\Repository\SourceRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SourceRepository::class)]
class Source
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $rssUrl = null;
    
    private ?int $newsCount = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getRssUrl(): ?string
    {
        return $this->rssUrl;
    }

    public function setRssUrl(string $rssUrl): static
    {
        $this->rssUrl = $rssUrl;

        return $this;
    }
    
    public function getNewsCount(): ?int
    {
        return $this->newsCount;
    }
    
    public function setNewsCount(int $count): self
    {
        $this->newsCount = $count;
        return $this;
    }
    
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'rssUrl' => $this->rssUrl,
        ];
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
