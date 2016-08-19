<?php
/**
 * RevisionableModel.php
 * @package    revisionable
 * @author     raf <raftalks@gmail.com>
 * @copyright  Copyright (c) 2016, raf
 */

namespace Raftalks\Revisionable;


interface RevisionableModel
{

    public function revisions();

    public function getDiff();

    public function saveRevision($user_id = null, $diff = null);
}