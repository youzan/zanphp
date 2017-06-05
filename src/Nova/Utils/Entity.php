<?php

namespace Kdt\Iron\Nova\Utils;


use Kdt\Iron\Nova\Foundation\Protocol\TStruct;

class Entity
{
    /**
     * 实体过滤null值,键值改为蛇形
     * @param $entity
     * @return array
     */
    public static function change($entity)
    {
        $data = self::entityToArray($entity);
        $data = self::filterNull($data);
        $data = self::mate($data, self::camelToSnake(array_keys($data)));
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $k => $v) {
                    $data[$key][$k] = self::change($v);
                }
            }
            if ($value instanceof TStruct) {
                $data[$key] = self::change($value);
            }
        }
        return $data;
    }

    /**
     * 实体转数组
     * @param $entity
     * @param bool $isCarmen
     * @return mixed
     */
    public static function entityToArray($entity, $isCarmen = false)
    {
        if ($entity instanceof TStruct) {
            $entity = $entity->toArray();

            if ($isCarmen) {
                //卡门接口的特殊处理，因thrift的关键字问题，__A变量会变为A
                foreach ($entity as $key => $value) {
                    if (strpos($key, '__') === 0) {
                        $newKey = substr($key, 2);
                        $entity[$newKey] = $value;
                        unset($entity[$key]);
                    }
                }
            }
        }
        return $entity;
    }

    /**
     * 过滤默认值Null
     * @param $data
     * @return array
     */
    public static function filterNull($data)
    {
        return Arr::where($data, function ($key, $value) {
            return !is_null($value);
        });
    }

    /**
     * 根据键值对嫁接数组
     * @param array $data
     * @param       $map
     * @return array
     */
    public static function mate(array $data, $map)
    {
        foreach ($data as $key => $value) {
            if (isset($map[$key])) {
                unset($data[$key]);
                $data[$map[$key]] = $value;
            }
        }

        return $data;
    }

    /**
     * 数组值驼峰转蛇形
     * @param $keys
     * @return array|string
     */
    public static function camelToSnake($keys)
    {
        if (is_array($keys)) {
            $data = array_flip($keys);
            array_walk($data, function (& $value, $key) {
                $value = Str::snake($key);
            });

            return $data;
        }

        return Str::snake($keys);
    }

    /**
     * 瘦身,实体转数组
     * @param $entity
     * @param bool $isCarmen
     * @return array
     */
    public static function toArray($entity, $isCarmen = false)
    {
        $data = self::entityToArray($entity, $isCarmen);
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $k => $v) {
                        $data[$key][$k] = self::toArray($v, $isCarmen);
                    }
                }
                if ($value instanceof TStruct) {
                    $data[$key] = self::toArray($value, $isCarmen);
                }
            }
        }

        return $data;
    }

    /**
     * 数组值蛇形转驼峰
     * @param $keys
     * @return array|string
     */
    public static function snakeToCamel($keys)
    {
        if (is_array($keys)) {
            $data = array_flip($keys);
            array_walk($data, function (& $value, $key) {
                $value = Str::camel($key);
            });

            return $data;
        }

        return Str::camel($keys);
    }

    /**
     * 数组转成实体
     * @param array $data
     * @param       $entity
     * @return mixed
     */
    public static function arrayToEntity(array $data, $entity)
    {
        foreach ($data as $attributes => $value) {
            if (property_exists($entity, $attributes)) {
                $entity->$attributes = $value;
            }
        }

        return $entity;
    }
}