<?php

namespace Controllers;

use \Models\Tree;

/**
 * Class TreeController
 * @package Controllers
 */
class TreeController extends BaseController
{
    /**
     * /tree
     *
     * @throws \Exceptions\DBException
     */
    public function tree()
    {
        echo json_encode([
            'code' => 0,
            'message' => 'ok',
            'tree' => Tree::load(),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    /**
     * /tree/:id
     *
     * @param int route_$id
     * @param array $data
     * @throws \Exceptions\DBException | \Exceptions\TreeException
     */
    public function leaf($route_id, $data)
    {
        echo json_encode([
            'code' => 0,
            'message' => 'ok',
            'leaf' => Tree::getLeaf($data),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    /**
     * @param int route_$id
     * @param array $data
     * @throws \Exceptions\DBException | \Exceptions\TreeException
     */
    public function save($route_id, $data)
    {
        $id = tree::save($data);
        echo json_encode([
            'code' => 0,
            'message' => 'ok',
            'id' => $id,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    /**
     * @param int $route_id
     * @param array $data
     * @throws \Exceptions\DBException | \Exceptions\TreeException
     */
    public function delete($route_id, $data)
    {
        tree::delete($data);
        echo json_encode([
            'code' => 0,
            'message' => 'ok',
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    /**
     * @param int $route_id
     * @param array $data
     * @throws \Exceptions\DBException | \Exceptions\TreeException
     */
    public function parent($route_id, $data)
    {
        tree::parent($data);
        echo json_encode([
            'code' => 0,
            'message' => 'ok',
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }
}