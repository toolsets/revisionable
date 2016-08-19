<?php

namespace Raftalks\Revisionable;


use Closure;
use Illuminate\Support\Collection;

trait RevisionableTrait
{

    /**
     * An array of fields that may not be tracked for changes on any model
     * @var array
     */
    protected $_ignored_revisions = [];

    /**
     * Last revision id
     * @var integer
     */
    protected $_last_revision = null;


    /**
     * Register the trait for model updating event
     */
    public static function bootRevisionableTrait()
    {

        static::updating(function(RevisionableModel $model)
        {
            $model->saveRevision();
        });

    }

    /**
     * @return Collection
     */
    public function revisions()
    {
        return $this->morphMany(Revision::class, 'revisionable');
    }


    /**
     * Use this method to store revisions for a relation
     * @param $relation
     * @param $values
     */
    final public function syncUpdate($relation, $values)
    {
        $model = $this;

        DB::transaction(function () use($relation, $values, $model) {

            $this->savePrivotRevision($relation, $values, function($relation, $values) use($model)
            {
                $model->$relation()->sync($values);
            });

        });


    }

    /**
     * Returns the last revision id if exists or return null
     * @return integer|null
     */
    final public function getLastRevisionId()
    {
        if(! is_null($this->_last_revision))
        {
            return $this->_last_revision->id;
        }
        else
        {
            $last = $this->revisions()->orderBy('id', 'desc')->first();

            if(!empty($last))
            {
                return $last->id;
            }
        }

        return null;
    }


    /**
     * Return the last revision model if exist or return null
     * @return int|null
     */
    public function getLastRevision()
    {
        if(! is_null($this->_last_revision))
        {
            return $this->_last_revision;
        }
        else
        {
            $last = $this->revisions()->orderBy('id', 'desc')->first();

            if(empty($last))
            {
                $this->_last_revision = $last;
                return $last;
            }
        }

        return null;
    }



    final public function savePrivotRevision($relation, $value, Closure $saveCallback)
    {
        if(is_array($value))
        {
            //relationship belongsToMany
            $diff = $this->diffManyToMany($relation, $value);
            if(!is_null($diff))
            {
                // run the saving callback
                $saveCallback($relation, $value);

                if(! is_null($this->_last_revision))
                {
                    // merge into current revision
                    $this->_last_revision->mergeDiff($diff);
                }
                else
                {
                    // create a new revision
                    $this->saveRevision(null, $diff);
                }
            }
        }

    }


    final public function saveRevision($user_id = null, $diff = null)
    {
        $user_id = $user_id ?: Auth::id();

        $diff = $diff ?: $this->getDiff();

        if(! is_null($diff))
        {
            $revision = new Revision();
            $revision->user_id = $user_id;
            $revision->before = $diff['before'];
            $revision->after = $diff['after'];

            $this->_last_revision = $this->revisions()->save($revision);
        }
    }


    final public function diffManyToMany($relation, Array $values)
    {
        if(method_exists($this, $relation))
        {
            $current = $this->{$relation};
            $before = [];
            $after = [];

            if($current->count())
            {
                $current_ids = $current->pluck('id')->toArray();

                $removed = array_diff($current_ids, $values);
                $added = array_diff($values, $current_ids);
                $changed_count = count($removed) + count($added);

                if($changed_count > 0)
                {

                    $before[$relation] = $current_ids;
                    $after[$relation] = $values;
                }
            }
            else
            {
                $before[$relation] = [];
                $after[$relation] = $values;

            }

            if(! empty($after))
            {

                return [
                    'before' => $before,
                    'after' => $after
                ];

            }

        }

        return null;
    }

    /**
     * @return array
     */
    final public function getDiff()
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


    protected function getChanged()
    {
        return $this->getRevisionableItems($this->getDirty());
    }

    protected function getBeforeChanged()
    {

        $freshCopy = $this->fresh();

        if(is_null($freshCopy))
        {
            return $this->original;
        }

        return $this->getRevisionableItems($freshCopy->toArray());
    }

    protected function getRevisionableItems($values)
    {
        $model_ignored_revisions =  isset($this->ignored_revisions) ? $this->ignored_revisions : [];
        $ignored_fields = array_merge($this->_ignored_revisions, $model_ignored_revisions);
        return array_diff_key($values, array_flip($ignored_fields));
    }


}
