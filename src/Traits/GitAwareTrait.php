<?php

namespace Dashifen\Git\Traits;

use Dashifen\Git\Branch;
use Dashifen\Git\BranchInterface;
use Dashifen\Git\BranchException;

trait GitAwareTrait
{
  private string $root;
  
  /**
   * getGitBranch
   *
   * Returns a BranchInterface object that encapsulates the information encoded
   * into the current Git branch name.
   *
   * @return BranchInterface|null
   */
  public function getGitBranch(): ?BranchInterface
  {
    if ($this->isGitRepo()) {
      $branch = $this->getGitBranches()[0] ?? null;
  
      if ($branch !== null) {
        $object = $this->getGitBranchObjectName();
        return new $object($branch);
      }
    }
    
    return null;
  }
  
  /**
   * isGitRepo
   *
   * Returns true if we're within a Git repo.
   *
   * @return bool
   */
  public function isGitRepo(): bool
  {
    try {
      
      // the getGitDirectory method (below) will throw an exception when it
      // can't find the .git folder.  if it finishes completely without doing
      // so, then we're within a git repo.
      
      $this->getGitDirectory();
      return true;
    } catch (BranchException $e) {
      return false;
    }
  }
  
  /**
   * getGitDirectory
   *
   * Starting from the current directory, this method moves up the filesystem
   * until it finds the .git folder and then returns that location.  If it
   * can't find that folder, it'll quit after 50 iterations.  If there's 50
   * folders between the root of a repo and your code, refactor.
   *
   * @return void
   * @throws BranchException
   */
  public function getGitDirectory(): string
  {
    if (isset($this->root)) {
      return $this->root;
    }
    
    $limit = 0;
    $directory = __DIR__;
    while (!is_dir($directory . '/.git') && ++$limit < 50) {
      $directory = dirname($directory);
    }
    
    // because the limit is incremented _after_ we check for the .git folder,
    // we know that it's never found if we reached 50.  that's how we know to
    // throw our exception here.
    
    if ($limit === 50) {
      throw new BranchException(
        'Limit reached; Git root not found.',
        BranchException::UNKNOWN_ROOT
      );
    }
    
    return $this->root = $directory;
  }
  
  /**
   * getGitBranches
   *
   * Returns an array of the branches in this git repo with the current branch
   * as the first one in it.
   *
   * @return array
   */
  public function getGitBranches(): array
  {
    exec('git branch', $branches);
    
    // the exec call above will take the output from the git branch command
    // and cram it into $branches as an array.  we'll loop over that array and
    // trim off the leading spaces that that command adds to its output and
    // watch for the asterisk that indicates the current branch.  that branch
    // we move to the front of the array.
    
    $current = '';
    foreach ($branches as $i => &$branch) {
      if (substr($branch, 0, 1) === '*') {
        
        // in here, we've found the current branch.  we record its name in
        // the $current variable after removing the asterisk and following
        // space from its name.  then, we remove it from the $branches array
        // temporarily.
        
        $current = substr($branch, 2);
        unset($branches[$i]);
      } else {
        
        // for all other branches, we just remove the two spaces that the
        // command line adds to the front of branch names to make room for the
        // asterisk we processed above.  because $branch is a reference, the
        // change we make here sticks after the loop.
        
        $branch = trim($branch);
      }
    }
    
    // before we return our list of branches, we put the current branch back
    // at the front of the list with array_unshift.  then, we run it throw
    // both array_filter and array_values to remove blanks and make sure that
    // it's numbered from zero to N with no gaps.
    
    array_unshift($branches, $current);
    return array_values(array_filter($branches));
  }
  
  /**
   * getGitBranchObjectName
   *
   * By default, we assume that this is working with the Branch object within
   * this repo.  But, if not, then this method can get overridden to return the
   * name of a different object that implements BranchInterface, typically when
   * the BRANCH_PATTERN constant needs to change.
   *
   * @return string
   */
  protected function getGitBranchObjectName(): string
  {
    return Branch::class;
  }
}
