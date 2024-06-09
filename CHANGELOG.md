![K2](https://updates.getk2.org/images/k2_logo.png)
***

# Changelog

### v2.11.20240609 - June 9th, 2024
- Improved database performance for the K2 Content module. When selecting specific items, a single query will now be executed instead of distinct queries, as was done previously.
- Improve database performance for itemlist views (category, tag, user, date, search) by using a forced index on the category JOIN
- New update endpoints (now hosted on GitHub) - this will allow for faster code updates & releases from essentially a single point/codebase

---

### Previous Versions
Please visit our blog for old release announcements: https://getk2.org/blog
