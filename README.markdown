A VERY SIMPLE WORDPRESS SPAM FILTERING PLUGIN.
=============================================

This queries stopforumspam.com every time a comment is submitted, and if it's
a known spammer, the comment is marked as spam. If it's not a known spammer,
it is not marked as spam. If something doesn't work for some reason, it's
set to pending human review. It's a crazy idea, I know, but I think it's so
crazy it JUST MIGHT WORK.

*PLEASE NOTE*, stopforumspam has a limit of 20,000 calls to its API per day.
If you're getting 20,000 comments a day (god help you), don't use this plugin
or they'll think you're DoSing them.