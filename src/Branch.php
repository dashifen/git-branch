<?php

namespace Dashifen\Git;

use Dashifen\Repository\Repository;
use Dashifen\Git\Traits\GitAwareTrait;
use Dashifen\Repository\RepositoryException;

/**
 * Branch
 *
 * An object that encapsulates the information encoded into my branch naming
 * scheme.
 *
 * @property-read int    $date
 * @property-read string $type
 * @property-read string $description
 * @property-read string $parent
 * @property-read string $name
 */
class Branch extends Repository implements BranchInterface
{
  use GitAwareTrait;
  
  public const BRANCH_PATTERN =
    '/^'                        // starting from the front of the string
    . '(?<date>\d{6})'          // match a six digit string as a date (e.g. 220622 for June 22, 2022)
    . '(?<type>[rfb])-'         // type of the branch:  release, feature, or bugfix (followed by a hyphen)
    . '(?<description>[-\w]+)'  // a description of the purpose for this branch
    . '$/';                     // and nothing else.
  
  protected int $date;
  protected string $type;
  protected string $description;
  protected string $parent;
  protected string $name;
  
  /**
   * Branch constructor.
   *
   * Given a Git branch's name, determines if it's valid and constructs an
   * object that encapsulates the data encoded in that name.
   *
   * @param string $branch
   * @param bool   $throw
   *
   * @throws BranchException
   * @throws RepositoryException
   */
  public function __construct(string $branch, bool $throw = false)
  {
    if (preg_match(static::BRANCH_PATTERN, $branch, $matches)) {
      
      // the $matches array that preg_match builds for us is almost what we
      // need to pass to our parent's constructor, but it still has the numeric
      // indices.  this filter removes those and keeps the named indices we
      // want.
      
      $matches = array_filter($matches, fn($key) => !is_numeric($key), ARRAY_FILTER_USE_KEY);
    } else {
      
      // if things didn't match, if we're throwing exceptions we do that.
      // otherwise, we set properties by hand as much as we can and proceed
      // hoping that code elsewhere will handle the problem as gracefully as it
      // can.
      
      if ($throw) {
        throw new BranchException(
          'Invalid branch: ' . $branch . '.',
          BranchException::INVALID_BRANCH
        );
      }
      
      $matches = [
        'type'        => '?',
        'date'        => date('ymd'),
        'description' => $branch,
      ];
    }
    
    // above, we constructed $matches either from our BRANCH_PATTER constant
    // or by hand in the else-block.  but, it lacks one final index that we add
    // here:  the name.  once that's ready to go, we can pass it all to the
    // Repository constructor.
    
    $matches['name'] = $branch;
    parent::__construct($matches);
  }
  
  /**
   * __toString
   *
   * Turning our parsed Git branch back into a string is easy because we record
   * that information in this object's name property.
   *
   * @return string
   */
  public function __toString(): string
  {
    return $this->name;
  }
  
  /**
   * isRelease
   *
   * Returns true if this is a release branch (i.e. if the type is "r").
   *
   * @return bool
   */
  public function isRelease(): bool
  {
    return $this->type === 'r';
  }
  
  /**
   * isFeature
   *
   * Returns true if this is a feature branch (i.e. if the type is "f").
   *
   * @return bool
   */
  public function isFeature(): bool
  {
    return $this->type === 'f';
  }
  
  /**
   * isBugFix
   *
   * Returns true if this is a bug fix branch (i.e. if the type is "b").
   *
   * @return bool
   */
  public function isBugFix(): bool
  {
    return $this->type === 'b';
  }
  
  /**
   * isTypeUnknown
   *
   * Returns true if the type is "?" (i.e. it's not one of the known types).
   *
   * @return bool
   */
  public function isTypeUnknown(): bool
  {
    return $this->type === '?';
  }
  
  /**
   * isParent
   *
   * Returns true if this branch is the "parent" of another one (i.e. other
   * branches have been made with it as a starting point).
   *
   * @return bool
   */
  public function isParent(): bool
  {
    // if this branches name is the prefix to another branch in this repo,
    // then it's a parent.  to know that, we get the list of branches, filter
    // using our name, and see if there are any left.  first:  if we're not
    // at PHP8 yet, we won't have an str_starts_with method.  we'll create it
    // here to use in that case.
    
    if (!function_exists('str_starts_with')) {
      function str_starts_with(string $haystack, string $needle): bool
      {
        return strpos($haystack, $needle) === 0;
      }
    }
    
    $branches = $this->getGitBranches();
    $branches = array_filter($branches, fn($branch) => str_starts_with($this->name, $branch));
    return sizeof($branches) > 0;
  }
  
  /**
   * isChild
   *
   * Returns true if this branch was made from any branch other than main.
   *
   * @return bool
   */
  public function isChild(): bool
  {
    // a child can be identified when there are are two adjacent hyphens in
    // its name.  for example:  220622f-parent--child.  so, if we find those
    // hyphens, we return true.
    
    return strpos($this->name, '--') !== false;
  }
  
  /**
   * setDate
   *
   * Sets the date property.
   *
   * @param int $date
   *
   * @return void
   * @throws RepositoryException
   */
  public function setDate(int $date): void
  {
    [$year, $month, $day] = str_split($date, 2);
    
    if ($year < 22) {
      
      // i'm writing this in 2022.  i'll be dead by the time 2100 roles around
      // so making sure that we have years that are greater than or equal to
      // 2022 seems like an acceptable risk.  if i somehow cheat death and live
      // to be 121, this will start throwing exceptions, but that seems like an
      // acceptable limitation at the moment.
      
      throw new RepositoryException(
        'Invalid year: ' . $year . '.',
        RepositoryException::INVALID_VALUE
      );
    }
    
    if ($month < 1 || $month > 12) {
      throw new RepositoryException(
        'Invalid month: ' . $month . '.',
        RepositoryException::INVALID_VALUE
      );
    }
    
    $lastDay = date('t', mktime(0, 0, 0, $month, 1, $year));
    if ($day > $lastDay) {
      throw new RepositoryException(
        sprintf('Invalid day during %d/20%d: %d', $month, $year, $day),
        RepositoryException::INVALID_VALUE
      );
    }
    
    $this->date = $date;
  }
  
  /**
   * setType
   *
   * Sets the type property.
   *
   * @param string $type
   *
   * @return void
   * @throws RepositoryException
   */
  public function setType(string $type): void
  {
    if (!in_array($type, ['?', 'r', 'f', 'b'])) {
      
      // while the r, f, and b types correspond to what we want, in situations
      // when the type cannot be determined, we send a question mark here.  so,
      // we throw this exception only if none of those four values are what we
      // have encountered.
      
      throw new RepositoryException(
        'Invalid type: ' . $type . '.',
        RepositoryException::INVALID_VALUE
      );
    }
    
    $this->type = $type;
  }
  
  /**
   * setDescription
   *
   * Sets the description property.
   *
   * @param string $description
   *
   * @return void
   */
  public function setDescription(string $description): void
  {
    $this->description = $description;
  }
  
  /**
   * setName
   *
   * Sets the name and parent properties.
   *
   * @param string $name
   *
   * @return void
   */
  public function setName(string $name): void
  {
    $this->name = $name;
    
    // the parent of this branch is anything that precedes the final set of
    // hyphens.  so in 220622f-parent--child the parent is "220622f-parent"
    // but in 220622f-parent--child--grandchild, the branch's parent will be
    // "220622f-parent--child."
    //
    $this->parent = preg_match('/(?<parent>.+)--\w+$/', $name, $matches)
      ? $matches['parent']
      : '';
  }
}
