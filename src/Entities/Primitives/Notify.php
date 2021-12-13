<?php

namespace Innokassa\MDK\Entities\Primitives;

use Innokassa\MDK\Exceptions\Base\InvalidArgumentException;

/**
 * Адреса уведомлений покупателя
 */
class Notify
{
    /**
     * @throws InvalidArgumentException
     * @param string|null $value
     */
    public function __construct(string $value=null)
    {
        if($value === null)
            return;

        try{
            $this->setEmail($value);
        } catch(InvalidArgumentException $e)
        {}

        try{
            $this->setPhone($value);
        } catch(InvalidArgumentException $e)
        {}

        if(!$this->email && !$this->phone)
            throw new InvalidArgumentException("invalid notify value '$value'");
    }

    //**********************************************************************

    /**
     * Установить email
     *
     * @throws InvalidArgumentException
     * @param string $email
     * @return self
     */
    public function setEmail(string $email): self
    {
        $email = trim($email);
        if(!($email = filter_var($email, FILTER_VALIDATE_EMAIL)))
            throw new InvalidArgumentException("invalid notify email '$email'");

        $this->email = $email;

        return $this;
    }

    /**
     * Получить email
     *
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    //**********************************************************************

    /**
     * Установить номер телефона, в строке допускаются следующие символы: + ()-
     *
     * @throws InvalidArgumentException
     * @param string $phone
     * @return self
     */
    public function setPhone(string $phone): self
    {
        $phone = trim($phone);

        if(!$phone)
            throw new InvalidArgumentException("invalid notify phone ''");
		
		$phone = str_replace("+", "", $phone);
		$phone = str_replace("(", "", $phone);
		$phone = str_replace(")", "", $phone);
		$phone = str_replace("-", "", $phone);
		$phone = str_replace(" ", "", $phone);

        if($phone[0] == '8')
			$phone = '+7'.substr($phone, 1);

        if($phone[0] == '7')
			$phone = '+'.$phone;

        if(!preg_match("/\+7\d{10}/", $phone))
            throw new InvalidArgumentException("invalid notify phone '$phone'");

        $this->phone = $phone;

        return $this;
    }

    /**
     * Получить номер телефона
     *
     * @return string
     */
    public function getPhone(): string
    {
        return $this->phone;
    }

    //######################################################################
    // PRIVATE
    //######################################################################

    private $email = '';
    private $phone = '';
};
