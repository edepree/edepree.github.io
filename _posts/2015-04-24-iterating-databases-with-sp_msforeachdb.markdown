---
layout: post
title:  "Iterating MSSQL Databases With sp_MSforeachdb"
---

One task I have been working on automating is reviewing Microsoft SQL databases and the data that resides in them. Using a built-in Microsoft stored procedure and Microsoft utilities I implemented a process to execute SQL command(s) against multiple MSSQL databases and instances using native methods. 

Grossly simplifying looking for data can be broken into the following steps:

1. Launch Microsoft SQL Server Management Studio (SSMS).
2. Click through items in the GUI.
3. Find data.

This is an easy process that works well enough when looking at one or two databases over an entire assessment. When the number of databases expands, coupled with multiple users with possibly different levels of access, this becomes a much more time consuming task. My first attempt to solve this problem involved a simple SQL script I could run when using SSMS. The script prints the names of all the columns in a database.

{% highlight sql %}
SELECT t.name AS [Table], SCHEMA_NAME(schema_id) AS [Schema], c.name AS [Column]
FROM sys.tables AS t
INNER JOIN sys.columns c ON t.OBJECT_ID = c.OBJECT_ID
ORDER BY [Schema], [Table];
{% endhighlight %}

While the script performed its function it suffered from multiple flaws.

1. It had to be executed on each database individually.
2. It required manually connecting to each database through SSMS.

To solve the first issue I discovered a stored procedure built into MSSQL called *sp_MSforeachdb*. This stored procedure allows for a SQL query to be executed against each database in a MSSQL instance. Most importantly for me, the stored procedure allowed me to avoid creating any temporary tables while collecting data for multiple databases. One part of my plan was to run a script that would pull the data I needed without causing any writes or involved any cleanup on the database. Armed with the new stored procedure my script was enhanced.

{% highlight sql %}
exec sp_MSforeachdb
    @command1 = 'USE [?];
                 SELECT DB_NAME() AS [Database], t.name AS [Table], SCHEMA_NAME(schema_id) AS [Schema], c.name AS [Column]
                 FROM sys.tables AS t
                 INNER JOIN sys.columns c ON t.OBJECT_ID = c.OBJECT_ID
                 ORDER BY [Database], [Schema], [Table];'
{% endhighlight %}

The enhanced script executes against all databases on a MSSQL instance. It utilizes a parameter to pass a SQL query to the database. There are additional parameters for the sp_MSforeachdb which also can be used. In my searching I did not find official Microsoft documentation for the stored procedure, but I did find enough information from other individuals to get it working for me. Armed with the new script I was able to couple it with [sqlcmd][1] to execute it against multiple MSSQL instances.

Using a built-in Microsoft stored procedure that appears to be for internal use only has helped me execute custom SQL payloads against multiple MSSQL databases and instances in an environment. It is a nifty stored procedure to keep in one's pocket to help with tasks that involve repetitive actions on MSSQL. As with any automation, there is always danger in blasting anything in the environment so be cognizant of the downside of automation.

[1]: https://msdn.microsoft.com/en-us/library/ms162773.aspx
