# Git Branch
An object that encapsulates information embedded in my branch naming scheme.

Based on the success of a similar scheme that I use professionally, for newer 
personal projects, I've decided to be more precise in the way that I name Git
branches.  Instead of naming them whatever I want when I create them, I'll be
prefixing them with pertinent information as in `220622f-new-feature` or
`220622b-bugfix`.  

The first six digits are the date in YYMMDD format followed by either a 
lower-case "F" or "B" for a new feature or a bugfix branch type.  Then, after a
hyphen, a description of the purpose of the branch ends its name.  

Encoding these data into the branch name helps with semantic versioning.  At
a glance, I can see if I want to bump a minor version number or if this is a 
patch.  And, the date helps me to know when I started a branch which may help
to know how to merge things.

