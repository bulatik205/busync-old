<?php
class validateLimits
{
    public $limit;
    public $offset;

    public function __construct($limit, $offset)
    {
        $this->limit = $limit;
        $this->offset = $offset;
    }

    public function validate(): array
    {
        $data = [];

        if (isset($this->limit)) {
            if (!is_numeric($this->limit)) {
                $data['success'] = false;
                $data['error']['message'] = "Invalid type";
                return $data;
            }

            if ($this->limit > 50 || $this->limit < 0) {
                $data['success'] = false;
                $data['error']['message'] = "Invalid limit scope";
                return $data;
            }
        }

        if (isset($this->offset)) {
            if (!is_numeric($this->offset)) {
                $data['success'] = false;
                $data['error']['message'] = "Invalid type";
                return $data;
            }

            if ($this->offset > 100000 || $this->offset < 0) {
                $data['success'] = false;
                $data['error']['message'] = "Invalid offset scope";
                return $data;
            }
        }

        $data['success'] = true;
        return $data;
    }
}
