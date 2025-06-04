![K2](https://updates.getk2.org/images/k2_logo.png)
***

# Changelog

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
