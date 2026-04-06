/**
 * Client-side form validation (messages FR) + helpers for server error maps.
 */
(function (global) {
    'use strict';

    function nearestWrap(input) {
        return input.closest('[data-field-wrap], .modal-field, .modal-form-group, .form-group, .form-field') || input.parentElement;
    }

    function clearFormErrors(form) {
        if (!form) {
            return;
        }
        form.querySelectorAll('.field-error-msg').forEach(function (el) {
            el.remove();
        });
        form.querySelectorAll('.input-invalid').forEach(function (el) {
            el.classList.remove('input-invalid');
            el.style.borderColor = '';
        });
    }

    function showFieldError(input, message) {
        if (!input || !message) {
            return;
        }
        input.classList.add('input-invalid');
        input.style.borderColor = '#c0392b';
        var wrap = nearestWrap(input);
        if (!wrap) {
            return;
        }
        if (wrap.querySelector(':scope > .field-error-msg')) {
            return;
        }
        var p = document.createElement('p');
        p.className = 'field-error-msg';
        p.setAttribute('role', 'alert');
        p.textContent = message;
        p.style.cssText = 'color:#c0392b;font-size:0.8rem;margin:6px 0 0;font-weight:600;';
        wrap.appendChild(p);
    }

    function trimVal(v) {
        return (v || '').toString().trim();
    }

    function isValidEmail(s) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(s);
    }

    /**
     * @param {string} rawValue
     * @returns {string|null} message if invalid (non-empty but bad format); null if empty or valid
     */
    function emailFormatMessage(rawValue) {
        var t = trimVal(rawValue);
        if (t === '') {
            return null;
        }
        if (t.indexOf('@') === -1) {
            return 'L\'email doit contenir le symbole @ (exemple : nom@domaine.com).';
        }
        if (!isValidEmail(t)) {
            return 'Le type ou le format de l\'email n\'est pas valide : utilisez une adresse avec un domaine correct (ex. contact@societe.fr).';
        }
        return null;
    }

    function isValidTelOptional(s) {
        var t = trimVal(s);
        if (t === '') {
            return true;
        }
        return /^[0-9+\s().-]{8,}$/.test(t);
    }

    function isValidImageFile(input) {
        if (!input || !input.files || !input.files[0]) {
            return true;
        }
        var f = input.files[0];
        var ok = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        return ok.indexOf(f.type) !== -1;
    }

    function attachClearOnInput(form) {
        if (!form || form.dataset.validationClearBound === '1') {
            return;
        }
        form.dataset.validationClearBound = '1';
        form.addEventListener('input', function () {
            clearFormErrors(form);
        });
        form.addEventListener('change', function () {
            clearFormErrors(form);
        });
    }

    function validateDashboardAddForm(form) {
        clearFormErrors(form);
        var ok = true;
        var nom = form.querySelector('[name="nom"]');
        var prenom = form.querySelector('[name="prenom"]');
        var email = form.querySelector('[name="email"]');
        var password = form.querySelector('[name="password"]');
        var tel = form.querySelector('[name="telephone"]');
        var photo = form.querySelector('[name="photo_profil"]');

        if (!trimVal(nom && nom.value)) {
            showFieldError(nom, 'Le nom est requis.');
            ok = false;
        }
        if (!trimVal(prenom && prenom.value)) {
            showFieldError(prenom, 'Le prénom est requis.');
            ok = false;
        }
        if (!trimVal(email && email.value)) {
            showFieldError(email, 'L\'email est requis.');
            ok = false;
        } else {
            var addEmErr = emailFormatMessage(email.value);
            if (addEmErr) {
                showFieldError(email, addEmErr);
                ok = false;
            }
        }
        if (!password || trimVal(password.value) === '') {
            showFieldError(password, 'Le mot de passe est requis.');
            ok = false;
        } else if (password.value.length < 6) {
            showFieldError(password, 'Le mot de passe doit contenir au moins 6 caractères.');
            ok = false;
        }
        if (tel && !isValidTelOptional(tel.value)) {
            showFieldError(tel, 'Le format du téléphone n\'est pas valide.');
            ok = false;
        }
        if (photo && !isValidImageFile(photo)) {
            showFieldError(photo, 'Formats acceptés : JPG, PNG, WEBP, GIF.');
            ok = false;
        }
        return ok;
    }

    function validateDashboardEditForm(form) {
        clearFormErrors(form);
        var ok = true;
        var nom = form.querySelector('[name="nom"]');
        var prenom = form.querySelector('[name="prenom"]');
        var email = form.querySelector('[name="email"]');
        var password = form.querySelector('[name="password"]');
        var tel = form.querySelector('[name="telephone"]');
        var photo = form.querySelector('[name="photo_profil"]');

        if (!trimVal(nom && nom.value)) {
            showFieldError(nom, 'Le nom est requis.');
            ok = false;
        }
        if (!trimVal(prenom && prenom.value)) {
            showFieldError(prenom, 'Le prénom est requis.');
            ok = false;
        }
        if (!trimVal(email && email.value)) {
            showFieldError(email, 'L\'email est requis.');
            ok = false;
        } else {
            var editEmErr = emailFormatMessage(email.value);
            if (editEmErr) {
                showFieldError(email, editEmErr);
                ok = false;
            }
        }
        if (password && trimVal(password.value) !== '' && password.value.length < 6) {
            showFieldError(password, 'Le mot de passe doit contenir au moins 6 caractères.');
            ok = false;
        }
        if (tel && !isValidTelOptional(tel.value)) {
            showFieldError(tel, 'Le format du téléphone n\'est pas valide.');
            ok = false;
        }
        if (photo && !isValidImageFile(photo)) {
            showFieldError(photo, 'Formats acceptés : JPG, PNG, WEBP, GIF.');
            ok = false;
        }
        return ok;
    }

    function validateLoginForm(form) {
        clearFormErrors(form);
        var ok = true;
        var email = form.querySelector('[name="_username"]');
        var password = form.querySelector('[name="_password"]');
        if (!email || trimVal(email.value) === '') {
            showFieldError(email, 'L\'email est requis.');
            ok = false;
        } else {
            var loginEmErr = emailFormatMessage(email.value);
            if (loginEmErr) {
                showFieldError(email, loginEmErr);
                ok = false;
            }
        }
        if (!password || trimVal(password.value) === '') {
            showFieldError(password, 'Le mot de passe est requis.');
            ok = false;
        }
        return ok;
    }

    function validateRegisterForm(form) {
        clearFormErrors(form);
        var ok = true;
        var prenom = form.querySelector('[name="register_prenom"]');
        var nom = form.querySelector('[name="register_nom"]');
        var email = form.querySelector('[name="register_email"]');
        var password = form.querySelector('[name="register_password"]');
        var tel = form.querySelector('[name="register_telephone"]');
        var photo = form.querySelector('[name="register_photo"]');

        if (!trimVal(prenom && prenom.value)) {
            showFieldError(prenom, 'Le prénom est requis.');
            ok = false;
        }
        if (!trimVal(nom && nom.value)) {
            showFieldError(nom, 'Le nom est requis.');
            ok = false;
        }
        if (!trimVal(email && email.value)) {
            showFieldError(email, 'L\'email est requis.');
            ok = false;
        } else {
            var regEmErr = emailFormatMessage(email.value);
            if (regEmErr) {
                showFieldError(email, regEmErr);
                ok = false;
            }
        }
        if (!password || trimVal(password.value) === '') {
            showFieldError(password, 'Le mot de passe est requis.');
            ok = false;
        } else if (password.value.length < 8) {
            showFieldError(password, 'Le mot de passe doit contenir au moins 8 caractères.');
            ok = false;
        }
        if (tel && !isValidTelOptional(tel.value)) {
            showFieldError(tel, 'Le format du téléphone n\'est pas valide.');
            ok = false;
        }
        if (photo && photo.files && photo.files[0] && !isValidImageFile(photo)) {
            showFieldError(photo, 'Formats acceptés : JPG, PNG, WEBP, GIF.');
            ok = false;
        }
        return ok;
    }

    function validateStandaloneUserEditForm(form) {
        return validateDashboardEditForm(form);
    }

    /**
     * Symfony UsersType (form name "users") → ids users_nom, users_password_first, etc.
     */
    function validateSymfonyUsersForm(form) {
        clearFormErrors(form);
        var ok = true;

        function q(id) {
            return form.querySelector('#' + id);
        }

        var nom = q('users_nom');
        var prenom = q('users_prenom');
        var email = q('users_email');
        var tel = q('users_telephone');
        var photo = q('users_photo_profil');
        var passFirst =
            q('users_password_first') || form.querySelector('input[name="users[password][first]"]');
        var passSecond =
            q('users_password_second') || form.querySelector('input[name="users[password][second]"]');

        if (nom && trimVal(nom.value) === '') {
            showFieldError(nom, 'Le nom est requis.');
            ok = false;
        }
        if (prenom && trimVal(prenom.value) === '') {
            showFieldError(prenom, 'Le prénom est requis.');
            ok = false;
        }
        if (email && trimVal(email.value) === '') {
            showFieldError(email, 'L\'email est requis.');
            ok = false;
        } else if (email) {
            var symEmErr = emailFormatMessage(email.value);
            if (symEmErr) {
                showFieldError(email, symEmErr);
                ok = false;
            }
        }
        if (tel && trimVal(tel.value) === '') {
            showFieldError(tel, 'Le téléphone est requis.');
            ok = false;
        } else if (tel && !isValidTelOptional(tel.value)) {
            showFieldError(tel, 'Le format du téléphone n\'est pas valide.');
            ok = false;
        }
        if (passFirst && passSecond) {
            var p1 = trimVal(passFirst.value);
            var p2 = trimVal(passSecond.value);
            var needPassword = passFirst.hasAttribute('required');
            if (needPassword) {
                if (p1 === '') {
                    showFieldError(passFirst, 'Le mot de passe est requis.');
                    ok = false;
                } else if (p1.length < 6) {
                    showFieldError(passFirst, 'Le mot de passe doit contenir au moins 6 caractères.');
                    ok = false;
                } else if (p1 !== p2) {
                    showFieldError(passSecond, 'Les mots de passe ne correspondent pas.');
                    ok = false;
                }
            } else {
                if (p1 !== '' && p1.length < 6) {
                    showFieldError(passFirst, 'Le mot de passe doit contenir au moins 6 caractères.');
                    ok = false;
                } else if (p1 !== '' && p1 !== p2) {
                    showFieldError(passSecond, 'Les mots de passe ne correspondent pas.');
                    ok = false;
                }
            }
        }
        if (photo && photo.files && photo.files[0] && !isValidImageFile(photo)) {
            showFieldError(photo, 'Formats acceptés : JPG, PNG, WEBP, GIF.');
            ok = false;
        }

        return ok;
    }

    function applyServerErrors(form, errors) {
        if (!form || !errors) {
            return;
        }
        clearFormErrors(form);
        Object.keys(errors).forEach(function (name) {
            var input = form.querySelector('[name="' + name + '"]');
            if (!input && name === 'photo_profil') {
                input = form.querySelector('[name="photo_profil"]');
            }
            if (!input && name.indexOf('users') === 0) {
                input = form.querySelector('[name="' + name + '"]');
            }
            if (!input) {
                input = form.querySelector('#users_' + name.replace(/\[|\]/g, '_'));
            }
            if (input) {
                showFieldError(input, errors[name]);
            }
        });
    }

    global.AppFormValidation = {
        clearFormErrors: clearFormErrors,
        showFieldError: showFieldError,
        attachClearOnInput: attachClearOnInput,
        validateDashboardAddForm: validateDashboardAddForm,
        validateDashboardEditForm: validateDashboardEditForm,
        validateLoginForm: validateLoginForm,
        validateRegisterForm: validateRegisterForm,
        validateStandaloneUserEditForm: validateStandaloneUserEditForm,
        validateSymfonyUsersForm: validateSymfonyUsersForm,
        applyServerErrors: applyServerErrors,
    };
})(typeof window !== 'undefined' ? window : this);
