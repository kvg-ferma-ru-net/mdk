<?php

namespace Innokassa\MDK\Entities\Primitives;

use Innokassa\MDK\Exceptions\Base\InvalidArgumentException;

/**
 * Покупатель
 */
class Customer
{
    /**
     * @throws InvalidArgumentException
     * @param string $name
     * @param string $tin
     */
    public function __construct(string $name=null, string $tin=null)
    {
        if($name !== null)
            $this->setName($name);
        
        if($tin !== null)
            $this->setTin($tin);
    }

    //**********************************************************************

    /**
     * Установить ФИО
     * 
     * @throws InvalidArgumentException
     *
     * @param string $name
     * @return self
     */
    public function setName(string $name): self
    {
        $name = trim($name);
        if($name == '')
            throw new InvalidArgumentException('invalid customer name');

        $this->name = $name;

        return $this;
    }

    /**
     * Получить ФИО
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    //**********************************************************************

    /**
     * Установить ИНН
     * 
     * @throws InvalidArgumentException
     *
     * @param string $tin
     * @return self
     */
    public function setTin(string $tin): self
    {
        $tin = trim($tin);
        if($tin == '')
            throw new InvalidArgumentException('invalid customer tin');

        $this->tin = $tin;

        return $this;
    }

    /**
     * Получить ИНН
     *
     * @return string
     */
    public function getTin(): string
    {
        return $this->tin;
    }

    //######################################################################
    // PROTECTED
    //######################################################################

    protected $name = '';
    protected $tin = '';
};
