<?php
class validateInput {
    public $itemId;

    public function __construct($itemId) {
        $this->itemId = $itemId;
    }

    public function validate() : bool {
        $maxLength = 1000000;
        $minLength = 1;
        $error = false;

        if (!preg_match('#^[0-9]+$#', $this->itemId)) {
            $error = true;
        } else {
            $itemIdInt = (int)$this->itemId;
            if ($maxLength < $itemIdInt || $minLength > $itemIdInt) {
                $error = true;
            }
        }

        return $error;
    }
}