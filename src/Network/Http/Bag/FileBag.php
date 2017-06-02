<?php
namespace Zan\Framework\Network\Http\Bag;

class FileBag
{
    private $name;

    private $type;

    private $tmpName;

    private $error;

    private $size;

    public function __construct(array $files = array())
    {
        $this->init($files);
    }

    private function init($files)
    {
        if ([] == $files) {
            return;
        }
        $this->name = $files['file']['name'];
        $this->type = $files['file']['type'];
        $this->tmpName = $files['file']['tmp_name'];
        $this->error = $files['file']['error'];
        $this->size = $files['file']['size'];
    }

    public function getName()
    {
        return $this->name;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getTmpName()
    {
        return $this->tmpName;
    }

    public function getError()
    {
        return $this->error;
    }

    public function getSize()
    {
        return $this->size;
    }

    public function valid()
    {
        return null !== $this->error && 0 == $this->error;
    }
}
