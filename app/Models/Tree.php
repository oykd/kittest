<?php

namespace Models;

use \Exceptions\TreeException as Fail;
use \DB\MySQL as DB;

/**
 * Class Tree
 * @package Models
 */
class Tree extends BaseModel
{
    /** Таблица */
    const table = 'tree';

    /** Минимальная длина имени */
    const minNameLength = 5;

    /** Максимальная длина имени */
    const maxNameLength = 100;


    /** Если длина имени больше указанного числа, то урезаем ее при загрузке */
    const visibleNameLength = 35;

    /**
     * Загружаем древовидную структуру
     *
     * @throws \Exceptions\DBException | \Exceptions\TreeException
     */
    public static function load()
    {
        // возвращаем узлы дерева
        $leafs = DB::query("SELECT * FROM `" . self::table . "`")->all();

        // обрезаем имена, если они слишком длинные
        for ($i = 0; $i < count($leafs); $i++) {
            if (mb_strlen($leafs[$i]['name']) > static::visibleNameLength) {
                $leafs[$i]['name'] = mb_substr($leafs[$i]['name'], 0, static::visibleNameLength);
                $leafs[$i]['name'] = trim($leafs[$i]['name']) . '...';
            }
        }

        $tree = [
            'id' => null,
            'branches' => [],
        ];

        // добавляем в дерево каждый элемент рекурсивной функцией
        $recursive = [];
        while (!empty($leafs)) {
            $leaf = array_shift($leafs);

            // Если элемент встречается повторно и состояние дерева не изменилось
            // с первой попытки его вставки, то вставить его невозможно
            if (in_array($leaf, $recursive))
                throw Fail::endlessRecursion($leaf['id']);

            // если добавить элемент не удалось, переместим его в конец массива
            if (!self::addToTree($tree, $leaf)) {
                $leafs[] = $leaf;
                // добавим элемент в группу, которая может вызвать бесконечный цикл
                $recursive[] = $leaf;
            } else {
                // состояние дерева изменилось
                $recursive = [];
            }
        }

        return $tree;
    }

    /**
     * Добавляем элемент к дереву, если возможно
     * recursive
     *
     * @param array $tree
     * @param array $leaf
     * @return bool
     */
    protected static function addToTree(&$tree, $leaf)
    {
        if ($tree['id'] == $leaf['parent_id']) {
            $tree['branches'][] = [
                'id' => $leaf['id'],
                'name' => $leaf['name'],
                'content' => $leaf['content'],
                'branches' => [],
            ];
            return true;
        }
        foreach ($tree['branches'] as &$branch) {
            if (static::addToTree($branch, $leaf)) return true;
        }
        return false;
    }

    /**
     * Сохраняем отдельный элемент дерева
     *
     * @param array $data
     * @return int|null
     * @throws \Exceptions\TreeException | \Exceptions\DBException
     */
    public static function save($data)
    {
        $required = isset($data['id']) ? ['id', 'name', 'content'] : ['parent_id', 'name', 'content'];

        // Переданы некорректные параметры
        if (array_diff(array_keys($data), $required))
            throw Fail::incorrectParameters();

        $data['name'] = trim($data['name']);

        // проверяем на минимальную длину
        if (mb_strlen($data['name']) < self::minNameLength)
            throw Fail::nameToShort(self::minNameLength);

        // проверяем на максимальную длину
        if (mb_strlen($data['name']) > self::maxNameLength)
            throw Fail::nameToLong(self::maxNameLength);

        // ищем родительский элемент
        if (isset($data['parent_id']) && $data['parent_id'] != 0) {
            if (!DB::query("SELECT * FROM `" . self::table . "` WHERE `id` = ? ", $data['parent_id'])->first())
                throw Fail::parentIdNotFound($data['parent_id']);
        }

        // если нам передали элемент с родителем id 0 его следует сделать null
        if (isset($data['parent_id']) && $data['parent_id'] == 0) $data['parent_id'] = null;

        // обновляем или добавляем элемент
        if (isset($data['id'])) {
            DB::query(
                "UPDATE `" . self::table . "` SET `name` = ?, `content` = ? WHERE `id` = ? ",
                [$data['name'], $data['content'], $data['id']]
            );
            return DB::get()->affected() > 0 ? $data['id'] : null;
        } else {
            DB::query(
                "INSERT INTO `" . self::table . "` (`parent_id`, `name`, `content`) VALUES (?, ?, ?)",
                [$data['parent_id'], $data['name'], $data['content']]
            );
            return DB::get()->lastInsertId();
        }
    }

