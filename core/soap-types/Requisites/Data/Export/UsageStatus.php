<?php
namespace Environment\Soap\Types\Data\Export;

class UsageStatus {
    public
        $isActive,
        $dateTime,
        $description;

    public function __construct($isActive, $dateTime, $description){
        $this->isActive    = $isActive;
        $this->dateTime    = $dateTime;
        $this->description = $description;
    }
}
?>