<?php

namespace App;

trait GuardedTrait
{
    public function guarded()
    {
        return ['id'];
    }
}
