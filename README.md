expensify-api
=============

Unofficial API for expensify.com

Usage
-----

Right now, this is very limited. You can read reports and get a single report. If you need more functionality, feel free to submit a ticket, or better yet, submit a pull request with the new code!

	$expensifyAPI = new ExpensifyAPI('username', 'password', 'absolute cookie location (optional)', 'User agent (optional)');
	
	$expense = $expensifyAPI->getReports(20);
	$expense = $expensifyAPI->getReport(11111111);

Docs
----
Retry param is mainly for internal code hackery. I thought it was a decent solution for performance and efficiency.

Public methods

	getReports($limit = 10, $offset = 0, $sort_by = 'started', $retry = false)
	getReport($report_id, $retry = false)