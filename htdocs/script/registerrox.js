var Register = {
    form: false,
    elements: false,
    submits: false,

    initialize: function(form) {
        if (!$(form) || !$(form).id)
            throw 'specified form not found!';
        this.form = $(form);
        this.submits = $A(Form.getInputs(this.form, 'submit'));
        // if (!is_op) {
            // this.submits.each(function(e) {
                // e.disabled = 'true';
            // });
        // }
        var elements = $A(Form.getElements(this.form));
        this.elements = elements.findAll(function(e) {return e.id;});
        this.elements = this.elements.inject([], function(n, v) {
            v.check = Register.check.bind(Register);
            v.checked = false;
            if (v.value) {v.check(v);}
            Event.observe(v, 'keyup', function(ev) {Event.element(ev).check(Event.element(ev));});
            new Form.Element.EventObserver(v, function(ev) {
                var e = Event.element(ev);
                if (!e)
                    return false;
                e.check(e);
            });
            n.push(v);
            return n;
        });
    },

    check: function(e) {
        switch(e.name) {
        case 'username':
            if (!$F(e).match(/^[a-z][a-z0-9_\-.]{3,}$/i)) {
                Register.setError(e);
            } else {
                Register.setError(e);
                var url = http_baseuri+'signup/checkhandle/'+$F(e);
                new Ajax.Request(url,
                {
                    method:'get',
                    onSuccess: function(req) {
                        if (req.responseText == '1') {
                            Register.setClear(Register.elements.detect( function(e) { return (e.name == 'username'); } ));
                        }
                    }
                });
            }
            break;
        case 'email':
            Register.setError(e);
            var url = http_baseuri+'signup/checkemail';
            new Ajax.Request(url,
            {
                method:'get',
                parameters:Form.Element.serialize(e),
                onSuccess: function(req) {
                    if (req.responseText == '1') {
                        Register.setClear(Register.elements.detect( function(e) { return (e.name == 'email'); } ));
                    }
                }
            });
            break;
        case 'emailcheck':
            var email = this.elements.detect(function(e) {return (e.name == 'email');});
            var emailcheck = this.elements.detect(function(e) {return (e.name == 'emailcheck');});
            if ($F(email) != $F(emailcheck)) {
                Register.setError(e);
                break;
            }
            if ($F(email).length < 1) {
                Register.setError(e);
                break;
            }
            this.setClear(emailcheck);
            break;
        case 'password':
            if ($F(e).length < 6) {
                Register.setError(e);
                break;
            }
            var password = this.elements.detect(function(e) {return (e.name == 'password');});
            this.setClear(password);
            break;
        case 'passwordcheck':
            var password = this.elements.detect(function(e) {return (e.name == 'password');});
            var passwordcheck = this.elements.detect(function(e) {return (e.name == 'passwordcheck');});
            if ($F(password) != $F(passwordcheck)) {
                Register.setError(e);
                break;
            }
            if ($F(e).length < 6) {
                Register.setError(e);
                break;
            }
            this.setClear(passwordcheck);
            break;
        case 'firstname':
        case 'lastname':
        // case 'street':
        // case 'housenumber':
            if ($F(e).length < 1) {
                Register.setError(e);
                break;
            }
            this.setClear(e);
            break;
        }
    },

    checkHandle: function(ev) {
        var h = Event.element(ev);
    },

    checkPassword: function(ev) {
        var password = Event.element(ev);
        if ($F(password).length < 8) {
            Register.setError(password);
            return false;
        }
    },

    insButton: function(e, img, alt, title) {
        if (!$(e).id)
            return false;
        if (!alt)
            alt = 'button';
        var imgId = 'b'+$(e).id;
        if (!$(imgId)) {
            new Insertion.After(e, '<div id="'+imgId+'" class="statbtn"></div>');
        }
        var htmlSrc = '<img src="'+http_baseuri+img+'" alt="'+alt+'"/>';
        Element.update(imgId, htmlSrc);
        return true;
    },

    setError: function(e) {
        if (!$(e))
            return false;
        $(e).checked = false;
        this.insButton(e, 'images/icons/error.png', 'problem');
        if (!is_op) {
            var errors = this.elements.findAll(function(e) {return (!e.checked)});
            /*if (errors.length > 0) {
                this.submits.each(function(e) {e.disabled = 'true'});
            } else {
                this.submits.each(function(e) {e.disabled = 'false'});
            } */
        }
    },

    setClear: function(e) {
        if (!$(e))
            return false;
        $(e).checked = true;
        this.insButton(e, 'images/icons/tick.png', 'ok');
        var errors = this.elements.findAll(function(e) {return (!e.checked)});
        if (!is_op) { /*
            if (errors.length > 0) {
                this.submits.each(function(e) {e.disabled = 'true'});
            } else {
                this.submits.each(function(e) {e.disabled = 'false'});
            }*/
        }
    }
}
