/*
 * Patch for reCAPTCHA with Mootools 1.2 Compatibility Layer
 *
 * @author James Sleeman <james@gogo.co.nz>
 * @see https://github.com/google/recaptcha/issues/374
 *
 * Source: https://gist.github.com/sleemanj/f076ed2c0b887ab08074b55dad2fd636
 *
 */

Function.prototype._compatbind = Function.prototype.bind;

delete Function.prototype.bind;

Function.implement({
    _polybind: function(bind) {
        var self = this,
            args = (arguments.length > 1) ? Array.slice(arguments, 1) : null;

        return function() {
            if (!args && !arguments.length) return self.call(bind);
            if (args && arguments.length) return self.apply(bind, args.concat(Array.from(arguments)));
            return self.apply(bind, args || arguments);
        };
    },
    bind: function(bind, args) {
        if ((new Error()).stack.match(/recaptcha/)) {
            return this._polybind(bind, args);
        }

        return this._compatbind(bind, args);
    }
});
