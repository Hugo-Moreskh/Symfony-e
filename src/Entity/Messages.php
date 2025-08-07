<?php

namespace App\Entity;

use App\Repository\MessagesRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MessagesRepository::class)]
class Messages
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

 #[ORM\ManyToOne(targetEntity: User::class)]
#[ORM\JoinColumn(name: "sender_id", referencedColumnName: "id", nullable: false)]
private ?User $sender = null;

#[ORM\ManyToOne(targetEntity: User::class)]
#[ORM\JoinColumn(name: "recipient_id", referencedColumnName: "id", nullable: false)]
private ?User $recipient = null;


    #[ORM\Column]
    private ?int $group_id = null;

    #[ORM\Column(length: 255)]
    private ?string $content = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $send_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $read_at = null;

    public function getId(): ?int
    {
        return $this->id;
    }
public function getSender(): ?User
{
    return $this->sender;
}

public function setSender(User $sender): static
{
    $this->sender = $sender;
    return $this;
}
public function getRecipient(): ?User
{
    return $this->recipient;
}

public function setRecipient(User $recipient): static
{
    $this->recipient = $recipient;
    return $this;
}

    public function getGroupId(): ?int
    {
        return $this->group_id;
    }

    public function setGroupId(int $group_id): static
    {
        $this->group_id = $group_id;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getSendAt(): ?\DateTimeImmutable
    {
        return $this->send_at;
    }

    public function setSendAt(\DateTimeImmutable $send_at): static
    {
        $this->send_at = $send_at;

        return $this;
    }

    public function getReadAt(): ?\DateTimeImmutable
    {
        return $this->read_at;
    }

    public function setReadAt(\DateTimeImmutable $read_at): static
    {
        $this->read_at = $read_at;

        return $this;
    }
}
