# Permissions

Access to certain areas of the website is restricted. Access is granted by a BeWelcome admin
through the rights administration tool. Old rights are mapped to Symfony roles in [src\Entity\Member](src/Entity/Member.php).

To check for any of the roles:
```php
if (!$this->isGranted(Member::ROLE_ADMIN)) {
    throw new AccessDeniedHttpException();
}
```

While the old concept had scopes and levels those are not used in the new code at the moment (except for translations).
