# lowfatcms
 Low Fat CMS

Content Management System based on Fat Free Framework and FatFreeLogin.

To install: 
1. create a database on your server 
2. create the database tables from the /_db/db.sql file 
3. upload all folders and files (except the /_db folder)
4. change the settings in the config.ini file.

A manual will be available on your new site.
You can login with admin/FatFree123

## Overview
The CMS allows to manage: 

    - pages: page content
    - metatags: tags linked to pages
    - chunks: html code that can be used on pages
    - news: news items
    - contact requests: when a contact form gets filled out it will not only be sent to the email set in the config file, but also stored in the database
	- users
	
The site menu gets generated automatically. 
Templates can be changed in the /app/views/ folder. 

## Special pages
Contact, news pages, news overview and sitemap.xml are available out of the box.

## Placeholder functions - Snippets
The following placeholder functions or snippets can be used on all pages.
[[children?id=parent_page_id&list=true]]
to list all child pages of a specific page

[[gallery?"description"=>"path/to/image/jpg","description img 2"=>"path/to/image2/jpg"]]
for a picture gallery (clickable and opens in bootstrap modal)

[[listpics?/assets/images/listpics/]] 
to list all pictures in the specified folder (clickable and opens in bootstrap modal)

[[metatag?id=1]]
list all pages linked to the specified metatag

More information will be shown in the manual when the site is installed.