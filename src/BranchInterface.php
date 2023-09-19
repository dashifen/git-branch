<?php

namespace Dashifen\Git;

interface BranchInterface
{
  /**
   * isRelease
   *
   * Returns true if this is a release branch (i.e. if the type is "r").
   *
   * @return bool
   */
  public function isRelease(): bool;
  
  /**
   * isFeature
   *
   * Returns true if this is a feature branch (i.e. if the type is "f").
   *
   * @return bool
   */
  public function isFeature(): bool;
  
  /**
   * isBugFix
   *
   * Returns true if this is a bug fix branch (i.e. if the type is "b").
   *
   * @return bool
   */
  public function isBugFix(): bool;
  
  /**
   * isTypeUnknown
   *
   * Returns true if the type is "?" (i.e. it's not one of the known types).
   *
   * @return bool
   */
  public function isTypeUnknown(): bool;
  
  /**
   * isParent
   *
   * Returns true if this branch is the "parent" of another one (i.e. other
   * branches have been made with it as a starting point).
   *
   * @return bool
   */
  public function isParent(): bool;
  
  /**
   * isChild
   *
   * Returns true if this branch was made from any branch other than main.
   *
   * @return bool
   */
  public function isChild(): bool;
}
