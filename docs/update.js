/* === K2 Update Service === */

/* TO DO:
- Add cookie to hide notification
- Update styling to match the Joomla backend
*/

(function() {

    /* === Helpers === */

    /**
     * Simply compares two string version values.
     *
     * Example:
     * versionCompare('1.1', '1.2') => -1
     * versionCompare('1.1', '1.1') =>  0
     * versionCompare('1.2', '1.1') =>  1
     * versionCompare('2.23.3', '2.22.3') => 1
     *
     * Returns:
     * -1 = left is LOWER than right
     *  0 = they are equal
     *  1 = left is GREATER = right is LOWER
     *  And FALSE if one of input versions are not valid
     *
     * @function
     * @param {String} left  Version #1
     * @param {String} right Version #2
     * @return {Integer|Boolean}
     * @author Alexey Bass (albass)
     * @since 2011-07-14
     */

    var versionCompare = function(left, right) {
        if (typeof left + typeof right != 'stringstring') return false;
        var a = left.split('.'),
            b = right.split('.'),
            i = 0,
            len = Math.max(a.length, b.length);
        for (; i < len; i++) {
            if ((a[i] && !b[i] && parseInt(a[i]) > 0) || (parseInt(a[i]) > parseInt(b[i]))) {
                return 1;
            } else if ((b[i] && !a[i] && parseInt(b[i]) > 0) || (parseInt(a[i]) < parseInt(b[i]))) {
                return -1;
            }
        }
        return 0;
    };

    var kookie = {
        create: function(name, value, days) {
            var expires = "";
            if (days) {
                var date = new Date();
                date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                expires = "; expires=" + date.toGMTString();
            }
            document.cookie = name + "=" + value + expires + "; path=/";
        },
        read: function(name) {
            var nameEQ = name + "=";
            var ca = document.cookie.split(';');
            for (var i = 0; i < ca.length; i++) {
                var c = ca[i];
                while (c.charAt(0) == ' ') c = c.substring(1, c.length);
                if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
            }
            return null;
        },
        erase: function(name) {
            this.create(name, "", -1);
        }
    };

    /* === K2 App === */
    var K2_LATEST_VERSION = '2.11.20241016';
    var K2_RELEASE_NOTES = 'https://github.com/getk2/k2/blob/master/CHANGELOG.md';

    if (kookie.read('K2_hideUpdateMessage')) return;

    if (versionCompare(K2_LATEST_VERSION, K2_INSTALLED_VERSION) > 0) {
        var b = document.getElementsByTagName('body')[0];
        var notice = '<div id="k2UpdateService_Msg" style="font-size:11px;text-align:center;padding:4px 8px;background:#00b243;color:#fff;opacity:0.9;position:fixed;left:0;bottom:60px;border-right:4px solid #026b25;z-index:9999999;width:180px;">A new version of K2 (v' + K2_LATEST_VERSION + ') is now available to download.<br />Check out the <a style="color:#fff;font-weight:bold;" target="_blank" href="' + K2_RELEASE_NOTES + '">release notes</a>.<br /><br /><a id="k2UpdateService_HideMsg" href="#" style="color:#eee;font-weight:normal;font-size:10px;">[Dismiss this message for 1 week]</a></div>';
        var mountNotice = document.createElement('div');
        mountNotice.innerHTML = notice;
        b.appendChild(mountNotice);

        var hideMsgLink = document.getElementById('k2UpdateService_HideMsg');
        hideMsgLink.onclick = function() {
            kookie.create('K2_hideUpdateMessage', 'true', 7);
            document.getElementById('k2UpdateService_Msg').setAttribute('style', 'display:none;');
            return false;
        };
    }

})();
