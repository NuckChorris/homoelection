# HomoElection.php: *Elections for Contra*
## Installation on Linux & Mac
Navigate to Contra's extensions directory (`plugins/extensions`) and run the following:

```
$ curl -L https://github.com/NuckChorris/homoelection/archive/master.tar.gz | tar -xv homoelection-master/homoelection.php
```
	
## Commands
### !election create [name] [length] [-stahp]
Creates an election with the given `name` and `length`, and starts it automatically, unless `-stahp` is set.

`name` is case-insensitive (though case will be used for display) and may contain any non-word-breaking characters --- that means no spaces.  This may be changed in future to allow for quoted strings, but in the meantime there is no such capability.

`length` is a [relative time](http://php.net/manual/en/datetime.formats.relative.php) passed directly to PHP's strtotime.

`-stahp` prevents the election from being started automatically.  This can be leveraged to create a pre-nomination period or allow further setup before the election begins.

### !election delete [name]
Deletes an election.  **THIS CANNOT BE UNDONE.** Use it very carefully, and don't bitch at me if you delete something you didn't mean to.

### !election stop [name]
Stops an election.  The election may be restarted with the below `!election start` command.  While an election is stopped, people cannot vote, but all previous votes are retained, and the results can be viewed.  The ending time for the election will also be delayed by the amount of time the election is stopped.

### !election start [name] [-lock]
Starts an election.  Generally you will not need this command, as elections are automatically started when you use `!election create` (unless you used `-stahp`), except when you previously used `!election stop` on the election.

`-lock` automatically locks the election --- this is to make pre-nomination periods more convenient, and is just a shortcut for `!election lock [name]`.

### !election lock [name]
Locks in nominations for an election, so people cannot nominate more candidates.

### !election unlock [name]
Removes the nomination lock on an election, allowing additional nominees.

### !election reset [name]
Resets an election's votes and timers to their original values.  If something goes horribly wrong in an election, this is a good way to redo everything.

### !elections [-all]
Lists all active (`start`'d) elections, unless `-all` is set, in which case all elections are listed.

### !nominate [election] [thing]
### !nominate [thing] for [election]
Nominates `thing` as a candidate for `election`.  If `election` does not exist, murders the user.  `thing` defaults to the current user (so the same as `!volunteer [election]`).  Either format gets the job done (unless you are trying to nominate `for` in an election, in which case you have to use the latter format)

### !dropout [election] [thing]
Removes `thing` from the running for `election` — any votes cast for this user are removed, and those who voted for them are allowed to re-cast their votes for another user.

### !volunteer [election]
Shortcut to volunteer yourself in an election!  Same as `!nominate [election]`

### !nominees [election]
Gives the results of an election if it's `stop`'d or just lists the nominees if it's not.

### !omnomnominate [user]
Nibbles on the corner of `user`.

### !vote [election] [thing]
### !vote [thing] for [election]
Submits a vote for a nominee in an election.