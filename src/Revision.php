<?php
/**
 * Revision.php
 * @package    revisionable
 * @author     raf <raftalks@gmail.com>
 * @copyright  Copyright (c) 2016, raf
 */

namespace Raftalks\Revisionable;


use Illuminate\Database\Eloquent\Model;

class Revision extends Model
{

    protected $table = 'revisions';

    protected $fillable = [
        'user_id', 'before', 'after'
    ];

    protected $casts = [
        'before' => 'json',
        'after' => 'json'
    ];

    

    public function revisionable()
    {
        return $this->morphTo();
    }


    public function mergeDiff($diff)
    {
        if(! is_null($diff) && is_array($diff))
        {
            $currentBefore = (array) $this->before;
            $currentAfter = (array) $this->after;

            if(!empty($currentBefore))
            {
                $this->before = array_merge($currentBefore, $diff['before']);
            }
            else
            {
                $this->before = $diff['before'];
            }

            if(!empty($currentAfter))
            {
                $this->after = array_merge($currentAfter, $diff['after']);
            }
            else
            {
                $this->after =  $diff['after'];
            }

            $this->save();
        }

    }
}