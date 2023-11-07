# Artisan Commands

You can create a role or permission from a console with artisan commands.

```bash
php artisan acl:create-role writer
```

```bash
php artisan acl:create-permission 'edit articles'
```

When creating permissions and roles for specific guards you can specify the guard names as a second argument:

```bash
php artisan acl:create-role writer web
```

```bash
php artisan acl:create-permission 'edit articles' web
```

---

**Next**: [Database Seeding &raquo;](seeding.md)
