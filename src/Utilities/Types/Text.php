<?php
/**
 * Created by PhpStorm.
 * User: suqian
 * Date: 16/3/22
 * Time: 下午2:48
 */

namespace Zan\Framework\Utilities\Types;


class Text
{

    /**
     * Generates a random string of a given type and length. Possible
     * values for the first argument ($type) are:
     *
     *  - alnum    - alpha-numeric characters (including capitals)
     *  - alpha    - alphabetical characters (including capitals)
     *  - hexdec   - hexadecimal characters, 0-9 plus a-f
     *  - numeric  - digit characters, 0-9
     *  - nozero   - digit characters, 1-9
     *  - distinct - clearly distinct alpha-numeric characters.
     *
     * For values that do not match any of the above, the characters passed
     * in will be used.
     *
     * ##### Example
     *
     *     echo Text::random('alpha', 20);
     *
     *     // Output:
     *     DdyQFCddSKeTkfjCewPa
     *
     *     echo Text::random('distinct', 20);
     *
     *     // Output:
     *     XCDDVXV7FUSYAVXFFKSL
     *
     * @param   string   $type     A type of pool, or a string of characters to use as the pool
     * @param   integer  $length   Length of string to return
     * @return  string
     */
    public static function random($type = 'alnum', $length = 8)
    {
        $utf8 = FALSE;

        switch ($type)
        {
            case 'alnum':
                $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            case 'alpha':
                $pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            case 'lowalnum':
                $pool = '0123456789abcdefghijklmnopqrstuvwxyz';
                break;
            case 'hexdec':
                $pool = '0123456789abcdef';
                break;
            case 'numeric':
                $pool = '0123456789';
                break;
            case 'nozero':
                $pool = '123456789';
                break;
            case 'distinct':
                $pool = '2345679ACDEFHJKLMNPRSTUVWXYZ';
                break;
            default:
                $pool = (string) $type;
                $utf8 = ! Text::isAscii($pool);
                break;
        }

        // Split the pool into an array of characters
        $pool = ($utf8 === TRUE) ? UTF8::strSplit($pool, 1) : str_split($pool, 1);

        // Largest pool key
        $max = count($pool) - 1;

        $str = '';
        for ($i = 0; $i < $length; $i++)
        {
            // Select a random character from the pool and add it to the string
            $str .= $pool[mt_rand(0, $max)];
        }

        // Make sure alnum strings contain at least one letter and one digit
        if ($type === 'alnum' AND $length > 1)
        {
            if (ctype_alpha($str))
            {
                // Add a random digit
                $str[mt_rand(0, $length - 1)] = chr(mt_rand(48, 57));
            }
            elseif (ctype_digit($str))
            {
                // Add a random letter
                $str[mt_rand(0, $length - 1)] = chr(mt_rand(65, 90));
            }
        }

        return $str;
    }

    public static function isAscii($str)
    {
        return is_string($str) AND ! preg_match('/[^\x00-\x7F]/S', $str);
    }

}