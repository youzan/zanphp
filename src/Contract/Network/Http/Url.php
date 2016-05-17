<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/4/9
 * Time: 11:06
 */

namespace Zan\Framework\Contract\Network\Http;


interface Url
{
    public function setDomain($domain);
    public function getDomain();
    public function setPath($path);
    public function getPath();
}