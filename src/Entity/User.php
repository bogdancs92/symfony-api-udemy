<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use App\Controller\ResetPasswordAction;

/**
 * @ApiResource(
 *     itemOperations={
 *     "get"={
 *          "access_control"="is_granted('IS_AUTHENTICATED_FULLY')",
 *          "normalization_context"={
 *                  "groups"={"get"}
 *              },
 *          },
 *     "put"={
 *          "access_control"="is_granted('IS_AUTHENTICATED_FULLY') and object==user",
 *          "denormalization_context"={"groups"={"put"}},
 *          "normalization_context"={
 *                  "groups"={"get"}
 *              },
 *          },
 *     "put-reset-password"={
 *          "access_control"="is_granted('IS_AUTHENTICATED_FULLY') and object==user",
 *          "method"="PUT",
 *          "path"="/user/{id}/reset-password",
 *          "controller"=ResetPasswordAction::class,
 *          "denormalization_context"={"groups"={"put-reset-password"}},
 *          }
 *     },
 *     collectionOperations={
 *     "post"={
 *           "denormalization_context"={"groups"={"post"}},
 *             "normalization_context"={
 *                  "groups"={"get"}
 *              },
 *          }
 *     }
 * )
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity("username")
 * @UniqueEntity("email")
 */
class User implements UserInterface
{
    const ROLE_COMMENTATOR = 'ROLE_COMMENTATOR';
    const ROLE_WRITER = 'ROLE_WRITER';
    const ROLE_EDITOR = 'ROLE_EDITOR';
    const ROLE_ADMIN = 'ROLE_ADMIN';
    const ROLE_SUPERADMIN = 'ROLE_SUPERADMIN';
    const DEFAULT_ROLES = [self::ROLE_COMMENTATOR];
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"get", "get-comment-with-author"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"get","post", "get-comment-with-author","get-post-with-author"})
     * @Assert\NotBlank(groups={"post"})
     * @Assert\Length(min="6",max="255",groups={"post"})
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"put","post","get-admin","get-owner"})
     * @Assert\Email()
     */
    private $email;

    /**
     * @ORM\Column(type="boolean")
     */
    private $enabled;

    /**
     * @ORM\Column(type="string", length=40,nullable=true)
     */
    private $confirmationToken;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"post"})
     * @Assert\NotBlank()
     * @Assert\Regex(
     *     pattern="/(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9]).{7,}/",
     *     message="password at least 7, digit and numbers required"
     * )
     */
    private $password;

    /**
     * @ORM\Column(type="integer",nullable=true)
     */
    private $passwordChangedDate;

    /**
     * @Assert\NotBlank(groups={"post"})
     * @Groups({"post"})
     * @Assert\Expression(
     *     "this.getPassword()===this.getRetypedPassword()",message="Password doesn't match",groups={"post"}
     * )
     */
    private $retypedPassword;

    /**
     * @Groups({"put-reset-password"})
     * @Assert\NotBlank(groups={"put-reset-password"})
     * @Assert\Regex(
     *     pattern="/(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9]).{7,}/",
     *     message="password at least 7, digit and numbers required",
     *     groups={"put-reset-password"}
     * )
     */
    private $newPassword;

    /**
     * @Assert\NotBlank(groups={"put-reset-password"})
     * @Groups({"put-reset-password"})
     * @Assert\Expression(
     *     "this.getNewPassword()===this.getNewRetypedPassword()",message="Password doesn't match",
     * )
     */
    private $newRetypedPassword;
    /**
     * @Assert\NotBlank(groups={"put-reset-password"})
     * @Groups({"put-reset-password"})
     * @UserPassword(groups={"put-reset-password"})
     */
    private $oldPassword;
    /**
     * @Groups({"get", "put","post"})
     * @ORM\OneToMany(targetEntity="App\Entity\BlogPost",mappedBy="author")
     * @Groups({"get"})
     */
    private $posts;

    /**
     * @Groups({"get","put","post"})
     * @ORM\OneToMany(targetEntity="App\Entity\Comment",mappedBy="author")
     * @Groups({"get"})
     */
    private $comments;
    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"get","put","post"})
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @ORM\Column(type="simple_array",length=200)
     * @Groups({"get-admin","get-owner"})
     */
    private $roles;

    public function __construct()
    {
        $this->posts = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->roles = self::DEFAULT_ROLES;
        $this->enabled = false;
        $this->confirmationToken = null;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
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

    /**
     * @return Collection
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    /**
     * @return Collection
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }


    /**
     * @inheritDoc
     */
    public function getRoles() : array
    {
        // TODO: Implement getRoles() method.
        return $this->roles;
    }

    public function setRoles(array $roles)
    {
        $this->roles = $roles;
    }
    /**
     * @inheritDoc
     */
    public function getSalt()
    {
        // TODO: Implement getSalt() method.
        return null;
    }

    /**
     * @inheritDoc
     */
    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

    public function getRetypedPassword()
    {
        return $this->retypedPassword;
    }

    public function setRetypedPassword($retypedPassword): void
    {
        $this->retypedPassword = $retypedPassword;
    }

    public function getNewPassword() :?string
    {
        return $this->newPassword;
    }

    public function setNewPassword($newPassword): void
    {
        $this->newPassword = $newPassword;
    }

    public function getNewRetypedPassword() : ?string
    {
        return $this->newRetypedPassword;
    }

    public function setNewRetypedPassword($newRetypedPassword): void
    {
        $this->newRetypedPassword = $newRetypedPassword;
    }

    public function getOldPassword() : ?string
    {
        return $this->oldPassword;
    }

    /**
     * @param mixed $oldPassword
     */
    public function setOldPassword($oldPassword): void
    {
        $this->oldPassword = $oldPassword;
    }

    /**
     * @return mixed
     */
    public function getPasswordChangedDate()
    {
        return $this->passwordChangedDate;
    }

    public function setPasswordChangedDate($passwordChangedDate): void
    {
        $this->passwordChangedDate = $passwordChangedDate;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function getConfirmationToken()
    {
        return $this->confirmationToken;
    }

    public function setConfirmationToken($confirmationToken): void
    {
        $this->confirmationToken = $confirmationToken;
    }


}
