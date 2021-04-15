SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

CREATE TABLE `contactform` (
  `id` int(11) NOT NULL,
  `time_set` timestamp NOT NULL DEFAULT current_timestamp(),
  `name` varchar(256) NOT NULL,
  `email` varchar(256) NOT NULL,
  `subject` varchar(256) NOT NULL,
  `message` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


INSERT INTO `contactform` (`id`, `time_set`, `name`, `email`, `subject`, `message`) VALUES
(1, '2021-04-14 11:55:08', 'sender\'s name', 'sendersmail@mail.org', '', 'I just want to say that I just LOVE your CMS!');

CREATE TABLE `metatags` (
  `id` int(11) NOT NULL,
  `metatag` varchar(256) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_on` timestamp NOT NULL DEFAULT current_timestamp(),
  `edited_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `metatags` (`id`, `metatag`, `active`, `created_on`, `edited_on`) VALUES
(1, 'f3', 1, '2021-04-14 07:33:14', '0000-00-00 00:00:00'),
(2, 'cms', 1, '2020-11-26 16:35:40', '0000-00-00 00:00:00');

CREATE TABLE `metatags_content` (
  `id` int(11) NOT NULL,
  `metatag_id` int(11) NOT NULL,
  `content_id` int(11) NOT NULL,
  `created_on` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `metatags_content` (`id`, `metatag_id`, `content_id`, `created_on`) VALUES
(1, 1, 3, '2021-04-14 10:11:17'),
(2, 1, 2, '2021-04-14 12:29:32');

CREATE TABLE `news` (
  `id` int(5) NOT NULL,
  `spec` char(20) NOT NULL DEFAULT '',
  `title` varchar(256) NOT NULL DEFAULT '',
  `author` mediumtext NOT NULL,
  `intro_text` varchar(256) NOT NULL,
  `full_article` longtext NOT NULL,
  `thumbnail` varchar(256) NOT NULL,
  `published` int(11) NOT NULL DEFAULT 1,
  `link` varchar(256) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_edit` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `news` (`id`, `spec`, `title`, `author`, `intro_text`, `full_article`, `thumbnail`, `published`, `link`, `created_at`, `last_edit`) VALUES
(1, 'news', 'Low Fat CMS Installed', 'Administrator', 'You have succesfully installed Low Fat CMS', 'Well done! If you are not sure how to use this piece of software, check our the demo pages!', '/assets/images/lowfatcms.png', 1, 'manual.html', '2021-04-09 15:41:49', '2021-04-14 18:17:44');

CREATE TABLE `site_content` (
  `id` int(10) NOT NULL,
  `type` varchar(20) NOT NULL DEFAULT 'document',
  `pagetitle` varchar(255) NOT NULL DEFAULT '',
  `longtitle` varchar(255) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL DEFAULT '',
  `thumbnail` varchar(256) NOT NULL,
  `alias` varchar(245) DEFAULT '',
  `published` int(1) NOT NULL DEFAULT 0,
  `parent` int(10) NOT NULL DEFAULT 0,
  `isfolder` int(1) NOT NULL DEFAULT 0,
  `introtext` text DEFAULT NULL,
  `content` mediumtext DEFAULT NULL,
  `menuindex` int(10) NOT NULL DEFAULT 0,
  `searchable` int(1) NOT NULL DEFAULT 1,
  `menutitle` varchar(255) NOT NULL DEFAULT '',
  `hidemenu` tinyint(1) NOT NULL DEFAULT 0,
  `showinmenu` int(11) NOT NULL,
  `show_moreinfo` int(11) NOT NULL DEFAULT 0,
  `showchildrenaslist` tinyint(4) NOT NULL DEFAULT 0,
  `canonical` varchar(256) DEFAULT NULL,
  `alias_visible` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_edit` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `site_content` (`id`, `type`, `pagetitle`, `longtitle`, `description`, `thumbnail`, `alias`, `published`, `parent`, `isfolder`, `introtext`, `content`, `menuindex`, `searchable`, `menutitle`, `hidemenu`, `showinmenu`, `show_moreinfo`, `showchildrenaslist`, `canonical`, `alias_visible`, `created_at`, `last_edit`) VALUES
(1, 'document', 'home', 'Low Fat CMS', 'FAT FREE CMS - a CMS based on the Fat Free Framework.', '', 'index', 1, 0, 0, 'introtext', '<p>It\'s pretty cool that you have installed our Low Fat CMS framework! If you can see this, things have gone right!</p>\r\n<p>You can now login the admin area with username <i>admin</i> and password <i>FatFree123</i> (make sure to change them as soon as you can).</p>\r\n<p>Hope you enjoy our framework!</p>', 1, 1, '', 1, 1, 0, 0, '', 1, '2021-04-09 08:22:28', '2021-04-14 18:16:07'),
(2, 'document', 'Low fat CMS', 'Low fat CMS', 'This CMS is built on the Fat Free Framework and Fat Free Login', '/assets/images/lowfatcms.png', 'lowfatcms', 1, 0, 0, NULL, '<p>This CMS is built on the Fat Free Framework and Fat Free Login.</p>\r\n[[children?list=true]]', 200, 0, '', 0, 0, 0, 0, '', 1, '2021-04-13 09:12:44', '2021-04-14 14:29:32'),
(3, 'reference', 'Fat Free Login', 'Fat Free Login', 'This script is built on top of the Fat Free Login script.', '/assets/images/f3.png', 'fatfreelogin', 1, 2, 0, NULL, 'https://github.com/fatfreelogin/Fat-Free-Login', 1, 0, '', 0, 0, 0, 0, '', 1, '2021-04-13 09:13:47', '2021-04-14 12:11:17'),
(4, 'document', 'Manual', 'Low Fat CMS manual', 'test', '', 'manual', 1, 0, 1, NULL, '<p>This page demonstrates how to use the Low Fat CMS:</p>\r\n[[children?list=true]]', 20, 0, '', 0, 0, 0, 0, '', 1, '2021-04-13 09:20:58', '2021-04-14 18:16:38'),
(5, 'document', 'Snippets', 'Snippets - Placeholder functions', 'There are some handy integrated placeholder functions already available in the CMS. ', '', 'snippets', 1, 4, 0, NULL, '<p>There are some integrated placeholder functions or <em>snippets</em> already available. You can check the code and add some more functions in the PageController file.</p>\r\n<h2>Metatags</h2>\r\n<p>You can create metatags and link these metatags to pages. It is then possible to show all pages linked to this metatag by putting [ [ metatag?id=metatag_id ] ] (without spaces), where metatag id is the ... well... id of the metatag...</p>\r\n<p>The thumbnail pic set for the pages will also be shown. </p>\r\n<p>Eg. metatag \"f3\" has been set with id 1. using [ [metatag?id=1] ], outputs: </p>\r\n[[metatag?id=1]]\r\n\r\n<h2>List child pages</h2>\r\n<p>To return a list of the current page\'s children, you can use [ [ children?id=page_id ] ] (without the spaces). </p>\r\n<p>It is possible to set the ouput to list or not. If <em>list=true</em> it will return an unsorted list (ul, li), otherwise it will be a bootstrap cards. Output for [ [ children?id=4 & list=true ] ]</p>\r\n [[children?id=4&list=true]]\r\n\r\n<h2>Gallery</h2>\r\n<p>Set images to return a photo gallery. Specify each picture\'s description and path. Clicking an image will open it in a modal.</p>\r\n<p>Put both description and path between quotes, seperate multiple images using a comma. It is not possible to use a comma in the description for now.</p>\r\n<p>Eg.: [ [ gallery? \"F3 logo\"=>\"/assets/images/f3.png\",<br>\r\n\"Low Fat CMS logo\"=>\"/assets/images/lowfatcms.png\",<br>\r\n\"A cute puppy in a cup\"=>\"/assets/images/pexels-pixabay-39317.jpg\" ] ] </p>\r\n[[gallery? \"F3 logo\"=>\"/assets/images/f3.png\",\r\n\"Low Fat CMS logo\"=>\"/assets/images/lowfatcms.png\",\r\n\"A cute puppy in a cup\"=>\"/assets/images/listpics/pexels-pixabay-39317.jpg\"]] \r\n\r\n<h2>List pictures</h2>\r\n<p>Similar to the Gallery snippet, but outputs all pictures in the specified directory. Clicking an image will open it in a modal.<br>\r\nEg.: [ [ listpics?/assets/images/listpics/ ] ] (don\'t use quotes!)</p>\r\n\r\n[[listpics?/assets/images/listpics/]]', 10, 1, '', 0, 0, 0, 0, '', 1, '2021-04-13 09:23:42', '2021-04-14 12:17:56'),
(6, 'document', 'Page Management', 'Page Management', 'This shows some of the built in functionality of this CMS', '', 'pages', 1, 4, 0, NULL, '<p>The Low Fat CMS allows you to log in the administrative section where you can create, edit and delete pages, metatags, chunks and news.</p>\r\n<h2>Admin login</h2>\r\n<p>Log in to the admin zone by going to the login page (which can be set in config.ini)/. By default this is yoursite/loginpage.</p>\r\n<p>Default login is admin, with password: FatFree123.</p>\r\n<p>After a succesful login you will be redirected to the admin area (url can be set in config as well).</p>\r\n<p>The menu has the following items:<ul>\r\n<li>pages</li>\r\n<li>metatags</li>\r\n<li>chunks</li>\r\n<li>news</li>\r\n<li>contacs</li>\r\n</ul>\r\n<h2>Pages</h2>\r\n<p>Click on the Pages menu item to see a list of already made pages. Click on one to edit or delete it, or click \"Add new page\" to create a new page.</p>\r\n<p>You can set the following items: </p>\r\n<ul>\r\n<li>Page name: url (don\'t use spaces)</li>\r\n<li>Page title: this is how the page will appear in the menu</li>\r\n<li>Long pagae title: the title of the page as it appears at the top of the page itself</li>\r\n<li>Page thimbnail: you can add an image thumbnail to a page which can be shown in lists</li>\r\n<li>Searchable: the cms contains a default search option (top right search icon). You can choose not to include this page in the search results</li>\r\n<li>Parent page: you can set a parent page for navigation, this page can appear in a submenu below its parent page</li>\r\n<li>Menu order: select where the page should appear in the menu, the lower this number the higher in the list</li>\r\n<li>Menu: show child pages: if this is set to show child pages in menu, the page\'s child pages can be shown below this page (by default this works a level deep only)</li>\r\n<li>Show in menu (should be self-explanatory)</li>\r\n<li>show \"more info\" button on page: if this is activated a standard button will appear on the page that can take the visitor to the contact form</li>\r\n<li>Publish: only published pages will appear</li>\r\n<li>Page type: html for a proper page, reference can be used for redirecting to another page</li>\r\n<li>Metatags: you can choose multiple metatags, it is possible to show a list containing of pages linked to these metatags (check <a href=\"/snippets.html\">place holder functions</a> </li>\r\n<li>Canonical url: this will set the canonical metatag, in case you need duplciate content, for SEO it is best to set 1 page to be the \"main\" version</li>\r\n<li>Page description: will be the description metatag, will also be shown in lists generated by the placeholders</li>\r\n<li>Content: the page content, should be the HTML code.</li>\r\n</ul>', 1, 1, '', 0, 0, 0, 0, '', 1, '2021-04-13 14:49:09', '2021-04-14 18:17:15'),
(8, 'document', 'Special pages', 'Special pages', 'Some pages are available out of the box, like contact, news and sitemap.xml', '', 'specialpages', 1, 4, 0, NULL, '<p>Some content pages are already available out of the box.</p>\r\n<h2>Contact page</h2>\r\n<p>The contact page is set in the views/page/contact_page.htm.</p>\r\n<p>It is possible to set text on the contact page by adding a new page with <em>Page name</em> \"contact\". The content will be set on the page, together with the form.</p>\r\n<p>If a contact form has been filled out by a visitor it will be mailed to the emailadress that is specified in the config file. It will also be stored in the database and is available for the administrator in the <em>Contacts</em> page.</p>\r\n<h2>News</h2>\r\n<p>The news page is available out of the box and contains a list of news items. News items can be created by the administrator in the News section. News items contain a title, intro text, full article\r\n<p>The standard layout puts the recent item on the homepage (only title and intro text) with a link to the full article.</p>\r\n<p>It is possible to set a thumbnail and a hyperlink for a news item.</p>\r\n<h2>sitemap.xml</h2>\r\n<p>A sitemap.xml file will be automatically generated for search engines.</p>\r\n<h2>Wordpress hack attempts</h2>\r\n<p>Ok, this is probably completely useless and a waste of time, but I was tired of seeing 404 errors in the logs every time bots target Wordpress installations.</p>\r\n<p>Now they are caught (up to 4 levels deep from the site root) and logged seperately by the WordpressController.</p>\r\n<p>If you don\'t need this, the routes can be disabled in the routes.ini file and the WordpressController can be safely deleted.</p>', 10, 1, '', 0, 0, 0, 0, '', 1, '2021-04-14 08:15:44', '2021-04-14 12:24:31'),
(9, 'document', 'Specials', 'Specials', 'Metatags and chunks are handy little extra\'s. Breadcrumbs are automatically generated.', '', 'specials', 1, 4, 0, NULL, '<p>Metatags and chunks are handy little addendums.</p>\r\n<h2>Metatags</h2>\r\n<p>Metatags can allow you to sort pages and link them. It is possible to output all pages containing a metatag with a <a href=\"snippets.html\">metatag placeholder</a>.</p>\r\n\r\n<h2>Chunks</h2>\r\n<p>HTML code you often reuse can be put in a chunk. If you change the chunk it will automatically be changed everywhere it is used on the site.</p>\r\n<p>To use a chunk on a page (in the content area only), use { { chunkname } } (without spaces). Eg.: for the contact chunk: </p>\r\n{{contact_us}}\r\n\r\n<h2>Breadcrumbs</h2>\r\n<p>Breadcrumbs are generated automatically for all pages 1 level deep or more. </p>\r\n\r\n<h2>More info button</h2>\r\n<p>You can set the <em>More info</em> button for a page. If this option is active, this will be shown:</p>', 30, 1, '', 0, 0, 1, 0, '', 1, '2021-04-14 08:20:31', '2021-04-14 11:48:36'),
(10, 'reference', 'Fat Free Framework', 'Fat Free Framework', 'One framework to rule them all.', '', 'fatfreeframework', 1, 2, 0, NULL, 'https://fatfreeframework.com/3.7/home', 0, 0, '', 0, 0, 0, 0, '', 1, '2021-04-14 09:25:48', '2021-04-14 11:28:59'),
(11, 'reference', 'Fat Free google group', 'Fat Free google group', 'Go here for help with F3 or this script', '', 'fatfreegroup', 1, 2, 0, NULL, 'https://groups.google.com/g/f3-framework', 0, 0, '', 0, 0, 0, 0, '', 1, '2021-04-14 09:26:26', '2021-04-14 11:29:25');

CREATE TABLE `site_htmlsnippets` (
  `id` int(10) NOT NULL,
  `name` varchar(100) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL DEFAULT 'Chunk',
  `snippet` mediumtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_edit` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `site_htmlsnippets` (`id`, `name`, `description`, `snippet`, `created_at`, `last_edit`) VALUES
(1, 'contact_us', 'Chunk', '<p><a href=\"/contact.html\">Contact us</a></p>', '2021-04-14 07:33:35', '2021-04-14 09:33:35');

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(80) NOT NULL,
  `password` varchar(256) NOT NULL,
  `hash` varchar(256) NOT NULL,
  `email` varchar(200) NOT NULL,
  `user_type` varchar(20) NOT NULL,
  `activated` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `users` (`id`, `username`, `password`, `hash`, `email`, `user_type`, `activated`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2y$10$EHQOVhzmHeKBrV45fI.jMeoRuxikzSn3.rZV9fcASPgIBzB/SVpQq', '', 'mail@mail.com', '100', 1, '2020-09-01 08:06:35', '2021-04-14 15:30:10');

ALTER TABLE `contactform`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `metatags`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `metatags_content`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `news`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `site_content`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `site_htmlsnippets`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `contactform`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `metatags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `metatags_content`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `news`
  MODIFY `id` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `site_content`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

ALTER TABLE `site_htmlsnippets`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
