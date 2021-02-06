<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use App\Repository\BlogPostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\RangeFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Serializer\Filter\PropertyFilter;


// "access_control"="is_granted('IS_AUTHENTICATED_FULLY') and object.getAuthor()==user"
/**
 * @ORM\Entity(repositoryClass=BlogPostRepository::class)
 * @ApiFilter(
 *     SearchFilter::class,
 *     properties={
 *          "id":"exact",
 *          "title":"ipartial",
 *          "content":"partial",
 *          "author":"exact",
 *          "author.name": "partial"
 *     }
 * )
 * @ApiFilter(
 *     DateFilter::class,
 *     properties={
 *      "published"
 *     }
 * )
 * @ApiFilter(
 *     RangeFilter::class,
 *     properties={"id"}
 * )
 * @ApiFilter(
 *     OrderFilter::class,
 *     properties={"id","published","title"}
 * )
 * @ApiFilter(
 *     PropertyFilter::class,
 *     arguments={
 *          "parameterName" : "properties",
 *          "overrideDefaultProperties" : false,
 *          "whitelist" : {"id","title"}
 *     }
 * )
 * @ApiResource(
 *     attributes={
 *          "order"={"published":"DESC"},
 *          "pagination_client_items_per_page"=true
 *     },
 *     itemOperations={
 *     "get"={
 *              "normalization_context"={
 *                 "groups"={"get-post-with-author"}
 *             }
 *     },
 *     "put"={
 *          "access_control"="is_granted('ROLE_EDITOR') or (is_granted('ROLE_WRITER') and object.getAuthor()==user)"
 *          },
 * },
 *     collectionOperations={
 *     "get",
 *     "post"={
 *          "access_control"="is_granted('ROLE_WRITER')"
 *          },
 *     },
 *     denormalizationContext={
 *          "grouops"={"post"}
 *     }
 * )
 */
class BlogPost implements AuthoredEntityInterface, PublishedDateInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * @Groups({"post","get-post-with-author"})
     */
    private $title;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"get-post-with-author"})
     */
    private $published;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     * @Groups({"post","get-post-with-author"})
     */
    private $content;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User",inversedBy="posts")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"get-post-with-author"})
     */
    private $author;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment",mappedBy="post")
     * @ORM\JoinColumn(nullable=false)
     * @ApiSubresource()
     * @Groups({"post","get-post-with-author"})
     */
    private $comment;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Image")
     * @ORM\JoinTable()
     * @ApiSubresource()
     * @Groups({"post","get-post-with-author"})
     */
    private $images;

    public function __construct()
    {
        $this->comment = new ArrayCollection();
        $this->images = new ArrayCollection();
    }

    public function getComment(): Collection
    {
        return $this->comment;
    }

    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     * @Groups({"post"})
     */
    private $slug;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getPublished(): ?\DateTimeInterface
    {
        return $this->published;
    }

    public function setPublished(\DateTimeInterface $published): PublishedDateInterface
    {
        $this->published = $published;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }

    public function getSlug() : ?string
    {
        return $this->slug;
    }

    /**
     * @return User
     */
    public function getAuthor() : User
    {
        return $this->author;
    }

    public function setAuthor(UserInterface $author): AuthoredEntityInterface
    {
        $this->author = $author;
        return $this;
    }

    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(Image $image) {
        $this->images->add($image);
    }

    public function removeImage(Image $image) {
        $this->images->removeElement($image);
    }
}
