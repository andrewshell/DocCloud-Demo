DocCloud Demo
=============

This is a demo application to function as a proof of concept.

Dave Winer discusses in his post [A simple proposal for discussion software makers](http://scripting.com/stories/2012/05/26/simpleProposalToDiscussion.html) an idea where instead of having to only publish content inside of the application (Ex: WordPress, Tumblr, Quora), you could specify a URL for your content and the application would fetch that content and through a process it stays updated with pings from the document source.

When I read this I thought.  Isn't that basically what RSS Cloud already does?  The only issue was that the discovery of the RSS Cloud server is currently limited to a `<cloud>` element in an RSS or Atom feed.

What I propose is the addition to the specification that allows for an X-RSS-Cloud HTTP header that specifies the RSS Cloud server.  Since we're limited by a single URL, Clouds discovered by this method are assumed to work via REST as opposed to XML-RPC or SOAP.

Everything else functions exactly as it would in the [RSS Cloud Specification](http://rsscloud.org/walkthrough.html).  Since RSS Cloud doesn't parse the "feed" but rather just checks to see if it has changed, this HTTP header method can be used to track updates for any kind of document accessable from on the internet including binary files like images.

Installation
------------

1. Download or clone repository into your document root.
2. Set cache folder to be writable by web server and cron user (Ex: `chmod 777 cache`).
3. Edit `functions.php` file with your server information.  Most likely you'll only need to edit `$users` and the constants `MY_DOMAIN` and possibly `MY_PATH` if you're running in a subdirectory.
4. Set up cron to run `cron.php` at least once every 24 hours.  This is to maintain the subscription to the various URLs.
5. Visit your site and login with credentials specified in the `$users` variable in `functions.php`.

Usage
-----

Like I said earlier, this is a proof of concept, not a useful web application.  With that in mind this is how to test the application:

1. Type in a filename like *test.txt* in "New File Filename" text field.
2. Type in some content into the "File Contents" textarea field.
3. Click the "Submit" button.

You should now see a link at the top of the page for `test.txt`. If you click on that link you'll be taken to a page where you can see the contents of the file.  This URL (Ex: `/index.php?filename=test.txt`) is what you would paste into the other application.  This page serves up the X-RSS-Cloud HTTP header.

You can actually test the sync functionality on the same application.  Just copy the full URL from your address bar and hit the back button.  Once you're back on the application homepage you can test as follows:

1. Paste the document URL into the "Subscribe to URL" text field.
2. Press the "Submit" button

What you should see is a new document in your list like 

    http-web03.andrewshell.org-index.php-filename-test.txt

If you click on it you'll see the same contents as your original `test.txt` file.  Next let's test an update by:

1. Selecting your original `test.txt` file in the "Existing File" dropdown field.
2. Typing in some new text into the "File Contents" textarea field.
3. Click the "Submit" button.

Now if you click on the longer synchronised filename it should be updated with your new content.  This was accomplished by the full ping process.  You can also try this with several sites set up across many servers and it would work the same way.