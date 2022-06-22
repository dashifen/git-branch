<?php

namespace Dashifen\Git;

use Dashifen\Exception\Exception;

class BranchException extends Exception
{
  public const UNKNOWN_ROOT = 1;
  public const INVALID_BRANCH = 2;
}
