# Permissions

The entire `/admin` section of the site is restricted to users with `ROLE_ADMIN`,
which maps to having the 'Admin' right. The user's roles are loaded upon
login via [Rox\Member\Model\Member](module/Member/src/Model/Member.php)::getRoles()

This needs to be scaled back to use the correct rights in certain
controllers of the admin section. The `^/admin` line should be removed from
`access_control.global.yml` and permission checks added to controller actions.

To check for the admin role:
```php
if (!$this->getAuthChecker()->isGranted('ROLE_ADMIN')) {
    throw new AccessDeniedHttpException();
}
```

To check for a right:
```php
if (!$this->getMember()->getRightLevel('Logs')) {
    throw new AccessDeniedHttpException();
}
```

To check for a right with certain scope:
```php
if (!$this->getMember()->getRightLevel('Words', 'en')) {
    throw new AccessDeniedHttpException();
}
```
