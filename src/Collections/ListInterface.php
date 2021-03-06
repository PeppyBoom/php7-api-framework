<?php

namespace Asd\Collections;

use Asd\Collections\CollectionInterface;

interface ListInterface extends CollectionInterface
{
    public function get(int $index);
    public function add($obj) : self;
    public function addAt($obj, int $index) : self;
    public function remove(int $index) : self;
    public function clear() : self;
    public function contains($obj) : bool;
    public function indexOf($obj) : int;
}
