![K2](https://updates.getk2.org/images/k2_logo.png)
***

The latest K2 release is always available from: https://getk2.org/downloads/?f=K2_Rolling_Release.zip

# Changelog

### v2.27 - June 7th, 2026
This is a security release addressing issues reported via the Joomla VEL programme.

**Important context:** with the exception of the first item below, all issues require that a site administrator has explicitly granted content-creation access to users (in the backend or frontend). Also, K2 does not grant "publish" rights in the frontend by default. Sites that do not expose a frontend article submission form, or that restrict it to fully trusted users, are not meaningfully affected by items 2 through 8. Site administrators remain the authoritative trust boundary — granting author rights to untrusted users carries inherent risk regardless of the measures below. In other words, if you don't trust who contributes content in your site, all reported "issues" (after the first one in the list) below are the least of your problems...

- **Fixed unauthenticated gallery folder deletion via `sigProFolder` parameter.** The frontend `checkin` task accepted a `sigProFolder` GET parameter and deleted the corresponding folder under `/media/k2/galleries/` with no authentication check, no CSRF token, and no ownership validation. Any unauthenticated visitor could wipe gallery contents with a single GET request. Fixed by requiring an authenticated (non-guest) session in the frontend item controller's `checkin()` method. *This is the only issue in this release that does not require any user trust relationship.*
- **Fixed Remote Code Execution via gallery ZIP archive extraction.** When a ZIP gallery archive was uploaded and extracted, the post-extraction loop renamed safe image files but silently left all other file types (including `.php`) in place under the publicly served `/media/k2/galleries/` directory. Fixed by deleting any extracted file whose extension is not in the image allow-list (`avif`, `gif`, `jpg`, `jpeg`, `png`, `webp`) or `txt` files which are used for gallery captions/labels. *Requires author access (admin-granted).*
- **Fixed path traversal in the attachment `existing` POST field.** The attachment save path used `JPath::clean()` on the user-supplied `existing` value, which normalises slashes but does not reject `..` sequences. An author-tier user could copy server-executable files into the public attachments directory. Fixed by using `realpath()` to confine the resolved source path to within `JPATH_SITE` (preserving the legitimate use of elFinder to browse any site file) and by blocking server-executable and sensitive extensions (`asp`, `aspx`, `bash`, `bat`, `cgi`, `htaccess`, `htm`, `html`, `htpasswd`, `inc`, `ini`, `js`, `jsp`, `jspx`, `phar`, `php`, `php3`–`php8`, `phps`, `phpt`, `pht`, `phtm`, `phtml`, `pl`, `py`, `rb`, `sh`, `sql`, `wsdl`) from being copied. *Requires author access (admin-granted).*
- **Fixed executable file upload via attachment upload handler.** The attachment upload handler was supplemented with an extension deny-list covering server-executable extensions (`php`, `php3`–`php5`, `php7`, `php8`, `phtml`, `phar`, `pl`, `py`, `rb`, `sh`, `bash`, `cgi`, `asp`, `aspx`, `jsp`, `jspx`, `htaccess`, `htpasswd`) while leaving all other file types unrestricted. The client-supplied extension is now normalised to lowercase before use. *Requires author access (admin-granted).*
- **Minor: prevented registered users from overwriting their own moderator notes in the K2 user plugin (`plg_user_k2`).** The `onAfterStoreUser` handler's `bind()` call allowed a registered user to POST a `notes` value into their own `#__k2_users` row. The `notes` field is a K2 backend-only annotation field, never queried or rendered in any frontend template, and its only legitimate writer is K2 itself (e.g. the stopforumspam flag). The value is HTML-escaped by `JFilterOutput::objectHTMLSafe()` before rendering in the backend, so no script execution is possible regardless. Fixed by restoring the pre-`bind()` row value for `notes` after binding, so POST cannot overwrite it, while still preserving any value K2 itself sets via the Joomla user event (e.g. spammer annotations). *Requires a registered user account and is of negligible practical impact.*
- **Defence-in-depth: added output escaping to avatar `src` attributes in frontend templates.** The `K2HelperUtilities::getAvatar()` helper value was echoed into `src` attributes in two templates (the comment block in `templates/default/item.php` and `templates/default/user.php`) without `htmlspecialchars()`. In practice this column is always set to `{id}.webp` by the upload handler, and the mass-assignment issue that could have written an arbitrary value to it has also been fixed in this release. The escaping is added as defence-in-depth.
- **Minor: added missing CSRF token on backend `import()` actions.** The `import()` methods in the backend items and users controllers were the only actions in their respective controllers without a `JRequest::checkToken()` call. In practice the worst case is a logged-in administrator being tricked into triggering a bulk import of Joomla com_content articles into K2, or a bulk mapping of Joomla users into K2 user groups — both additive, fully reversible operations. Fixed anyway for consistency by adding `checkToken('get')` to both controllers and appending `JSession::getFormToken()` to the server-generated import button URLs.
- **Fixed missing CSRF token on the frontend `reportSpammer()` action.** The frontend comments controller's `reportSpammer()` method performed a `core.admin` authorisation check but had no CSRF token validation. Fixed by adding `checkToken('get')` to the controller and appending `JSession::getFormToken()` to the server-generated report link. *Requires a logged-in administrator to be tricked into clicking a crafted link.*
- **Defence-in-depth: expanded the elFinder media manager's download blocklist.** The backend media controller already blocked direct download of `.php` files via elFinder; the blocklist has been extended to cover all known server-executable and sensitive extensions: `asp`, `aspx`, `bash`, `bat`, `cgi`, `htaccess`, `htm`, `html`, `htpasswd`, `inc`, `ini`, `js`, `jsp`, `jspx`, `phar`, `php`, `php3`–`php8`, `phps`, `phpt`, `pht`, `phtm`, `phtml`, `pl`, `py`, `rb`, `sh`, `sql`, `wsdl`.
- **Defence-in-depth: protected the `image` column from POST manipulation in the K2 user plugin.** The `bind()` call in `onAfterStoreUser` could have allowed a crafted POST to overwrite the `image` column before the upload handler ran. The pre-`bind()` DB value is now snapshotted and restored immediately after binding; only the file upload handler and the `del_image` path below it may legitimately change this column.
- **Not fixed / by design: the reported Stored XSS via the `embedVideo` field.** The field intentionally uses `JREQUEST_ALLOWRAW` to preserve raw HTML, which is required for virtually all modern third-party embeds (Instagram, X/Twitter, TikTok and others) that deliver their content via `<script>` tags. Filtering out script elements would break this core functionality. Access to this field is gated by the K2 author permission, which site administrators explicitly grant. Decisions about content trust are the responsibility of the site administrator, not K2.

### v2.26 - May 31st, 2026
- Improve compatibility with YooTheme Pro on PHP 8.x.

### v2.25 - May 27th, 2026
- URL router improvements.

### v2.24 - April 22nd, 2026
- Fixed missing token errors with AJAX requests in attachments and tag in the backend forms.

### v2.23 - April 20th, 2026
- Further improvements for PHP 8.x compatibility: replaced all strftime() occurences with date()
- Added support for pagination changes introduced in Joomla 3.10.17 eLTS (or newer)
- Further security hardening: fixed stored XSS in comment username output; fixed comment mass-assignment (force INSERT, prevent POST-based overwrite); fixed guest userID spoofing in comment submission; added CSRF protection to deleteAttachment, tag/addTag and category saveMove actions; expanded media controller file type blocklist to cover additional server-executable extensions (phtml, phar, php3-7, shtml and others).

### v2.22 - March 26th, 2026
- Added sorting for the user (author) view in K2's Settings.

### v2.21 - January 9th, 2026
- Fixed variable typo in the K2 Search plugin which caused search results from the "Search" component in the frontend (not K2's direct search though) to essentially return all K2 items.

### v2.20 - December 30th, 2025
- K2 search has been extended to the "alias" field in items and categories and the "username" field for users. This is especially helpful when redirecting old URLs (e.g. after a site/CMS migration) where some cannot be matched to new URLs and redirecting immediately to the K2 search is a valid option.

### v2.19 - December 27th, 2025
- Ensure K2 works flawlessly in PHP versions 5.6, 7.4 and 8.x (realistically up to 8.4 as 8.5 is not widely used in production yet - though these fixes should cover 8.5+ as well).
- Replaced deprecated code and fixed code that could log warnings.
- Added more practical pagination count defaults in all backend lists. The pagination drop-down now features: 20, 50, 100, 200, 300, 400, 500, 1000 and "all"
- Updated elFinder to v2.1.66 (latest at the time of release)

### v2.18 - December 26th, 2025
- This version lays the groundwork for a unified search functionality in K2 and improved search in future versions, in the form of FULLTEXT based search in MySQL/MariaDB as well as integrations with external search engines like Elasticsearch, Meilisearch and so on. All search functionality has been offloaded to a new class which is used in the frontend, backend as well as the K2 search plugin (for when using Joomla's global search).

### v2.17 - December 24th, 2025
- Minor UI updates in the item form. Also corrected the link to AllVideos' documentation.

### v2.16 - October 3rd, 2025
- Frontend/Item: multi-select value rendering in extra fields should always be an array
- Backend/Items: add hidden data attributes in item links when invoked through a modal so item data can be retrieved programmatically if required
- Backend/Tags: add hidden data attributes in tag links when invoked through a modal so tag data can be retrieved programmatically if required

### v2.15 - September 15th, 2025
- Backend/Modules: Fixed the layout display of selected tags in the K2 Tools module

### v2.14 - July 18th, 2025
- Added new onK2ItemRender plugin event which is triggered anywhere the K2 item model is used (e.g. in itemlist view, the K2 Content module etc.). Such an event could be used e.g. for a custom K2 plugin that changes the URL of an item on runtime (e.g. for advertorials or sponsored content) allowing to link directly to external content.
- Added the option to sort selected items in the K2 Content module by touch, which now makes the module fully mobile/tablet-friendly.

### v2.13 - July 17th, 2025
- Moving (back) to a new simpler version naming.
- New backend layout of selected items in the K2 Content module, which also brings that section higher (to make it more usable for content editors).
- Add include/exclude filter for categories in the K2 Content & K2 Comments modules (to be expanded in other places were categories are selected).
- Visual aid for unpublished and/or trashed items wherever there is multiple item selection (e.g. in the K2 Content module).
- Added cleaning of `&nbsp;` in item titles (on render time).
- Fixed PHP warnings in the K2 item model (frontend).

### v2.12.20250620 - June 20th, 2025
- Further improvements in the performance of the items list in the K2 backend (that build on the work of the previous release).
- Updated the PHP class used to process uploads and image file conversions to its latest version ([class.upload.php](https://github.com/verot/class.upload.php) @ 10/09/2024) which adds support for WebP image handling in K2 in comparison to the older copy K2 had. Since WebP images are now ubiquitous, category and user image uploads will now be converted to WebP by default. Item images will be tackled in future releases.
- Uploaded category images maintain the category ID as their name, however we now add a timestamp at the end of the rendered file so any server or CDN caches can be bypassed when updating the image of a category.
- Default quality for image uploading is now set to 90% (for items, categories, users). As always it can be overridden in K2's settings.
- Video uploads in K2 will now use the actual file name of the uploaded media (cleaned and normalized), instead of the item's ID. That data was either way stored in the database, so it will affect new or updated uploads only (if you already run a K2 site).
- The stats module in the Joomla backend dashboard will now list the unpublished (but not trashed) items as drafts, useful for sites that moderate content. Thanks to @raramuridesign for this.

### v2.12.20250608 - June 8th, 2025
- Major performance improvements in SQL queries for itemlist views (categories, tags, users etc.). These changes make K2 further resilient to aggressive (but legitimate) bots/crawlers (like GoogleBot, BingBot, AI crawlers and so on).

### v2.12.20250604 - June 4th, 2025
- Properly encode tags when building SEF URLs
- Fix warnings when executing CLI scripts that handle K2 data that require using the K2 router
- Added onBeforeK2Save & onAfterK2Save plugin events on the tag edit form - this was long overdue and will complement an upcoming plugin to log all content actions in K2 to the Joomla User Actions Log component.

### v2.11.20250512 - May 12th, 2025
- Better handling for K2 items that would normally return a 404 when the alias does not match an actual K2 item (with K2 Advanced SEF enabled).
- Fix PHP warnings that relate to class.upload.php & the "related items" block in the item view.
- Extra white space trimming in HTML attributes
- K2 Categories: Allow overriding inherited template (theme) on a per category basis. This change in K2 allows you to bypass template inheritance ONLY, when a category already inherits the parameters of another category. Just set a different template under the "Display Settings" tab, option "Select a template" and the category will override the template from the inherited category but allow all other options to stay inherited.

### v2.11.20241016 - October 16th, 2024
- Ensure "moduleID" is a cache modifier/differentiator in itemlist views. This resolves issues where 2 or more URLs containing different "moduleID" parameters where cached as the same.

### v2.11.20240911 - September 11th, 2024
- Fixed reported vulnerability in the third-party PHP library "class.upload.php" (https://github.com/getk2/k2/issues/561)

### v2.11.20240609 - June 9th, 2024
- Improved database performance for the K2 Content module. When selecting specific items, a single query will now be executed instead of distinct queries, as was done previously.
- Improved database performance for itemlist views (category, tag, user, date, search) by using a forced index on the category JOIN. The performance benefits for this change will be greatly noticed on sites with dozens to hundreds of thousands of K2 items, especially during crawling hours by search engines, SEO bots etc.
- New update endpoints (now hosted on GitHub) - this will allow for faster code updates & releases from essentially a single point/codebase

---

### Previous Versions
Please visit our blog for old release announcements: https://getk2.org/blog
