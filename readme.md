# Git Branch
An object that encapsulates information embedded in my branch naming scheme.

Based~~ on the success of a similar scheme that I use professionally, for newer 
personal projects, I've decided to be more precise in the way that I name Git
branches.  Instead of naming them whatever I want when I create them, I'll be
prefixing them with pertinent information as in `220622f-new-feature` or
`220622b-bugfix`.    

The first six digits are the date in YYMMDD format followed by either one of
the following:  r, f, or b.  These correspond to a release, feature, or bugfix
branch respectively.  Following those data, a short description (what might 
have been the entire branch name in the past) is included as well.  

Encoding these data into the branch name helps with semantic versioning.  At a 
glance, I can see how to alter the version number based on the type of the 
branch.  And, the date helps me to know when I started a branch which may help
to know how to merge things.

## Branched Branches

If I need to branch a "child" branch off a "parent," then the child's name
follows the parents preceded by two hyphens.  For example:  
`220622f-parent--child`.  This isn't strictly necessary, a child branch could
simply be named as above, but the order of merges might become a bit more 
messy without a record of what was branched off of what.  
