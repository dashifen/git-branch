<?php

namespace Dashifen\Composer\Bumper\Git;

use Dashifen\Repository\Repository;

/**
 * Branch
 *
 * An object that encapsulates the information encoded into my branch naming
 * scheme.
 *
 * @property-read int $date
 * @property-read string $type
 * @property-read string $feature
 * @property-read string $name
 */
class Branch extends Repository implements BranchInterface
{
  public const BRANCH_PATTERN =
    '/^'                    // starting from the front of the string
    . '(?<date>\d{6})'      // match a six digit string as a date (e.g. 220622 for June 22, 2022)
    . '(?<type>[rbh])-'     // either f or b as the type of branch (feature or bugfix) followed by a hyphen
    . '(?<feature>[-\w]+)' // and the name of a feature which could include more dashes
    . '$/';
    
  protected int $date;
  protected string $type;
  protected string $feature;
  protected string $name;
  
  
}
