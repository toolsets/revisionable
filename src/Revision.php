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
    public function __construct(array $attributes = [])
    {
        $this->table = 'revisions';
        
        parent::__construct($attributes);
    }
}