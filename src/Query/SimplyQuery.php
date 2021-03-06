<?php

namespace Simply\Core\Query;

use Simply\Core\Contract\ModelInterface;
use Simply\Core\Model\ModelFactory;

class SimplyQuery {
    /**
     * @var ModelInterface
     */
    private static $currentObject = null;
    public $query;

    public function __construct(\WP_Query $query) {
        $this->query = $query;
    }

    /**
     * Get the model associated with the WordPress queried object
     * @return ModelInterface|false
     * @throws \Exception
     */
    public static function getCurrentObject() {
        if (!is_null(self::$currentObject)) {
            return self::$currentObject;
        }

        $currentObject = get_queried_object();
        try {
            $model = ModelFactory::create($currentObject);
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return false;
        }
        self::$currentObject = $model;
        return $model;
    }

    /**
     * @return SimplyQuery
     */
    public static function getCurrentQuery() {
        global $wp_query;
        return new self($wp_query);
    }

    /**
     * @return array|false
     * @throws \Exception
     */
    public function getQueriedPosts() {
        $allPosts = $this->query->posts;
        if (sizeof($allPosts) > 0) {
            $returnPosts = [];
            foreach ($allPosts as $aPost) {
                $returnPosts[] = ModelFactory::create($aPost);
            }
            return $returnPosts;
        }
        return false;
    }
}
