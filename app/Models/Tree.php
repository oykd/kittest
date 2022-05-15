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
     * @throws \Exceptions\DBException
     */
    public static function load()
    {
        // возвращаем узлы дерева
        $leafs = DB::query("SELECT * FROM `" . self::table . "`")->all();

        // добавляем в дерево каждый элемент рекурсивной функцией
        $tree = [
            'id' => null,
            'branches' => [],
        ];
        foreach ($leafs as $leaf) {
            // обрезаем имя, если оно слишком длинное
            if (mb_strlen($leaf['name']) > static::visibleNameLength) {
                $leaf['name'] = mb_substr($leaf['name'], 0, static::visibleNameLength);
                $leaf['name'] = trim($leaf['name']) . '...';
            }
            static::addToTree($tree, $leaf);
        }

        return $tree;
    }

    /**
     * recursive
     *
     * @param array $tree
     * @param array $leaf
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
            return;
        }
        foreach ($tree['branches'] as &$branch)
            static::addToTree($branch, $leaf);
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
     * @param array $data
     * @return array
     * @throws \Exceptions\DBException | \Exceptions\TreeException
     */
    public static function getLeaf($data)
    {
        if (!isset($data['id']))
            throw Fail::incorrectParameters();

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
}