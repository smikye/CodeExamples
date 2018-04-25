<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="menu")
 */
class MenuItem
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     */
    protected $name;

    /**
     * @ORM\Column(type="float")
     */
    protected $price;

    /**
     * @ORM\Column(type="integer")
     */
    protected $likes;

    /**
     * @ORM\Column(type="integer")
     */
    protected $dislikes;

    /**
     * @ORM\ManyToMany(targetEntity="Receipt", mappedBy="menuItems")
     */
    protected $receipts;


    /**
     * @return integer;
     */
    public function getId(){
        return $this->id;
    }

    /**
     * @return string;
     */
    public function getName(){
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name){
        $this->name = $name;

        return $this;
    }

    /**
     * @return float;
     */
    public function getPrice(){
        return $this->price;
    }

    /**
     * @param float $price
     * @return $this
     */
    public function setPrice($price){
        $this->price = $price;

        return $this;
    }

    /**
     * @return integer;
     */
    public function getLikes(){
        return $this->likes;
    }

    /**
     * @return $this
     */
    public function addLike(){
        $this->likes++;

        return $this;
    }

    /**
     * @return integer;
     */
    public function getDislikes(){
        return $this->dislikes;
    }

    /**
     * @return $this
     */
    public function addDislike(){
        $this->dislikes++;

        return $this;
    }

    /**
     * @return ArrayCollection|Receipt[]
     */
    public function getReceipts()
    {
        return $this->receipts;
    }

    /**
     * @param ArrayCollection $receipts
     * @return $this
     */
    public function setReceipts(ArrayCollection $receipts)
    {
        $this->receipts = $receipts;

        return $this;
    }

    /**
     * @param Receipt $receipt
     * @return $this
     */
    public function addAdminGroup(Receipt $receipt)
    {
        $this->receipts[] = $receipt;

        return $this;
    }
}