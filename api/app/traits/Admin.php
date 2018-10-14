<?php

namespace FrcPortal;

/**
 * Class HasAccountTrait
 *
 * @package App
 */
trait AdminStuff
{
    /**
     * @param Account $account
     * @return $this
     */
    public function isAdmin()
    {
      return $this->status && $this->admin;
    }
}
