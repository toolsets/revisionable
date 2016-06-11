<?php

namespace Raftalks\Revisionable;

use Illuminate\Database\Eloquent\Model;

trait RevisionableTrait
{

    /**
     * Temporarily store the last revision of the model
     * @var null|Model
     */
    protected $_last_revision = null;

    /**
     * An array of fields that may not be tracked for changes on any model
     * @var array
     */
    protected $_ignored_revisions = [];


    /**
     * Register callback when model is updating and then it calls saveRevision method to store the revision
     */
    public static function bootRevisionableTrait()
    {

        static::updating(function(RevisionableModel $model)
        {
            $model->saveRevision();
        });

    }



    final public function getLastRevisionId()
    {
        if(! is_null($this->_last_revision))
        {
            return $this->_last_revision->id;
        }
        else
        {
            $last = $this->getLastRevision();

            if(!empty($last))
            {
                return $last->id;
            }
        }

        return null;
    }


    public function getLastRevision()
    {
        if(! is_null($this->_last_revision))
        {
            return $this->_last_revision;
        }
        else
        {
            $last = Repository::getLastRevisionOf($this);

            if(!empty($last))
            {
                $this->_last_revision = $last;
                return $last;
            }
        }

        return null;
    }



    /**
     * Save revision of the model
     * @param null $diff
     */
    final public function saveRevision($diff = null)
    {

        $diff = $diff ?: $this->getDiff();

        if(! is_null($diff))
        {

            $repo = Repository::newInstance($diff);
            /**
             * @param $repo Repository
             */
            $this->_last_revision = $repo->saveRevisionTo($this);
        }
    }


    /**
     * Get the diff of the model
     * @return null|Array
     */
    public function getDiff()
    {
        $changed = $this->getChanged();
        $original = $this->getBeforeChanged();

        if(!empty($changed))
        {
            $before = array_intersect_key($original, $changed);
            $after = $changed;

            return compact('before', 'after');
        }

        return null;
    }


    public function getChanged()
    {
        return $this->getRevisionableItems($this->getDirty());
    }


    public function getBeforeChanged()
    {

        $freshCopyOfMe = $this->fresh();

        if(is_null($freshCopyOfMe))
        {
            return $this->original;
        }

        return $this->getRevisionableItems($freshCopyOfMe->toArray());
    }


    public function getRevisionableItems($values)
    {
        $model_ignored_revisions =  isset($this->ignored_revisions) ? $this->ignored_revisions : [];
        $ignored_fields = array_merge($this->_ignored_revisions, $model_ignored_revisions);
        return array_diff_key($values, array_flip($ignored_fields));
    }


}
