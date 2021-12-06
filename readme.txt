=== Project Panorama File Upload ===
Contributors: Ross Johnson
Tags: project, management, project management, basecamp, status, client, admin, intranet
Requires at least: 4.7.0
Tested up to: 5.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Allow users to upload files to your Panorama projects via the front end of your website.

== Description ==

This plugin allows users to add files to your projects from individual project pages. Users can upload files or specifiy web addresses for Google docs, sheets, etc...

For security reasons the add file button will ONLY be displayed if you're using Panorama access management.

= Website =
http://www.projectpanorama.com

= Documentation =
http://www.projectpanorama.com/docs

= Bug Submission and Forum Support =
http://www.projectpanorama.com/forums
http://www.projectpanorama.com/support


== Installation ==

1. Upload 'panorama-file-upload' to the '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Make sure you have Project Panorama Professional installed and activated
4. Visit a project that is restricting access to specific users and you'll see an "Add File" button in the documents section

== Troubleshooting ==

= Q. I can't seem to upload certain files =
A. The ability to upload large files (2 megs plus) is largely dependent on your hosting configuration. If you're having trouble uploading large files you will need to contact your host to resolve.

You can read more here http://www.wpbeginner.com/wp-tutorials/how-to-increase-the-maximum-file-upload-size-in-wordpress/


= Q. I'd like to restrict file uploads to certain file types =
A. This is not supported at the moment

= Q. I don't see the add file button? =
A. Make sure that the project is restricting access to specific users. Then make sure you have the most recent version of project panorama.

= 2.0.9 =
* Supports rich text for upload messages

= 2.0.8 =
* Supports notify all users by default option

= 2.0.7 =
* Additional fixes per 2.0.6 issue

= 2.0.6 =
* Fixes issue with having the wrong upload field selected by default

= 2.0.5 =
* Updates to support tasks in new task modal
* UI improvements

= 2.0.4 =
* Checks to make sure leanModal is a function before init

= 2.0.3 =
* Fixes isset notices on non-pages (like login, 404, etc...)

= 2.0 =
* Updates for Panorama 2.0

= 1.6.3.6 =
* Changes what element the field switch binds to for theme compatibility

= 1.6.3.5 =
* Updates POT file

= 1.6.3.4 =
* Allows non-valid URLs in web address field for local stored files

= 1.6.3.1 =
* Back ported for older versions of Panorama

= 1.6.3 =
* Better support for custom template usage

= 1.6.2 =
* Adds support for private phases and notifications

= 1.6.1 =
* Adds support for tasks

= 1.5.7 =
* Added filter to new document default status
* Adds automated notification for files uploaded

= 1.5.5 =
* Fixed bug where form submitted even with validation errors

= 1.5.4 =
* Added loading indicator on upload

= 1.5.2 =
* BUG: Fixes issue where dialog doesn't disappear after uploading
* BUG: Fixes issue trying to upload a document to a project with no documents

= 1.5.1 =
* ENHANCEMENT: Supports Panorama 1.5.7 and phase documents

= 1.5 =
* COMPATIBILITY: Necessary for Panorama 1.5
* ENHANCEMENT: Nicer experience when selecting users to notify
* ENHANCEMENT: Usernames are now clickable to select to notify
* ENHANCEMENT: Added permission psp_upload_documents

= 1.4.4 =
* Uploaded documents appear at the top of the list instead of the bottom

= 1.4.3 =
* Update for new modal compatibility

= 1.4.2.1 =
* Misc bug fixes

= 1.4.2 =
* Fixed issues with outgoing e-mails

= 1.4.1 =
* Bug fixes
* Changed update API
* Uses new Project Panorama notification API

= 1.3.1 =
* Added ability to notify users on upload

= 1.3 =
* Improved styling
* Compatibility patch for Panorama 1.3.5

= 1.0 =
* Initial Release!
