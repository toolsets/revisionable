<?php
/**
 * Repository.php
 * @package    revisionable
 * @author     raf <raftalks@gmail.com>
 * @copyright  Copyright (c) 2016, raf
 */

namespace Raftalks\Revisionable;


use Illuminate\Database\Eloquent\Model;

class Repository
{

    /**
     * @var Revision
     */
    protected $model;


    /**
     * Repository constructor.
     * @param Revision $revision
     */
    public function __construct(Revision $revision)
    {
        $this->model = $revision;
    }


    public static function newInstance($attributes = [])
    {
        return new static(new Revision($attributes));
    }

    
    public static function getLastRevisionOf(Model $model)
    {
        if(static::isRevisionableModel($model))
        {
            return $model->revisions()->orderBy('id', 'desc')->first();
        }

        return null;
    }


    final function saveRevisionTo(Model $model)
    {
        if(static::isRevisionableModel($model))
        {
            return $model->revisions()->save($this->model);
        }

        return null;
    }


    final static public function isRevisionableModel(Model $model)
    {
        $traits = class_uses($model);

        if(is_array($traits))
        {
            $flipped = array_flip($traits);

            $lookupTrait = __NAMESPACE__ . '\RevisionableTrait';
            if(isset($flipped[$lookupTrait]))
            {
                return true;
            }
        }

        return false;
    }
}