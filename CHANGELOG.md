![K2](https://updates.getk2.org/images/k2_logo.png)
***

The latest K2 release is always available from: https://getk2.org/downloads/?f=K2_Rolling_Release.zip

# Changelog

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
- Added cleaning of "&nbsp;" in item titles (on render time).
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
