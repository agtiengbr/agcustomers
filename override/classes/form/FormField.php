<?php

class FormField extends FormFieldCore
{
    protected $readonly;

    public function toArray()
    {
        return parent::toArray() + [
            'readonly' => $this->getReadonly()
        ];
    }


    /**
     * Get the value of readonly
     */ 
    public function getReadonly()
    {
        return $this->readonly;
    }

    /**
     * Set the value of readonly
     *
     * @return  self
     */ 
    public function setReadonly($readonly)
    {
        $this->readonly = $readonly;

        return $this;
    }
}