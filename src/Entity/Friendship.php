<?php

namespace App\Entity;

use App\Repository\FriendshipRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\User;

#[ORM\Entity(repositoryClass: FriendshipRepository::class)]
#[ORM\Index(columns: ["user_id", "friend_id"], name: "friendship_users_idx")]
class Friendship
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "id", nullable: false, onDelete: "CASCADE")]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "friend_id", referencedColumnName: "id", nullable: false, onDelete: "CASCADE")]
    private ?User $friend = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $accepted_at = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $id_room = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getFriend(): ?User
    {
        return $this->friend;
    }

    public function setFriend(?User $friend): static
    {
        $this->friend = $friend;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;
        return $this;
    }

    public function getAcceptedAt(): ?\DateTimeImmutable
    {
        return $this->accepted_at;
    }

    public function setAcceptedAt(?\DateTimeImmutable $accepted_at): static
    {
        $this->accepted_at = $accepted_at;
        return $this;
    }

    public function getIdRoom(): ?string
    {
        return 'room_' . min($this->user->getId(), $this->friend->getId()) . '_' . max($this->user->getId(), $this->friend->getId());
    }

    public function setIdRoom(?string $id_room): static
    {
        $this->id_room = $id_room;
        return $this;
    }
}