    /**
     * Удаляем элемент дерева
     *
     * @param array $data
     * @return bool
     * @throws \Exceptions\DBException | \Exceptions\TreeException
     */
    public static function delete($data)
    {
        if (!isset($data['id']))
            throw Fail::incorrectParameters();

        DB::query("DELETE FROM `" . self::table . "` WHERE `id` = ? ", $data['id']);

        return DB::get()->affected() > 0;
    }

    /**
     * Получаем элемент по его id
     *
     * @param array $data
     * @return array
     * @throws \Exceptions\DBException | \Exceptions\TreeException
     */
    public static function getLeaf($data)
    {
        if (!isset($data['id']))
            throw Fail::incorrectParameters();

        // ищем элемент дерева
        $leaf = DB::query("SELECT * FROM `" . self::table . "` WHERE `id` = ? ", $data['id'])->first();
        if (!$leaf)
            throw Fail::idNotFound($data['id']);

        // обрезаем имя, если оно слишком длинное
        $leaf['visible_name'] = $leaf['name'];
        if (mb_strlen($leaf['visible_name']) > static::visibleNameLength) {
            $leaf['visible_name'] = mb_substr($leaf['visible_name'], 0, static::visibleNameLength);
            $leaf['visible_name'] = trim($leaf['visible_name']) . '...';
        }

        return $leaf;
    }

    /**
     * Меняем родителя для элемента
     *
     * @param array $data
     * @throws \Exceptions\DBException | \Exceptions\TreeException
     */
    public static function parent($data)
    {
        if (!isset($data['id'], $data['parent_id']) || $data['id'] == $data['parent_id'])
            throw Fail::incorrectParameters();

        // получаем все элементы дерева
        $leafs = DB::query("SELECT * FROM `tree`")->all();

        // получаем список всех id
        $idList = array_map(function ($item) {
            return $item['id'];
        }, $leafs);

        // проверяем наличие элемента
        if (!in_array($data['id'], $idList))
            throw Fail::idNotFound($data['id']);

        // проверяем наличие нового родителя
        if (!in_array($data['id'], $idList) && $data['parent_id'] != 0)
            throw Fail::parentIdNotFound($data['parent_id']);

        // проверяем не совпадают ли текущий и новый родители
        foreach ($leafs as $leaf) {
            if ($leaf['id'] == $data['id'] && $leaf['parent_id'] == $data['parent_id'])
                throw Fail::sameParent();
        }

        // проверяем не является ли новый родитель потомком перемещаемого элемента
        $descendants = [];
        self::getDescendants($data['id'], $leafs, $descendants);
        if (in_array($data['parent_id'], $descendants))
            throw Fail::incorrectParent($data['id'], $data['parent_id']);

        // сохраняем нового родителя
        if ($data['parent_id'] == 0) $data['parent_id'] = null;
        DB::query(
            "UPDATE `" . self::table . "` SET `parent_id` = ? WHERE `id` = ?",
            [$data['parent_id'], $data['id']]
        );
    }

    /**
     * Получаем id всех потомков для элемента
     *
     * @param int $parent
     * @param array $leafs
     * @param $descendants
     */
    public static function getDescendants($parent, $leafs, &$descendants)
    {
        foreach ($leafs as $leaf) {
            if (in_array($leaf['id'], $descendants)) continue;
            if ($leaf['parent_id'] == $parent) {
                $descendants[] = $leaf['id'];
                static::getDescendants($leaf['id'], $leafs, $descendants);
            }
        }
    }
}