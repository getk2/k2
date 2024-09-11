![K2](https://updates.getk2.org/images/k2_logo.png)
***

# Changelog

### v2.11.20240911 - September 11th, 2024
- Fixed reported vulnerability in the third-party PHP library "class.upload.php" (https://github.com/getk2/k2/issues/561)

### v2.11.20240609 - June 9th, 2024
- Improved database performance for the K2 Content module. When selecting specific items, a single query will now be executed instead of distinct queries, as was done previously.
- Improved database performance for itemlist views (category, tag, user, date, search) by using a forced index on the category JOIN. The performance benefits for this change will be greatly noticed on sites with dozens to hundreds of thousands of K2 items, especially during crawling hours by search engines, SEO bots etc.
- New update endpoints (now hosted on GitHub) - this will allow for faster code updates & releases from essentially a single point/codebase

---

### Previous Versions
Please visit our blog for old release announcements: https://getk2.org/blog
