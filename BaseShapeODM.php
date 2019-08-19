<?php

namespace Shape\Doctrine\ODM\Entity;

use JsonSerializable;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * base shape class for sketch.
 *
 * @ODM\MappedSuperclass
 * @property string    $id              - primary key
 * @property int        $x              - x coordinate of shape
 * @property int        $y              - y coordinate of shape
 */
abstract class BaseShape implements JsonSerializable
{
    /**
     * @var string
     * @ODM\Id
     */
    protected $id;

    /**
     * @var int
     * @ODM\Field(type="int", nullable=false)
     */
    protected $x;

    /**
     * @var int
     * @ODM\Field(type="int", nullable=false)
     */
    protected $y;

    /**
     * @var string
     * @ODM\Field(type="string", nullable=true)
     */
    protected $fill;

    /**
     * @var string
     * @ODM\Field(type="string", nullable=true)
     */
    protected $stroke;

    /**
     * @var int
     * @ODM\Field(type="int", nullable=true)
     */
    protected $strokeWidth;

    /**
     * BaseShape constructor.
     *
     * @param int $x
     * @param int $y
     * @param string|null $fill
     * @param string|null $stroke
     * @param int|null $strokeWidth
     */
    public function __construct(int $x, int $y, string $stroke = null, int $strokeWidth = null, string $fill = null)
    {
        $this->x = $x;
        $this->y = $y;
        $this->fill = $fill;
        $this->stroke = $stroke;
        $this->strokeWidth = $strokeWidth;
    }

    /**
     * Set id
     *
     * @param string $id
     * @return self
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Get id
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set x coordinate of shape
     *
     * @param int $x
     * @return BaseShape
     */
    public function setX($x)
    {
        $this->x = $x;
        return $this;
    }

    /**
     * Get x coordinate of shape
     *
     * @return int
     */
    public function getX()
    {
        return $this->x;
    }

    /**
     * Set y coordinate of shape
     *
     * @param int $y
     * @return BaseShape
     */
    public function setY($y)
    {
        $this->y = $y;
        return $this;
    }

    /**
     * Get y coordinate of shape
     *
     * @return int
     */
    public function getY()
    {
        return $this->x;
    }

    /**
     * Set fill color
     *
     * @param string $fill
     * @return BaseShape
     */
    public function setFill($fill)
    {
        $this->fill = $fill;
        return $this;
    }

    /**
     * Get fill color
     *
     * @return string
     */
    public function getFill()
    {
        return $this->fill;
    }

    /**
     * Set stroke color
     *
     * @param string $stroke
     * @return BaseShape
     */
    public function setStroke($stroke)
    {
        $this->stroke = $stroke;
        return $this;
    }

    /**
     * Get stroke color
     *
     * @return string
     */
    public function getStroke()
    {
        return $this->stroke;
    }

    /**
     * Set stroke width
     *
     * @param int $strokeWidth
     * @return BaseShape
     */
    public function setStrokeWidth($strokeWidth)
    {
        $this->strokeWidth = $strokeWidth;
        return $this;
    }

    /**
     * Get stroke width
     *
     * @return int
     */
    public function getStrokeWidth()
    {
        return $this->strokeWidth;
    }

    /**
     * @return string
     */
    abstract public function jsonSerialize();

}
