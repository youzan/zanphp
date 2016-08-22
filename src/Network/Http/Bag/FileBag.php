<?php
/**
 * Created by PhpStorm.
 * User: xiaoniu
 * Date: 16/8/22
 * Time: 下午8:11
 */
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
        $this->name = $files['name'];
        $this->type = $files['type'];
        $this->tmpName = $files['tmp_name'];
        $this->error = $files['error'];
        $this->size = $files['size'];
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
        return 0 == $this->error;
    }
}
