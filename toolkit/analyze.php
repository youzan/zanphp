<?php


var_export(analyze());

// 计算命名空间对class的依赖
function analyze()
{
    $nsDeps = [];

    $iter = scanPHPFiles(__DIR__ . "/../src");
    foreach ($iter as $path) {
        // TODO lint
        $code = file_get_contents($path);
        list($ns, $useNsList) = getUses($code);
        if ($useNsList) {
            if (!isset($nsDeps[$ns])) {
                $nsDeps[$ns] = [];
            }
            $nsDeps[$ns] += array_flip($useNsList);
        }
    }

    return $nsDeps;
}


function scanPHPFiles($dir)
{
    $regex = '/.*\.php$/';
    $iter = new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS);
    $iter = new \RecursiveIteratorIterator($iter, \RecursiveIteratorIterator::LEAVES_ONLY);
    $iter = new \RegexIterator($iter, $regex, \RegexIterator::GET_MATCH);

    foreach ($iter as $file) {
        yield realpath($file[0]);
    }
}


function getUses($code)
{
    $parser = new PHPParser($code);

    $useNsList = [];
    $ns = "";

    while ($token = $parser->nextToken()) {
        switch ($token->type) {
            case T_NAMESPACE:
                $list = [];
                while ($sub = $parser->nextToken()) {
                    if ($sub->type === PHPToken::T_CHAR && $sub->value === ";") {
                        break;
                    }
                    $list[] = $sub;
                }

                foreach ($list as $_token) {
                    switch ($_token->type) {
                        case T_NS_SEPARATOR:
                        case T_STRING:
                            // 统一成小写
                            $ns .= strtolower($_token->value);
                    }
                }

                break;

            case T_USE:
                $isNs = true;
                /** @var PHPToken[] $list */
                $list = [];
                while ($sub = $parser->nextToken()) {
                    if ($sub->type === PHPToken::T_CHAR) {
                        if ($sub->value === "(") {
                            $isNs = false;
                            break;
                        }
                        if ($sub->value === ";") {
                            break;
                        }
                    }
                    $list[] = $sub;
                }

                if ($isNs === false) {
                    continue;
                }

                $useNs = "";
                foreach ($list as $_token) {
                    switch ($_token->type) {
                        case T_NS_SEPARATOR:
                        case T_STRING:
                            // 统一成小写
                            // $useNs .= strtolower($_token->value);
                            $useNs .= $_token->value;
                            break;
                        case T_AS:
                            break 2; // 不获取别名
                    }
                }
                $useNsList[] = $useNs;
                break;
        }
    }

    return [$ns, $useNsList];
}



class PHPToken
{
    const T_CHAR = -1;

    public $line;
    public $type;
    public $value;
    public $name;

    public function __construct(...$args)
    {
        if (count($args) === 1) {
            $this->value = $args[0];
            $this->type = -1;
            $this->name = "char";
        } else {
            list($this->type, $this->value, $this->line) = $args;
            $this->name = token_name($this->type);
        }
    }

    public function __toString()
    {
        if ($this->type) {
            return "[$this->name $this->value]";

        } else {
            return "[char $this->value]";
        }
    }
}


class PHPParser
{
    private $tokens;
    private $i;
    private $c;

    public function __construct($codes)
    {
        $this->tokens = token_get_all($codes);
        $this->i = 0;
        $this->c = count($this->tokens);
    }

    public function nextToken()
    {
        if (++$this->i <= $this->c) {
            return new PHPToken(...(array)($this->tokens[$this->i - 1]));
        } else {
            return null;
        }
    }
}
