<?php


namespace App\Entity;


interface PublishedDateInterface
{
    public function setPublished(\DateTimeInterface $published): PublishedDateInterface;
}